<?php
final class CommonController extends AbstractController {

    public static function get($method) {

        return CommonController::class . ":{$method}";
    }

    public function getHome($request, $response, $args){

        return $this->view->render($response, 'index.php', [
            'message' => translate('Welcome')
        ]);
    }

    // TEST ONLY
    public function getPage($request, $response, $args) {

        $p = $request->getQueryParams();

        $page = isset($p['page']) ? $p['page'] : 'test.php';

        return $this->view->render($response, $page, [
            
        ]);
    }

    // phpinfo
    public function getPHPInfo($request, $response, $args) {
        // echo information directly
        phpinfo();
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

        $result = SessionManager::signIn($login, $passcode);

        if($result) {
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
            CommonUtility::setLanguage($language);
            Session::init()->setUser(Session::init()->getUser());
        }

        return $this->view->render($response, 'settings.php', [
            'language' => $language, 
            'message' => translate('You have successfully updated the settings, please re-visit the page to see the differences.')
        ]);
    }

    public function getUser($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $user = new UserModel();

        if($id > 0) {
            $result = UserManager::getUserById($id);
            $user->setDisplay($result['display']);
            $user->setRoleId($result['idrole']);
            $user->setToken($result['token']);
            $user->setLogin($result['login']);
            $user->setLanguage($result['language']);
            $user->setActiveTime($result['activetime']);
        }

        $roles = UserManager::getRoles();

        return $this->view->render($response, 'user.php', [
            'user' => $user, 
            'id' => $id, 
            'roles' => $roles
        ]);
    }

    public function postUser($request, $response, $args) {

        $post = $request->getParsedBody();
        $id = isset($args['id']) ? $args['id'] : 0;
        $isNew = TRUE;

        if($id > 0) {
            UserManager::updateUser($id, $post['display'], $post['login'], $post['idrole'], $post['passcode'], $post['language']);
            $isNew = FALSE;
        }
        else {
            $id = UserManager::addUser($post['display'], $post['login'], $post['idrole'], $post['passcode'], $post['language']);
        }
        if($isNew) {
            return $response->withStatus(200)->withHeader('Location', "/users");
        }

        return $response->withStatus(200)->withHeader('Location', "/users/{$id}");
    }
        
    public function getUsers($request, $response, $args) {

        $users = UserManager::getUsers();
        return $this->view->render($response, 'users.php', [
            'users' => $users
        ]);
    }

    public function getRole($request, $response, $args) {

        $id = isset($args['id']) ? $args['id'] : 0;
        $role = new RoleModel();

        if($id > 0) {
            $result = UserManager::getRoleById($id);
            $role->setId($result['id']);
            $role->setName($result['name']);
            $role->setDescription($result['description']);
        }

        $acls = UserManager::getACLs($id);

        return $this->view->render($response, 'role.php', [
            'role' => $role, 
            'id' => $id, 
            'acls' => $acls
        ]);
    }

    public function postRole($request, $response, $args) {

        $post = $request->getParsedBody();

        $id = isset($args['id']) ? $args['id'] : 0;
        $isNew = TRUE;

        if($id > 0) {
            UserManager::updateRole($id, $post['name'], $post['description']);
            $isNew = FALSE;
        }
        else {
            $id = UserManager::addRole($post['name'], $post['description']);
        }

        $acls = $post['acls'];
        UserManager::removeRoleACL($id);
        foreach($acls as $idacl => $val) {

            UserManager::addRoleACL($id, $idacl);
        }

        if($isNew) {
            return $response->withStatus(200)->withHeader('Location', "/roles");
        }

        return $response->withStatus(200)->withHeader('Location', "/roles/{$id}");
    }

    public function getRoles($request, $response, $args) {

        $roles = UserManager::getRoles();
        return $this->view->render($response, 'roles.php', [
            'roles' => $roles
        ]);
    }
        
    // Public access to the app builds
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