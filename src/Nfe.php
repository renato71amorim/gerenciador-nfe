<?php

namespace Uspdev\GerenciadorNfe;

use \RedBeanPHP\R as R;
use \Uspdev\Nfe\NfeWsConsumer;

class Nfe
{

    protected $sefaz;

    public function __construct($cfg)
    {
        $this->nfews = $cfg['nfews'];
        $this->logfile = $cfg['logfile'];
    }

    public function setSefaz(NfeWsConsumer $sefaz)
    {
        $this->sefaz = $sefaz;
    }

    /**
     * verificaNfe Verifica se um anexo é nfe ou não
     *
     * @param  string $xml Contém o candidato ao xml nfe
     *
     * @return mixed Se for nfe retorna os dados de consulta à sefaz,
     *               se não retorna false
     */
    public function isNfe($xml)
    {
        $sefaz = $this->sefaz->consultaXML($xml);

        if ($sefaz['status'] == 'ok') { // sim é uma nfe
            $ret['sefaz'] = $sefaz;
            $ret['xml'] = $arq_xml;
            return $ret;
        }
        return false;
    }

    /**
     * store Salva um xml de nfe no banco de dados
     *
     * @param  array $sefaz Retorno da colsulta à sefaz
     * @param  object $email Objeto do email associado à nfe
     *
     * @return string retorna 'existente' se já existe no BD e atualizou
     *                retorna 'novo' se adicionado ao bd
     */
    public function store($sefaz, $email)
    {
        $prot = $sefaz['sefaz'];
        $chave = $prot['chave'];

        $ret = 'existente';
        // se a nfe já existir vamos atualizar os dados pois
        // pode ser que o xml anterior estava com problemas.
        //  Se o novo estiver ruim então lascou-se.
        if (!$nfe = Database::findOne('nfe', 'chave = ?', [$chave])) {
            // ou vamos criar uma nova
            // o find_or_create deu algum problema
            $nfe = Database::dispense('nfe');
            $ret = 'novo';
        }

        $nfe->chave = $chave;
        $nfe->xml = $sefaz['xml'];

        // todo: quando o protocolo vier com rejeicao não deve guardar. O que fazer?
        $nfe->prot = $prot['prot']['raw']; // vamos guardar o protocolo completo
        $nfe->emit = json_encode($prot['nfe']['emit']);
        $nfe->dest = json_encode($prot['nfe']['dest']);
        $nfe->ide = json_encode($prot['nfe']['ide']);
        $nfe->sefaz = json_encode($prot['sefaz']);
        $nfe->infadic = $prot['nfe']['infadic'];

        $nfe->unidade = $email['unidade'];
        $nfe->ano = $email['ano'];
        //$nfe->grupo = ''; // o grupo será preservado se existir
        $nfe->email = $email; // nfe pertence à email
        $nfe->removed = ''; // se for removido vai voltar

        Database::store($nfe);
        return $ret;
    }

    /**
     * Verifica se um arquivo é nfe ou não
     * pode vir qualquer arquivo mas anteriormente foi filtrado para vir somente com extensão xml
     * @param object $anexo É um objeto de PhpMimeMailParser\Parser;
     * @return array Retorna os dados da sefaz ou falso se não for nfe
     */
    public function verificaNfe($xml)
    {
        //echo $xml;
        $sefaz = $this->sefaz->consultaXML($xml);
        //print_r($sefaz);
        if ($sefaz['status'] == 'ok') { // sim é uma nfe
            $ret['sefaz'] = $sefaz;
            $ret['xml'] = $xml;
            return $ret;
        }
        return false;
    }

    /**
     * # usoDB
     *
     * Mostra o uso em MB de uma tabela do banco de dados.
     *
     * @param string $table Nome da tabela a ser verificada. Default = email
     * @return string Uso da tabela em MB
     */
    public static function usoDB()
    {
        return Database::uso('nfe');
    }

    public static function anos()
    {
        return Database::anos('nfe');
    }
}
