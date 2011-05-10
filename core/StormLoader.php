<?php
/**
 * Storm framework v2 ALPHA
 * 
 * @author Stormbreaker
 * @copyright 2011
 */
class StormLoader
{	
	public $name, $instance;
	private $reflection, $loaded = false;
	
	public function __construct($name)
	{
		$this->name = $name;
		$this->instance = new $name();
		
		$this->reflection = new ReflectionClass($this->instance);
	}
	
	public function GetReflection()
	{
		return $this->reflection;
	}
	
	public function IsMethod($name, $withProtected = true)
	{
		if ( $name[0] == '_' )
			return false;
		
		if ( isset($this->instance->config['routes'][$name]) )
			return true;
		
		try
		{
			$method = $this->reflection->getMethod($name);
			
			if ( $method->isPublic() || ( $withProtected && $method->isProtected() ) )
				return true;
			else
				return false;
		} catch ( ReflectionException $e )
		{
			return false;
		}
	}
	
	public function CallMethod($name, $vars = array(), $callMagic = true)
	{
		if ( $callMagic )
		{
			if ( !$this->loaded )
				$this->CallMagic('load');
			
			$magic = $this->CallMagic('call', array($name, $vars));
			
			if ( !is_null($magic) )
				return $magic;
		}
		
		$this->loaded = true;
		
		$method = $this->reflection->getMethod($name);
		
		if ( $method->isProtected() )
			$method->setAccessible(true);
			
		return $method->invokeArgs($this->instance, $vars);
	}
	
	public function CallMagic($name, $params = array(), $force = false)
	{
		if ( !$force && !$this->reflection->hasMethod('_'.$name) )
			return;
		
		$method = $this->reflection->getMethod('_'.$name);
		
		return $this->invokeLazyFunction($method, $params);
	}
	
	public function CallVirtual($name = null, $params = array())
	{
		if ( is_null($name) )
			$name = $this->instance->config['default'];
			
		
	}
	
	private static function getLazyParams($reflect, $vals = array())
	{		
		if ( $reflect->getNumberOfParameters() == 0 )
			return array();
		
		$params = $reflect->getParameters();
		$ar = array();
		
		foreach ( $params as $param )
		{
			$name = $param->getName();
			$type = 'string';
			
			if ( preg_match("/^(.+)__([a-z]+)$/i", $name, $match) )
			{
				$name = $match[1];
				$type = $match[2];
			}
			
			if ( array_key_exists($name, $vals) )
			{
				try
				{
					$v = self::parseParam($type, $vals[$name]);
				} catch (Exception $e)
				{
					$r = $this->CallMagic('invalidParams', array( $name, $vals[$name], $type ));
					
					if ( is_null($r) )
						return false;
					else
						return $r;
				}
				
				$ar[] = $v;
			}
			elseif ( $param->isOptional() )
				$ar[] = $param->getDefaultValue();
			else
				return false;
		}
		
		return $ar;
	}
	
	private static function parseParam($type, $val)
	{
		if ( $type == 'string' )
			return $val;
		elseif ( $type == 'int' || $type == 'integer' )
		{
			if ( is_numeric($val) )
				return (int)$val;
			else
				throw new Exception('Invalid argument \''.$val.'\' of type \''.$type.'\'');
		}
		elseif ( $type == 'bool' || $type == 'boolean' )
		{
			if ( $val === true || $val == '1' || $val == 'true' || $val == 'TRUE' )
				return true;
			elseif ( $val === false || $val == '0' || $val == 'false' || $val == 'FALSE' )
				return false;
			else
				throw new Exception('Invalid argument \''.$val.'\' of type \''.$type.'\'');
		}
		else
			throw new Exception('Unknown argument type \''.$type.'\'');
	}
	
	private function invokeLazyFunction($method, $params = array())
	{
		$max = count($params);
		
		if ( $method->isProtected() )
			$method->setAccessible(true);
		
		if ( $max == 0 )
			return $method->invoke($this->instance);
			
		$num = $method->getNumberOfParameters();
		
		return $method->invokeArgs($this->instance, array_slice($params, 0, $num));
	}
	
	public function Unload()
	{
		$this->CallMagic('unload');
	}
}

class NoSuchMethodException extends Exception { }
?>