<?php
/**
 * @author Michael <mihui.net@outlook.com>
 *
 */
define('CONF', '_CONFIG');

$_ENV[CONF] = array(
	'DB' 			=> array(),
	'TIMEZONE'		=> 'Asia/Shanghai'
);

$_ENV[CONF]['DB']['DSN'] 			= 'mysql:dbname=%s;port=%s;host=%s';
$_ENV[CONF]['DB']['DRIVEROPTIONS'] 	= array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';");
$_ENV[CONF]['DB']['PREFIX'] 		= '';

if(isset($_ENV["VCAP_SERVICES"])){ //local dev

	$vcap_services = json_decode($_ENV["VCAP_SERVICES" ]);
    if($vcap_services->{'compose-for-mysql'}){ //if "mysql" db service is bound to this application
        $db = $vcap_services->{'compose-for-mysql'}[0]->credentials;
    }
    else { 
        echo "Error: No suitable MySQL database bound to the application. <br>";
        die();
    }
} else { //running in Bluemix
    $_ENV[CONF]['DB']['HOST'] 		= $_ENV['MYSQL_HOST'];
	$_ENV[CONF]['DB']['PORT'] 		= $_ENV['MYSQL_PORT'];
	$_ENV[CONF]['DB']['USERNAME'] 	= $_ENV['MYSQL_USERNAME'];
	$_ENV[CONF]['DB']['PASSWORD']   = $_ENV['MYSQL_PASSWORD'];
	$_ENV[CONF]['DB']['SCHEMA'] 	= $_ENV['MYSQL_SCHEMA'];
}

/**

 * @author Michael <mihui@cn.ibm.com>

 *

 */
final class db
{
	/**

	 * PDO instance

	 * @var PDO

	 */
	private static $_instance = NULL;
	private static $_count = 0;
	private static $_connect = 0;
	private static $_rowCount = 0;

	/**

	 * Get DB instance

	 * @return PDO

	 */
	public static function getInstance()
	{
		if(self::$_instance === NULL)
		{
			try
			{
				self::$_instance = new PDO
				(
					sprintf(
						$_ENV[CONF]['DB']['DSN'],
						$_ENV[CONF]['DB']['SCHEMA'],
						$_ENV[CONF]['DB']['PORT'],
						$_ENV[CONF]['DB']['HOST']
					),
					$_ENV[CONF]['DB']['USERNAME'],
					$_ENV[CONF]['DB']['PASSWORD'],
					$_ENV[CONF]['DB']['DRIVEROPTIONS']
				);
				self::$_instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				# self::$_instance->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

				self::$_connect++;

			}
			catch(PDOException $e)
			{
				// $html = helper::getInstance()->get_message('Sorry, unexpected error occured. Please try to refresh this page later.', helper::TYPE_ERROR);

				// echo $html;
				var_dump($e); die;
			}
		}
		return self::$_instance;
	}

	/**

	 * Database Query

	 *

	 * @param string $sql

	 * @param mix $args

	 * @param int $fetchmode

	 * @return PDOStatement

	 */
	private static function __query($sql, $args = array(), $fetchmode = PDO::FETCH_ASSOC)
	{
		if(self::getInstance())
		{
			$rs = self::$_instance->prepare($sql);
			// printVars(array($sql, $args));

			$rs->setFetchMode($fetchmode);

			$args = is_array($args) ? $args : array($args);
			$rs->execute($args);
			self::$_count++;
			return $rs;
		}
		return null;
	}

	/**

	 * Excute SQL

	 * @param string SQL

	 * @param array|mix $args

	 * @return array resource

	 */
	public static function query($sql, $args = array())
	{
		$rs = self::__query($sql, $args);
		if($rs == null) return array();
		return $rs->fetchAll();
	}

	/**

	 * Query first column

	 *

	 * @param string $sql

	 * @param mix $args

	 */
	public static function queryOne($sql, $args = array())
	{
		$rs = self::__query($sql, $args, PDO::FETCH_NUM);
		if($rs == null) return '';
		$result = $rs->fetch();
		if(count($result) > 0)
			return isset($result[0]) ? $result[0] : '';
		return '';
	}

	/**

	 * Query one row

	 *

	 * @param string $sql

	 * @param mix $args

	 */
	public static function queryRow($sql, $args = array())
	{
		$rs = self::__query($sql, $args);
		if($rs == null) return array();
		$result = $rs->fetch();
		return $result;
	}

	/**

	 * Get the last inserted ID;

	 * @return int

	 */
	public static function getLastInsertId()
	{
		if(self::getInstance())
		{
			return self::$_instance->lastInsertId();
		}
		return 0;
	}

	/**

	 * Execute SQL

	 * @param string $query SQL

	 * @param array|mix $args

	 * @return boolean|int

	 */
	public static function execute($sql, $args = array())
	{
		if(self::getInstance())
		{
			$rs = self::$_instance->prepare($sql);
			// printVars(array($sql, $args));

			$args = is_array($args) ? $args : array($args);

			$retval = $rs->execute($args);
			self::$_count++;
			self::$_rowCount = $rs->rowCount();
			return $retval;
		}
		return false;
	}

	/**

	 * Begin Transaction

	 */
	public static function begin()
	{
		if(self::getInstance())
		{
			self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			return self::$_instance->beginTransaction();
		}
		return false;
	}

	/**

	 * Commit Transaction

	 */
	public static function commit()
	{
		if(self::getInstance())
		{
			return self::$_instance->commit();
		}
		return false;
	}

	/**

	 * Roll Back Transaction

	 */
	public static function rollBack()
	{
		if(self::getInstance())
		{
			return self::$_instance->rollBack();
		}
		return false;
	}

	/**

	 * Get affected rows

	 * @return int

	 */
	public static function getAffectedRows(){ return self::$_rowCount; }

	/**

	 * Get the query time;

	 * @return int

	 */
	public static function getQueryCount() { return self::$_count; }

	/**

	 * Get the time count of DB Opened

	 * @return int

	 */
	public static function getOpenCount(){ return self::$_connect; }

	/**

	 * Places quotes around the input string (if required) and escapes special characters within the input string, using a quoting style appropriate to the underlying driver.

	 * @param string $str

	 * @param int $paramtype

	 * @return string

	 */
	public static function quote($str, $paramtype = PDO::PARAM_STR)
	{
		if(self::getInstance())
			return self::$_instance->quote($str, $paramtype);
		return $str;
	}
}