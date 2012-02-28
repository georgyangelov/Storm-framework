<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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
			$magic = $this->CallMagic('call', array($name, $vars));
			
			if ( !is_null($magic) )
				return $magic;
		}
		
		$method = $this->reflection->getMethod($name);
		
		if ( $method->isProtected() )
			$method->setAccessible(true);
			
		return $method->invokeArgs($this->instance, $vars);
	}
	
	public function CallMagic($name, $params = array(), $force = false, $callLoad = true)
	{
		if ( $callLoad && !$this->loaded )
		{
			$this->loaded = true;
			$this->CallMagic('load');
		}
		
		if ( !$force && !$this->reflection->hasMethod('_'.$name) )
			return;
		
		$method = $this->reflection->getMethod('_'.$name);
		
		return $this->invokeLazyFunction($method, $params);
	}
	
	public function CallVirtual($name = null, $params = array(), $withProtected = false)
	{
		if ( is_null($name) )
			$name = $this->instance->config['default'];
		
		$is404 = false;
		try
		{
			if ( isset($this->instance->config['routes'][$name]) )
			{
				$func = $this->FindBestOverload($this->instance->config['routes'][$name], $params);
				$name = $func['name'];
				$p = $func['params'];
			}
			elseif ( $this->GetReflection()->HasMethod($name) )
			{
				$m = $this->GetReflection()->GetMethod($name);
				
				if ( !$m->isProtected() || ( $m->isProtected() && $withProtected ) )
					$p = self::getLazyParams($m, $params);
				else
					throw new NoSuchMethodException($name);
			}
			else
				throw new NoSuchMethodException($name);
			
			try
			{
				$r = $this->CallMethod($name, $p);
			}
			catch ( Exception $e )
			{
				$r = $this->CallMagic('catch', array($name, $e, $p), false, false);
				
				if ( is_null($r) )
					throw $e;
			}
		}
		catch ( ParamsException $e )
		{
			$v = array( $name, $e->getName(), $e->getValue(), $e->getType() );
			$this->CallMagic('call', array( $name, $v ));
			$r = $this->CallMagic('invalidParams', $v );
			$name = '_invalidParams';
			
			if ( is_null($r) )
				throw $e;
		}
		catch ( NoSuchMethodException $e )
		{
			$r = $this->call404( $e->getMethod() );
			$is404 = true;
			
			if ( is_null($r) )
				throw $e;
		}
		
		if ( $r instanceof IStormResult )
			$r->ProcessResult();
		
		if ( !$is404 && $r instanceof Status && $r->getInvoke() && $r->getCode() == 404 )
		{
			$p = $this->call404($name, true);
			
			if ( !is_null($p) )
				$r = $p;
		}
		
		return $r;
	}
	
	private function call404($method, $explicit = false)
	{
		$r = $this->CallMagic('404', array( $method, $explicit ));
		
		if ( $r instanceof Status && $r->getCode() == 404 )
			$r->used();
			
		return $r;
	}
	
	private function FindBestOverload($funcs, $params)
	{
		$reflect = $this->GetReflection();
		
		usort($funcs, function($func1, $func2) use ($reflect) {
			$r1 = $reflect->GetMethod($func1);
			$r2 = $reflect->GetMethod($func2);
			
			$required = ( $r1->getNumberOfRequiredParameters() - $r2->getNumberOfRequiredParameters() );
			if ( $required != 0 )
				return $required;
			
			return ( $r1->getNumberOfParameters() - $r2->getNumberOfParameters() );
		});
		$funcs = array_reverse($funcs);
		
		$lastExc = null;
		foreach ( $funcs as $function )
		{
			try
			{
				return array( 'name' => $function, 'params' => $this->getLazyParams($reflect->getMethod($function), $params) );
			}
			catch ( NoRequiredParamsException $e )
			{
				$lastExc = $e;
			}
		}
		
		if ( $lastExc !== null )
			throw $lastExc;
		else
			throw new Exception('This shouldn\'t be happening?!?!?');
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
				$v = self::parseParam($name, $type, $vals[$name]);
				
				$ar[] = $v;
			}
			elseif ( $param->isOptional() )
				$ar[] = $param->getDefaultValue();
			else
				throw new NoRequiredParamsException($name, '', $type);
		}
		
		return $ar;
	}
	
	private static function parseParam($name, $type, $val)
	{
		if ( $type == 'string' )
			return $val;
		elseif ( $type == 'int' || $type == 'integer' )
		{
			if ( is_numeric($val) )
				return (int)$val;
			else
				throw new InvalidParamsException($name, $val, $type);
		}
		elseif ( $type == 'bool' || $type == 'boolean' )
		{
			if ( $val === true || $val == '1' || $val == 'true' || $val == 'TRUE' )
				return true;
			elseif ( $val === false || $val == '0' || $val == 'false' || $val == 'FALSE' )
				return false;
			else
				throw new InvalidParamsException($name, $val, $type);
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
		$this->CallMagic('unload', array(), false, false);
	}
}

class NoSuchMethodException extends Exception
{
	private $name;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function getMethod()
	{
		return $this->name;
	}
}

class ParamsException extends Exception
{
	private $type, $val, $name;
	
	public function __construct($name, $val, $type)
	{
		parent::__construct('Passed argument `'. $name .'` with value \''. $val .'\' that should be of type `'. $type .'`');
		
		$this->name = $name;
		$this->type = $type;
		$this->val = $val;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getValue()
	{
		return $this->val;
	}
}
class NoRequiredParamsException extends ParamsException { }
class InvalidParamsException extends ParamsException { }
?>