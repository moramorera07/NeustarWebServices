<?php

namespace Authentication\Controller;

use App\Controller\AppController as BaseController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\Validation\Validator;
use Firebase\JWT\JWT;

/**
 * This controller contains the basic methods and configurations for the authentication plugin
 */
class AppController extends BaseController
{
    /**
     * Extended constructor for configuring components of the plugin
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate' => [
                'Form' => [
                    //'scope' => ['Users.active' => 1],
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password'
                    ]
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'users',
                    //'scope' => ['Users.active' => 1],
                    'fields' => [
                        'username' => 'username'
                    ],
                    'queryDatasource' => true
                ]
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => false,
            'authError' => 'server:api.auth_controller.error.authentication'
        ]);
    }

    /**
     * Standard success response
     *
     * @param string $msg message of the response
     * @param string|array $output if is necessary output data of the response
     *
     * @return Response 200
     *
     */
    public function success($msg, $output = array('@#EWC$$F@'))
    {
        $body = $this->response->getBody();

        if (!$output) $output = [];
        else
            if (is_array($output))
                if (in_array('@#EWC$$F@', $output, true)) $output = 200;

        $tokenHeader = $this->request->getHeader('UpdateToken');
        $token = $tokenHeader ? $tokenHeader[0] : '';
        $data = json_encode([
            'status' => 'ok',
            'output' => $output,
            'message' => $msg,
            'token' => $token,
        ]);

        $body->write($data);

        return $this->response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Standard error response
     *
     * @param string $msg message of the response
     *
     * @param integer $output reponse output
     *
     * @return Response 401
     *
     */
    public function error($msg, $output = 401)
    {
        $body = $this->response->getBody();

        $tokenHeader = $this->request->getHeader('UpdateToken');
        $token = $tokenHeader ? $tokenHeader[0] : '';
        $data = json_encode([
            'status' => 'error',
            'output' => $output,
            'message' => $msg,
            'token' => $token,
        ]);

        $body->write($data);

        return $this->response->withHeader('Content-Type', 'application/json');
    }


    /**
     * Create validator object for specific attributes
     *
     * @param array $attributes attributes to be validated
     *
     * @return Validator
     *
     */
    public function validationDefault($attributes = null)
    {
        if (!$attributes)
            $attributes = [
                'email',
                'full_name'
            ];
        $validator = new Validator();
        $validator
            ->requirePresence($attributes)
            ->notEmpty($attributes);

        if (in_array('email', $attributes))
            $validator->add('email', [
                'email' => [
                    'rule' => ['email']
                ]
            ]);

        return $validator;
    }
}

