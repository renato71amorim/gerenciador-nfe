<?php

require '../app/app.php';

use Uspdev\GerenciadorNfe\Nfe;

$nfes = new Nfe($cfg);

$id = $_GET['id'];

$nfe = $nfes->load($id);
$ide = json_decode($nfe->ide);

//echo $nfe->id;
?>

<a href="nfes.php">Lista de nfes</a><br>
<br>

Unidade: <?php echo $nfe->unidade ?> |
Ano: <?php echo $nfe->ano ?> |
Chave: <?php echo $nfe->chave ?><br>
<br>

Identificação<br>
<?php echo $nfe->ide ?><br>
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

Informações Adicionais<br>
<?php echo $nfe->infadic ?><br>
<br>

Downloads<br>
<a href>XML</a> | <a href>PDF</a> | <a href>Sefaz</a>