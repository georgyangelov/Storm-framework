<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
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