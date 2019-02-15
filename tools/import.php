<?php

/**
 * esse script importará nfes da aplicação antiga
 * em django para esta aplicação
 * convertendo os formatos adequadamente
 *
 */

if ('cli' != PHP_SAPI) {
    die('Não está na linha de comando');
}

require '../app/app.php';
use Uspdev\Nfe\NfeWsConsumer;

// verifica o ultimo id que parou
if (is_file('lastid.txt')) {
    $last_id = file_get_contents('lastid.txt');
} else {
    $last_id = 0;
}

use \RedBeanPHP\R as R;
$nfeConsumer = new NfeWsConsumer($nfews['srv'], $nfews['usr'], $nfews['pwd']);

//vamos conectar no BD antigo e selecionar as NFEs
// se for removido não vamos importar
// vamos continuar a partyir do ultimo id armazenado
R::addDatabase('delos', 'mysql:host=delos.eesc.usp.br;dbname=delos', 'delos', 'fwv0983uarc093q0jf09ehgw9ph0389qwv');
R::selectDatabase('delos');
$nfes = R::findCollection('nfe_nfe', ' removed = 0 and id > ?', [$last_id]);

// vamos para o BD novo e percorrer a lista de antigos para importar as NFEs
R::selectDatabase('default');
$nfe = new Uspdev\GerenciadorNfe\Nfe($cfg);
$nfe->setSefaz($nfeConsumer);

$c1 = 0;
$c2 = 0;
while ($nfe_old = $nfes->next()) {

    //vamos consultar o webservice de NFE e recuperar os detalhes da NFE
    $data = $nfe->verificaNfe($nfe_old->xml);

    if ($nfe_old->unidade_id == 1) {
        $data['unidade'] = 'EESC';
    } else {
        echo 'Qual unidade? ' . $nfe_old->unidade_id . PHP_EOL;
        // quando for uma unidade nao monitorada
        // vamos parar e perguntar
        exit;
    }

    // o store verifica se já tem e atualiza ou cria novo
    // conforme necessário
    $ret = $nfe->store($data, '');

    if ($ret == 'novo') {
        $c1++;
    } else {
        $c2++;
    }

    //R::selectDatabase('delos');
    //print_r($nfe);
    //exit;
    file_put_contents('lastid.txt', $nfe_old->id);
    if ($c1 == 5) {
        break;
    }
    echo '.';

}

echo PHP_EOL;
echo 'importar: ' . $c1 . ', não importar: ' . $c2 . PHP_EOL;

//$nfe = R::load('nfe_nfe',1);
