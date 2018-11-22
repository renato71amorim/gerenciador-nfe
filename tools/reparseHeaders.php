<?php
require '../app/app.php';

use \Uspdev\Popper\Popper;
$popper = new Popper($cfg);

// foi usado para corrigir unidades que vieram como null por erro no código

//$emails = $popper->getAll();
use \RedBeanPHP\R as R;
$emails = R::exec("SELECT id, unidade, raw_header FROM nfeemail WHERE unidade='NONE' ORDER BY id DESC;");
echo 'inicio';
foreach ($emails as $email) {
    $header = imap_rfc822_parse_headers($email->raw_header);
    $unidade = $popper->getUnidade($header);
    if ($unidade != $email->unidade) {
        $email->unidade = $unidade;
        $popper->storeEmail($email);
        $ret = $popper->parseEmail($email);
        echo 'Parser: '. json_encode($ret).'<br>';
        echo 'Unidade alterada email_id '.$email->id.'<br>';
    }
}

echo 'fim';
unset($emails);