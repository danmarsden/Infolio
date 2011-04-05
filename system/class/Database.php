<?php

/**
 * Class Database
 * 
 * Responsible to connect to db with details supplied on /system/conf.php
 * 
 * LICENSE: This is an Open Source Project
 * 
 * @author     	Elvir Leonard
 * @copyright  	2008 Rix Centre
 * @license    	http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    	$Id: Database.php 707 2009-07-19 20:39:34Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

class Database
{
	// Constants
	const INSERT = 'insert';
	const UPDATE = 'update';
	const DATETIME_FORMAT = 'Y-m-d H:i:s';
	
	// Member variables
	static $_instance;
	private $_db;
	
	/**
	 * Constructor function
	 * Create connection to database and throwing exception if failed then notify user
	 * @return 
	 */
	private function __construct()
	{
		if(!($this->_db = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD))){
			Logger::Write('Database error connecting to database server', Logger::TYPE_ERROR);
		}
		if(!($this->_db = mysql_select_db(DB_DATABASE))){
			Logger::Write('Database error connecting to database', Logger::TYPE_ERROR);
		}
		return $this->_db;
	}
	
	/**
	 * get the instance of database
	 * @return $object the instance
	 */
	public static function getInstance()
	{
		if( ! (self::$_instance instanceof self) ){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Query to active database 
	 * @return 
	 * @param $sql Object
	 */
	public function query($sql)
	{
		try{
			return mysql_query( $sql ) ;
		}
		catch(Exception $e) {
			echo "Error :: " . $e->getMessage();
		}
	}

    function table_exists($tableName)
    {
      $sql ='SHOW TABLES WHERE Tables_in_' . DB_DATABASE . ' = \'' . $tableName . '\'';
      $rs = mysql_query($sql);

      if(!mysql_fetch_array($rs)) {
          return FALSE;
      } else {
          return TRUE;
      }
    }
	/**
	 * Get number of rows on a query
	 * @return $count Integer number of rows
	 * @param $sql Object|String this can be object or string of sql statement
	 */
	public function numRows($sql)
	{
		if(is_string($sql))	{
			$query = $this->query($sql);
		}
		else {
			$query = $sql;
		}

		$numOfRows = mysql_num_rows($query);
		return $numOfRows;
	}
		
	/**
	 * Close mysql connection to db server
	 * @return boolean return true|false
	 */
	public function close()
	{
		return mysql_close($$link);
	}
	
	/**
	 * Get a value of a specific column. You will have to supply the tableName, fieldName and whereStatement
	 * @return $string 
	 * @param $tableName String
	 * @param $fieldName String
	 * @param $whereStatement String
	 */
	public function getData($tableName, $fieldName, $whereStatement)
	{
		$sql	=	"SELECT " . $fieldName . " FROM " . $tableName . " WHERE " . $whereStatement;
		$query	=	$this->query($sql);

		if($this->numRows($query)>0){
			$res=$this->fetchArray($query);
			return $res[0];
		}else{
			return "";
		}
	}
	
	/**
	 * Gets a type of column inside a table
	 * @return string type of a supplied column
	 * @param $tableName Object
	 * @param $fieldName Object
	 */
	public function getFieldType($tableName, $fieldName)
	{
		$sql 	= "DESCRIBE " . $tableName;
		$query 	= $this->query($sql);
		while($res=mysql_fetch_object($query)){
			if($res->Field==$fieldName)
				return $res->Type;
		}
	}
	
	/**
	 * Perform update or insert to a table
	 * @return $object
	 * @param $table Object
	 * @param $data Object	format needs to be:
	 * 						$data=array(
	 * 							"[column name]" => "[value]"
	 * 						);
	 * @param $action Object[optional] this can be insert or update / case insensitive
	 * @param $parameters Object[optional]
	 * @param $outputSql Object[optional]
	 */
	public function perform($table, $data, $action = Database::INSERT, $parameters = '', $outputSql = false)
	{
		// Check all data is safe for DB
		foreach($data as $dataKey=>$dataItem) {
            if (is_object($dataItem)) { //this shouldn't be cleaned or included in insert 
                unset($data[$dataKey]);
            } else {
			    $data[$dataKey] = Safe::Input($dataItem);
            }
		}
		reset($data); //put array's internal pointer to the first element

		$action	=	strtolower($action);
		if ($action == Database::INSERT) {
		  $sql = 'insert into ' . $table . ' (';
		  while (list($columns, ) = each($data)) {
			$sql .= $columns . ', ';
		  }
		  $sql = substr($sql, 0, -2) . ') values (';
		  reset($data);
		  while (list(, $value) = each($data)) {
			switch ((string)$value) {
			  case 'now()':
				$sql .= 'now(), ';
				break;
			  case 'null':
				$sql .= 'null, ';
				break;
			  default:
				$sql .= "'{$value}', ";
				break;
			}
		  }
		  $sql = substr($sql, 0, -2) . ')';
		}
		elseif ($action == Database::UPDATE) {
		  $sql = 'update ' . $table . ' set ';
		  while (list($columns, $value) = each($data)) {
			switch ((string)$value) {
			  case 'now()':
				$sql .= $columns . ' = now(), ';
				break;
			  case 'null':
				$sql .= $columns . ' = null, ';
				break;
			  default:
				$sql .= "{$columns} = '{$value}', ";
				break;
			}
		  }
		  $sql = substr($sql, 0, -2) . ' where ' . $parameters;
		}

		// Return result or raw SQL
		if($outputSql==true) {
			return $sql;
		}
		else {
			static $count=0;
			Debugger::debug("SQL: sql", "Database::perform ".$count++, Debugger::LEVEL_SQL);
			return $this->query($sql);
		}
	}
	
	/**
	 * Fetch query result to an array
	 * @return array of rows
	 * @param $query Object
	 */
	public function fetchArray($query)
	{
		//debug_print_backtrace(); //DEBUG

		return mysql_fetch_array($query);
	}
	
	/**
	 * Retrieves the contents of one cell from a MySQL result set. 
	 * @return String
	 * @param $query Object
	 * @param $row Object
	 * @param $field Object[optional]
	 */
	public function result($query, $row, $field = '')
	{
		if(is_string($query)){
			$query = $this->query($query);
		}
		return mysql_result($query, $row, $field);
	}
	
	/**
	 * Retrieves the ID generated for an AUTO_INCREMENT column by the previous INSERT query
	 * @return int ID generated by mysql
	 */
	public function insertId()
	{
		return mysql_insert_id();
	}
	
	/**
	 * Free result memory
	 * @return 
	 * @param $query Object
	 */
	public function freeResultMemory($query)
	{
		if(is_string($query)){
			$query = $this->query($query);
		}
		return mysql_free_result($query);
	}
	
	/**
	 * Returns an object containing field information. This function can be used to obtain information about fields in the provided query result. 
	 * @return 
	 * @param $query Object|String
	 */
	public function fetchFields($query)
	{
		if(is_string($query)){
			$query = $this->query($query);
		}
		$metaData=mysql_fetch_field($query);
		return $metaData;
	}
	
	/**
	 * Clean/sanitise string from database
	 * @return 
	 * @param $string Object
	 */
	private function output($string)
	{
		$string	= htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		return $string;
	}
	
	/**
	 * Sanitise variable before putting it into db
	 * @return $string sanitised string
	 * @param $string Object
	 */
	private function input($string)
	{
		$string = trim($string);
		$string = str_replace("'", "''", $string);
		return $string;
	}
}
