<?php
/**
 * StormPDO
 * 
 * @package Storm Framework
 * @author Stormbreaker
 * @copyright Stormbreaker
 * @version 2.0
 * @access public
 */
class StormPDO extends PDO
{
	const MYSQL_TIMESTAMP = '%Y-%m-%d %H:%M:%S';
	
	public function __construct($dsn, $username = null, $password = null, $driver_options = null)
	{
		parent::__construct($dsn, $username, $password, $driver_options);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array ('StormPDOStatement', array($this)));
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}
	
	/**
	 * Execute SQL and return num of affected rows
	 * 
	 * @param string Query
	 * @param string Parameters
	 * @return int Num affected rows
	 */
	public function exec($statement, $values = null)
	{
		if ( is_array($values) )
		{
			$stmt = $this->prepare($statement);
			$stmt->execute($values);
			
			return $stmt->rowCount();
		}
		else
			return parent::exec($statement);
	}
	
	/**
	 * Execute query and return StormPDOStatement
	 * 
	 * @param string $statement
	 * @param mixed $var
	 * @param mixed $obj1
	 * @param mixed $obj2
	 * @return
	 */
	public function query($statement, $var = null, $obj1 = null, $obj2 = null)
	{
		if ( is_array($var) )
		{
			$stmt = $this->prepare($statement);
		
			$stmt->execute($var);
			return $stmt;
		}
		else
			return parent::query($statement);
	}
	
	/** MemCache functions */
	public function CachedQuery($statement, $var = null, $key = null, $expire = null)
	{
		if ( !class_exists('MemoryCache') )
			return $this->query($statement, $var)->fetchAll(PDO::FETCH_OBJ);
		
		if ( $key === null || empty($key) )
			$key = 'slides:generic_query_'.md5($statement).( $var !== null ? md5(serialize($var)) : '' );
		
		$t =& $this;
		return MemoryCache::wrap($key, function() use ($statement, $var, $t){
			return $t->query($statement, $var)->fetchAll(PDO::FETCH_OBJ);
		}, $expire);
	}
	
	/**
	 * Get the time difference and display in current language
	 * 
	 * @param int $time UNIX timestamp of the date and time
	 * @param array $lang Array with language constants
	 * @return string
	 */
	public static function TimeElapsed($time, $lang)
	{
		$t = strtotime('now') - $time;
				
		$y = (int)strftime('%Y', $t) - 1970;
		if ( $y > 0 )
			return ( $y - 1970 ) . ' ' . ( $y == 1 ? $lang['years_1'] : $lang['years'] );
		
		$m = (int)strftime('%m', $t) - 1;
		if ( $m > 0 )
			return $m . ' ' . ( $m == 1 ? $lang['months_1'] : $lang['months'] );
			
		$w = (int)strftime('%W', $t);
		if ( $w > 0 )
			return $w . ' ' . ( $w == 1 ? $lang['weeks_1'] : $lang['weeks'] );
			
		$d = (int)strftime('%d', $t) - 1;
		if ( $d > 0 )
			return $d . ' ' . ( $d == 1 ? $lang['days_1'] : $lang['days'] );
			
		$H = (int)strftime('%H', $t) - (int)strftime('%H', 0);
		if ( $H > 0 )
			return $H . ' ' . ( $H == 1 ? $lang['hours_1'] : $lang['hours'] );
			
		$M = (int)strftime('%M', $t);
		if ( $M > 0 )
			return $M . ' ' . ( $M == 1 ? $lang['minutes_1'] : $lang['minutes'] );
			
		$S = (int)strftime('%S', $t);
		if ( $S > 0 )
			return $S . ' ' . ( $S == 1 ? $lang['seconds_1'] : $lang['seconds'] );
	}

}

/**
 * PSPDOStatement
 * 
 * @package PHPStorm
 * @author Stormbreaker
 * @copyright Stormbreaker
 * @version 1.1
 * @access public
 */
class StormPDOStatement extends PDOStatement
{
	protected $pdo;
	/**
	 * PSPDOStatement::__construct()
	 * 
	 * @param mixed $pdo
	 * @return
	 */
	protected function __construct($pdo)
	{
		$this->pdo = $pdo;
	}
	
	/**
	 * PSPDOStatement::execute()
	 * 
	 * @param mixed $params
	 * @return
	 */
	public function execute($params = array())
	{
		if ( is_array($params) )
		foreach ( $params as $key => $value )
		{
			if ( is_int($key) )
				$key += 1;
            else
                $key = ':'.$key;
			
			if ( is_array($value) )
				$this->bindValue($key, $value[0], $value[1]);
			elseif ( is_int($value) )
				$this->bindValue($key, $value, PDO::PARAM_INT);
			elseif ( is_bool($value) )
				$this->bindValue($key, $value, PDO::PARAM_BOOL);
			elseif ( is_null($value) )
				$this->bindValue($key, $value, PDO::PARAM_NULL);
			else
				$this->bindValue($key, $value);
		}
		
		parent::execute();
		
		return $this;
	}
	
	/**
	 * PSPDOStatement::fetch()
	 * 
	 * @param mixed $fetch_style
	 * @param mixed $cursor_orientation
	 * @param integer $cursor_offset
	 * @return
	 */
	public function fetch( $fetch_style = PDO::FETCH_ASSOC, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0 )
	{
		return parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
	}
}
?>