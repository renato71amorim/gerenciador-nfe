<?php
// para ser rodado na linha de comando
if ('cli' != PHP_SAPI) {
    die('Não está na linha de comando');
}

require '../app/app.php';

// foi usado para corrigir unidades que vieram como null por erro no código

$emails = $popper->findCollectionByYear();

echo 'inicio' . PHP_EOL;
while ($email = $emails->next()) {
    $header = imap_rfc822_parse_headers($email->raw_header);
    $unidade = $popper->getUnidade($header);

    if ($unidade != $email->unidade) {
        echo 'id: ' . $email->id . ';';
        $email->unidade = $unidade;
        $popper->store($email);
        $ret = $popper->parseEmail($email);
        echo 'Parser: ' . json_encode($ret) . PHP_EOL;
        echo 'Unidade alterada email_id ' . $email->id . PHP_EOL;
    }
}

echo 'fim' . PHP_EOL;
