<?php

class SimpleCounter extends StormModel
{
	private $pdo;
	
	public function __construct(StormPDO $pdo)
	{
		parent::__construct();
		
		$this->pdo = $pdo;
	}
	
	protected function increment($id)
	{
		$this->pdo->exec("INSERT INTO `simplecounter` (`id`, `count`) VALUES (?, 1)
			ON DUPLICATE KEY UPDATE `count` = `count` + 1", array(
				(int)$id
			));
			
		$this->testParamValue();
	}
	
	protected function decrement($id)
	{
		$this->pdo->exec("INSERT INTO `simplecounter` (`id`, `count`) VALUES (?, -1)
			ON DUPLICATE KEY UPDATE `count` = `count` - 1", array(
				(int)$id
			));
	}
	
	protected function _call($name, $args)
	{
		var_dump($name);
	}
}
?>