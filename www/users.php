<?php
session_start();

require '../app/app.php';

use Uspdev\GerenciadorNfe\ConfigManager;

function level()
{
    global $configMgr;
    if ($configMgr->isUser()) {
        $msg = 'é usuário ';
    } else {
        $msg = 'não é usuario ';
    }

    if ($configMgr->isAdmin()) {
        $msg .= 'é admin ';
    } else {
        $msg .= 'não é admin ';
    }
    return $msg . '<br>' . PHP_EOL;
}

function users()
{
    global $configMgr;
    return json_encode(
        $configMgr->getUsers()
    ) . '<br>' . PHP_EOL;
}

$configMgr = new ConfigManager('gerenciador-nfe', 'EESC');

echo 'current user: ' . json_encode(
    $configMgr->setCurrentUser('54321')
) . '<br>' . PHP_EOL;
echo 'level: ' . level();
echo 'users: ' . users();
echo 'adduser: ' . json_encode(
    $configMgr->addUser('54321')
) . '<br>' . PHP_EOL;

echo 'deluser: ' . json_encode(
    $configMgr->delUser('54321')
) . '<br>' . PHP_EOL;

echo '<br>' . PHP_EOL;

echo 'current user: ' . json_encode(
    $configMgr->setCurrentUser('12345')
) . '<br>' . PHP_EOL;

echo 'level: ' . level();
echo 'users: ' . users();
echo '<br>' . PHP_EOL;

echo 'adduser: ' . json_encode(
    $configMgr->addUser('12345', 2)
);
echo '<br>' . PHP_EOL;

echo 'level: ' . level();
echo '<br>' . PHP_EOL;
echo '<br>' . PHP_EOL;

exit;

echo 'added ' . json_encode(
    $configMgr->addUser(12345)
);
echo '<br>' . PHP_EOL;

echo 'level: ' . level();
echo '<br>' . PHP_EOL;

#echo 'added '. $configMgr->addUser(12345,2);
$users = $configMgr->getUsers();
#print_r($users);
#echo 'deleted ' . $configMgr->delUser(12345);
$users = $configMgr->getUsers();
#print_r($users);

$env = $configMgr->getEnv();
echo 'Ambiente ';
print_r($env);

echo level();
