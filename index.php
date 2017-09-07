<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

include 'inc/db.php';

// Managers
include 'inc/managers/asset-manager.php';
include 'inc/managers/catalog-manager.php';
include 'inc/managers/company-manager.php';
include 'inc/managers/visitor-manager.php';
include 'inc/managers/event-manager.php';

define("KEY_INDUSTRY", "INDUSTRY");
define("KEY_TECHNOLOGY", "TECHNOLOGY");

define("OPTION_YES", "Y");
define("OPTION_NO", "N");

date_default_timezone_set('PRC');

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

// Assets
$app->get('/assets', function ($request, $response, $args) {

    $list = AssetManager::getAssets();

    return $this->view->render($response, 'assets.php', [
        'assets' => $list
    ]);
});
$app->get('/download', function ($request, $response, $args) {

    $bundle = 'com.ibm.cio.be.ifundit.platform.mobile';

    return $this->view->render($response, 'download.php', [
        'bundle' => $bundle
    ]);
});

// Companies
$app->get('/companies', function ($request, $response, $args) {

    $list = CompanyManager::getCompanies();

    return $this->view->render($response, 'companies.php', [
        'companies' => $list
    ]);
});

// Company details
$app->get('/companies/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $industries = CatalogManager::getCatalog(KEY_INDUSTRY);

    $result = array(
        'id' => 0, 
        'name' => '', 
        'description' => '', 
        'logo' => NULL, 
        'idindustry' => 0
    );

    if($id > 0) {
        $result = CompanyManager::getCompany($id);
    }

    return $this->view->render($response, 'company.php', [
        'id' => $id, 
        'company' => $result, 
        'industries' => $industries
    ]);
});

// Company update
$app->post('/companies/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $post = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    $logo = NULL;
    if(isset($files['logo']) && count($files['logo']) === 1) {
        $size = $files['logo'][0]->getSize();
        if($size > 0) {
            $logo = file_get_contents($files['logo'][0]->file);
        }
    }
    $isNew = TRUE;
    if($id > 0) {
        CompanyManager::updateCompany($id, $post['name'], $post['idindustry'], $post['description']);
        $isNew = FALSE;
    }
    else {
        $id = CompanyManager::addCompany($post['name'], $post['idindustry'], $post['description']);
    }

    if($id > 0 && $logo !== NULL) {
        CompanyManager::updateLogo($id, $logo);
    }

    if($isNew) {
        return $response->withStatus(200)->withHeader('Location', "/companies");
    }

    return $response->withStatus(200)->withHeader('Location', "/companies/{$id}");
});

// Catalogs
$app->get('/catalog/{name}', function ($request, $response, $args) {

    $result = array();

    $name = isset($args['name']) ? $args['name'] : KEY_INDUSTRY;

    $result = CatalogManager::getCatalogWithAssetCount($name);

    return $this->view->render($response, 'catalog.php', [
        'catalogs' => $result, 
        'type' => $name
    ]);
});

// Asset update
$app->post('/assets/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $post = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    $images = $files['binary'];
    $technologies = $post['technology'];

    if($id > 0) {
        // update asset
        $result = AssetManager::updateAsset($id, $post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl']);

        AssetManager::deleteCatalogToAsset($id);
        foreach($technologies as $idcatalog) {
            AssetManager::addCatalogToAsset(KEY_TECHNOLOGY, $id, $idcatalog);
        }

        AssetManager::addFiles($id, $images);
        AssetManager::updateFileIds($id);

        return $response->withStatus(200)->withHeader('Location', "/assets/{$id}");
    }
    // insert asset
    $id = AssetManager::addAsset($post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl']);

    if($id > 0) {
        AssetManager::deleteCatalogToAsset($id);
        foreach($technologies as $idcatalog) {
            AssetManager::addCatalogToAsset(KEY_TECHNOLOGY, $id, $idcatalog);
        }

        AssetManager::addFiles($id, $images);
        AssetManager::updateFileIds($id);
    }

    return $response->withStatus(200)->withHeader('Location', "/assets");
});

// Asset details
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

    $industries = CatalogManager::getCatalog(KEY_INDUSTRY);
    $technologies = CatalogManager::getCatalog(KEY_TECHNOLOGY);
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

// Visitors
$app->get('/visitors', function ($request, $response, $args) {

    $list = VisitorManager::getVisitors();

    return $this->view->render($response, 'visitors.php', [
        'visitors' => $list
    ]);
});

// Visitor details
$app->get('/visitors/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $result = [
        'id' => 0, 
        'firstname' => '', 
        'lastname' => '', 
        'idcompany' => 0, 
        'website' => '', 
        'linkedin' => '', 
        'facebook' => '',
        'twitter' => ''
    ];

    $visitor = VisitorManager::getVisitor($id);
    $companies = CompanyManager::getCompanies();

    return $this->view->render($response, 'visitor.php', [
        'id' => $id, 
        'visitor' => $visitor, 
        'companies' => $companies
    ]);
});

