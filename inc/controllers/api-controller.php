<?php
final class APIController extends AbstractController {

    public static function get($method) {

        return APIController::class . ":{$method}";
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
            $visitors = [];
        }
        if($event === FALSE) {
            $event = new stdClass();
        }
        return $response->withJson([ 'event' => $event, 'visitors' => $visitors ]);
    }

    public function getVisitorsByEvent($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        // $idcompany = isset($args['idcompany']) ? $args['idcompany'] : 0;
        // Get visitors and selected IDs
        //$all = VisitorManager::getVisitorsByCompanyId($idcompany);
        $all = VisitorManager::getVisitorsForEvent();
        $selected = EventManager::getVisitorsByEventId($id);

        return $response->withJson([ 
            'all' => $all, 
            'selected' => $selected, 
            'translations' => [ 'visitors' => translate('Visitors') ] 
        ]);
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
        $l = CommonUtility::toLanguage($p);

        $list = CatalogManager::getCatalog($q, $l);
        return $response->withJson($list);
    }

    public function postCatalog($request, $response, $args) {

        $post = $request->getParsedBody();

        $id = isset($post['id']) ? $post['id'] : 0;
        $key = isset($post['type']) ? $post['type'] : KEY_INDUSTRY;
        $name = isset($post['name']) ? $post['name'] : '';
        $language = CommonUtility::toLanguage($post);

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

    public function deleteAppBuild($request, $response, $args) {

        $idapp = isset($args['idapp']) ? $args['idapp'] : 0;
        $idbuild = isset($args['idbuild']) ? $args['idbuild'] : 0;
        $appx = DistributionManager::getAppById($idapp);
        $build = DistributionManager::getBuildById($idbuild);
    
        DistributionManager::removeFile($appx, $build);
    
        return $response->withStatus(200)->withJson(array('status' => TRUE));
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
}