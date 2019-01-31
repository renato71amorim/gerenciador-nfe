<?php
namespace Uspdev\GerenciadorNfe;

class Unidade
{
    /**
     * getUnidades
     *
     * @return array Contendo todas as unidades
     */
    public static function getUnidades()
    {
        $unidades = [];
        $unidades[0]['id'] = 1;
        $unidades[0]['sigla'] = 'EESC';
        $unidades[0]['email_nfe'] = 'nfe@eesc.usp.br';

        $unidades[1]['sigla'] = 'FO';
        $unidades[1]['email_nfe'] = 'nfe.fo@usp.br';

        return $unidades;

    }

    
    /**
     * getUnidadeByEmailNfe
     *
     * @param  mixed $email
     *
     * @return varchar Sigla da unidade procurada ou false se não encontrar
     */
    public static function getUnidadeByEmailNfe($email)
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
