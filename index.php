<?php

require 'vendor/autoload.php';

$env = new Dotenv\Dotenv(__DIR__);

// Bluemix
if(isset($_ENV["VCAP_SERVICES"])) {
	$vcap_services = json_decode($_ENV["VCAP_SERVICES" ]);
    if($vcap_services->{'compose-for-mysql'}) {
        $credentials = $vcap_services->{'compose-for-mysql'}[0]->credentials;

		$_ENV['MYSQL_HOST'] = $credentials->hostname;
		$_ENV['MYSQL_PORT'] = $credentials->port;
		$_ENV['MYSQL_USERNAME'] = $credentials->username; 
		$_ENV['MYSQL_PASSWORD'] = $credentials->password;
    }
    else { 
        echo "Error: No suitable MySQL database bound to the application. <br>";
        die();
    }
}
// Localhost
else {
    $env->load();
}

include 'inc/db.php';

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