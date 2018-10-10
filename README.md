# gerenciador-nfe
Gerenciador de notas fiscais recebidas

O objetivo desse sistema é organizar e arquivar notas fiscais recebidas pela unidade a fim de cumprir com a legislação tributária vigente.

O sistema monitora uma caixa de postal de e-mail para onde são enviadas as notas fiscais. Os emails são baixados e verificados quanto à existência de notas fiscais. Uma vez recuperadas, as notas são validadas e guardadas no banco de dados para que o usuário possa consultá-las.


## Email popper

Este projeto conecta num servidor pop de email (gmail), baixa todos e guarda em banco de dados.\
Depois verifica os emails baixados procurando anexos e verifica se algum é nfe (xml) 
guardando no banco também.

A verificação do nfe é feita consultando o nfe-ws (http://github.com/uspdev/nfe-ws). 
Como o nfe-ws valida o xml e consulta o protocolo na sefaz, ele já guarda essas informações junto ao email.

## Instalação

Primeiro tem de instalar o pecl-mailparse.

O instalador padrão não funciona por um bug (5/2018)

Se for Ubuntu 16.04, rode os comandos abaixo numa pasta vazia:

    pecl download mailparse
    tar -xvf mailparse-3.0.2.tgz
    cd mailparse-3.0.2/
    phpize
    ./configure
    sed -i 's/#if\s!HAVE_MBSTRING/#ifndef MBFL_MBFILTER_H/' ./mailparse.c
    make
    sudo mv modules/mailparse.so /usr/lib/php/20151012/

Para outras distros tem de ver a pasta de destino de 'libs' do php

Reinicie o apache
    
    service apache2 restart

Instale as dependencias do composer.

    composer install

Crie o arquivo config.php a partir do config.sample.php e ajuste o necessário.
    
    cp config.sample.php config.php
    
    
## Utilização

Em princípio foi estruturado para baixar via cron do sistema, por meio de:

    php bin/cron.php
    
Colocar o cron com intervalo de 1h é razoável.

O cron acessa uma url ('www/index.php') que executa todas as tarefas.

Portanto www/ necessita estar configurado no apache para ser acessado publicamente.

Acessando a url diretamente pelo navegador também dispara o processo.

