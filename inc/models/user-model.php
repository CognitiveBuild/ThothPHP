<?php
class UserModel {

	const NEW_ID = '0';

	private $_id;
	private $_display;
	private $_login;
	private $_passcode;
	private $_token;
	private $_activetime;
	private $_language;

	function __construct($id = self::NEW_ID, $login = '', $display = '', $token = '', $language = DEFAULT_LANGUAGE, $activeTime = 0) {

        $this->_id = $id;
        $this->_login = $login;
        $this->_display = $display;
        $this->_token = $token;
        $this->_activetime = $activeTime;
		$this->_language = $language;
		$this->_passcode = '';
	}

	function __destruct() {

		unset($this->_id);
		unset($this->_display);
		unset($this->_login);
		unset($this->_passcode);
		unset($this->_token);
		unset($this->_language);
		unset($this->_activetime);
	}

    public function __sleep() {

        return array('_id', '_login', '_token', '_display', '_language');
    }

	public function getId() { return $this->_id; }

	public function getDisplay() { return $this->_display; }
	public function setDisplay($val) { $this->_display = $val; }

	public function getToken() { return $this->_token; }
	public function setToken($val) { $this->_token = $val; }

	public function getPasscode() { return $this->_passcode; }

	public function getLogin() { return $this->_login; }
	public function setLogin($val) { $this->_login = $val; }

	public function getLanguage() { return $this->_language; }
	public function setLanguage($val) { $this->_language = $val; }
}