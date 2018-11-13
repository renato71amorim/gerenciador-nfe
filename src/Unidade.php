<?php
namespace Uspdev\Popper;

class Unidade
{
    public function __construct()
    {
    }

    public static function getUnidades()
    {
        $unidades = [];
        $unidades[0]['sigla'] = 'EESC';
        $unidades[0]['email_nfe'] = 'nfe@eesc.usp.br';

        $unidades[1]['sigla'] = 'FO';
        $unidades[1]['email_nfe'] = 'nfe.fo@usp.br';

        return $unidades;

    }

    public function getUnidadeByEmailNfe($email)
    {
        $unidades = Unidade::getUnidades();
        foreach ($unidades as $unidade) {
            if ($unidade['email_nfe'] == $email) {
                return $unidade['sigla'];
            }
        }
        return false;
    }
}
