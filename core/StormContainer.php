<?php
/**
 * Storm framework v2 ALPHA
 * 
 * @author Stormbreaker
 * @copyright 2011
 */
abstract class StormContainer
{
	private $instances = array();
	private $methods = array();
	
	protected function instance($name, $func)
	{
		if ( !isset($this->instances[$name]) )
			$this->instances[$name] = $func($this);
		
		return $this->instances[$name];
	}
	
	public function callLoad()
	{
		try
		{
			$method = new ReflectionMethod($this, '_load');
			$method->setAccessible(true);
			
			$method->invoke($this);
		}
		catch ( ReflectionException $e )
		{
		}
	}
	
	public function get($realname)
	{
		$name = str_replace('\\', '_', $realname);
		$name = preg_replace("/^_?model_(.*)/", "$1", $name);
		
		if ( isset($this->methods[$name]) )
			return $this->methods[$name];
		
		try
		{
			$method = new ReflectionMethod($this, $name);
		}
		catch ( ReflectionException $e )
		{
			$constructor = new ReflectionMethod($realname, '__construct');
			
			if ( $constructor->getNumberOfRequiredParameters() == 0 )
			{
				$this->methods[$name] = new $realname();
				return $this->methods[$name];
			}
			else
				throw new Exception('Tried to access model "'.$realname.'" but it cannot be automatically created. Please use the container.');
		}
		
		$method->setAccessible(true);
			
		$this->methods[$name] = $method->invoke($this);
		return $this->methods[$name];
	}
}
?>