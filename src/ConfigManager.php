<?php
namespace Uspdev\GerenciadorNfe;

use \RedBeanPHP\R as R;

/**
 * Manipula a tabela de config dos apps
 * Para cada unidade especifica a lista de usuários com
 * acesso, lista de admins e variáveis de configuração
 */

class ConfigManager
{
    protected $app;
    protected $unidade;
    protected $currentUser = [];
    protected $config;

    public function __construct($app, $unidade, $codpes)
    {
        $this->app = $app;
        $this->unidade = $unidade;
        $this->config = $this->loadConfig();
        $this->setCurrentUser($codpes);
    }

    protected function loadConfig()
    {
        if ($this->config = R::findOne('config', 'app = ? and unidade = ?',
            [$this->app, $this->unidade])) {
        } else {
            $this->newConfig();
        }
        return $this->config;
    }

    protected function newConfig()
    {
        $this->config = R::dispense('config');
        $this->config->app = $this->app;
        $this->config->unidade = $this->unidade;
        $this->config->users = json_encode([], JSON_NUMERIC_CHECK);
        $this->config->env = json_encode([], JSON_NUMERIC_CHECK);
        return $this->saveConfig();
    }

    protected function saveConfig()
    {
        return R::store($this->config);
    }

    // -------------------------------------------------

    public function setCurrentUser($codpes)
    {
        $this->currentUser['codpes'] = $codpes;
        $this->currentUser['level'] = $this->getLevel();

        // se não houver usuário algum o primeiro vai ser admin (level=2)
        // isso acontece na primeira configuração somente
        if (empty(json_decode($this->config->users))) {
            $this->currentUser['level'] = 2;
            $this->addUser($codpes, 2);
        }
        return $this->currentUser;
    }

    // será que precisa disso?
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * getUsers Retorna a lista de usuários cadastrados
     * Somente se é admin
     *
     * @return void
     */
    public function getUsers()
    {
        if ($this->isAdmin()) {
            return json_decode($this->config->users, JSON_NUMERIC_CHECK);
        } else {
            return false;
        }
    }

    public function addUser($codpes, $level = 1)
    {
        if ($this->isAdmin()) {
            $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
            $user = ['codpes' => $codpes, 'level' => $level];

            $key = array_search($codpes, array_column($users, 'codpes'));
            if ($key === false) {
                // usuario não está na lista entao vamos adicionar
                array_push($users, $user);
                $this->config->users = json_encode($users, JSON_NUMERIC_CHECK);
                $this->saveConfig();
                return $user;
            } else {
                if ($users[$key]['level'] != $level) {
                    // usuário esta na lista e alterou o level
                    $users[$key]['level'] = $level;
                    $this->config->users = json_encode($users, JSON_NUMERIC_CHECK);
                    $this->saveConfig();
                    return $users[$key];
                }
            }
            // se o usuario já existe e nao mudou nada
            return $user;
        } else {
            return false;
        }
    }

    public function delUser($codpes)
    {
        // o usuario tem de ser admin e
        // o usuário não pode remover a si mesmo
        if ($this->isAdmin() and $codpes != $this->currentUser['codpes']) {
            $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
            $key = array_search($codpes, array_column($users, 'codpes'));
            // se encontrou o usuario a ser removido executa a ação
            if ($key !== false) {
                $user = array_splice($users, $key, 1);
                $this->config->users = json_encode($users, JSON_NUMERIC_CHECK);
                $this->saveConfig();
                return json_encode($user);
            }
        }
        return false;
    }

    public function isAdmin()
    {
        return ($this->currentUser['level'] == 2) ? 1 : 0;
    }

    public function isUser()
    {
        return ($this->currentUser['level'] > 0) ? 1 : 0;
    }

    // -------------------------------------------
    public function getEnv()
    {
        if ($this->isUser()) {
            return json_decode($this->config->env, JSON_NUMERIC_CHECK);
        } else {
            return false;
        }
    }

    public function setEnv(array $env)
    {
        $this->config->env = json_encode($env);
        $this->saveConfig();
        
    }

    // ----------------------------------------
    // daqui para baixo não está sendo usado
    protected function getLevel()
    {
        $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
        $key = array_search($this->currentUser['codpes'], array_column($users, 'codpes'));
        if ($key !== false) {
            return $users[$key]['level'];
        } else {
            // o level 0 corresponde a usuario sem cadastro
            return 0;
        }
    }

    public function isInProfile($profile)
    {

    }

    /**
     * profiles
     *
     * mostra os perfis existentes
     * pre definidos: user e admin
     * podem ser criados novos perfis
     *
     * @return array Perfis
     */
    public function userProfiles()
    {

    }

    public function setUserProfile($profile)
    {

    }

}
