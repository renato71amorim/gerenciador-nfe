<?php
require '../app/app.php';

$ret['novos emails'] = count($popper->downloadEmails());
$ret = array_merge($ret, $popper->parseNewEmails());
$popper->log($ret);
unset($popper);

echo json_encode($ret);

