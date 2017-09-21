<?php
final class SessionManager {

    public static function signIn($login, $passcode) {

        $succeed = FALSE;
        $time = time();
        $newToken = self::generateToken($login, $passcode, $time);
        $encryptedPasscode = self::generateToken($login, $passcode);

        $result = db::queryFirst("SELECT * FROM `user` WHERE `login` = ?;", array($login));

        if($result === NULL) {
            return $succeed;
        }

        // Special case for first login user
        // @todo: Remove & SSO
        if($result['passcode'] === '') {
            db::update("UPDATE `user` SET `passcode` = ?, `token` = ?, `activetime` = ? WHERE `login` = ?", array($encryptedPasscode, $newToken, $time, $login));
            $succeed = TRUE;
        }

        if($result['passcode'] === $encryptedPasscode) {
            db::update("UPDATE `user` SET `token` = ?, `activetime` = ? WHERE `login` = ?", array($newToken, $time, $login));
            $succeed = TRUE;
        }

        if($succeed) {
            
            $user = new UserModel($result['id'], $login, $result['display'], $newToken, $result['language'], $time);
            Session::init()->setUser($user);
            
        }

        return $succeed;
    }

    public static function signOut() {

        Session::init()->unsetUser();
    }

    public static function validate() {

        return Session::init()->isActive();
    }


	/**
	 * Generate token string
	 *
	 * @param string $login
	 * @param string $encryptedPassword
	 */
	public static function generateToken($login, $passcode, $timestamp = '') {

        return md5($login.$passcode.$timestamp);
    }
}

final class Session {

    const USER_SESSION_KEY = 'usr';
    private static $__instance = null;

    /**
     * Get Session instance
     *
     * @return Session
     */
    public static function init() {

        if(self::$__instance == null) {
            self::$__instance = new Session();
        }
        return self::$__instance;
    }

    function __construct() {

        session_start();
    }

    function __destruct() {

        session_write_close();
    }

    /**
     * Set session
     *
     * @param string $key
     * @param mix $value
     */
    public function setSession($key, $value) {

        $_SESSION[$key] = $value;
    }

    /**
     * Get session
     *
     * @param string $key
     * @return mix
     */
    public function getSession($key) {

        return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
    }

    /**
     * Set cookie
     *
     * @param string $key
     * @param string $value
     * @param string $expire [optional]
     * @param string $path [optional]
     * @param string $domain [optional]
     * @param string $secure [optional]
     * @param boolean $httponly [optional]
     *
     * @return boolean If output exists prior to calling this function, setcookie will fail and return false. If setcookie successfully runs, it will return true. This does not indicate whether the user accepted the cookie.
     */
    public function setCookie($key, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {

        return setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Get cookie
     *
     * @param string $key
     * @return string
     */
    public function getCookie($key) {

        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : NULL;
    }

    /**
     * Set user session
     *
     * @param UserModel $val
     */
    public function setUser(UserModel $val){ $this->setSession(self::USER_SESSION_KEY, $val); }

    /**
     * Get user object
     *
     * @return UserModel
     */
    public function getUser() {

        return $this->getSession(self::USER_SESSION_KEY);
    }

    /**
     * Destroy user instance from session
     */
    public function unsetUser() {

        $this->setSession(self::USER_SESSION_KEY, NULL);
    }

    /**
     * Authenticate user
     *
     * @return boolean
     */
    public function isActive() {

        if($this->getUser() === NULL) return FALSE;
        $token = $this->getUser()->getToken();
        if($token == NULL) return FALSE;
        return TRUE;
    }

}

final class UserManager {
    
    public static function updateSettings($language, $id) {

        return db::execute("UPDATE `user` SET `language` = ? WHERE `login` = ?", array($language, $id));
    }
}