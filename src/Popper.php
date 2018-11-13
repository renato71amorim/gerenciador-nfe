<?php
namespace Uspdev\Popper;

use \RedBeanPHP\R as R;

class Popper
{
    public $debug = false; //se true gera log interno

    public function __construct($cfg)
    {
        $this->imap = $cfg['imap'];
        $this->nfews = $cfg['nfews'];
        $this->logfile = $cfg['logfile'];
    }

    /**
     * Retorna a unidade USP associada ao email de nfe
     *
     * Regra:
     *
     * * 1o. pelo endereço do remetente - from
     * * 2o. pelo assunto com email do rementente
     * * 3o. pelo destinatário - to
     * * 4o. pelo cc (com cópia)
     *
     * @param string $header Cabeçalho do email (raw)
     * @return string Sigla da unidade correspondente
     */
    public function getUnidade($header)
    {
        // verifica pelo from
        $from = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        if ($unidade = Unidade::getUnidadeByEmailNfe($from)) {
            return $unidade;
        }

        // depois verifica o subject
        $subject = iconv_mime_decode($header->subject, 0, 'UTF-8');
        preg_match('/\b[^\s]+@[^\s]+/', $subject, $email_candidate);
        if ($email = filter_var($email_candidate,FILTER_VALIDATE_EMAIL)){
            if ($unidade = Unidade::getUnidadeByEmailNfe($email)) {
                return $unidade;
            }
        }

        // pelo To
        foreach ($header->to as $src) {
            $to = strtolower($src->mailbox . '@' . $src->host);
            if ($unidade = Unidade::getUnidadeByEmailNfe($to)) {
                return $unidade;
            }
        }
        
        // pelo CC
        foreach ($header->cc as $src) {
            $cc = strtolower($src->mailbox . '@' . $src->host);
            if ($unidade = Unidade::getUnidadeByEmailNfe($cc)) {
                return $unidade;
            }
        }

        return 'NONE';
    }

    /**
     * downloadEmails
     *
     * Baixa todos os emails do servidor pop e guarda no banco de dados
     *
     * @return array Ids dos emails baixados
     */
    public function downloadEmails()
    {
        // emails baixados novos ficam com parsed == false
        $inbox = imap_open($this->imap['host'], $this->imap['usr'], $this->imap['pwd']) or die('Cannot connect to Gmail: ' . imap_last_error());

        $ids = [];
        $count = imap_num_msg($inbox);
        for ($i = 1; $i <= $count; $i++) {
            //$header = imap_headerinfo($inbox, $i);
            $header = imap_rfc822_parse_headers(imap_fetchheader($inbox, $i, FT_PREFETCHTEXT));
            //print_r($header);

            // se subject estiver em branco ele vem indefinido. Vamos popular com vazio.
            // no rfc822 mesmo em branco ele retorna o array
            //$header->subject = empty($header->subject) ? '' : $header->subject;

            $email = R::dispense('nfeemail');

            // de qual unidade veio esse email? A classificação
            // é com base no email de recebimento de nfe cadastrado na unidade
            $email['unidade'] = $this->getUnidade($header);

            $email['data'] = $this->ajustaDataEmail($header->date);
            $email['ano'] = date('Y', strtotime($email['data']));
            $email['assunto'] = iconv_mime_decode($header->subject, 0, 'UTF-8');
            $email['remet'] = $header->from[0]->mailbox . '@' . $header->from[0]->host;
            $email['raw_header'] = imap_fetchheader($inbox, $i, FT_PREFETCHTEXT);
            $email['raw_body'] = utf8_encode(imap_body($inbox, $i));
            //$email['raw'] = imap_fetchheader($inbox, $i, FT_PREFETCHTEXT). imap_body($inbox, $i);

            $status['fetchdate'] = date("Y-m-d H:i:s");
            $status['parsed'] = false;
            $status['parsedate'] = '';
            $status['anexos'] = [];
            $email['status'] = json_encode($status);

            $ids[$i] = R::store($email);
        }
        if ($this->debug) {
            $this->log('Novos emails: ' . $count);
        }

        imap_close($inbox);
        return $ids;
    }

