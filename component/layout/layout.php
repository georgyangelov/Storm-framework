<?php
class layout extends StormComponent
{
	private $template, $show = true;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->template = new Template( $this->GetAbsolute('templates/main.php'), true );
	}
	
	protected function file($path)
	{
		return $this->RelativePath.'/templates/'.$path;
	}
	
	protected function _call($name, $vars)
	{
		if ( method_exists($this->template, $name) )
		{
			call_user_func_array(array($this->template, $name), $vars);
			
			return true;
		}
	}
	
	protected function Unload()
	{
		$this->show = false;
	}
	
	protected function _unload()
	{
		if ($this->show && $this->template instanceOf Template )
			$this->template->ShowOutput();
	}
}
?>