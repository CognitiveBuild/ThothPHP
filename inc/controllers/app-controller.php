<?php
final class AppController extends AbstractController {

    public static function get($method) {

        return AppController::class . ":{$method}";
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

}