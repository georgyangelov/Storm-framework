<?php
class helloworld extends StormComponent
{	
	public function index($var1__bool = false, $var2 = 'default')
	{
		$template = new Template( $this->GetAbsolute('templates/index.php') );
		
		layout::AddVar('test', 'index');
		
        $test = new namespaced\WithNamespace();
        
		return $template;
	}
	
	public function test__xml__()
	{
		layout::AddVar('test', 'test__xml__');
	}
	
	public function test()
	{
		layout::AddVar('test', 'test');
		//return new RedirectResult( VirtualPages::GetLink() );
		
		SimpleCounter::increment(1);
	}
}
?>