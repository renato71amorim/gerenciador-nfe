<?php
require '../app/app.php';
use Uspdev\GerenciadorNfe\Nfe;

$nfes = new Nfe($cfg);

echo 'Uso tabela nfes ' . $nfes->usoDB() . 'MB';

$unidade = empty($_GET['unidade']) ? '%' : $_GET['unidade'];
$ano = empty($_GET['ano']) ? date('Y') : $_GET['ano'];

echo ' | Anos: ';
foreach ($nfes->anos() as $a) {
    echo '<a href=?ano=' . $a . '&unidade=' . $unidade . '>' . $a . '</a> : ';
}
echo '<a href=?ano=%&unidade=' . $unidade . '>TODOS</a>';
#echo '<br>';

echo ' | Unidades: ';
foreach ($nfes->unidades() as $u) {
    echo '<a href=?ano=' . $ano . '&unidade=' . $u . '>' . $u . '</a> : ';
}
echo '<a href=?ano=' . $ano . '&unidade=%>TODOS</a>';
echo '<br>';

echo '<br>';

$nfes->unidade = $unidade;
$nfes->ano = $ano;
$list = $nfes->findCollection();

echo 'Filtros:';
echo ' Unidade: ' . $nfes->unidade;

echo ' | Ano: ' . $nfes->ano . '<br>';
//echo 'Total: ' . count($emails) . '<br>';

while ($nfe = $list->next()) {
    $status = json_decode($nfe['status'], true);
    $emit = json_decode($nfe['emit'], true);
    $ide = json_decode($nfe['ide'], true);
    echo $nfe['id'] . ' - ' . $nfe['unidade'] . ' - ' . $nfe['ano'];
    echo ' - ' . $ide['dataemi'] . '; no/s: ' . $ide['nro'] . '/' . $ide['serie'];
    echo '; Total: ' . $ide['total'];
    echo '; Emit: ' . $emit['nome'];
    echo '<br>';
}
