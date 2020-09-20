<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 02/05/17
 * Time: 12:05 PM
 */

namespace Authentication\Middleware;

use Cake\Core\Configure;
use Cake\Utility\Security;
use Exception;
use Firebase\JWT\JWT;
use Cake\ORM\TableRegistry;
use Zend\Cache\Exception\UnexpectedValueException;

class JwtMiddleware
{
    private $_LOGTAG = "server:api.auth_controller.";

    public function __invoke($request, $response, $next)
    {

        $request->session()->write('token', '');
        $token = str_replace('Bearer ', '', $request->getHeader('Authorization'));
        $body = $response->getBody();
        $env = Configure::read('App')['env'];
        $saltKey = Security::salt();

        if ($token) {
            $token = $token[0];
            try {
                JWT::decode($token, $saltKey, ['HS256']);
                $payload = $this->tokenPayload($token);
                $request->session()->write('USER_' . $payload->sub . '_LAST', time());
            } catch (Exception $e) {
                switch ($e->getMessage()) {
                    case 'Expired token':
                        if ($env['EXPIRE_TOKEN']){
                            $body->write($this->error(["token" => $this->_LOGTAG . 'error.expired_token']));
                        }
                        break;
                    case 'Syntax error, malformed JSON':
                        $body->write($this->error(["token" => $this->_LOGTAG . 'error.format_token']));
                        break;
                    case 'Signature verification failed':
                        $body->write($this->error(["token" => $this->_LOGTAG . 'error.format_token']));
                        break;
                    case 'Wrong number of segments':
                        $body->write($this->error(["token" => $this->_LOGTAG . 'error.format_token']));
                        break;
                    case 'Unexpected control character found':
                        $body->write($this->error(["token" => $this->_LOGTAG . 'error.format_token']));
                        break;
                }
                return $response->withHeader('Content-Type', 'application/json');
            }
        } else {
            if (!in_array(str_replace('api/', '', $request->url), $env['ALLOWED_ROUTES'])) {
                $body->write($this->error(["token" => $this->_LOGTAG . 'error.authentication']));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        return $next($request, $response);
    }

    private function error($msg)
    {
        return json_encode([
            'status' => 'error',
            'output' => 401,
            'message' => $msg,
        ]);
    }

    private function tokenPayload($token)
    {
        $tks = explode('.', $token);
        if (count($tks) != 3) {
            throw new UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $payload = JWT::urlsafeB64Decode($bodyb64);
        return json_decode($payload);
    }

}