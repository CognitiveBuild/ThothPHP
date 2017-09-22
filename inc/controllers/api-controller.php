<?php
final class APIController extends AbstractController {

    public function get($method) {

        return APIController::class . $method;
    }

    public function getUnAuthorizedHome($request, $response, $args) {

        $login = '';
        $passcode = '';
        return $this->view->render($response, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => ''
        ]);
    }

    public function getSignedOut($request, $response, $args) {

        $display = Session::init()->getUser()->getDisplay();
        SessionManager::signOut();
        $login = '';
        $passcode = '';

        return $this->view->render($response, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => translate('Hi %s, you have signed out.', [ $display ])
        ]);
    }

    public function getSignOut($request, $response, $args) {

        $login = '';
        $passcode = '';
        return $this->view->render($response, 'signin.php', [
            'login' => $login, 
            'passcode' => $passcode, 
            'message' => ''
        ]);
    }

    public function getVisitorAvatar($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $result = VisitorManager::getVisitor($id);

        return $response->withHeader('Content-Type', 'image/png')->write($result['avatar']);
    }

    public function getEventToday($request, $response, $args) {

        $event = EventManager::getEventOfToday();
        $visitors = VisitorManager::getVisitorsOfToday();
        if($visitors === FALSE) {
            $visitors = array();
        }
        if($event === FALSE) {
            $event = new stdClass();
        }
        return $response->withJson(array( 'event' => $event, 'visitors' => $visitors ));
    }

    public function getVisitorsByEvent($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        // $idcompany = isset($args['idcompany']) ? $args['idcompany'] : 0;
        // Get visitors and selected IDs
        //$all = VisitorManager::getVisitorsByCompanyId($idcompany);
        $all = VisitorManager::getVisitorsForEvent();
        $selected = EventManager::getVisitorsByEventId($id);

        return $response->withJson(array('all' => $all, 'selected' => $selected));
    }

    public function getCompanyLogo($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $result = CompanyManager::getCompany($id);

        return $response->withHeader('Content-Type', 'image/png')->write($result['logo']);
    }

    public function getAssetsByCatalogId($request, $response, $args) {

        $catalog = isset($args['catalog']) ? $args['catalog'] : KEY_INDUSTRY;
        $id = isset($args['id']) ? $args['id'] : 0;

        $list = AssetManager::getAssetsByCatalogId($catalog, $id);
        return $response->withJson($list);
    }

    public function getAssetsByCompanyId($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;

        $list = AssetManager::getAssetsByCompanyId($id);
        return $response->withJson($list);
    }

    public function getAssetsByCatalogName($request, $response, $args) {

        $catalog = isset($args['catalog']) ? $args['catalog'] : KEY_INDUSTRY;
        $name = isset($args['name']) ? $args['name'] : '';

        $list = AssetManager::getAssetsByCatalogName($catalog, $name);
        return $response->withJson($list);
    }
    
    public function getAttachment($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $result = AssetManager::readFile($id);

        return $response->withHeader('Content-Type', $result['type'])->write($result['binary']);
    }

    public function getCatalogs($request, $response, $args) {

        $q = isset($args['q']) ? $args['q'] : KEY_INDUSTRY;
        $p = $request->getQueryParams();
        $l = isset($p['language']) ? $p['language'] : LANGUAGE;

        $list = CatalogManager::getCatalog($q, $l);
        return $response->withJson($list);
    }

    public function postCatalog($request, $response, $args) {

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
    }

    public function getApps($request, $response, $args) {

        $apps = DistributionManager::getApps();

        return $this->view->render($response, 'apps.php', [
            'apps' => $apps
        ]);
    }

    public function getApp($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $appx = DistributionManager::getAppById($id);
        $builds = DistributionManager::getBuildsByAppId($id);

        return $this->view->render($response, 'app.php', [
            'app' => $appx, 
            'builds' => $builds
        ]);
    }

    public function getAppBuild($request, $response, $args) {

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
    }

    public function postAppBuild($request, $response, $args) {

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
    }

    public function deleteAppBuild($request, $response, $args) {

        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $idbuild = isset($args['idbuild']) ? $args['idbuild'] : 0;
        $appx = DistributionManager::getAppById($idapp);
        $build = DistributionManager::getBuildById($idbuild);
    
        DistributionManager::removeFile($appx, $build);
    
        return $response->withStatus(200)->withJson(array('status' => TRUE));
    }

    public function postApp($request, $response, $args) {

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
    }

    public function getDistribute($request, $response, $args) {

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
    }

    public function postDistribute($request, $response, $args) {

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
    }

    public function deleteTimeline($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $result = EventManager::deleteTimelineById($id);

        return $response->withJson(array('status' => $result));
    }

    public function deleteVisitorAvatar($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $result = VisitorManager::updateAvatar($id, NULL);
    
        return $response->withJson(array('status' => $result));
    }

    public function deleteCompanyLogo($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $result = CompanyManager::updateLogo($id, NULL);
    
        return $response->withJson(array('status' => $result));
    }

    public function deleteAssetAttachment($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;

        $file = AssetManager::readFile($id);
        $result = FALSE;
        if(isset($file['id'])) {
            $result = AssetManager::deleteFile($id);
            AssetManager::updateFileIds($file['idasset']);
        }

        return $response->withJson(array('status' => $result));
    }

    public function postEvent($request, $response, $args) {

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
    }
}