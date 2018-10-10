<?php
require '../app/app.php';

use \Uspdev\Popper\Popper;
$popper = new Popper($cfg);
$ret['novos emails'] = count($popper->downloadEmails());
$ret = array_merge($ret, $popper->parseNewEmails());
$popper->log($ret);
unset($popper);

echo json_encode($ret);

