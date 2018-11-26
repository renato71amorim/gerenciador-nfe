<?php
// para ser rodado na linha de comando
if ('cli' != PHP_SAPI) {
    die('Não está na linha de comando');
}

require '../app/app.php';

// foi usado para corrigir unidades que vieram como null por erro no código

$anos = $popper->anos();
$emails = $popper->findCollectionByYear(1969);

echo 'inicio' . PHP_EOL;
$total_c = 0;
$unidade_c = 0;
$ano_c = 0;
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
        $unidade_c++;
    }

    // com o ano mal foramtado ele seta para 1969
    //$ano = date('Y', strtotime($email['data']));
    if ($email['ano'] == 1969) {
        echo $header->date;
        $data = $popper->ajustaDataEmail($header->date);
        echo $data;
        $email['data'] = $data;
        $email['ano'] = date('Y', strtotime($email['data']));
        \Uspdev\GerenciadorNfe\Database::store($email);
        echo 'id '. $email->id. ' - ' .$email['ano'].' - corrigido ano'.PHP_EOL;
        $ano_c++;
    }

    $total_c++;
}

echo 'Total: ' . $total_c . PHP_EOL;
echo 'Unidade: ' . $unidade_c . PHP_EOL;
echo 'Ano: ' . $ano_c . PHP_EOL;

echo 'fim' . PHP_EOL;
