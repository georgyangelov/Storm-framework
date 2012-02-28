<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
abstract class StormModel
{
	private static $instances = array();
	
	public function __construct()
	{
		self::$instances[get_class($this)] =& $this;
	}
	
	public static function __callStatic($name, $args)
	{
		if ( !isset(self::$instances[get_called_class()]) )
			throw new Exception('ERROR! Did you forget to call "parent::__construct()" in your model\'s constructor?');
		
		$obj = self::$instances[get_called_class()];
		
		try
		{
			$method = new ReflectionMethod($obj, $name);
			
			if ( $method->isProtected() )
			{
				foreach ( $method->getParameters() as $k => $param )
				{
					if ( $param->isPassedByReference() )
						$args[$k] =& $args[$k];
				}
				
				$method->setAccessible(true);
				return $method->invokeArgs($obj, $args);
			}
			else
				throw new Exception('The called method '.$name.' must be declared protected');
		}
		catch ( ReflectionException $e )
		{
			try
			{
				$method = new ReflectionMethod($obj, '_call');
				
				if ( $method->isProtected() )
					$method->setAccessible(true);
					
				return $method->invokeArgs($obj, array( $name, $args ));
			}
			catch ( ReflectionException $e )
			{
				throw new Exception('Called non-existent method \''.$name.'\' in model \''.get_called_class().'\'');
			}
		}
	}
	
	public function __call($name, $args)
	{
		try
		{
			$method = new ReflectionMethod($this, $name);
			
			$method->setAccessible(true);
			return $method->invokeArgs($this, $args);
		}
		catch ( ReflectionException $e )
		{
			try
			{
				$method = new ReflectionMethod($this, '_call');
				
				if ( $method->isProtected() )
					$method->setAccessible(true);
					
				return $method->invokeArgs($this, array( $name, $args ));
			}
			catch ( ReflectionException $e )
			{
				throw new Exception('Called non-existent method \''.$name.'\' in model \''.get_class($this).'\'');
			}
		}
	}
	
	public static function _get()
	{
		if ( !isset(self::$instances[get_called_class()]) )
			throw new Exception('Model '.get_called_class().' should have been created by the autoloader!');
			
		return self::$instances[get_called_class()];
	}
}
?>