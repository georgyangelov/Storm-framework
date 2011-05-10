<?php
/**
 * CREATED BY: Георги Сергеев Ангелов (STORMBREAKER)
 */
ob_start();

error_reporting(E_ALL | E_STRICT | E_NOTICE);
set_error_handler(create_function('$a, $b, $c, $d', 'throw new ErrorException($b, 0, $a, $c, $d);'), E_ERROR | E_WARNING | E_RECOVERABLE_ERROR | E_USER_ERROR | E_USER_WARNING );

require_once dirname(__FILE__).'/framework.php';
Storm::Init();

require_once dirname(__FILE__).'/container/application.php';
Storm::SetContainer(new application());

//try
//{
    VirtualPages::ProcessPath();
//}
//catch ( Exception $e )
//{
//	//var_dump( $e->getTrace() );
//	header('HTTP/1.1 500 Internal Server Error');
//}
?>