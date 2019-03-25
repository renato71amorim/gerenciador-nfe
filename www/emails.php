<?php
require '../app/app.php';

echo 'Uso tabela email ' . $popper->usoDB() . 'MB';
#echo '<br>';


$unidade = empty($_GET['unidade']) ? '%' : $_GET['unidade'];
$ano = empty($_GET['ano']) ? date('Y') : $_GET['ano'];


echo ' | Anos: ';
foreach ($popper->anos() as $a) {
    echo '<a href=?ano=' . $a . '&unidade='.$unidade.'>' . $a . '</a> : ';
}
echo '<a href=?ano=%&unidade='.$unidade.'>TODOS</a>';
#echo '<br>';

echo ' | Unidades: ';
foreach ($popper->unidades() as $u) {
    echo '<a href=?ano='.$ano.'&unidade=' . $u . '>' . $u . '</a> : ';
}
echo '<a href=?ano='.$ano.'&unidade=%>TODOS</a>';
echo '<br>';

echo '<br>';

$popper->unidade = $unidade;
$popper->ano = $ano;
$emails = $popper->findCollection();

echo 'Filtros:';
echo ' Unidade: '.$popper->unidade;

echo ' | Ano: ' . $popper->ano . '<br>';
//echo 'Total: ' . count($emails) . '<br>';

while ($email = $emails->next()) {
    $status = json_decode($email['status'], true);
    echo $email['id'] . ' - ' . $email['unidade'] . ' - ' . $status['fetchdate'] . ' - Anexos: ' . count($status['anexos']);
    echo ' - Assunto: ' . $email['assunto'];
    echo ' - <a href="email.php?id=' . $email['id'] . '">Detalhes</a>';
    echo '<br>';
}

//unset($emails);