// Visitor update
$app->post('/visitors/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $post = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    $avatar = NULL;
    if(isset($files['avatar']) && count($files['avatar']) === 1) {
        $size = $files['avatar'][0]->getSize();
        if($size > 0) {
            $avatar = file_get_contents($files['avatar'][0]->file);
        }
    }
    $isNew = TRUE;
    if($id > 0) {
        $isNew = FALSE;
        VisitorManager::updateVisitor($id, $post['firstname'], $post['lastname'], $post['idcompany'], $post['website'], $post['linkedin'], $post['facebook'], $post['twitter'], $post['order']);
    }
    else {
        $id = VisitorManager::addVisitor($post['firstname'], $post['lastname'], $post['idcompany'], $post['website'], $post['linkedin'], $post['facebook'], $post['twitter'], $post['order']);
    }

    if($id > 0 && $avatar !== NULL) {
        VisitorManager::updateAvatar($id, $avatar);
    }

    if($isNew) {
        return $response->withStatus(200)->withHeader('Location', "/visitors");
    }

    return $response->withStatus(200)->withHeader('Location', "/visitors/{$id}");
});

// Events
$app->get('/events', function ($request, $response, $args) {

    $list = EventManager::getEvents();

    return $this->view->render($response, 'events.php', [
        'events' => $list
    ]);
});

// Event details
$app->get('/events/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $today = date("Y-m-d");
    $result = [
        'id' => 0, 
        'visitdate' => $today, 
        'idcompany' => 0, 
        'isactive' => 'Y', 
        'displayas' => ''
    ];

    $companies = CompanyManager::getCompanies();

    $timelines = EventManager::getTimelinesByEventId($id);

    if($id > 0) {
        $result = EventManager::getEvent($id);
    }

    return $this->view->render($response, 'event.php', [
        'id' => $id, 
        'event' => $result, 
        'companies' => $companies, 
        'timelines' => $timelines
    ]);
});

// Event update
$app->post('/events/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $post = $request->getParsedBody();

    $visitors = isset($post['idvisitor']) ? $post['idvisitor'] : array();

    $timestarts = isset($post['timeline_timestart']) ? $post['timeline_timestart'] : array();
    $timeends = isset($post['timeline_timeend']) ? $post['timeline_timeend'] : array(); 
    $activitis = isset($post['timeline_activity']) ? $post['timeline_activity'] : array();
    $isactive = isset($post['isactive']) ? $post['isactive'] : OPTION_NO;

    $isNew = TRUE;

    // Make sure there is no conflict events on same date
    if($isactive === OPTION_YES) {
        EventManager::deactivateEvent();
    }

    if($id > 0) {
        $isNew = FALSE;
        EventManager::updateEvent($id, $post['visitdate'], $post['displayas'], $post['idcompany'], $isactive);
    }
    else {
        $id = EventManager::addEvent($post['visitdate'], $post['displayas'], $post['idcompany'], $isactive);
    }

    EventManager::delteVisitorByEventId($id);
    foreach($visitors as $key => $idvisitor) {
        EventManager::addVisitorByEventId($id, $idvisitor);
    }

    EventManager::deleteTimelineByEventId($id);
    foreach($timestarts as $key => $time_start) {

        $time_start = $timestarts[$key];
        $time_end = $timeends[$key];
        $activity = $activitis[$key];

        EventManager::addTimeline($id, $time_start, $time_end, $activity);
    }

    if($isNew) {
        return $response->withStatus(200)->withHeader('Location', "/events");
    }

    return $response->withStatus(200)->withHeader('Location', "/events/{$id}");
});








/// APIs

// Catalogs by query
$app->get('/api/v1/catalog/{q}', function ($request, $response, $args) {

    $q = isset($args['q']) ? $args['q'] : KEY_INDUSTRY;
    $list = CatalogManager::getCatalog($q);
    return $response->withJson($list);
});
// Catalogs
$app->post('/api/v1/catalog', function ($request, $response, $args) {
    $post = $request->getParsedBody();

    $id = isset($post['id']) ? $post['id'] : 0;
    $key = isset($post['type']) ? $post['type'] : KEY_INDUSTRY;
    $name = isset($post['name']) ? $post['name'] : '';

    if($name === '') {
        return $response->withStatus(400)->withJson(array('status' => 400, 'id' => $id));
    }

    if($id === 0 || $id === '0') {
        $id = CatalogManager::addCatalog($name, $key);
    }
    else {
        CatalogManager::updateCatalog($key, $id, $name);
    }

    return $response->withStatus(200)->withJson(array('status' => 200, 'id' => $id));
});
// Delete attachment
$app->delete('/api/v1/assets/attachment/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $file = AssetManager::readFile($id);
    $result = FALSE;
    if(isset($file['id'])) {
        $result = AssetManager::deleteFile($id);
        AssetManager::updateFileIds($file['idasset']);
    }

    return $response->withJson(array('status' => $result));
});
// Attachment render
$app->get('/api/v1/assets/attachment/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = AssetManager::readFile($id);

    return $response->withHeader('Content-Type', $result['type'])->write($result['binary']);
});
// Query assets by catalog, INDUSTRY | TECHNOLOGY
$app->get('/api/v1/assets/catalog/{catalog}/name/{name}', function ($request, $response, $args) {

    $catalog = isset($args['catalog']) ? $args['catalog'] : KEY_INDUSTRY;
    $name = isset($args['name']) ? $args['name'] : '';

    $list = AssetManager::getAssetsByCatalogName($catalog, $name);
    return $response->withJson($list);
});
// Query assets by company
$app->get('/api/v1/assets/company/id/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $list = AssetManager::getAssetsByCompanyId($id);
    return $response->withJson($list);
});
// Query assets by catalog id
$app->get('/api/v1/assets/catalog/{catalog}/id/{id}', function ($request, $response, $args) {

    $catalog = isset($args['catalog']) ? $args['catalog'] : KEY_INDUSTRY;
    $id = isset($args['id']) ? $args['id'] : 0;

    $list = AssetManager::getAssetsByCatalogId($catalog, $id);
    return $response->withJson($list);
});

