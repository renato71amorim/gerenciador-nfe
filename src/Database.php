<?php
namespace Uspdev\GerenciadorNfe;

use \RedBeanPHP\R as R;

class Database
{

    public static function connect($connection_string, $usr, $pwd)
    {
        // vamos conectar no banco de dados
        R::setup($connection_string, $usr, $pwd);
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

    public static function findCollection($bean, $sql, $param)
    {
        return R::findCollection($bean, $sql, $param);
    }

    /**
     * # uso
     *
     * Mostra o uso em MB de uma tabela do banco de dados.
     *
     * @param string $table Nome da tabela a ser verificada. Default = email
     * @return string Uso da tabela em MB
     */
    public static function uso($bean)
    {
        $q = 'SELECT table_name AS `Table`,
        round(((data_length + index_length) / 1024 / 1024), 2) `mb`
        FROM information_schema.TABLES
        WHERE table_schema = DATABASE()
        AND table_name = "' . $bean . '"';

        $r = R::getAll($q);
        return $r[0]['mb'];
    }

    public static function anos($bean)
    {
        $q = 'SELECT DISTINCT ano from ' . $bean;
        $r = R::getCol($q);
        //print_r($r);exit;
        return $r;
    }

}
