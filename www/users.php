<?php
session_start();

require '../app/app.php';

use Uspdev\GerenciadorNfe\ConfigManager;

$config = new ConfigManager('gerenciador-nfe', 'EESC', '54321');

echo 'env = ' . json_encode($config->getEnv()) . '<br>';
$env['anos'] = [2019,2018];
$config->setEnv($env);
echo 'env = ' . json_encode($config->getEnv()) . '<br>';

echo 'current user: ' . json_encode($config->getCurrentUser()) . '<br>';
echo 'is user: ' . $config->isUser() . '<br>';
echo 'is admin: ' . $config->isAdmin() . '<br>';

echo 'users = ' . json_encode($config->getUsers()) . '<br>';

echo 'Add user = ' . json_encode($config->addUser('45678',2)) . '<br>';

echo 'users = ' . json_encode($config->getUsers()) . '<br>';

echo 'Del user = ' . json_encode($config->delUser('45678')) . '<br>';

echo 'users = ' . json_encode($config->getUsers()) . '<br>';

echo 'Del user = ' . json_encode($config->delUser('54321')) . '<br>';

echo 'setCurrentUser = ' . json_encode($config->setCurrentUser('45678')) . '<BR>';

echo 'is user: ' . $config->isUser() . '<br>';
echo 'is admin: ' . $config->isAdmin() . '<br>';

echo 'users = ' . json_encode($config->getUsers()) . '<br>';

exit;

echo 'adduser: ' . json_encode(
    $config->addUser('54321')
) . '<br>' . PHP_EOL;

echo 'deluser: ' . json_encode(
    $config->delUser('54321')
) . '<br>' . PHP_EOL;

echo '<br>' . PHP_EOL;

echo 'current user: ' . json_encode(
    $config->setCurrentUser('12345')
) . '<br>' . PHP_EOL;

echo 'level: ' . level();
echo 'users: ' . users();
echo '<br>' . PHP_EOL;

echo 'adduser: ' . json_encode(
    $config->addUser('12345', 2)
);
echo '<br>' . PHP_EOL;

echo 'level: ' . level();
echo '<br>' . PHP_EOL;
echo '<br>' . PHP_EOL;

exit;

echo 'added ' . json_encode(
    $config->addUser(12345)
);
echo '<br>' . PHP_EOL;

echo 'level: ' . level();
echo '<br>' . PHP_EOL;

#echo 'added '. $config->addUser(12345,2);
$users = $config->getUsers();
#print_r($users);
#echo 'deleted ' . $config->delUser(12345);
$users = $config->getUsers();
#print_r($users);

$env = $config->getEnv();
echo 'Ambiente ';
print_r($env);

echo level();
