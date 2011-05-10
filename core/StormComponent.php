<?php
/**
 * Storm framework v2.1
 * 
 * @author Stormbreaker
 * @copyright 2011
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