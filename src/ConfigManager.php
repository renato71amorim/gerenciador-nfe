<?php
namespace Uspdev\GerenciadorNfe;

use \RedBeanPHP\R as R;

/**
 *   manipula a tabela de config dos apps
 */

class ConfigManager
{

    public $app;
    public $unidade;
    public $currentUser = array();
    public $config;

    public function __construct($app, $unidade)
    {
        $this->unidade = $unidade;
        $this->app = $app;
        //$this->currentUser = $user;
        $this->config = $this->loadConfig();
    }

    public function loadConfig()
    {
        if ($this->config = R::findOne('config', 'app = ? and unidade = ?',
            [$this->app, $this->unidade])) {
        } else {
            $this->newConfig();
        }
        return $this->config;
    }

    public function newConfig()
    {
        $this->config = R::dispense('config');
        $this->config->app = $this->app;
        $this->config->unidade = $this->unidade;
        $this->config->users = json_encode([], JSON_NUMERIC_CHECK);
        $this->config->env = json_encode([], JSON_NUMERIC_CHECK);
        return $this->saveConfig();
    }

    public function setCurrentUser($codpes)
    {
        $this->currentUser['codpes'] = $codpes;
        if (empty(json_decode($this->config->users))) {
            $this->currentUser['level'] = 2;
            $this->addUser($codpes, 2);
        } else {
            $this->currentUser['level'] = $this->getLevel();
        }
        return $this->currentUser;
    }

    public function getCurrentUser()
    {
        $this->currentUser['codpes'] = $codpes;
        $this->currentUser['level'] = $this->getLevel();
        if ($this->isUser()) {
            return true;
        } else {
            return false;
        }
    }

    private function lastAdmin(){
        $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
        $key = array_search(2, array_column($users, 'level'));
        echo 'fechou';
        exit;
    }

    public function getUsers()
    {
        if ($this->currentUser['level'] > 0) {
            return json_decode($this->config->users, JSON_NUMERIC_CHECK);
        } else {
            return false;
        }
    }

    public function getEnv()
    {
        return json_decode($this->config->env, JSON_NUMERIC_CHECK);
    }

    public function addUser($codpes, $level = 1)
    {
        if ($this->currentUser['level'] < 2) {
            return false;
        }
        $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
        $user = ['codpes' => $codpes, 'level' => $level];

        $key = array_search($codpes, array_column($users, 'codpes'));
        if ($key === false) {
            // usuario não está na lista
            array_push($users, $user);
            $this->config->users = json_encode($users, JSON_NUMERIC_CHECK);
            $this->saveConfig();
            return $user;
        } else {
            if ($users[$key]['level'] != $level) {
                // usuário alterou o level
                $users[$key]['level'] = $level;
                $this->config->users = json_encode($users, JSON_NUMERIC_CHECK);
                $this->saveConfig();
                return $users[$key];
            }
        }

        return true;
    }

    public function delUser($codpes)
    {
        if ($this->currentUser['level'] < 2) {
            return false;
        }

        $key = array_search($codpes, array_column($users, 'codpes'));
        $user = array_splice($users, $key, 1);
        $this->config->users = json_encode($users, JSON_NUMERIC_CHECK);
        $this->saveConfig();

        return json_encode($user);
    }

    public function saveConfig()
    {
        return R::store($this->config);
    }

    protected function getLevel()
    {
        $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
        $key = array_search($this->currentUser['codpes'], array_column($users, 'codpes'));
        if ($key !== false) {
            return $users[$key]['level'];
        } else {
            return 0;
        }
    }

    public function isAdmin()
    {
        $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
        $key = array_search($this->currentUser['codpes'], array_column($users, 'codpes'));
        if ($key !== false and $users[$key]['level'] == 2) {
            return true;
        } else {
            return false;
        }
    }

    public function isUser()
    {
        $users = json_decode($this->config->users, JSON_NUMERIC_CHECK);
        $key = array_search($this->currentUser['codpes'], array_column($users, 'codpes'));
        if ($key !== false) {
            return true;
        } else {
            return false;
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

    public static function acesso($app)
    {
        if ($_SESSION['user']['mode'] == 'admin') {
            return 2; // se for admin global libera acesso de admin

        } else { // verifica o acesso do usuario no config
            $config = Config::getConfig($app, $_SESSION['unidade']['sigla']);
            $users = json_decode($config->users, JSON_NUMERIC_CHECK);

            foreach ($users as $user) {
                if ($_SESSION['user']['codpes'] == $user['codpes']) {
                    return $user['level'];
                }
            }
        }
        return false; // retorna false se não tiver acesso
    }

}
