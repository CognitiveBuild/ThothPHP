<?php

require 'vendor/autoload.php';
include 'inc/db.php';

if(isset($_ENV["VCAP_SERVICES"]) === FALSE) {
    $env = new Dotenv\Dotenv(__DIR__);
    $env->load();
}

$app = new Slim\App();

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('views/');
};

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.php', [
        'message' => 'This is Thoth Asset Center'
    ]);
});

$app->get('/api/v1/catalog/{q}', function ($request, $response, $args) {
    $q = isset($args['q']) ? $args['q'] : 'INDUSTRY';
    $list = db::query('SELECT `id`, `name` FROM `catalog` WHERE `key` = ?;', $q);
    $result = json_encode($list);
    return $response->withHeader('Content-Type', 'application/json')->write($result);
});

$app->run();