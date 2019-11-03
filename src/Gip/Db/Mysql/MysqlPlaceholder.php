<?php
/**
 * FORK GIP
 * 
 * @author col.shrapnel@gmail.com
 * @link http://phpfaq.ru/safemysql
 * 
 * Safe and convenient way to handle SQL queries utilizing type-hinted placeholders.
 * 
 * Key features
 * - set of helper functions to get the desired result right out of query, like in PEAR::DB
 * - conditional query building using parse() method to build queries of whatever comlexity, 
 *   while keeping extra safety of placeholders
 * - type-hinted placeholders
 * 
 *  Type-hinted placeholders are great because 
 * - safe, as any other [properly implemented] placeholders
 * - no need for manual escaping or binding, makes the code extra DRY
 * - allows support for non-standard types such as identifier or array, which saves A LOT of pain in the back.
 * 
 * Supported placeholders at the moment are:
 * 
 * ?s ("string")  - strings (FLOAT and DECIMAL)
 * ?d ("date")    - date. If date time zone php differs from date time zone Db this date will be converted in time zone Db
 * ?t ("table")   - table name without prefix
 * ?i ("integer") - the name says it all 
 * ?n ("name")    - identifiers (table and field names) 
 * ?a ("array")   - complex placeholder for IN() operator  (substituted with string of 'a','b','c' format, without parentesis)
 * ?u ("update")  - complex placeholder for SET operator (substituted with string of `field`='value',`field`='value' format)
 * and
 * ?p ("parsed") - special type placeholder, for inserting already parsed statements without any processing, to avoid double parsing.
 * 
 * Connection:
 *
 * $db = new SafeMySQL(); // with default settings
 * 
 * $opts = array(
 *		'user'    => 'user',
 *		'pass'    => 'pass',
 *		'db'      => 'db',
 *		'charset' => 'latin1'
 * );
 * $db = new SafeMySQL($opts); // with some of the default settings overwritten
 * 
 * Alternatively, you can just pass an existing mysqli instance that will be used to run queries 
 * instead of creating a new connection.
 * Excellent choice for migration!
 * 
 * $db = new SafeMySQL(['mysqli' => $mysqli]);
 * 
 * Some examples:
 * 
 * $name = $db->getOne('SELECT name FROM table WHERE id = ?i',$_GET['id']);
 * $data = $db->getInd('id','SELECT * FROM ?n WHERE id IN ?a','table', array(1,2));
 * $data = $db->getAll("SELECT * FROM ?n WHERE mod=?s LIMIT ?i",$table,$mod,$limit);
 *
 * $ids  = $db->getCol("SELECT id FROM tags WHERE tagname = ?s",$tag);
 * $data = $db->getAll("SELECT * FROM table WHERE category IN (?a)",$ids);
 * 
 * $data = array('offers_in' => $in, 'offers_out' => $out);
 * $sql  = "INSERT INTO stats SET pid=?i,dt=CURDATE(),?u ON DUPLICATE KEY UPDATE ?u";
 * $db->query($sql,$pid,$data,$data);
 * 
 * if ($var === NULL) {
 *     $sqlpart = "field is NULL";
 * } else {
 *     $sqlpart = $db->parse("field = ?s", $var);
 * }
 * $data = $db->getAll("SELECT * FROM table WHERE ?p", $bar, $sqlpart);
 * 
 */

//declare(strict_types = 1);

namespace Vgip\Gip\Db\Mysql;

use DateTime;
use DateTimeZone;
use \Vgip\Gip\Db\Mysql\Config;

class MysqlPlaceholder
{
    protected $conn;
    protected $stats;
    protected $emode;
    protected $exname;
    protected $db;
    protected $tablePrefix;
    protected $timezone;
    
    protected $defaults = [
        'host'          => 'localhost',
        'user'          => 'root',
        'pass'          => '',
        'db'            => 'test',
        'port'          => null,
        'socket'        => null,
        'pconnect'      => false,
        'charset'       => 'utf8',
        'errmode'       => 'exception',
        'exception'     => '\\Exception',
        'table_prefix'  => null,
        'timezone'      => 'UTC',
    ];

