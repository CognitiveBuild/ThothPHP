<?php
final class CommonController extends AbstractController {

    public function get($method) {

        return CommonController::class . $method;
    }

    public function getHome($request, $response, $args){

        return $this->view->render($response, 'index.php', [
            'message' => translate('Welcome')
        ]);
    }

    public function postHome ($request, $response, $args) {

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
    }

    public function getSettings($request, $response, $args) {

        return $this->view->render($response, 'settings.php', [
            'message' => ''
        ]);
    }

    public function postSettings ($request, $response, $args) {

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
    }

    public function getApp($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $appx = DistributionManager::getAppById($id);
        $builds = DistributionManager::getBuildsByAppId($id);
    
        return $this->view->render($response, 'app.download.php', [
            'app' => $appx, 
            'builds' => $builds
        ]);
    }

    public function getQRCode($request, $response, $args) {
    
        $id = isset($args['id']) ? $args['id'] : 0;
        $qrCode = DistributionManager::getQRCodeById($id);

        $imageResponse = $response->withHeader('Content-type', $qrCode->getContentType());
        echo $qrCode->writeString();
        return $imageResponse;
    }

    public function getMeta($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;

        $build = DistributionManager::getBuildById($id);

        $xmlResponse = $response->withHeader('Content-type', 'application/xml');

        return $this->view->render($xmlResponse, 'plist.php', [
            'build' => $build
        ]);
    }

    public function getBuild($request, $response, $args) {

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
            return $response->withStatus(400);
        }

    }
}