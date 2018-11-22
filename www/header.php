<?php
require '../app/app.php';

$id = $_GET['id'];

$email = $popper->getEmail($id);

// vamos imprimir as coisas na tela
echo 'id ' . $id . '<br>';
echo 'unidade ' . $email->unidade . '<br>';
echo '<br>';

echo 'Status<br>';
$status = json_decode($email->status);

echo '  fetchdate: ' . $status->fetchdate . '<br>';
echo '  parsed: ' . $status->parsed . '<br>';
echo '  parsedate: ' . $status->parsedate . '<br>';
echo '  anexos: ' . count($status->anexos) . '<br>';
foreach ($status->anexos as $anexo) {
    echo $anexo->parser .': <a href>'. sys_get_temp_dir() . '/'.$anexo->filename . '</a> (' . $anexo->filesize . ' bytes)<br>';
}

//$nfes = R::find('nfe', ' email_id = ? ', [$email->id]);
echo 'nfes associadas a este email: ' . $email->countOwn('nfe') . ' <a href>ver nfe(s)</a><br>';


$header = imap_rfc822_parse_headers($email->raw_header);
echo '<br>';
echo 'Debug<br>';
echo '<pre>';
print_r($header);
echo '</pre>';
