<?php
class RedirectResult implements IStormResult
{
	private $url;
	
	public function __construct($url)
	{
		$this->url = $url;
	}
	
	public function ProcessResult()
	{
		header("Location:". $this->url);
	}
}
?>