    const RESULT_ASSOC = MYSQLI_ASSOC;
    const RESULT_NUM   = MYSQLI_NUM;

    public function __construct($config) 
    {
        if ($config instanceof Config) {
            $opt = $config->getAll();
        } else {
            $opt = $config;
        }
        
        $opt = array_merge($this->defaults, $opt);

        $this->emode        = $opt['errmode'];
        $this->exname       = $opt['exception'];
        $this->db           = $opt['db'];
        $this->tablePrefix  = $opt['table_prefix'];
        $this->timezone     = new DateTimeZone($opt['timezone']);

        if (isset($opt['mysqli'])) {
            if ($opt['mysqli'] instanceof mysqli) {
                $this->conn = $opt['mysqli'];
                return;
            } else {
                $this->error("mysqli option must be valid instance of mysqli class");
            }
        }

        if ($opt['pconnect']) {
            $opt['host'] = "p:" . $opt['host'];
        }

        @$this->conn = mysqli_connect($opt['host'], $opt['user'], $opt['pass'], $opt['db'], $opt['port'], $opt['socket']);
        if (!$this->conn) {
            $this->error(mysqli_connect_errno() . " " . mysqli_connect_error());
        }

        mysqli_set_charset($this->conn, $opt['charset']) or $this->error(mysqli_error($this->conn));
    }
    
    public function getConnection()
    {
        $mysqli = $this->conn;
        
        return $mysqli;
    }
    
    public function getDb()
    {
        return $this->db;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }
    
    public function getTableNameFull($table, $quotes = true)
    {
        $tablePrefix = $this->tablePrefix;
        $tableNameFull = $this->tablePrefix.$table;
        
        if (true === $quotes) {
            $tableNameFull = '`'.$tableNameFull.'`';
        }

        return $tableNameFull;
    }
    
    public function getTimezone() : string
    {
        return $this->timezone->getName();
    }

    /**
	 * Conventional function to run a query with placeholders. A mysqli_query wrapper with placeholders support
	 * 
	 * Examples:
	 * $db->query("DELETE FROM table WHERE id=?i", $id);
	 *
	 * @param string $query - an SQL query with placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
	 * @return resource|FALSE whatever mysqli_query returns
	 */
	public function query()
	{	
		return $this->rawQuery($this->prepareQuery(func_get_args()));
	}

    /**
     * Conventional function to fetch single row. 
     * 
     * @param resource $result - myqli result
     * @param int $mode - optional fetch mode, RESULT_ASSOC|RESULT_NUM, default RESULT_ASSOC
     * @return array|FALSE whatever mysqli_fetch_array returns
     */
    public function fetch($result, $mode=self::RESULT_ASSOC)
    {
        $res = mysqli_fetch_array($result, $mode);
        
        return $res;
    }

	/**
	 * Conventional function to get number of affected rows. 
	 * 
	 * @return int whatever mysqli_affected_rows returns
	 */
	public function affectedRows()
	{
		return mysqli_affected_rows ($this->conn);
	}

	/**
	 * Conventional function to get last insert id. 
	 * 
	 * @return int whatever mysqli_insert_id returns
	 */
	public function insertId()
	{
		return mysqli_insert_id($this->conn);
	}

	/**
	 * Conventional function to get number of rows in the resultset. 
	 * 
	 * @param resource $result - myqli result
	 * @return int whatever mysqli_num_rows returns
	 */
	public function numRows($result)
	{
		return mysqli_num_rows($result);
	}

	/**
	 * Conventional function to free the resultset. 
	 */
	public function free($result)
	{
		mysqli_free_result($result);
	}

	/**
	 * Helper function to get scalar value right out of query and optional arguments
	 * 
	 * Examples:
	 * $name = $db->getOne("SELECT name FROM table WHERE id=1");
	 * $name = $db->getOne("SELECT name FROM table WHERE id=?i", $id);
	 *
	 * @param string $query - an SQL query with placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
	 * @return string|FALSE either first column of the first row of resultset or FALSE if none found
	 */
	public function getOne()
	{
		$query = $this->prepareQuery(func_get_args());
		if ($res = $this->rawQuery($query))
		{
			$row = $this->fetch($res);
			if (is_array($row)) {
				return reset($row);
			}
			$this->free($res);
		}
		return FALSE;
	}

