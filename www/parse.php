<?php
require '../app/app.php';

$id = $_GET['id'];

$email = $popper->getEmail($id);


$ret = $popper->parseEmail($email);
echo 'Parser (anexos, nfe exist, nfe novos): ' . json_encode($ret) . '<br>';

