<?php
require '../app/app.php';

#use \RedBeanPHP\R as R;
use \Uspdev\Popper\PopperFactory;

$popper = PopperFactory::create($cfg);

$id = $_GET['id'];

$email = $popper->getEmail($id);

// // vamos verificar a unidade pois pode haver problemas de detecção
// $header = imap_rfc822_parse_headers($email->raw_header);
// $unidade = $popper->getUnidade($header);

// // se for diferente vamos atualziar no BD
// if ($unidade != $email->unidade) {
//     $email->unidade = $unidade;
//     $popper->storeEmail($email);
//     echo 'Unidade alterada<br>';
// }

// // vamos corrigir o ano que vem errado
// if ($email->ano < 2018) {
//     $email['data'] = Popper::ajustaDataEmail($header->date);
//     $email['ano'] = date('Y', strtotime($email['data']));
//     R::store($email);
//     echo 'Ajustado ano errado<br>';
// }

$ret = $popper->parseEmail($email);
echo 'Parser (anexos, nfe exist, nfe novos): ' . json_encode($ret) . '<br>';

