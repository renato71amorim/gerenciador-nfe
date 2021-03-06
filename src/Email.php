<?php
namespace Uspdev\GerenciadorNfe;

use \PhpMimeMailParser\Parser;
use \RedBeanPHP\R as R;

class Email
{
    //use Database;

    public $debug = false; //se true gera log interno

    // instância da nfe
    protected $nfe;
    protected $parser;
    const tbl = 'email';
    public $unidade = 'NONE';
    public $ano = '0';

    public function __construct($cfg)
    {
        $this->imap = $cfg['imap'];
        //$this->nfews = $cfg['nfews'];
        $this->logfile = $cfg['logfile'];
    }

    public function setNfe(Nfe $nfe)
    {
        $this->nfe = $nfe;
    }

    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * getUnidadeFromHeader
     *
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
    public function getUnidadeFromHeader($header)
    {
        // verifica pelo from
        $from = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        if ($unidade = Unidade::getUnidadeByEmailNfe($from)) {
            return $unidade;
        }

        // depois verifica o subject
        if (!empty($header->subject)) {
            //$subject = iconv_mime_decode($header->subject, 0, 'UTF-8');
            $subject = mb_decode_mimeheader($header->subject);
            preg_match('/\b[^\s]+@[^\s]+/', $subject, $email_candidate);
            if ($email = filter_var($email_candidate, FILTER_VALIDATE_EMAIL)) {
                if ($unidade = Unidade::getUnidadeByEmailNfe($email)) {
                    return $unidade;
                }
            }
        }

        // pelo To
        if (!empty($header->to)) {
            foreach ($header->to as $src) {
                $to = strtolower($src->mailbox . '@' . $src->host);
                if ($unidade = Unidade::getUnidadeByEmailNfe($to)) {
                    return $unidade;
                }
            }
        }

        // pelo CC
        if (!empty($header->cc)) {
            foreach ($header->cc as $src) {
                $cc = strtolower($src->mailbox . '@' . $src->host);
                if ($unidade = Unidade::getUnidadeByEmailNfe($cc)) {
                    return $unidade;
                }
            }
        }

        return 'NONE';
    }

    public function getUnidadeFromHeader2($raw_header)
    {
        $unidades = Unidade::getUnidades();
        foreach ($unidades as $unidade) {
            if (stripos($raw_header, ' ' . $unidade['email_nfe']) !== false) {
                // o espaço em branco ajuda a delimitar o email
                return $unidade['sigla'];
            }
        }
        return 'NONE';
    }

    /**
     * popNow
     *
     * Baixa todos os emails do servidor pop e guarda no banco de dados
     *
     * @return array Ids dos emails baixados
     */
    public function popNow()
    {
        // emails baixados novos ficam com parsed == false
        $inbox = imap_open($this->imap['host'], $this->imap['usr'], $this->imap['pwd']) or die('Cannot connect to Gmail: ' . imap_last_error());

        $ids = [];
        $count = imap_num_msg($inbox);
        for ($i = 1; $i <= $count; $i++) {
            //$header = imap_headerinfo($inbox, $i);
            $raw_header = imap_fetchheader($inbox, $i, FT_PREFETCHTEXT);
            $header = imap_rfc822_parse_headers($raw_header);
            //print_r($header);

            // se subject estiver em branco ele vem indefinido. Vamos popular com vazio.
            // no rfc822 mesmo em branco ele retorna o array
            //$header->subject = empty($header->subject) ? '' : $header->subject;

            $email = Database::dispense(self::tbl);

            // de qual unidade veio esse email? A classificação
            // é com base no email de recebimento de nfe cadastrado na unidade
            $email['unidade'] = $this->getUnidadeFromHeader2($raw_header);

            $email['data'] = $this->ajustaDataEmail($header->date);
            $email['ano'] = date('Y', strtotime($email['data']));
            $email['assunto'] = iconv_mime_decode($header->subject, 0, 'UTF-8');
            $email['remet'] = $header->from[0]->mailbox . '@' . $header->from[0]->host;
            $email['raw_header'] = $raw_header;
            $email['raw_body'] = utf8_encode(imap_body($inbox, $i));
            //$email['raw'] = imap_fetchheader($inbox, $i, FT_PREFETCHTEXT). imap_body($inbox, $i);

            $status['fetchdate'] = date("Y-m-d H:i:s");
            $status['parsed'] = false;
            $status['parsedate'] = '';
            $status['anexos'] = [];
            $email['status'] = json_encode($status);

            $ids[$i] = Database::store($email);
        }
        if ($this->debug) {
            $this->log('Novos emails: ' . $count);
        }

        imap_close($inbox);
        return $ids;
    }

