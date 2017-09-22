<?php
date_default_timezone_set('PRC');

define('INSTANCE', '_INST_DEFAULT');

define("KEY_INDUSTRY", "INDUSTRY");
define("KEY_TECHNOLOGY", "TECHNOLOGY");

define("OPTION_YES", "Y");
define("OPTION_NO", "N");

define('PRODUCTION_HOST', 'thoth-assets.mybluemix.net');
define('TRANSLATION_DIR', str_replace('\\', DIRECTORY_SEPARATOR, dirname(__FILE__)));
define('DEFAULT_LANGUAGE', 'en-us');

require 'vendor/autoload.php';
require 'inc/translations/translator.php';
/// Pear
// Pager
include 'inc/pear/Pager.php';
/// Utilities
// Common
include 'inc/utilities/common-utility.php';
// Database
include 'inc/db.php';
// Models
// User model
include 'inc/models/user-model.php';
// Build model
include 'inc/models/build-model.php';
// Managers
include 'inc/managers/session-manager.php';
include 'inc/managers/asset-manager.php';
include 'inc/managers/catalog-manager.php';
include 'inc/managers/company-manager.php';
include 'inc/managers/visitor-manager.php';
include 'inc/managers/event-manager.php';
include 'inc/managers/distribution-manager.php';
// Controllers
include 'inc/controllers/abstract-controller.php';
include 'inc/controllers/common-controller.php';
include 'inc/controllers/api-controller.php';
include 'inc/controllers/app-controller.php';
include 'inc/controllers/event-controller.php';
include 'inc/controllers/asset-controller.php';

if(isset($_ENV["VCAP_SERVICES"]) === FALSE) {
    $env = new Dotenv\Dotenv(__DIR__);
    $env->load();
    define("HOST_NAME", PRODUCTION_HOST);
    define('DEBUG', TRUE);
}
else {
    define("HOST_NAME", CommonUtility::getServerVar('HTTP_HOST', PRODUCTION_HOST));
    define('DEBUG', FALSE);
}

$_language = CommonUtility::getAcceptedLanguage();
CommonUtility::loadTranslation($_language);

function translate($var = '', $args = NULL, $language = LANGUAGE) { return CommonUtility::getTranslation($var, $args, $language); }

define('LANGUAGE', $_language, FALSE);

$app = new Slim\App();

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('inc/views/');
};

// Override the default Not Found Handler
$container['notFoundHandler'] = function ($cf) {

    return function ($request, $response) use ($cf) {

        $newResponse = $cf['response']
        ->withStatus(404)
        ->withHeader('Content-Type', 'text/html');
        if(SessionManager::validate()) {
            $newResponse->write('404');
            return $newResponse;
        }

        $login = '';
        $passcode = '';
        $message = '';

        return $cf->view->render($newResponse, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => $message
        ]);
    };
};

// Sign in post
$app->post('/', CommonController::get('postHome'));
$app->get('/api/v1/test/image', CommonController::get('getImage'));

// App download entry
$app->get('/app/{id}', CommonController::get('getApp'));
// QR Code API
$app->get('/api/v1/build/code/{id}', CommonController::get('getQRCode'));
// Get meta data of build (iOS .plist)
$app->get('/api/v1/build/meta/{id}', CommonController::get('getMeta'));
// Download build
$app->get('/api/v1/build/download/{idbuild}', CommonController::get('getBuild'));

