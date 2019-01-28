<?php

/* Instalação
# Primeiro tem de instalar o pecl-mailparse

NUma pasta vazia:
pecl download mailparse
tar -xvf mailparse-3.0.2.tgz
cd mailparse-3.0.2/
phpize
./configure
sed -i 's/#if\s!HAVE_MBSTRING/#ifndef MBFL_MBFILTER_H/' ./mailparse.c
make

# para ubuntu 16.04
# para outros tem de ver a pasta de destino.
sudo mv modules/mailparse.so /usr/lib/php/20151012/

service apache2 restart

# para php 7.2
Isto se aplica para ubuntu 18.04 e para 16.04 com php 7.2 isntalado.
O php-mail-parse é uma biblioteca do composer

# instale as dependencias do composer.
composer install

# crie o arquivo config.php a partir deste e ajuste o necessário



*/
# Servidor de onde vai baixar os emails

#$imap_host = '{imap.gmail.com:993/imap/ssl}INBOX'; //imap

$url = 'http://143.107.233.111/git/tmp/email/www/';

$imap['host'] = '{pop.gmail.com:995/pop/ssl}INBOX'; //pop
$imap['usr'] = 'delos-nfe@eesc.usp.br';
$imap['pwd'] = 'xxxx';

# Os emails e nfes serão guardados em um banco de dados

$db['host'] = 'localhost';
$db['dbname'] = 'delos-php';
$db['usr'] = 'delos';
$db['pwd'] = 'xxxx';

# O popper gera logs. O arquivo de log (ou a pasta) tem de ter acesso de escrita
$logfile = __DIR__ . '/log/popper.log';

# Dados de acesso ao webservice de nfe
# https://github.com/uspdev/nfe-ws
$nfews['srv'] = "http://servidor/nfe-ws/api/";
$nfews['usr'] = 'delos';
$nfews['pwd'] = 'xxxxx';


