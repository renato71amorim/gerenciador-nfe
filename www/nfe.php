<?php

require '../app/app.php';

use Uspdev\GerenciadorNfe\Nfe;

$nfes = new Nfe($cfg);

$id = $_GET['id'];

$nfe = $nfes->load($id);
$ide = json_decode($nfe->ide);

//echo $nfe->id;
?>

Unidade: <?php echo $nfe->unidade ?> |
Ano: <?php echo $nfe->ano ?>
<br>
Identificação<br>

Chave: <?php echo $nfe->chave ?><br>
Número: <?php echo $ide->nro ?> |
Série: <?php echo $ide->serie ?> |
Data emi: <?php echo $ide->dataemi ?> |
Total: <?php echo $ide->total ?><br>
<br>
Emitente<br>
<?php echo $nfe->emit ?><br>

<br>
Destinatário<br>
<?php echo $nfe->dest ?><br>

<br>
Sefaz | <a href=>Consultar agora</a><br>
<?php echo $nfe->sefaz ?><br>
<br>
Inf Adic: <br>
<?php echo $nfe->infadic ?>
