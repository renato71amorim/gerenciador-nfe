# gerenciador-nfe
Gerenciador de notas fiscais recebidas

O objetivo desse sistema é organizar e arquivar notas fiscais recebidas pela unidade a fim de cumprir com a legislação tributária vigente.

O sistema monitora uma caixa de postal de e-mail para onde são enviadas as notas fiscais. Os emails são baixados e verificados quanto à existência de notas fiscais. Uma vez recuperadas, as notas são validadas e guardadas no banco de dados para que o usuário possa consultá-las.


## Email popper

Este projeto conecta num servidor pop de email (gmail), baixa todos e guarda em banco de dados.\
Depois verifica os emails baixados procurando anexos e verifica se algum é nfe (xml) guardando no banco também.

A verificação do anexo para saber se é nfe é feita consultando um webservice externo (github.com/uspdev/nfe-ws).

Como o nfe-ws valida o xml e consulta o protocolo na sefaz, ele já guarda essas informações junto ao xml no banco de dados.

Para o usuário o objetivo (não implementado ainda) é ter uma interface com a listagem das nfes contendo os dados básicos como emitente, data, número da NFE, etc. Também a situação da NFE (ok, cancelada, etc). Poderá baixar a DANFE, o XML ou o protocolo de situação (gerado pelo nfe-ws).

O sistema permite receber emails de diversas unidades e classificar adequadamente criando espaços de usuários distintos para cada um.

## Dependências

* Testado no PHP 7.0.32 (ubuntu 16.04) e no PHP 7.2 (ubuntu 18.04)

* ext-imap

É necessário ter o webservice nfe-ws funcional para essa aplicação funcionar adequadamente!

## Instalação

Clone o projeto do github.

Instale as dependencias do composer.

    composer install

Crie o arquivo config.php a partir do config.sample.php e ajuste o necessário.
    
    cp config.sample.php config.php

Em princípio foi estruturado para baixar via cron do sistema, por meio de:

    php bin/cron.php
    
Colocar o cron com intervalo de 1h é razoável.

A pasta www/ necessita estar configurado no apache para ser acessado publicamente.

## Interface de administração

Deverá conter os emails baixados e elementos para verificar possíveis problemas.

Deverá permitir cadastrar as unidades que irão utilizar esse sistema, incluindo um administrador local da unidade e o email que enviará as nfes ao sistema.

## Interface do usuário

O administrador cadastrado inicialmente poderá adicionar os usuários e outros administradores no sistema.

Os usuários entrarão de cara na listagem de notas fiscais emitidas contra a unidade ou as que foram enviadas ao email cadastrado na unidade.