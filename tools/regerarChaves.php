<?php

/**
 * O objetivo deste arquivo é regerar os dados do campo chave do BD a partir do XML.
 * Isso porque o redbean cria o campo como float e na verdade deveria ser decimal(44,0)
 * Dessa forma é um paleativo até criar o banco corretamente sempre que necessário
 *
 */

// para ser rodado na linha de comando
if ('cli' != PHP_SAPI) {
    die('Não está na linha de comando');
}

require '../app/app.php';
use \Uspdev\GerenciadorNfe\Database;
// aqui vai ter problema se o banco for grande de mais
// de estouro de memoria
$nfes = Database::findAll('nfe');

$c_ok = 0;
$c_dup = 0;
foreach ($nfes as $nfe) {
    $xml = $nfe->xml;
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $infNfe = $dom->getElementsByTagName('infNFe')->item(0);
    $chave = str_replace('NFe', '', $infNfe->getAttribute('Id'));

    $nfe->chave = $chave;
    if (empty($dup = Database::findOne('nfe', ' chave = ?', [$chave]))) {
        Database::store($nfe);
        $c_ok++;
    } else if ($dup->id != $nfe->id) {
        // vamos apagar se estiver duplicado.
        Database::trash($nfe);
        echo 'id ' . $dup->id . ' ' . $nfe->id . PHP_EOL;
        $c_dup++;
    } else {
        Database::store($nfe);
        $c_ok++;
    }

    //echo $nfe->id.PHP_EOL;
}
echo 'ok=' . $c_ok . ', dup e corr=' . $c_dup . PHP_EOL;

echo "Pronto!" . PHP_EOL;
