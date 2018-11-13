<?php

namespace Uspdev\Popper;

class Nfes
{


    /**
     * consulta_sefaz
     *
     * @param  string $xml NFE no formato XML
     *
     * @return Array Array contendo o retorno do webservice de consulta à sefaz
     */
    public function consulta_sefaz($xml)
    {
        // configuração do NFE-WS está em $this->>nfews
        // nao iremos verificar o $xml

        //echo $this->nfews['srv'] . 'xml';
        $curl = curl_init($this->nfews['srv'] . 'xml');
        $content = 'xml=' . curl_escape($curl, $xml);

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->nfews['usr'] . ":" . $this->nfews['pwd']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded; charset=UTF-8"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        // o status serve para ajudar a debugar erros
        //$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response = json_decode($json_response, true);
        return $response;
    }

    /*

     */
    /**
     * verificaNfe Verifica se um anexo é nfe ou não
     * 
     * @param  object $anexo objeto de PhpMimeMailParser\Parser
     *
     * @return mixed Se for nfe retorna os dados de consulta à sefaz, 
     *               se não retorna false
     */
    public function verificaNfe($anexo)
    {
        $arq_xml = $anexo->getContent();
        //echo $arq_xml;
        $sefaz = Nfes::consulta_sefaz($arq_xml);
        //print_r($sefaz);
        if ($sefaz['status'] == 'ok') { // sim é uma nfe
            $ret['sefaz'] = $sefaz;
            $ret['xml'] = $arq_xml;
            return $ret;
        }
        return false;
    }

    /**
     * salvaNfe Salva um xml de nfe no banco de dados
     *
     * @param  array $sefaz Retorno da colsulta à sefaz
     * @param  object $email Objeto do email associado à nfe
     *
     * @return string retorna 'existente' se já existe no BD e atualizou
     *                retorna 'novo' se adicionado ao bd
     */
    public function salvaNfe($sefaz, $email)
    {
        $prot = $sefaz['sefaz'];
        $chave = $prot['chave'];

        $ret = 'existente';
        // se a nfe já existir vamos atualizar os dados pois
        // pode ser que o xml anterior estava com problemas.
        //  Se o novo estiver ruim então lascou-se.
        if (!$nfe = R::findOne('nfe', 'chave = ?', [ $chave ])) {
            // ou vamos criar uma nova
            // o find_or_create deu algum problema
            $nfe = R::dispense('nfe');
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

        R::store($nfe);
        return $ret;
    }
}
