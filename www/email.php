<?php
/**
 * Detalhamento de um email
 */

require '../app/app.php';

$id = $_GET['id'];

$email = $popper->load($id);

echo '<a href= emails.php>Lista de emails</a><br><br>';

// vamos imprimir algumas coisas na tela
echo 'id ' . $id . ' | ';

echo 'unidade ' . $email->unidade;

// se a unidade for NONE vamos retestar para ver se achamos
$unidade_reparse = $popper->getUnidadeFromHeader2($email->raw_header);
if ($unidade_reparse == 'NONE') {
    echo ' (Confirmado)';
} else {
    if ($unidade_reparse != $email->unidade) {
        $email->unidade = $unidade_reparse;
        Database::store($email);
        echo ' -> ' . $unidade_reparse;
    } else {
        echo ' (OK)';
    }
}

echo '<br>';
echo '<br>';

$status = json_decode($email->status);
echo '  fetchdate: ' . $status->fetchdate . ' | ';
echo '  parsedate: ' . $status->parsedate;
echo ' | <a href=parse.php?id=' . $id . '>Parsear novamente</a><br>';
echo '<br>';

echo '  Remet: ' . $email->remet . '<br>';
echo '  Assunto: ' . $email->assunto . '<br>';

echo '<br>';
echo 'Anexos<br>';
foreach ($status->anexos as $anexo) {

    if ($anexo->parser == 'nfe') {
        echo '<a href=nfe.php?id=' . $anexo->nfe_id . '>' . $anexo->parser . '</a> | ';
    }
    echo $anexo->filename . ' (' . $anexo->filesize . ' bytes)';

    echo '<br>';
}

//echo $email->status;

echo '<br>';
echo '<hr>';
echo 'Debug (raw_header)<br>';
echo '<pre>';
print_r($email->raw_header);
echo '</pre>';
