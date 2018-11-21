<?php
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

use \RedBeanPHP\R as R;
// vamos conectar no banco de dados
R::setup('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['usr'], $db['pwd']);
if (!R::testConnection()) {
    die('Erro de conexão no banco de dados');
}

// vamos criar a instancia popper aqui pois todos vao usar
use \Uspdev\Popper\PopperFactory;
$popper = PopperFactory::create($cfg);