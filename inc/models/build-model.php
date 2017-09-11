<?php
class BuildModel {

    const NEW_ID = '0';

    const IOS = "iOS";
    const ANDROID = "Android";

    private $_id;
    private $_uid;
    private $_name;
    private $_display;
    private $_platform;
    private $_region;
    private $_container;
    private $_version;
    private $_time;

    function __construct($id = self::NEW_ID, $uid = '', $name = '', $display = '', $platform = self::IOS, $region = '', $container = 'builds', $version = '1.0.0', $time = 0) {
        
        $this->setId($id);
        $this->setUid($uid);
        $this->setName($name);
        $this->setDisplay($display);
        $this->setPlatform($platform);
        $this->setRegion($region);
        $this->setContainer($container);
        $this->setVersion($version);
        $this->setTime($time);
    }

    public function getId() { return $this->_id; }
    public function setId($val) { $this->_id = $val; }

    public function getUid() { return $this->_uid; }
    public function setUid($val) { $this->_uid = $val; }

    public function getName() { return $this->_name; }
    public function setName($val) { $this->_name = $val; }

    public function getDisplay() { return $this->_display; }
    public function setDisplay($val) { $this->_display = $val; }

    public function getPlatform() { return $this->_platform; }
    public function setPlatform($val) { $this->_platform = $val; }

    public function getRegion() { return $this->_region; }
    public function setRegion($val) { $this->_region = $val; }

    public function getContainer() { return $this->_container; }
    public function setContainer($val) { $this->_container = $val; }

    public function getVersion() { return $this->_version; }
    public function setVersion($val) { $this->_version = $val; }

    public function getTime() { return $this->_time; }
    public function setTime($val) { $this->_time = $val; }
}