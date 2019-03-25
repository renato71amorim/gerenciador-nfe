<?php
/**
 * Parseia novamente um dado email
 */

require '../app/app.php';

use Uspdev\GerenciadorNfe\Database;

$id = $_GET['id'];

$email = $popper->load($id);

// vamos imprimir algumas coisas na tela
echo 'id ' . $id . ' | ';
echo 'unidade ' . $email->unidade;
echo '<br>';

$ret = $popper->parseEmail($email);
echo 'Parser (anexos, nfe exist, nfe novos): ' . json_encode($ret) . '<br>';
//echo $email->status;
