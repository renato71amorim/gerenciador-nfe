<?php
require '../app/app.php';

$ret['novos emails'] = count($popper->popNow());
$ret = array_merge($ret, $popper->parseNew());
$popper->log($ret);
unset($popper);

echo json_encode($ret);

