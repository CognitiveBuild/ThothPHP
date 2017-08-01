<?php
require 'vendor/autoload.php';
include 'inc/db.php';
include 'inc/managers/asset-manager.php';

define("KEY_INDUSTRY", "INDUSTRY");
define("KEY_TECHNOLOGY", "TECHNOLOGY");

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

$app->get('/assets', function ($request, $response, $args) {

    $list = AssetManager::getAssets();

    return $this->view->render($response, 'assets.php', [
        'assets' => $list
    ]);
});

// POST asset
$app->post('/assets/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $post = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    $images = $files['binary'];
    $technologies = $post['technology'];

    if($id > 0) {
        // update asset
        $result = AssetManager::updateAsset($id, $post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl']);

        AssetManager::deleteCatalog($id);
        foreach($technologies as $idcatalog) {
            AssetManager::addCatalog(KEY_TECHNOLOGY, $id, $idcatalog);
        }

        AssetManager::addFiles($id, $images);

        return $response->withStatus(200)->withHeader('Location', "/assets/{$id}");
    }
    // insert asset
    $id = AssetManager::addAsset($post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl']);

    if($id > 0) {
        AssetManager::deleteCatalog($id);
        foreach($technologies as $idcatalog) {
            AssetManager::addCatalog(KEY_TECHNOLOGY, $id, $idcatalog);
        }

        AssetManager::addFiles($id, $images);
    }
    return $response->withStatus(200)->withHeader('Location', "/assets");
});

$app->get('/assets/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $result = [
        'id' => 0, 
        'name' => '', 
        'description' => '', 
        'idindustry' => 0, 
        'logourl' => '',
        'linkurl' => '',
        'videourl' => ''
    ];

    $industries = AssetManager::getCatalog(KEY_INDUSTRY);
    $technologies = AssetManager::getCatalog(KEY_TECHNOLOGY);
    $technologies_applied = array();
    $attachments = array();

    if($id > 0) {
        $result = db::queryFirst('SELECT `*` FROM `asset` WHERE `id` = ? ORDER BY `id` DESC;', $id);
        $technologies_applied = db::query('SELECT `idcatalog` FROM `catalog_to_asset` WHERE `key` = "'.KEY_TECHNOLOGY.'" AND `idasset` = ?;', $id);
        $attachments = db::query('SELECT `*` FROM `asset_to_file` WHERE `idasset` = ?;', $id);
    }

    return $this->view->render($response, 'asset.php', [
        'id' => $id, 
        'asset' => $result, 
        'industries' => $industries, 
        'technologies' => $technologies, 
        'technologies_applied' => $technologies_applied, 
        'attachments' => $attachments
    ]);
})->setName('asset-details');






/// APIs

// catalog
$app->get('/api/v1/catalog/{q}', function ($request, $response, $args) {

    $q = isset($args['q']) ? $args['q'] : KEY_INDUSTRY;
    $list = AssetManager::getCatalog($q);
    return $response->withJson($list);
});
// delete attachment
$app->delete('/api/v1/assets/attachment/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = AssetManager::deleteFile($id);

    return $response->withJson(array('status' => $result));
});
// get attachment rendered
$app->get('/api/v1/assets/attachment/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = AssetManager::readFile($id);

    return $response->withHeader('Content-Type', $result['type'])->write($result['binary']);
});
// query assets by catalog, INDUSTRY | TECHNOLOGY
$app->get('/api/v1/assets/catalog/{catalog}/name/{name}', function ($request, $response, $args) {

    $catalog = isset($args['catalog']) ? $args['catalog'] : KEY_INDUSTRY;
    $name = isset($args['name']) ? $args['name'] : '';

    $list = AssetManager::getAssetsByCatalogName($catalog, $name);
    return $response->withJson($list);
});
// query assets by catalog id
$app->get('/api/v1/assets/catalog/{catalog}/id/{id}', function ($request, $response, $args) {

    $catalog = isset($args['catalog']) ? $args['catalog'] : KEY_INDUSTRY;
    $id = isset($args['id']) ? $args['id'] : 0;

    $list = AssetManager::getAssetsByCatalogId($catalog, $id);
    return $response->withJson($list);
});
// unused
$app->delete('/api/v1/assets/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = AssetManager::deleteAsset($id);

    return $this->view->render($response, 'asset.php', [
        'asset' => $result
    ]);
});

$app->run();