// Company

// Get logo image
$app->get('/api/v1/companies/logo/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = CompanyManager::getCompany($id);

    return $response->write($result['logo']);
});
// Delete logo
$app->delete('/api/v1/companies/logo/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = CompanyManager::updateLogo($id, NULL);

    return $response->withJson(array('status' => $result));
});
// Visitors selected by event
$app->get('/api/v1/visitors/company/{idcompany}/event/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $idcompany = isset($args['idcompany']) ? $args['idcompany'] : 0;
    // Get visitors and selected IDs
    //$all = VisitorManager::getVisitorsByCompanyId($idcompany);
    $all = VisitorManager::getVisitorsForEvent();
    $selected = EventManager::getVisitorsByEventId($id);

    return $response->withJson(array('all' => $all, 'selected' => $selected));
});
// Event of today, Watson uses it
$app->get('/api/v1/event/today', function ($request, $response, $args) {

    $event = EventManager::getEventOfToday();
    $visitors = VisitorManager::getVisitorsOfToday();
    if($visitors === FALSE) {
        $visitors = array();
    }
    if($event === FALSE) {
        $event = new stdClass();
    }
    return $response->withJson(array( 'event' => $event, 'visitors' => $visitors ));
});
// Visitor avatar
$app->get('/api/v1/visitor/avatar/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = VisitorManager::getVisitor($id);

    return $response->write($result['avatar']);
});
// Visitor avatar delete
$app->delete('/api/v1/visitors/avatar/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = VisitorManager::updateAvatar($id, NULL);

    return $response->withJson(array('status' => $result));
});
// Timeline delete
$app->delete('/api/v1/timelines/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = EventManager::deleteTimelineById($id);

    return $response->withJson(array('status' => $result));
});
$app->get('/api/v1/download', function ($request, $response, $args) {
    $id = $request->getQueryParam('id', 'com.ibm.cio.be.ifundit.platform.mobile');
    $client = new Client();
    try {
        // Send out HTTP request
        $result = $client->request('POST', 'https://identity.open.softlayer.com/v3/auth/tokens', [
            'json' => [
                'auth' => [
                    'identity' => [
                        'methods' => [ 'password' ], 
                        'password' => [ 
                            'user' => [
                                'id' => '6083522eae4b4bad9d7ea9deee2df6f4', 
                                'password' => 'wx{WB#Ncd0j6]X#n'
                            ]
                        ]
                    ], 
                    'scope' => [
                        'project' => [
                            'id' => 'd92fbca7a9574e94b1c3d6c5a1283a6d'
                        ]
                    ]
                ]
            ], 
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        
        $content = $result->getBody()->getContents();
        $headers = $result->getHeader('X-Subject-Token');
        $json = json_decode($content, TRUE);

        //echo '<pre>';
        // echo $content;die;

        $base = '';
        $token = $headers[0];

        $catalogs = $json['token']['catalog'];
        foreach($catalogs as $key => $val) {
            $endpoints = $val['endpoints'];
            if('object-store' === $val['type']) {
                foreach($endpoints as $endpointKey => $endpoint) {
                    if($endpoint['region'] === 'dallas' && $endpoint['interface'] == 'public') {
                        $base = $endpoint['url'];
                        break;
                    }
                }
            }
        }
        $binaryUrl = "{$base}/builds/{$id}.ipa";
        // echo $binaryUrl;die;
        $result = $client->request('GET', $binaryUrl, [
            'headers' => [
                'X-Auth-Token' => $token
            ]
        ]);
        $fileName = "attachment; filename=\"{$id}.ipa\"";
        $response->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', $fileName)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ;
        echo $result;
        // echo $result;
    }
    catch (RequestException $e) {
        $message = $e->getMessage();
        if ($e->hasResponse()) {
            $message = Psr7\str($e->getResponse());
        }
        echo $message;
    }
    
});
$app->get('/api/v1/download/meta', function ($request, $response, $args) {

    $id = $request->getQueryParam('id', 'com.ibm.cio.be.ifundit.platform.mobile');
    $xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>https://thoth-assets.mybluemix.net/api/v1/download/{$id}</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>{$id}</string>
                <key>bundle-version</key>
                <string>1.0</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>iFundIT</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
EOT;

    return $response->withStatus(200)->withHeader('Content-Type', "application/xml")->write($xml);
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