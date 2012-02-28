<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
abstract class StormComponent
{
	public $Name;
	public $AbsolutePath, $RelativePath;
	public $IsDefault = false;
	public $config = array(
			
			'default' => 'index',
			'routes' => array()
			
		);
	
	public function __construct($config = array())
	{
		$this->Name = get_class($this);
		$this->config = array_merge($this->config, $config);
		
		$this->AbsolutePath = Storm::ToAbsolute(Storm::$ComponentsPath . '/' . $this->Name);
		$this->RelativePath = Storm::ToRelative($this->AbsolutePath);
	}
	
	protected function GetLink($page = null, $variables = null)
	{
		return VirtualPages::GetLink($this->Name, $page, $variables);
	}
	
	protected function GetAbsolute($file)
	{
		return Storm::FixPath($this->AbsolutePath . '/' . $file);
	}
	
	protected function GetRelative($file)
	{
		return $this->RelativePath . '/' . $file;
	}
	
	public static function __callStatic($name, $args)
	{
		return Storm::$LoadedComponents[get_called_class()]->CallMethod($name, $args);
	}
}
?>