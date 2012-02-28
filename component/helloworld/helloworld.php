<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
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