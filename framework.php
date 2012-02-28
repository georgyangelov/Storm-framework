<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class Storm
{
	private static $Initialized = false, $DocRoot, $Container, $AutoloadRegistered = false;
	public static $AbsolutePath, $RelativePath, $CorePath, $ClassesPath, $ComponentsPath, $ModelsPath;
	public static $LoadedComponents = array();
	
	public static function Init($CorePath = 'core', $ClassPath = 'class', $ComponentPath = 'component', $ModelPath = 'model')
	{
		self::$CorePath = $CorePath;
		self::$ClassesPath = $ClassPath;
		self::$ComponentsPath = $ComponentPath;
		self::$ModelsPath = $ModelPath;
		
		self::$DocRoot = self::FixPath($_SERVER['DOCUMENT_ROOT']);
		self::$AbsolutePath = dirname(__FILE__);
		self::$RelativePath = self::ToRelative(self::$AbsolutePath);
		
		if ( !self::$AutoloadRegistered )
		{
			spl_autoload_register(array('Storm', 'LoadClass'));
			spl_autoload_register(array('Storm', 'LoadModel'));
			spl_autoload_register(array('Storm', 'LoadCore'));
			
			self::$AutoloadRegistered = true;
		}
		
		register_shutdown_function( array('Storm', 'UnloadAll') );
		
		self::$Initialized = true;
	}
	
	public static function SetContainer($container)
	{
		self::$Container = $container;
		self::$Container->callLoad();		
	}
	
	public static function RequireFile($path)
	{
		$path = Storm::FixPath(self::$AbsolutePath ."/". self::$ClassesPath ."/". $path);
		
		require_once $path;
	}
	
	public static function LoadClass($name)
	{
		if ( class_exists($name, false) )
			return;
        
		$name = preg_replace("/^\\\?class\\\(.*)/", "$1", $name);
		
		$path = Storm::FixPath(self::$AbsolutePath ."/". self::$ClassesPath ."/". $name .".php");
		if ( is_file($path) )
			require_once $path;
        elseif ( is_file(Storm::FixPath(self::$AbsolutePath ."/". self::$ClassesPath ."/". $name .".class.php")) )
            require_once Storm::FixPath(self::$AbsolutePath ."/". self::$ClassesPath ."/". $name .".class.php");
	}
	public static function LoadCore($name)
	{
		if ( class_exists($name, false) )
			return;
		
		$path = Storm::FixPath(self::$AbsolutePath ."/". self::$CorePath ."/". $name .".php");
		if ( is_file($path) )
			require_once $path;
        elseif ( is_file(Storm::FixPath(self::$AbsolutePath ."/". self::$CorePath ."/". $name .".class.php")) )
            require_once Storm::FixPath(self::$AbsolutePath ."/". self::$CorePath ."/". $name .".class.php");
	}
	public static function LoadModel($realname)
	{
		if ( class_exists($realname, false) )
			return;
		
		$name = preg_replace("/^\\\?model\\\(.*)/", "$1", $realname);
		
		$path = Storm::FixPath(self::$AbsolutePath ."/". self::$ModelsPath ."/". $name .".php");
		if ( is_file($path) )
		{
			require_once $path;
			return self::$Container->get($realname);
		}
	}
	
	public static function LoadComponent($name)
	{
		if ( self::IsLoadedComponent($name) )
			return;
			
		require_once realpath(self::$AbsolutePath .'/'. self::$ComponentsPath .'/'. $name .'/'. $name .'.php');
		
		self::$LoadedComponents[$name] = new StormLoader($name);
	}
	
	public static function IsLoadedComponent($name)
	{
		if ( isset(self::$LoadedComponents[$name]) )
			return true;
		else
			return false;
	}
	
	public static function UnloadAll()
	{
		$ar = array_reverse(self::$LoadedComponents, true);
		
		foreach ( $ar as $loader )
			$loader->Unload();
		
		unset($ar);
		self::$LoadedComponents = array();
	}
	
	public static function FixPath($path)
	{
		return preg_replace("/\\".DIRECTORY_SEPARATOR."$/", '', str_replace(array('\\', '/', '//', '\\\\'), DIRECTORY_SEPARATOR, $path));
	}
	
	public static function ToRelative($path)
	{
 		return str_replace(str_replace("\\", "/", self::$DocRoot), "", str_replace("\\", "/", $path));
	}
	
	public static function ToAbsolute($path)
	{
		return Storm::FixPath(Storm::$AbsolutePath . '/' . str_replace(Storm::$RelativePath, "", $path));
	}
}
?>