    /**
    * Helper function to get single row right out of query and optional arguments
    * 
    * Examples:
    * $data = $db->getRow("SELECT * FROM table WHERE id=1");
    * $data = $db->getRow("SELECT * FROM table WHERE id=?i", $id);
    *
    * @param string $query - an SQL query with placeholders
    * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
    * @return array either associative array contains first row of resultset or empty array if none found
    */
    public function getRow() : array
    {
        $ret = [];
        
        $query = $this->prepareQuery(func_get_args());
	if ($res = $this->rawQuery($query)) {
            $fetch = $this->fetch($res);
            $ret = (null === $fetch) ? [] : $fetch;
            $this->free($res);
        }
        
        return $ret;
    }

	/**
	 * Helper function to get single column right out of query and optional arguments
	 * 
	 * Examples:
	 * $ids = $db->getCol("SELECT id FROM table WHERE cat=1");
	 * $ids = $db->getCol("SELECT id FROM tags WHERE tagname = ?s", $tag);
	 *
	 * @param string $query - an SQL query with placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
	 * @return array|FALSE either enumerated array of first fields of all rows of resultset or FALSE if none found
	 */
	public function getCol()
	{
		$ret   = array();
		$query = $this->prepareQuery(func_get_args());
		if ( $res = $this->rawQuery($query) )
		{
			while($row = $this->fetch($res))
			{
				$ret[] = reset($row);
			}
			$this->free($res);
		}
		return $ret;
	}

	/**
	 * Helper function to get all the rows of resultset right out of query and optional arguments
	 * 
	 * Examples:
	 * $data = $db->getAll("SELECT * FROM table");
	 * $data = $db->getAll("SELECT * FROM table LIMIT ?i,?i", $start, $rows);
	 *
	 * @param string $query - an SQL query with placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
	 * @return array enumerated 2d array contains the resultset. Empty if no rows found. 
	 */
	public function getAll()
	{
		$ret   = array();
		$query = $this->prepareQuery(func_get_args());
		if ( $res = $this->rawQuery($query) )
		{
			while($row = $this->fetch($res))
			{
				$ret[] = $row;
			}
			$this->free($res);
		}
		return $ret;
	}

	/**
	 * Helper function to get all the rows of resultset into indexed array right out of query and optional arguments
	 * 
	 * Examples:
	 * $data = $db->getInd("id", "SELECT * FROM table");
	 * $data = $db->getInd("id", "SELECT * FROM table LIMIT ?i,?i", $start, $rows);
	 *
	 * @param string $index - name of the field which value is used to index resulting array
	 * @param string $query - an SQL query with placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
	 * @return array - associative 2d array contains the resultset. Empty if no rows found. 
	 */
	public function getInd()
	{
		$args  = func_get_args();
		$index = array_shift($args);
		$query = $this->prepareQuery($args);

		$ret = array();
		if ( $res = $this->rawQuery($query) )
		{
			while($row = $this->fetch($res))
			{
				$ret[$row[$index]] = $row;
			}
			$this->free($res);
		}
		return $ret;
	}

	/**
	 * Helper function to get a dictionary-style array right out of query and optional arguments
	 * 
	 * Examples:
	 * $data = $db->getIndCol("name", "SELECT name, id FROM cities");
	 *
	 * @param string $index - name of the field which value is used to index resulting array
	 * @param string $query - an SQL query with placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the query
	 * @return array - associative array contains key=value pairs out of resultset. Empty if no rows found. 
	 */
	public function getIndCol()
	{
		$args  = func_get_args();
		$index = array_shift($args);
		$query = $this->prepareQuery($args);

		$ret = array();
		if ( $res = $this->rawQuery($query) )
		{
			while($row = $this->fetch($res))
			{
				$key = $row[$index];
				unset($row[$index]);
				$ret[$key] = reset($row);
			}
			$this->free($res);
		}
		return $ret;
	}

