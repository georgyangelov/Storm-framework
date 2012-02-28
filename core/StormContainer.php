<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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