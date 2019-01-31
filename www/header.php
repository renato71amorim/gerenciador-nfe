<?php
require '../app/app.php';

use Uspdev\GerenciadorNfe\Database;

$id = $_GET['id'];

$email = $popper->load($id);

// vamos imprimir as coisas na tela
echo 'id ' . $id . '<br>';
echo 'unidade ' . $email->unidade;

$unidade_reparse = $popper->getUnidadeFromHeader2($email->raw_header);
if ($unidade_reparse != 'NONE' and $unidade_reparse != $email->unidade) {
    $email->unidade = $unidade_reparse;
    Database::store($email);
    echo ' -> ' . $unidade_reparse;
} else {
    echo ' -> OK';
}

echo '<br><br>';

echo 'Status<br>';
$status = json_decode($email->status);

echo '  fetchdate: ' . $status->fetchdate . '<br>';
echo '  parsed: ' . $status->parsed . '<br>';
echo '  parsedate: ' . $status->parsedate . '<br>';
echo '  anexos: ' . count($status->anexos) . '<br>';
foreach ($status->anexos as $anexo) {
    echo $anexo->parser . ': <a href>' . sys_get_temp_dir() . '/' . $anexo->filename . '</a> (' . $anexo->filesize . ' bytes)<br>';
}

//$nfes = R::find('nfe', ' email_id = ? ', [$email->id]);
echo 'nfes associadas a este email: ' . $email->countOwn('nfe') . ' <a href>ver nfe(s)</a><br>';

//$header = imap_rfc822_parse_headers($email->raw_header);
echo '<br>';
echo 'Debug (raw_header)<br>';
echo '<pre>';
print_r($email->raw_header);
echo '</pre>';
