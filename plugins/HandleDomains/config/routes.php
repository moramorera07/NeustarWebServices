<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'HandleDomains',
    ['path' => '/api'],
    function (RouteBuilder $routes) {
        $settingsPost = function($action,$controller)
        {
            return [
                'controller' => $controller,
                'action' => $action,
                'plugin' => 'HandleDomains',
                '_method' => 'POST'
            ];
        };

        $settingsGet = function($action,$controller)
        {
            return [
                'controller' => $controller,
                'action' => $action,
                'plugin' => 'HandleDomains',
                '_method' => 'GET'
            ];
        };

        $routes->extensions(['json', 'xml']);

        $routes->connect('/domain/add', $settingsPost('add','HandleDomains'));

    }
);
