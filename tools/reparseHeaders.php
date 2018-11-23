<?php
// para ser rodado na linha de comando
if ('cli' != PHP_SAPI) {
    die('Não está na linha de comando');
}

require '../app/app.php';

// foi usado para corrigir unidades que vieram como null por erro no código

// aqui não usamos o popper paraque o redbean possa não trazer o raw_body
// que é grande e estoura a memória
//$emails = R::exec("SELECT id, unidade, raw_header FROM nfeemail WHERE unidade='NONE' ORDER BY id DESC;");

$emails = $popper->findCollectionByYear();

echo 'inicio' . PHP_EOL;
while ($email = $emails->next()) {
    $header = imap_rfc822_parse_headers($email->raw_header);
    $unidade = $popper->getUnidade($header);
    
    if ($unidade != $email->unidade) {
        echo 'id: ' . $email->id . ';';
        $email->unidade = $unidade;
        $popper->storeEmail($email);
        $ret = $popper->parseEmail($email);
        echo 'Parser: ' . json_encode($ret) . PHP_EOL;
        echo 'Unidade alterada email_id ' . $email->id . PHP_EOL;
    }
}

echo 'fim';
unset($emails);
