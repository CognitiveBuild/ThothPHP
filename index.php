<?php

require 'vendor/autoload.php';
require 'inc/db.php';

$app = new Slim\App();

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('views/');
};

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.php', [
        'message' => 'Hello World'
    ]);
})->setName('Hello');

$app->get('/api/v1/catalog/{q}', function ($request, $response, $args) {
    $q = isset($args['q']) ? $args['q'] : 'INDUSTRY';
    $list = db::query('SELECT `id`, `name` FROM `catalog` WHERE `key` = ?;', $q);
    $result = json_encode($list);
    return $response->write($result);
});

$app->run();