	/**
	 * Function to parse placeholders either in the full query or a query part
	 * unlike native prepared statements, allows ANY query part to be parsed
	 * 
	 * useful for debug
	 * and EXTREMELY useful for conditional query building
	 * like adding various query parts using loops, conditions, etc.
	 * already parsed parts have to be added via ?p placeholder
	 * 
	 * Examples:
	 * $query = $db->parse("SELECT * FROM table WHERE foo=?s AND bar=?s", $foo, $bar);
	 * echo $query;
	 * 
	 * if ($foo) {
	 *     $qpart = $db->parse(" AND foo=?s", $foo);
	 * }
	 * $data = $db->getAll("SELECT * FROM table WHERE bar=?s ?p", $bar, $qpart);
	 *
	 * @param string $query - whatever expression contains placeholders
	 * @param mixed  $arg,... unlimited number of arguments to match placeholders in the expression
	 * @return string - initial expression with placeholders substituted with data. 
	 */
	public function parse()
	{
		return $this->prepareQuery(func_get_args());
	}

	/**
	 * function to implement whitelisting feature
	 * sometimes we can't allow a non-validated user-supplied data to the query even through placeholder
	 * especially if it comes down to SQL OPERATORS
	 * 
	 * Example:
	 *
	 * $order = $db->whiteList($_GET['order'], array('name','price'));
	 * $dir   = $db->whiteList($_GET['dir'],   array('ASC','DESC'));
	 * if (!$order || !dir) {
	 *     throw new http404(); //non-expected values should cause 404 or similar response
	 * }
	 * $sql  = "SELECT * FROM table ORDER BY ?p ?p LIMIT ?i,?i"
	 * $data = $db->getArr($sql, $order, $dir, $start, $per_page);
	 * 
	 * @param string $iinput   - field name to test
	 * @param  array  $allowed - an array with allowed variants
	 * @param  string $default - optional variable to set if no match found. Default to false.
	 * @return string|FALSE    - either sanitized value or FALSE
	 */
	public function whiteList($input,$allowed,$default=FALSE)
	{
		$found = array_search($input,$allowed);
		return ($found === FALSE) ? $default : $allowed[$found];
	}

	/**
	 * function to filter out arrays, for the whitelisting purposes
	 * useful to pass entire superglobal to the INSERT or UPDATE query
	 * OUGHT to be used for this purpose, 
	 * as there could be fields to which user should have no access to.
	 * 
	 * Example:
	 * $allowed = array('title','url','body','rating','term','type');
	 * $data    = $db->filterArray($_POST,$allowed);
	 * $sql     = "INSERT INTO ?n SET ?u";
	 * $db->query($sql,$table,$data);
	 * 
	 * @param  array $input   - source array
	 * @param  array $allowed - an array with allowed field names
	 * @return array filtered out source array
	 */
	public function filterArray($input,$allowed)
	{
		foreach(array_keys($input) as $key )
		{
			if ( !in_array($key,$allowed) )
			{
				unset($input[$key]);
			}
		}
		return $input;
	}

	/**
	 * Function to get last executed query. 
	 * 
	 * @return string|NULL either last executed query or NULL if were none
	 */
	public function lastQuery()
	{
		$last = end($this->stats);
		return $last['query'];
	}

	/**
	 * Function to get all query statistics. 
	 * 
	 * @return array contains all executed queries with timings and errors
	 */
	public function getStats()
	{
		return $this->stats;
	}
        
          
    /**
     * Lock tables by array
     * 
     * key - table name without prefix
     * value - lock type (READ or WRITE)
     * 
     * @param array $tables
     * @return bool
     */
    public function setTableLock(array $tables) : bool
    {
        $res = false;
        $error = [];
        
        $lockSql = [];
        $lockWhitelist = ['read', 'write'];
        foreach ($tables AS $tableNameRaw => $lockType) {
            $tableNameArr = explode(' AS ', $tableNameRaw);
            $tableName = (string)$tableNameArr[0];
            $tableAlias = (isset($tableNameArr[1])) ? ' AS `'.$tableNameArr[1].'`' : '';
            $lockTypeValidate = mb_strtolower($lockType);
            if (!in_array($lockTypeValidate, $lockWhitelist, true)) {
                $error[] = 'table lock for table '.$tableName.' unknown;';
            } else if (empty($tableName)) {
                $error[] = 'table name cannot be empty';
            } else {
                $tableNameFull = $this->getTableNameFull($tableName);
                $lockSql[] = $tableNameFull.$tableAlias.' '.mb_strtoupper($lockType);
            }
        }
        
        if (count($error) > 0) {
            $res = false;
            $errorMess = 'Table lock error(s): '.join(',', $error);
            $this->error($errorMess);
        }
        if (count($lockSql) > 0) {
            $query = 'LOCK TABLES '. join(', ', $lockSql);
            $result = $this->query($query);
            if (true === $result) {
                $res = true;
            }
        }
        
        return $res;
    }
    
