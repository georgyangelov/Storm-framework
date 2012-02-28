<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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