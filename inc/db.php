<?php
/**
 * @author Michael <mihui.net@outlook.com>
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
					"mysql:dbname={$_ENV['MYSQL_SCHEMA']};port={$_ENV['MYSQL_PORT']};host={$_ENV['MYSQL_HOST']}", 
					$_ENV['MYSQL_USERNAME'],
					$_ENV['MYSQL_PASSWORD'],
					array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, time_zone = '+8:00'")
				);
				self::$_instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				self::$_connect++;
			}
			catch(PDOException $e)
			{
				echo '<pre>';
				print_r($e); 
				echo '</pre>';
				die;
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
		return NULL;
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
		if($rs == NULL) return array();
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
	public static function queryFirst($sql, $args = array())
	{
		$rs = self::__query($sql, $args);
		if($rs == NULL) return array();
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

	public static function update($sql, $args = array()) 
	{
		if(self::getInstance())
		{
			$rs = self::$_instance->prepare($sql);

			self::begin();

			$args = is_array($args) ? $args : array($args);

			$retval = $rs->execute($args);
			self::commit();

			self::$_count++;
			self::$_rowCount = $rs->rowCount();
		}
	}

	public static function insert($sql, $args = array(), $binds = array()) 
	{
		$id = 0;
		if(self::getInstance())
		{
			$rs = self::$_instance->prepare($sql);

			self::begin();

			$args = is_array($args) ? $args : array($args);

			$retval = $rs->execute($args);
			$id = self::getLastInsertId();
			self::commit();

			self::$_count++;
			self::$_rowCount = $rs->rowCount();
		}
		return $id;
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