    public function setTableUnlock() : bool
    {
        $res = false;
        
        $query = 'UNLOCK TABLES';
        $result = $this->query($query);
        if (true === $result) {
            $res = true;
        }
        
        return $res;
    }
    
    public function escapeByType($value, string $type)
    {
        $funcName = 'escape'.ucfirst($type);
        
        return $this->$funcName($value);
    }
    
//    public function getDateNow() : string
//    {
//        $date = new DateTime('now', $this->timezone);
//        
//        return $date->format('Y-m-d');
//    }
//    
//    public function getDateTiemNow() : string
//    {
//        $date = new DateTime('now', $this->timezone);
//        
//        return $date->format('Y-m-d H:i:s');
//    }

    /**
	 * protected function which actually runs a query against Mysql server.
	 * also logs some stats like profiling info and error message
	 * 
	 * @param string $query - a regular SQL query
	 * @return mysqli result resource or FALSE on error
	 */
	protected function rawQuery($query)
	{
		$start = microtime(TRUE);
		$res   = mysqli_query($this->conn, $query);
		$timer = microtime(TRUE) - $start;

		$this->stats[] = array(
			'query' => $query,
			'start' => $start,
			'timer' => $timer,
		);
		if (!$res)
		{
			$error = mysqli_error($this->conn);
			
			end($this->stats);
			$key = key($this->stats);
			$this->stats[$key]['error'] = $error;
			$this->cutStats();
			
			$this->error($error.' Full query: ['.$query.']');
		}
		$this->cutStats();
		return $res;
	}

    protected function prepareQuery($args) 
    {
        $query = '';
        $raw = array_shift($args);
        /** if set modifier "u" in end pattern and in $raw the variable will be 'some text '.inet_pton('11.25.33.132').' some text' - pattern not work */
        $array = preg_split('~(\?[tdnsiuap])~', $raw, null, PREG_SPLIT_DELIM_CAPTURE);
        $anum = count($args);
        $pnum = floor(count($array) / 2);
        if ($pnum != $anum) {
            $this->error("Number of args ($anum) doesn't match number of placeholders ($pnum) in [$raw]");
        }

        foreach ($array as $i => $part) {
            if (($i % 2) == 0) {
                $query .= $part;
                continue;
            }

            $value = array_shift($args);
            switch ($part) {
                case '?n':
                    $part = $this->escapeIdent($value);
                    break;
                case '?d':
                    $part = $this->escapeDate($value);
                    break;
                case '?t':
                    $part = $this->escapeTable($value);
                    break;
                case '?s':
                    $part = $this->escapeString($value);
                    break;
                case '?i':
                    $part = $this->escapeInt($value);
                    break;
                case '?a':
                    $part = $this->createIN($value);
                    break;
                case '?u':
                    $part = $this->createSET($value);
                    break;
                case '?p':
                    $part = $value;
                    break;
            }
            $query .= $part;
        }
        return $query;
    }

        protected function escapeInt($value)
	{
            if ($value === 'DEFAULT') {
                return 'DEFAULT';
            }
		if ($value === NULL)
		{
			return 'NULL';
		}
		if(!is_numeric($value))
		{
			$this->error("Integer (?i) placeholder expects numeric value, ".gettype($value)." given");
			return FALSE;
		}
		if (is_float($value))
		{
			$value = number_format($value, 0, '.', ''); // may lose precision on big numbers
		} 
		return $value;
	}

    public function escapeString($value)
    {
        if (null === $value) {
            $res = 'NULL';
        } else {
            $res = "'".$this->conn->escape_string($value)."'";
        }

    return $res;
    }
        
