<?php
class RoleModel {

	const NEW_ID = '0';

	private $_id;
	private $_name;
	private $_description;

	function __construct($id = self::NEW_ID, $name = '', $description = '') {

		$this->_id = $id;
		$this->_name = $name;
        $this->_description = $description;
	}

	function __destruct() {

		unset($this->_id);
		unset($this->_name);
		unset($this->_description);
	}

    public function getId() { return $this->_id; }
    public function setId($val) { $this->_id = $val; }

    public function getName() { return $this->_name; }
    public function setName($val) { $this->_name = $val; }

    public function getDescription() { return $this->_description; }
    public function setDescription($val) { $this->_description = $val; }
}