    /**
     * parseNew
     * Recupera emails do BD com status 'not parsed' e parseia
     *
     * @return array Contem dados dos emails parseados
     */
    public function parseNew()
    {
        $ret = [];
        $countNfeExist = 0;
        $countNfeNovo = 0;
        $countAnexo = 0;

        $emails = $this->getNotParsed();
        $countEmail = count($emails);
        foreach ($emails as $email) {
            // aqui que parseia de fato
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
        if (empty($this->nfe)) {
            die('Nfe não inicializado em parseEmail');
        }

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
            if (strtolower(substr($status['anexos'][$i]['filename'], -3)) == 'xml') {
                $xml = $anexo->getContent();
                //echo $xml;
                if ($sefaz = $this->nfe->verificaNfe($xml)) { // se for nfe
                    $nfe_stat = $this->nfe->store($sefaz, $email); // salva no bd
                    $status['anexos'][$i]['parser'] = 'nfe';
                    $status['anexos'][$i]['nfe_id'] = $nfe_stat['id'];
                    if ($nfe_stat['status'] == 'novo') {
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
        Database::store($email);

        return [$countAnexo, $countNfeExist, $countNfeNovo];
    }

    public function load($id)
    {
        return Database::load('email', $id);
    }

    /**
     * findCollectionByYear
     *
     * @param  mixed $year
     *
     * se não for passado $year então será atribuído o ano corrente
     * Retorna todos os emails do ano correspondente
     *
     * @return Collection de emails do ano correspondente
     */
    public function findCollectionByYear($year = '')
    {
        $year = empty($year) ? date('Y') : $year;
        return Database::findCollection('email', '(ano LIKE ?) and (unidade LIKE ?) ORDER BY id DESC ', [$year, $this->unidade]);
    }

    public function findCollection()
    {
        return Database::findCollection('email', '(ano LIKE ?) and (unidade LIKE ?) ORDER BY id DESC ', [$this->ano, $this->unidade]);
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
        return Database::find('email', ' status like ? order by id limit ?', ['%"parsed":false%', $limit]);
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
        //$parser = new \PhpMimeMailParser\Parser();
        $this->parser->setText($email->raw_header . utf8_decode($email->raw_body));

        // Pass in a writeable path to save attachments
        $anexo_dir = sys_get_temp_dir() . '/'; // Be sure to include the trailing slash
        $include_inline = true; // Optional argument to include inline attachments (default: true)
        $this->parser->saveattachments($anexo_dir, [$include_inline]);

        // Get an array of anexoment items from $Parser
        $attachments = $this->parser->getattachments([$include_inline]);

        return $attachments;
    }

    // vamos separar aqui

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
        return Database::uso('email');
    }

    public static function anos()
    {
        return Database::distinct('email', 'ano');
    }

    public static function unidades()
    {
        return Database::distinct('email', 'unidade');
    }

    /**
     * ajustaDataEmail
     *
     * Alguns emails vem com data no formato "Wed, 3 Oct 2018 17:55:22 -0003 (-3)"
     * o "(-3)" a mais não permite parsear corretamente.
     * Esta função verifica a presença desse adendo e elimina
     *
     * Alguns emails vem no formato "Wed, 21 Nov 2018 16:10:54 UT"
     * Elimina o "UT" final
     *
     * @param  string $date Data que veio do cabeçalho do email
     *
     * @return string Data ajustada
     */
    public static function ajustaDataEmail($date)
    {
        if (strpos($date, '(') > 0) { // corrige o (-3) no final
            return substr($date, 0, strpos($date, '('));

        } else if (strpos($date, 'UT') > 0) { // corrige UT no final.
            return substr($date, 0, strpos($date, 'UT'));
        }
        // se estiver tudo bem não faz nada
        return $date;
    }
}