    protected function escapeBool(bool $value) : string
    {
        $res = (false === $value) ? '0' : '1';
        return '\''.$res.'\'';
    }
        
        protected function escapeIdent($value)
	{
		if ($value)
		{
			return "`".str_replace("`","``",$value)."`";
		} else {
			$this->error("Empty value for identifier (?n) placeholder");
		}
	}
        
    protected function escapeDate(DateTime $datetime) : string
    {
        $datetime->setTimezone($this->timezone);
        $datetimeString = $datetime->format('Y-m-d H:i:s');
        
        return '"'.$this->conn->escape_string($datetimeString).'"';
    }

    protected function escapeTable($value)
    {
        if ($value) {
            return "`".$this->tablePrefix.str_replace("`","``",$value)."`";
        } else {
            $this->error("Empty value for identifier (?n) placeholder");
        }
    }

	protected function createIN($data)
	{
		if (!is_array($data))
		{
			$this->error("Value for IN (?a) placeholder should be array");
			return;
		}
		if (!$data)
		{
			return 'NULL';
		}
		$query = $comma = '';
		foreach ($data as $value)
		{
			$query .= $comma.$this->escapeString($value);
			$comma  = ",";
		}
		return $query;
	}

	protected function createSET($data)
	{
		if (!is_array($data))
		{
			$this->error("SET (?u) placeholder expects array, ".gettype($data)." given");
			return;
		}
		if (!$data)
		{
			$this->error("Empty array for SET (?u) placeholder");
			return;
		}
		$query = $comma = '';
		foreach ($data as $key => $value)
		{
			$query .= $comma.$this->escapeIdent($key).'='.$this->escapeString($value);
			$comma  = ",";
		}
		return $query;
	}

	protected function error($err)
	{
		$err  = __CLASS__.": ".$err;

		if ( $this->emode == 'error' )
		{
			$err .= ". Error initiated in ".$this->caller().", thrown";
			trigger_error($err,E_USER_ERROR);
		} else {
                    /** 
                     * After execute this simple code:
                     *   $message = 'some text '.inet_pton('11.25.33.132'); // And others many IPs
                     *   throw new \Exception($message);
                     * PHP returns Fatal Error
                     * Fatal error</b>: in ...
                     * Solved: $safeErrorMwssage = preg_replace( '/[^[:print:]\r\n]/', 'X', $err);
                     */
                    $safeErrorMessage = preg_replace( '/[^[:print:]\r\n]/', 'XxX', $err);
	            throw new $this->exname($safeErrorMessage);
		}
	}

	protected function caller()
	{
		$trace  = debug_backtrace();
		$caller = '';
		foreach ($trace as $t)
		{
			if ( isset($t['class']) && $t['class'] == __CLASS__ )
			{
				$caller = $t['file']." on line ".$t['line'];
			} else {
				break;
			}
		}
		return $caller;
	}

	/**
	 * On a long run we can eat up too much memory with mere statsistics
	 * Let's keep it at reasonable size, leaving only last 100 entries.
	 */
	protected function cutStats()
	{
		if ( count($this->stats) > 100 )
		{
			reset($this->stats);
			$first = key($this->stats);
			unset($this->stats[$first]);
		}
	}
    
        
    public function convertByValueType($value) : string
    {
        $typeName = gettype($value);
        $methodName = 'convert'.ucfirst(mb_strtolower($typeName));
        
        $valueConverted = $this->$methodName($value);
        
        return $valueConverted;
    }
    
    public function convertNull($value) : string
    {
        $valueConverted = 'NULL';
         
        return $valueConverted;
    }
    
    public function convertInteger(int $value) : string
    {
        $valueConverted = (string)$value;
         
        return $valueConverted;
    }
    
    public function convertString(string $value) : string
    {
        $valueConverted = $this->escapeString($value);
        
        return $valueConverted;
    }
    
    public function convertBoolean(bool $value) : string
    {
        if (true === $value) {
            $valueConverted = '\'1\'';
        } else {
            $valueConverted = '\'0\'';
        }
        
        return $valueConverted;
    }
}
