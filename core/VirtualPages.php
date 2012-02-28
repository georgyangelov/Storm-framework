<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class VirtualPages
{
	public static $Component, $Page, $PagePath;
	public static $DefaultComponent = null, $BaseURL = '';
	public static $Component404 = null, $Page404 = null;
	public static $Aliases = array(), $Rewrites = array(), $ReverseRewrites = array(), $Variables = array();
	
	/**
	 * Set the 404 page
	 * 
	 * @param string $Component
	 * @param string $Page
	 */
	public static function Set404($Component, $Page)
	{
		self::$Component404 = $Component;
		self::$Page404 = $Page;
	}
	
	/**
	 * Set the default page
	 * 
	 * @param string $Component
	 * @param string $Page
	 * @param string $Extension
	 */
	public static function SetDefault($Component)
	{
		self::$DefaultComponent = $Component;
	}
	
	/**
	 * Set the base URL to be used in generated URLs
	 * 
	 * @param string $URL
	 */
	public static function SetBaseURL($URL)
	{
		self::$BaseURL = $URL;
	}
	
	/**
	 * Set the current page path
	 * 
	 * @param string $path
	 */
	public static function SetPath($path = null)
	{
		if ( $path === null )
		{
			if ( !isset($_SERVER["REQUEST_URI"]) )
				$path = '/';
			else
				$path = preg_replace("/\?(.*)$/", '', preg_replace("#^". Storm::$RelativePath ."/#", '', $_SERVER["REQUEST_URI"]));
		}
		
		self::$PagePath = self::ProcessRewrites($path);
		
		self::GetPath();
	}
	
	/**
	 * Rewrite the URLs with the values set in Rewrite()
	 * 
	 * @param string $path
	 * @param bool $reverse
	 * 
	 * @return string
	 */
	private static function ProcessRewrites($path, $reverse = false)
	{
		if ( $reverse )
			$rw =& self::$ReverseRewrites;
		else
			$rw =& self::$Rewrites; 
		
		foreach ( $rw as $r )
			$path = preg_replace($r['regex'], $r['replace'], urldecode($path));
			
		return $path;
	}
	
	/**
	 * Process the given path.
	 * 
	 */
	private static function GetPath()
	{
		$Path = explode('/', self::$PagePath);
		
		if ( $Path === false )
			self::$Component = self::$DefaultComponent;
		else
		{
			$varOffset = 2;
			
			if ( empty($Path[0]) || self::IsVar($Path[0]) )
			{
				self::$Component = self::$DefaultComponent;
				$varOffset--;
			}
			else
				self::$Component = $Path[0];
			
		 	if ( empty($Path[1]) || self::IsVar($Path[1]) )
		 	{
				self::$Page = null;
				$varOffset--;
			}
			else
				self::$Page = $Path[1];
			
			for ( $i = $varOffset; $i < count($Path); $i++ )
			{
				if ( trim($Path[$i]) == '' )
					continue;
				
				$segment = explode(':', $Path[$i]);
				
				if ( isset($segment[1]) )
				{
					self::$Variables[$segment[0]] = str_replace('%2F', '/', $segment[1]);
					$_GET[$segment[0]] = self::$Variables[$segment[0]];
				}
				else
				{
					self::$Variables[$segment[0]] = '';
					$_GET[$segment[0]] = '';
				}
			}
		}
	}
	
	private static function IsVar($str)
	{
		if ( strpos($str, ':') )
			return true;
			
		return false;
	}
	
	private static function SwitchExt($string)
	{
		if ( preg_match("/^(.+)\.(.+)$/i", $string, $m) )
			return $m[1].'__'.$m[2].'__';
		
		return $string;
	}
	
	/**
	 * Process the given (in SetPath) path and call the appropriate component
	 * 
	 * @return mixed The return value of the function in the component
	 */
	public static function ProcessPath()
	{
		if ( self::$DefaultComponent === null )
		{
			$f = array_keys(Storm::$LoadedComponents);
			self::$DefaultComponent = $f[0];
		}
		
		if ( self::$Component === null )
			self::$Component = self::$DefaultComponent;
                
		if ( in_array(self::$Component, self::$Aliases) )
		{
			$ar = array_flip(self::$Aliases);
			self::$Component = $ar[self::$Component];
		}
		
		try
		{
			if ( Storm::IsLoadedComponent(self::$Component) )
				return self::OpenPage(self::$Component, self::SwitchExt(self::$Page));
			elseif ( Storm::IsLoadedComponent(self::$DefaultComponent) )
			{
				self::$Page = self::SwitchExt(self::$Component);
				self::$Component = self::$DefaultComponent;
	
				return self::OpenPage(self::$Component, self::$Page);
			}
			else
				return self::Open404();
		}
		catch ( NoSuchMethodException $e )
		{
			return self::Open404();
		}
	}
	
	/**
	 * Call the 404 (Not Found) page
	 * 
	 * @return mixed The return value of the function in the component
	 */
	public static function Open404()
	{
		header("HTTP/1.0 404 Not Found");
		
		if ( self::$Component404 != null && self::$Page404 != null && Storm::IsLoadedComponent(self::$Component404) )
			return self::OpenPage(self::$Component404, self::$Page404, true, false);
		else
			exit;
			
		return;
	}
	
	/**
	 * Wrapper for the framework's call to a component
	 * 
	 * @param string $Component
	 * @param string $Page
	 * @return mixed The return value of the function in the component
	 */
	public static function OpenPage($Component, $Page = null, $withProtected = false, $call404 = true)
	{
		try
		{
			$return = Storm::$LoadedComponents[$Component]->CallVirtual($Page, $_GET, $withProtected);
			
			if ( $call404 && $return instanceof Status && $return->getInvoke() && $return->getCode() == 404 )
				self::Open404();
		}
		catch ( NoSuchMethodException $e )
		{
			if ( $call404 )
				self::Open404();
			else
				throw $e;
		}
		catch ( InvalidParamsException $e )
		{
			if ( $call404 )
				self::Open404();
			else
				throw $e;
		}
		catch ( NoRequiredParamsException $e )
		{
			if ( $call404 )
				self::Open404();
			else
				throw $e;
		}
	}
	
	/**
	 * Call the default page
	 * 
	 * @return mixed The return value of the function in the component
	 */
	public static function OpenDefault()
	{
		if ( Storm::IsLoadedComponent(self::$DefaultComponent) )
			$class = self::$DefaultComponent;
		else
			throw new Exception("VirtualPages: Default component cannot be loaded! ". Storm::IsLoadedComponent(self::$DefaultComponent));
		
		return self::OpenPage($class);
	}
	
	/**
	 * Return the URL to given page with GET variables in the third parameter.
	 * 
	 * @param string $component
	 * @param string $page
	 * @param array $variables
	 * @return string The URL
	 */
	public static function GetLink($component = null, $page = null, $variables = null, $AppendVars = false, $WithDomain = true)
	{
		if ( preg_match("/^(.+)__(.+)__$/", $page, $m) )
			$page = $m[1].'.'.$m[2];
		else
			$page = $page;
			
		$vars = '';
		
		if ( $AppendVars )
		{
			if ( is_array($variables) )
				$variables = array_merge(self::$Variables, $variables);
			else
				$variables = self::$Variables;	
		}
		
		if ( !$WithDomain )
			$u = '';
		else
			$u = self::$BaseURL;
		
		if ( is_array($variables) )
		{
			foreach ($variables as $k => $v)
			{
				if ( trim($v) === '' || $v === null )
					$vars .= $k . '/';
				else
					$vars .= $k . ':' . str_replace('/', '%2F', $v) . '/';
			}
		}
		
		if ( $component == self::$DefaultComponent || $component === null )
		{
			if ( $page === null )
				return $u.Storm::$RelativePath . "/" . self::ProcessRewrites( $vars, true );
			else
				return $u.Storm::$RelativePath . "/" . self::ProcessRewrites( $page . ( $vars != '' ? '/'.$vars : '' ), true );
		}
		else
		{
			if ( isset(self::$Aliases[$component]) && !empty(self::$Aliases[$component]) )
				$component = self::$Aliases[$component];
			
			if ( $page === null )
				return $u.Storm::$RelativePath . "/" . self::ProcessRewrites( $component . ( $vars != '' ? '/'.$vars : '' ), true );
			else
				return $u.Storm::$RelativePath . "/" . self::ProcessRewrites( $component . '/' . $page . ( $vars != '' ? '/'.$vars : '' ), true );
		}
	}
	
	/**
	 * Return the URL to the currently open page (no normal GETs). Example use in forms or in canonical tag:
	 * <link rel="canonical" href="<?= VirtualPages::GetCanonical() ?>" />
	 * 
	 * @return string The URL
	 */
	 public static function GetCanonical($WithDomain = true, $IgnoredParams = array())
	 {
	 	if ( count($IgnoredParams) > 0 )
	 		return self::GetLink(self::$Component, self::$Page, array_diff_key(self::$Variables, array_flip($IgnoredParams)), false, $WithDomain);
	 	else
	 		return self::GetLink(self::$Component, self::$Page, null, true, $WithDomain);
	 }
	
	/**
	 * Set alias for component name
	 * 
	 * @param string $RealName
	 * @param string $alias
	 */
	public static function SetAlias($RealName, $alias)
	{
		self::$Aliases[$RealName] = $alias;
	}
	
	/**
	 * Set rewrite rule for URLs
	 * 
	 * @param string $regex
	 * @param string $replace
	 * @param bool $reverse
	 */
	public static function Rewrite($regex, $replace, $reverse = false)
	{
		if ( $reverse )
			$rw =& self::$ReverseRewrites;
		else
			$rw =& self::$Rewrites;
		
		$rw[] = array( 'regex' => $regex, 'replace' => $replace );
	}
	
	/**
	 * Check if script is loaded with AJAX.
	 * Usefull for AJAX process scripts
	 */
	public static function IsAJAX()
	{
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) )
        	return true;
		else
      		return false;
	}
}

?>