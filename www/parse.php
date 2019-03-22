<?php
/**
 * Parseia novamente um dado email
 */

require '../app/app.php';

use Uspdev\GerenciadorNfe\Database;

$id = $_GET['id'];

$email = $popper->load($id);

// vamos imprimir algumas coisas na tela
echo 'id ' . $id . '<br>';
echo 'unidade ' . $email->unidade;

if ($email->unidade == 'NONE') {
    $unidade_reparse = $popper->getUnidadeFromHeader2($email->raw_header);
    if ($unidade_reparse != 'NONE') {
        $email->unidade = $unidade_reparse;
        Database::store($email);
        echo ' -> '.$unidade_reparse;
    }
}

echo '<br>';

$ret = $popper->parseEmail($email);
echo 'Parser (anexos, nfe exist, nfe novos): ' . json_encode($ret) . '<br>';
echo $email->status;