if(SessionManager::validate()) {
    // Home
    $app->get('/', CommonController::get('getHome'));
    // View or update Settings
    $app->get('/settings', CommonController::get('getSettings'));
    $app->post('/settings', CommonController::get('postSettings'));

    // Visitors selected by event
    $app->get('/api/v1/visitors/company/{idcompany}/event/{id}', APIController::get('getVisitorsByEvent'));

    if(Session::init()->getUser()->getLogin() === 'mihui') {
        // Add or Catalogs
        $app->post('/api/v1/catalog', APIController::get('postCatalog'));
        // Assets
        $app->get('/assets', AssetController::get('getAssets'));
        // Catalogs
        $app->get('/catalog/{name}', AssetController::get('getCatalog'));
        // Asset update
        $app->post('/assets/{id}', AssetController::get('postAsset'));
        // Asset details
        $app->get('/assets/{id}', AssetController::get('getAsset'))->setName('asset-details');

        /// Events
        // Companies
        $app->get('/companies', EventController::get('getCompanies'));
        // Company details
        $app->get('/companies/{id}', EventController::get('getCompany'));
        // Company update
        $app->post('/companies/{id}', EventController::get('postCompany'));
        // Visitors
        $app->get('/visitors', EventController::get('getVisitors'));
        // Visitor details
        $app->get('/visitors/{id}', EventController::get('getVisitor'));
        // Visitor update
        $app->post('/visitors/{id}', EventController::get('postVisitor'));
        // Events
        $app->get('/events', EventController::get('getEvents'));
        // Event details
        $app->get('/events/{id}', EventController::get('getEvent'));
        // Add or update Event
        $app->post('/events/{id}', EventController::get('postEvent'));

        /// Private APIs
        // Delete attachment
        $app->delete('/api/v1/assets/attachment/{id}', APIController::get('deleteAssetAttachment'));
        // Delete logo
        $app->delete('/api/v1/companies/logo/{id}', APIController::get('deleteCompanyLogo'));
        // Visitor avatar delete
        $app->delete('/api/v1/visitors/avatar/{id}', APIController::get('deleteVisitorAvatar'));
        // Timeline delete
        $app->delete('/api/v1/timelines/{id}', APIController::get('deleteTimeline'));
    }

    // App list
    $app->get('/apps', AppController::get('getApps'));
    // App details
    $app->get('/apps/{id}', AppController::get('getApp'));
    // Add or Update app
    $app->post('/apps/{id}', AppController::get('postApp'));
    // Distribution list
    $app->get('/apps/{idapp}/distribute', AppController::get('getDistribute'));
    // Add or update distribution
    $app->post('/apps/{idapp}/distribute', AppController::get('postDistribute'));
    // Build information
    $app->get('/apps/{idapp}/builds/{idbuild}', AppController::get('getAppBuild'));
    // Add or Upload build
    $app->post('/apps/{idapp}/builds/{idbuild}', AppController::get('postAppBuild'));
    // Delete a build
    $app->delete('/api/v1/apps/{idapp}/builds/{idbuild}', APIController::get('deleteAppBuild'));

    // Sign out
    $app->get('/signout', APIController::get('getSignedOut'));

    $app->get('/api/v1/info', function ($request, $response, $args) {
        phpinfo();
    });
}
else {
    $app->get('/', APIController::get('getUnAuthorizedHome'));
    $app->get('/signout', APIController::get('getSignOut'));
}

/// Public APIs
// Catalogs by catalog keyword
$app->get('/api/v1/catalog/{q}', APIController::get('getCatalogs'));
// Attachment render
$app->get('/api/v1/assets/attachment/{id}', APIController::get('getAttachment'));
// Query assets by catalog, INDUSTRY | TECHNOLOGY
$app->get('/api/v1/assets/catalog/{catalog}/name/{name}', APIController::get('getAssetsByCatalogName'));
// Query assets by company
$app->get('/api/v1/assets/company/id/{id}', APIController::get('getAssetsByCompanyId'));
// Query assets by catalog id
$app->get('/api/v1/assets/catalog/{catalog}/id/{id}', APIController::get('getAssetsByCatalogId'));

/// Company
// Get logo image
$app->get('/api/v1/companies/logo/{id}', APIController::get('getCompanyLogo'));
// Event of today, Watson uses it
$app->get('/api/v1/event/today', APIController::get('getEventToday'));
// Visitor avatar
$app->get('/api/v1/visitor/avatar/{id}', APIController::get('getVisitorAvatar'));

// unused
// $app->delete('/api/v1/assets/{id}', function ($request, $response, $args) {

//     $id = isset($args['id']) ? $args['id'] : 0;
//     $result = AssetManager::deleteAsset($id);

//     return $this->view->render($response, 'asset.php', [
//         'asset' => $result
//     ]);
// });

$app->run();