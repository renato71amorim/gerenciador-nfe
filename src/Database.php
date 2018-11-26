<?php
namespace Uspdev\GerenciadorNfe;

use \RedBeanPHP\R as R;

class Database
{

    public static function connect($connection_string,$usr,$pwd)
    {
        // vamos conectar no banco de dados
        R::setup($connection_string,$usr,$pwd);
        if (!R::testConnection()) {
            die('Erro de conexão no banco de dados');
        }
    }

    public static function dispense($bean)
    {
        return R::dispense($bean);
    }

    public static function store($bean)
    {
        return R::store($bean);
    }

    public static function load($bean, $id)
    {
        return R::load($bean, $id);
    }

    public static function findOne($bean, $sql, $param)
    {
        return $nfe = R::findOne($bean, $sql, $param);
    }

}
