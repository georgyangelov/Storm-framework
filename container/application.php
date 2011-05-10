<?php

class application extends StormContainer
{
	public function __construct()
	{
		Storm::LoadComponent('helloworld');
		Storm::LoadComponent('layout');
		Storm::LoadComponent('RestAPI');
        
        VirtualPages::SetAlias('RestAPI', 'api');
        
        VirtualPages::SetDefault('helloworld');
        VirtualPages::SetPath();
	}
	
	private function getPDO()
	{
		return $this->instance('database', function($this){
			return new StormPDO('mysql:dbname=test;host=localhost;port=3306', 'root');
		});
	}
	
	protected function SimpleCounter()
	{
		return new SimpleCounter($this->getPDO());
	}
}
?>