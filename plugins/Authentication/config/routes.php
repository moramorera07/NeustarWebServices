<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'Authentication',
    ['path' => '/api'],
    function (RouteBuilder $routes) {

        $settingsPost = function($action, $controller)
        {
            return [
                'controller' => $controller,
                'action' => $action,
                'plugin' => 'Authentication',
                '_method' => 'POST'
            ];
        };

        $settingsGet = function($action, $controller)
        {
            return [
                'controller' => $controller,
                'action' => $action,
                'plugin' => 'Authentication',
                '_method' => 'GET'
            ];
        };

        $routes->extensions(['json', 'xml']);

        $routes->connect('/auth/login', $settingsPost('login','Authentication'));
    }
);
