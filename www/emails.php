<?php
require '../app/app.php';

echo 'Uso tabela email ' . $popper->usoDB() . 'MB';
#echo '<br>';
echo ' | Anos: ';
foreach ($popper->anos() as $ano) {
    echo '<a href=?ano=' . $ano . '>' . $ano . '</a> | ';
}
#echo '<br>';

echo ' | Unidades: ';
foreach ($popper->unidades() as $u) {
    echo '<a href=?ano=' . $u . '>' . $u . '</a> | ';
}
echo '<br>';

echo '<br>';

$ano = empty($_GET['ano']) ? date('Y') : $_GET['ano'];

$popper->unidade = '%';
$popper->ano = '%';
$emails = $popper->findCollection($ano);

echo 'Filtros: Unidade: '.$popper->unidade;

echo ' | Ano: ' . $popper->ano . '<br>';
//echo 'Total: ' . count($emails) . '<br>';

while ($email = $emails->next()) {
    $status = json_decode($email['status'], true);
    echo $email['id'] . ' - ' . $email['unidade'] . ' - ' . $status['fetchdate'] . ' - Anexos: ' . count($status['anexos']);
    echo ' - Assunto: ' . $email['assunto'];
    echo ' - <a href="parse.php?id=' . $email['id'] . '">Parse</a>';
    echo ' - <a href="header.php?id=' . $email['id'] . '">Header</a>';
    echo '<br>';
}

//unset($emails);
