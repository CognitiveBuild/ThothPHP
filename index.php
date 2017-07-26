<?php

require 'vendor/autoload.php';

$env = new Dotenv\Dotenv(__DIR__);

try {
    $env->load();
    include 'inc/db.php';
} catch (Exception $e) {
    die("No .env file found");
}

$app = new Slim\App();

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('templates/');
};

$app->get('/hello/world', function ($request, $response, $args) {
    $list = db::query("SELECT * FROM catalog;");
    return $this->view->render($response, 'index.php', [
        'list' => $list
    ]);
})->setName('Profile');

$app->get('/api/{name}', function ($request, $response, $args) {
    return $response->write("Hello, " . $args['name']);
});

$app->run();