<?php
class AppModel {

    const NEW_ID = '0';

    private $_id;
    private $_name;
    private $_region;
    private $_container;

    function __construct($id = self::NEW_ID, $name = '', $region = '', $container = 'builds') {

        $this->setId($id);
        $this->setName($name);
        $this->setRegion($region);
        $this->setContainer($container);
    }
        
    public function getId() { return $this->_id; }
    public function setId($val) { $this->_id = $val; }

    public function getName() { return $this->_name; }
    public function setName($val) { $this->_name = $val; }

    public function getRegion() { return $this->_region; }
    public function setRegion($val) { $this->_region = $val; }

    public function getContainer() { return $this->_container; }
    public function setContainer($val) { $this->_container = $val; }

}

class BuildModel {

    const NEW_ID = '0';
    const IOS = "iOS";
    const ANDROID = "Android";

    private $_buildId;
    private $_appId;
    private $_uid;

    private $_display;
    private $_platform;

    private $_version;
    private $_time;

    function __construct($idbuild = self::NEW_ID, $idapp = self::NEW_ID, $uid = '', $display = '', $platform = self::IOS, $version = '1.0.0', $time = 0) {

        $this->setBuildId($idbuild);
        $this->setAppId($idapp);
        $this->setUid($uid);
        $this->setDisplay($display);
        $this->setPlatform($platform);
        $this->setVersion($version);
        $this->setTime($time);
    }

    public function getBuildId() { return $this->_buildId; }
    public function setBuildId($val) { $this->_buildId = $val; }

    public function getAppId() { return $this->_appId; }
    public function setAppId($val) { $this->_appId = $val; }

    public function getUid() { return $this->_uid; }
    public function setUid($val) { $this->_uid = $val; }

    public function getDisplay() { return $this->_display; }
    public function setDisplay($val) { $this->_display = $val; }

    public function getPlatform() { return $this->_platform; }
    public function setPlatform($val) { $this->_platform = $val; }

    public function getVersion() { return $this->_version; }
    public function setVersion($val) { $this->_version = $val; }

    public function getTime() { return $this->_time; }
    public function setTime($val) { $this->_time = $val; }
}