    /**
     * parseNewEmails
     * Recupera emails do BD com status 'not parsed' e parseia
     *
     * @return array Contem dados dos emails parseados
     */
    public function parseNewEmails()
    {
        $ret = [];
        $countNfeExist = 0;
        $countNfeNovo = 0;
        $countAnexo = 0;

        $emails = $this->getNotParsed();
        $countEmail = count($emails);
        foreach ($emails as $email) {
            list($countAnexo1, $countNfeExist1, $countNfeNovo1) = $this->parseEmail($email);
            $countAnexo += $countAnexo1;
            $countNfeExist += $countNfeExist1;
            $countNfeNovo += $countNfeNovo1;
        }
        $ret['emails analisados'] = $countEmail;
        $ret['anexos encontrados'] = $countAnexo;
        $ret['nfe novas'] = $countNfeNovo;
        $ret['nfe existentes'] = $countNfeExist;

        if ($this->debug) {
            $this->log($ret);
        }

        return $ret;
    }

    /**
     * parseEmail
     * Interpreta o email pegando os anexos e verificando se é NFE.
     *
     * @param object $email Obejto do BD do email
     *
     * @return array Contagem de anexos e nfes encontrados
     */
    public function parseEmail($email)
    {
        $countNfeExist = 0;
        $countNfeNovo = 0;

        $anexos = $this->getAnexos($email);
        $countAnexo = count($anexos);

        $status = json_decode($email->status, true);

        for ($i = 0; $i < count($anexos); $i++) {
            $anexo = $anexos[$i];
            $anexo_dir = sys_get_temp_dir() . '/';
            $status['anexos'][$i]['filename'] = $anexo->getFilename();
            $status['anexos'][$i]['filesize'] = filesize($anexo_dir . $anexo->getFilename());
            $status['anexos'][$i]['filetype'] = $anexo->getContentType();
            $status['anexos'][$i]['parser'] = 'outros';

            // se for um xml
            if (substr($status['anexos'][$i]['filename'], -3) == 'xml') {
                //echo 'nfe ' . $filename;
                if ($sefaz = $this->verificaNfe($anexo)) { // se for nfe
                    $salvo = $this->salvaNFE($sefaz, $email); // salva no bd
                    $status['anexos'][$i]['parser'] = 'nfe';
                    if ($salvo == 'novo') {
                        $countNfeNovo++;
                    } else {
                        $countNfeExist++;
                    }
                }
            }

            if (substr($status['anexos'][$i]['filename'], -3) == 'pdf') {
                // se for pdf para nfese tem de parsear aqui
            }
            //$anexos[$i]['raw'] = strlen($attachments[$i]->getContent()); // (the whole MIME part of the anexoment)
        }

        $status['parsed'] = true;
        $status['parsedate'] = date("Y-m-d H:i:s");
        $email->status = json_encode($status);
        R::store($email);

        return [$countAnexo, $countNfeExist, $countNfeNovo];
    }

    public function getEmail($id)
    {
        return R::load('nfeemail', $id);
    }

    public function storeEmail($email)
    {
        return R::store($email);
    }

    /**
     * getAll
     * Retorna todos os emails limitados a $limit. Não retorna o raw_body pois irá exaurir a memória.
     *
     * @param  int $offset
     * @param  int $limit
     *
     * @return array Array do BD com os emails
     */
    public function getAll($offset = 0, $limit = 1000)
    {
        //return R::findAll('nfeemail', ' LIMIT ' . $limit);
        //return R::find('nfeemail','ORDER BY id DESC LIMIT '.$limit.' OFFSET '.$offset.' ;');
        return R::getAll('SELECT id, unidade, data, ano, assunto, remet, status, raw_header FROM nfeemail  ORDER BY id DESC LIMIT ' . $limit . ' OFFSET ' . $offset . ' ;');

        //[':limit' => $limit, ':offset' => $offset]);
    }

    /**
     * getNotParsed
     * Busca no banco de dados os emails não parseados. No processo primeiro os emails são baixados e depois parseados.
     *
     * @param  mixed $limit Limita o número de emails en caso de m..
     *
     * @return object Objeto do banco de dados contendo a lista de emails
     */
    public function getNotParsed($limit = 500)
    {
        return R::find('nfeemail', ' status like ? order by id limit ?', ['%"parsed":false%', $limit]);
    }

