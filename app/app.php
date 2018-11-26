<?php
namespace Uspdev\GerenciadorNfe;
// principal arquivo que gerencia a aplicação.
// Aqui estão todos os includes e configurações

// a memória grande foi para parsear muitos emails que estavam na fila
// pode ser que não precise de tudo isso
ini_set('memory_limit', '512M');

require '../config.php';
require '../vendor/autoload.php';

$cfg['imap'] = $imap;
$cfg['nfews'] = $nfews;
$cfg['logfile'] = $logfile;

Database::setup('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['usr'], $db['pwd']);

// vamos criar a instancia popper aqui pois todos vao usar
$popper = EmailFactory::create($cfg);