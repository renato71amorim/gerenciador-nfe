<?php
/**
 * Detalhamento de um email
 */

require '../app/app.php';

$id = $_GET['id'];

$email = $popper->load($id);

// vamos imprimir algumas coisas na tela
echo 'id ' . $id . '<br>';
echo 'unidade ' . $email->unidade;

echo '<br>';

echo '<a href=parse.php?id=' . $id . '>Parsear novamente</a>';
echo '<br>';
echo '<br>';

$status = json_decode($email->status);
foreach ($status->anexos as $anexo) {

    if ($anexo->parser == 'nfe') {
        echo '<a href=nfe.php?id=' . $anexo->nfe_id . '>'.$anexo->parser.'</a> | ';
    }
    echo $anexo->filename . '(' . $anexo->filesize . ' bytes)';

    echo '<br>';
}

//echo $email->status;

echo '<br>';
