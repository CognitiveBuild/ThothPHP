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
define('SUPPORTED_LANGUAGES', [ 'English (United States)' => 'en-us', 'Chinese (Simplified)' => 'zh-cn' ]);

require 'vendor/autoload.php';
require 'inc/translations/translator.php';
// Pear
include 'inc/pear/Pager.php';
// Utilities
include 'inc/utilities/common-utility.php';
// Database
include 'inc/db.php';
// Models
include 'inc/models/user-model.php';
include 'inc/models/build-model.php';
// Managers
include 'inc/managers/session-manager.php';
include 'inc/managers/asset-manager.php';
include 'inc/managers/catalog-manager.php';
include 'inc/managers/company-manager.php';
include 'inc/managers/visitor-manager.php';
include 'inc/managers/event-manager.php';
include 'inc/managers/distribution-manager.php';

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
    return new \Slim\Views\PhpRenderer('views/');
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

if(SessionManager::validate()) {

    $app->get('/', function ($request, $response, $args) {

        return $this->view->render($response, 'index.php', [
            'message' => translate('Welcome')
        ]);
    });

    $app->get('/settings', function ($request, $response, $args) {

        return $this->view->render($response, 'settings.php', [
            'message' => ''
        ]);
    });

    $app->post('/settings', function ($request, $response, $args) {
        $post = $request->getParsedBody();

        $language = $post['language'];

        $login = Session::init()->getUser()->getLogin();
        $result = UserManager::updateSettings($language, $login);

        if($result) {
            Session::init()->getUser()->setLanguage($language);
            Session::init()->setUser(Session::init()->getUser());
        }

        return $this->view->render($response, 'settings.php', [
            'language' => $language, 
            'message' => translate('You have successfully updated the settings, please re-visit the page to see the differences.')
        ]);
    });

    if(Session::init()->getUser()->getLogin() === 'mihui') {

        // Assets
        $app->get('/assets', function ($request, $response, $args) {

            $p = $request->getQueryParams();
            $language = isset($p['language']) ? $p['language'] : LANGUAGE;
            $list = AssetManager::getAssets($language);

            return $this->view->render($response, 'assets.php', [
                'assets' => $list, 
                'language' => $language
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
            $p = $request->getQueryParams();
            $l = isset($p['language']) ? $p['language'] : LANGUAGE;
            $industries = CatalogManager::getCatalog(KEY_INDUSTRY, $l);

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

            $result = [];

            $name = isset($args['name']) ? $args['name'] : KEY_INDUSTRY;
            $p = $request->getQueryParams();
            $language = isset($p['language']) ? $p['language'] : LANGUAGE;

            $result = CatalogManager::getCatalogWithAssetCount($name, $language);

            return $this->view->render($response, 'catalog.php', [
                'catalogs' => $result, 
                'type' => $name, 
                'language' => $language
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
                $result = AssetManager::updateAsset($id, $post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl'], $post['language']);

                AssetManager::deleteCatalogToAsset($id);
                foreach($technologies as $idcatalog) {
                    AssetManager::addCatalogToAsset(KEY_TECHNOLOGY, $id, $idcatalog);
                }

                AssetManager::addFiles($id, $images);
                AssetManager::updateFileIds($id);

                return $response->withStatus(200)->withHeader('Location', "/assets/{$id}");
            }
            // insert asset
            $id = AssetManager::addAsset($post['name'], $post['idindustry'], $post['description'], $post['logourl'], $post['videourl'], $post['linkurl'], $post['language']);

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
            $p = $request->getQueryParams();
            $l = isset($p['language']) ? $p['language'] : LANGUAGE;

            $result = [
                'id' => 0, 
                'name' => '', 
                'description' => '', 
                'idindustry' => 0, 
                'logourl' => '',
                'linkurl' => '',
                'videourl' => '', 
                'language' => $l
            ];

            $industries = CatalogManager::getCatalog(KEY_INDUSTRY, $l);
            $technologies = CatalogManager::getCatalog(KEY_TECHNOLOGY, $l);
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
                'attachments' => $attachments, 
                'language' => $l
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

    // Private APIs

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
        // Delete logo
        $app->delete('/api/v1/companies/logo/{id}', function ($request, $response, $args) {
            
                $id = isset($args['id']) ? $args['id'] : 0;
                $result = CompanyManager::updateLogo($id, NULL);
            
                return $response->withJson(array('status' => $result));
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
        
    }

    $app->get('/apps', function ($request, $response, $args) {
        
        $apps = DistributionManager::getApps();

        return $this->view->render($response, 'apps.php', [
            'apps' => $apps
        ]);
    });
    
    $app->get('/apps/{id}', function ($request, $response, $args) {
    
        $id = isset($args['id']) ? $args['id'] : 0;
        $appx = DistributionManager::getAppById($id);
        $builds = DistributionManager::getBuildsByAppId($id);
    
        return $this->view->render($response, 'app.php', [
            'app' => $appx, 
            'builds' => $builds
        ]);
    });
    
    $app->post('/apps/{id}', function ($request, $response, $args) {
    
        $isNew = FALSE;
        $id = isset($args['id']) ? $args['id'] : 0;
        $post = $request->getParsedBody();
        $builds = DistributionManager::getBuildsByAppId($id);
    
        $appx = new AppModel($id, $post['name'], $post['region'], $post['container']);
    
        if($id === AppModel::NEW_ID) {
            DistributionManager::addApp($appx);
            $isNew = TRUE;
        }
        else {
            DistributionManager::updateApp($appx);
        }
    
        if($isNew) {
            return $response->withStatus(200)->withHeader('Location', "/apps");
        }
    
        return $this->view->render($response, 'app.php', [
            'app' => $appx, 
            'builds' => $builds
        ]);
    });

    $app->get('/apps/{idapp}/builds/{idbuild}', function ($request, $response, $args) {
    
        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $idbuild = isset($args['idbuild']) ? $args['idbuild'] : 0;
    
        $appx = DistributionManager::getAppById($idapp);
        $build = DistributionManager::getBuildById($idbuild);
    
        return $this->view->render($response, 'app.build.php', [
            'app' => $appx, 
            'build' => $build, 
            'idapp' => $idapp, 
            'idbuild' => $idbuild
        ]);
    });
    
    // Upload build
    $app->post('/apps/{idapp}/builds/{idbuild}', function ($request, $response, $args) {
    
        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $idbuild = isset($args['idbuild']) ? $args['idbuild'] : 0;
    
        $appx = DistributionManager::getAppById($idapp);
    
        $post = $request->getParsedBody();
    
        $build = new BuildModel($idbuild, $idapp, $post['uid'], $post['display'], $post['platform'], $post['version'], $post['notes'], time());

        if($idbuild > 0) {
            DistributionManager::updateBuild($build);
        }
        else {
            $files = $request->getUploadedFiles();
            $builds = isset($files['binary']) ? $files['binary'] : NULL;
            if($builds === NULL) {
                return $this->view->render($response, 'app.build.php', [
                    'app' => $appx, 
                    'build' => $build, 
                    'idapp' => $idapp, 
                    'idbuild' => $idbuild
                ]);
            }
            DistributionManager::addFiles($appx, $build, $builds);
        }
    
        return $response->withStatus(200)->withHeader('Location', "/apps/{$idapp}");
    
        // return $this->view->render($response, 'upload.php', [
        //     'build' => $build
        // ]);
    });
    
    $app->delete('/api/v1/apps/{idapp}/builds/{idbuild}', function ($request, $response, $args) {
        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $idbuild = isset($args['idbuild']) ? $args['idbuild'] : 0;
        $appx = DistributionManager::getAppById($idapp);
        $build = DistributionManager::getBuildById($idbuild);
    
        DistributionManager::removeFile($appx, $build);
    
        return $response->withStatus(200)->withJson(array('status' => TRUE));
    });

    $app->get('/apps/{idapp}/distribute', function ($request, $response, $args) {

        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $query = $request->getQueryParams();
        $idbuild = isset($query['idbuild']) ? $query['idbuild'] : 0;
        $builds = DistributionManager::getBuildsByAppId($idapp);
    
        $appx = DistributionManager::getAppById($idapp);
    
        return $this->view->render($response, 'app.distribute.php', [
            'app' => $appx, 
            'idbuild' => $idbuild, 
            'builds' => $builds, 
            'message' => '', 
            'emails' => '', 
            'status' => FALSE
        ]);
    });

    $app->post('/apps/{idapp}/distribute', function ($request, $response, $args) {

        $iduser = Session::init()->getUser()->getId();

        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $query = $request->getQueryParams();
        $idbuild = isset($query['idbuild']) ? $query['idbuild'] : 0;
        $builds = DistributionManager::getBuildsByAppId($idapp);
        $post = $request->getParsedBody();

        $idbuild = $post['idbuild'];

        $appx = DistributionManager::getAppById($idapp);

        $message = $post['message'];
        $emails = $post['emails'];

        $status = DistributionManager::sendBuildEmail($idapp, $idbuild, $iduser, $emails, $message);

        return $this->view->render($response, 'app.distribute.php', [
            'app' => $appx, 
            'idbuild' => $idbuild, 
            'builds' => $builds, 
            'message' => $message, 
            'emails' => $emails, 
            'status' => $status
        ]);
    });

    // Sign out
    $app->get('/signout', function ($request, $response, $args) {
        $display = Session::init()->getUser()->getDisplay();
        SessionManager::signOut();
        $login = '';
        $passcode = '';

        return $this->view->render($response, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => translate('Hi %s, you have signed out.', [ $display ])
        ]);
    });

    $app->get('/api/v1/info', function ($request, $response, $args) {
        phpinfo();
    });
}
else {
    $app->get('/', function ($request, $response, $args) {
        $login = '';
        $passcode = '';
        return $this->view->render($response, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => ''
        ]);
    });
    $app->get('/signout', function ($request, $response, $args) {

        $login = '';
        $passcode = '';
        return $this->view->render($response, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => ''
        ]);
    });
    $app->post('/', function ($request, $response, $args) {

        $post = $request->getParsedBody();
        $login = $post['login'];
        $passcode = $post['passcode'];

        $data = [
            'message' => translate('Sign in failed, please use different credentials and try again.'),
            'login' => $login, 
            'passcode' => $passcode
        ];

        $template = 'signin.php';

        if(SessionManager::signIn($login, $passcode)) {
            $data = [
                'message' => translate('Welcome back %s.', [ Session::init()->getUser()->getDisplay() ])
            ];
            $template = 'index.php';
        }

        return $this->view->render($response, $template, $data);
    });

}


/// APIs

// Catalogs by query
$app->get('/api/v1/catalog/{q}', function ($request, $response, $args) {
    
    $q = isset($args['q']) ? $args['q'] : KEY_INDUSTRY;
    $p = $request->getQueryParams();
    $l = isset($p['language']) ? $p['language'] : LANGUAGE;

    $list = CatalogManager::getCatalog($q, $l);
    return $response->withJson($list);
});
// Catalogs
$app->post('/api/v1/catalog', function ($request, $response, $args) {
    $post = $request->getParsedBody();

    $id = isset($post['id']) ? $post['id'] : 0;
    $key = isset($post['type']) ? $post['type'] : KEY_INDUSTRY;
    $name = isset($post['name']) ? $post['name'] : '';
    $language = isset($post['language']) ? $post['language'] : '';

    if($name === '' || $language === '') {
        return $response->withStatus(400)->withJson(array('status' => 400, 'id' => $id));
    }

    if($id === 0 || $id === '0') {
        $id = CatalogManager::addCatalog($name, $key, $language);
    }
    else {
        CatalogManager::updateCatalog($key, $id, $name, $language);
    }

    return $response->withStatus(200)->withJson(array('status' => 200, 'id' => $id));
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


// unused
$app->delete('/api/v1/assets/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $result = AssetManager::deleteAsset($id);

    return $this->view->render($response, 'asset.php', [
        'asset' => $result
    ]);
});

$app->get('/app/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $appx = DistributionManager::getAppById($id);
    $builds = DistributionManager::getBuildsByAppId($id);

    return $this->view->render($response, 'app.download.php', [
        'app' => $appx, 
        'builds' => $builds
    ]);
});

$app->get('/api/v1/build/code/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;
    $qrCode = DistributionManager::getQRCodeById($id);

    $imageResponse = $response->withHeader('Content-type', $qrCode->getContentType());;
    echo $qrCode->writeString();
    return $imageResponse;
    // header('Content-Type: '.$qrCode->getContentType());
    // return $qrCode->writeString();
});

$app->get('/api/v1/build/download/{idbuild}', function ($request, $response, $args) {

    $id = isset($args['idbuild']) ? $args['idbuild'] : 0;
    $build = DistributionManager::getBuildById($id);
    $appx = DistributionManager::getAppById($build->getAppId());

    if($id > 0) {
        try {

            $resource = DistributionManager::sendBuild('GET', $build->getUid(), $build->getVersion(), $build->getPlatform(), NULL, $appx->getRegion(), $appx->getContainer(), TRUE);
            $body = CommonUtility::createStream($resource);

            $size = NULL;
            $type = NULL;
            $meta = $body->getMetadata('wrapper_data');

            foreach($meta as $key => $val) {
                $haystack = strtolower($val);
                $length_key   = 'content-length:';
                $type_key   = 'content-type:';
                if(strpos($haystack, $length_key) !== FALSE) {
                    $size = trim(substr($val, strlen($length_key)));
                }
                if(strpos($haystack, $type_key) !== FALSE) {
                    $type = trim(substr($val, strlen($type_key)));
                }
            }

            $ext = ($build->getPlatform() === BuildModel::IOS ? 'ipa' : 'apk');
            $disposition = "attachment; filename=\"{$build->getUid()}.{$ext}\"";

            $newResponse = $response
            ->withHeader('Content-Disposition', $disposition)
            ->withHeader('Content-Length', $size)
            ->withHeader('Content-Type', $type)
            ->withStatus(200)
            ->withBody($body);

            return $newResponse;
        }
        catch (RequestException $e) {
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $message = Psr7\str($e->getResponse());
            }
            echo $message;
        }
    }

});

$app->get('/api/v1/build/meta/{id}', function ($request, $response, $args) {

    $id = isset($args['id']) ? $args['id'] : 0;

    $build = DistributionManager::getBuildById($id);

    $xmlResponse = $response->withHeader('Content-type', 'application/xml');

    return $this->view->render($xmlResponse, 'plist.php', [
        'build' => $build
    ]);
});

$app->get('/api/v1/email', function($request, $response, $args) {

    $result = TRUE;

    // $result = array_map('trim',explode(",", 'mihui@cn.ibm.com, mihui.net@outlook.com,abc@test.com'));

    $result = explode(', ', 'mihui@cn.ibm.com, mihui.net@outlook.com,abc@test.com');
    $str = translate('You have sucessfully distributed the Build to: <br /><br /><strong>%s</strong>', [ 'test' ]);
    echo $str;die;

    $result = vsprintf($str, [ 'Test', 'Test' ]);
    
    return $response->withJson(array('status' => $result));
});

$app->run();