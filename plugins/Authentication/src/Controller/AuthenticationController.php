<?php

/**
 * Authentication Controller
 */

namespace Authentication\Controller;

use Cake\I18n\Date;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Exception;
use Firebase\JWT\JWT;
use Cake\Http\Response;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Cake\Http\ServerRequest;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Driver\Sqlite;

/**
 * This controller is in charge of the authentication web services
 */
class AuthenticationController extends AppController
{
    /**
     * @var string Tag for success and error messages of the front-end
     */
    private $_LOGTAG = "server:api.auth_controller.";

    /**
     * Extended constructor for adding routes without authentication
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'login'
        ]);
    }

    /**
     * Authenticate registered user | /api/auth/login
     *
     * @uses ServerRequest ["password", "email"]
     *
     * @return Response ["token", "full_name", "user_id", "avatar"]
     *
     */
    public function login()
    {
        $env = Configure::read('App')['env'];
        $validator = $this->validationDefault(['username', 'password']);
        $data = $this->request->getData();

        if ($validator->errors($data))
            return $this->error($this->_LOGTAG . 'error.validation');


        $tUsers = TableRegistry::get('users');
        $user = $tUsers->find()->where(['username' => $data["username"]])->first();


        if (!$user) {
            return $this->error($this->_LOGTAG . 'error.validation_user');
        }else{
            if(!password_verify($data['password'], $user['password'])){
                return $this->error($this->_LOGTAG . 'error.password_user');
            }
        }

        $route = Router::url($this->here, true);

        // ----------- Begin: Groups & Permissions -----------

        error_log("Time: " + $env['JWT_EXP'] + "\n", 3, LOGS . 'error.log');


        $jwtToken = JWT::encode([
            'sub' => $user['id'],
            "iss" => $route,
            "aud" => $route,
            "iat" => time(),
            'exp' => time() + $env['JWT_EXP'],
        ],
            Security::salt());

        $output = [
            'token' => $jwtToken,
            'user_id' => $user['id'],
            'username' => $user['username']
        ];

        // Store in session de current user.
        $this->request->session()->write('USER_' . $user['id'] . '_TOKEN', $jwtToken);
        $this->request->session()->write('USER_' . $user['id'] . '_LAST', time());
        // Store in session de current user.

        return $this->success($this->_LOGTAG . 'success.login', $output);
    }
}