    /**
     * log
     * Gera log dos dados informados. O local do arquivo de log é especificado no arquivo de configuração.

     * @param  mixed $msg Mensagem a ser guardada no log. Pode ser string ou array de strings
     *
     * @return void
     */
    public function log($msg)
    {
        $log = date("Y-m-d H:i:s") . '; ';
        if (is_array($msg)) {
            foreach ($msg as $k => $v) {
                $log .= $k . ': ' . $v . ', ';
            }
            $log = substr($log, 0, -2);
        } else {
            $log .= $msg;
        }
        file_put_contents($this->logfile, $log . PHP_EOL, FILE_APPEND);
    }

    /**
     * getAnexos
     * Dado um objeto $email, este método extrai os anexos e retorna um array com os dados.
     *
     * @param object $email É um objeto do banco de dados
     *
     * @return array Array contendo os dados dos anexos encontrados. Se não houver anexos o array será vazio.
     */
    public function getAnexos($email)
    {
        $parser = new \PhpMimeMailParser\Parser();
        $parser->setText($email->raw_header . utf8_decode($email->raw_body));

        // Pass in a writeable path to save attachments
        $anexo_dir = sys_get_temp_dir() . '/'; // Be sure to include the trailing slash
        $include_inline = true; // Optional argument to include inline attachments (default: true)
        $parser->saveattachments($anexo_dir, [$include_inline]);

        // Get an array of anexoment items from $Parser
        $attachments = $parser->getattachments([$include_inline]);

        return $attachments;
    }

    // vamos separar aqui

    /**
     * Verifica se um arquivo é nfe ou não
     * pode vir qualquer arquivo mas anteriormente foi filtrado para vir somente com extensão xml
     * @param object $anexo É um objeto de PhpMimeMailParser\Parser;
     * @return array Retorna os dados da sefaz ou falso se não for nfe
     */
    public function verificaNfe($anexo)
    {
        $arq_xml = $anexo->getContent();
        //echo $arq_xml;
        $sefaz = $this->consulta_sefaz($arq_xml);
        //print_r($sefaz);
        if ($sefaz['status'] == 'ok') { // sim é uma nfe
            $ret['sefaz'] = $sefaz;
            $ret['xml'] = $arq_xml;
            return $ret;
        }
        return false;
    }

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

    public function salvaNfe($sefaz, $email)
    {
        $prot = $sefaz['sefaz'];
        $chave = $prot['chave'];

        $ret = 'existente';
        // se a nfe já existir vamos atualizar os dados pois
        // pode ser que o xml anterior estava com problemas.
        //  Se o novo estiver ruim então lascou-se.
        if (!$nfe = R::findOne('nfe', 'chave = ?', [$chave])) {
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

    /**
     * # usoDB
     *
     * Mostra o uso em MB de uma tabela do banco de dados.
     *
     * @param string $table Nome da tabela a ser verificada. Default = nfeemail
     * @return string Uso da tabela em MB
     */
    public static function usoDB($table = 'nfeemail')
    {
        $q = 'SELECT table_name AS `Table`,
              round(((data_length + index_length) / 1024 / 1024), 2) `mb`
              FROM information_schema.TABLES
              WHERE table_schema = "delos-php"
              AND table_name = "' . $table . '"';

        $r = R::getAll($q);
        return $r[0]['mb'];
    }

    /**
     * ajustaDataEmail
     *
     * Alguns emails vem com data no formato "Wed, 3 Oct 2018 17:55:22 -0003 (-3)"
     * o "(-3)" a mais não permite parsear corretamente.
     * Esta função verifica a presença desse adendo e elimina
     *
     * @param  string $date Data que veio do cabeçalho do email
     *
     * @return string Data ajustada
     */
    public static function ajustaDataEmail($date)
    {
        if (strpos($date, '(') > 0) {
            return substr($date, 0, strpos($date, '('));
        } else {
            return $date;
        }
    }
}
