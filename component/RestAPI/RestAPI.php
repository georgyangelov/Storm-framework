<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class RestAPI extends StormComponent
{
	public function __construct()
	{
		parent::__construct(array(
			'routes' => array(
				'user' => array(
					'user_all',
					'user_by_id',
					'user_by_name'
				),
				'document' => array(
					'document_all',
					'document_by_id'
				)
			),
			
			'default' => 'index'
		));
	}
	
	public function _load()
	{
        layout::Unload();
	}
	
    public function _call($method, $params)
    {        
        echo 'Called: '. $method .'('. implode(', ', $params) .');';
    }
    
    public function _invalidParams($method, $name, $val, $type)
    {
    	echo 'Called _invalidParams('.$method.', '.$name.', '.$val.', '.$type.');';
    	
    	var_dump($method);
    	var_dump($name);
    	var_dump($val);
    	var_dump($type);
    	
    	return true;
    }
    
    public function _404($method, $explicit)
    {
    	echo 'Ooops the page `'. $method .'` can\'t be found!';
    	
    	return true;
    }
    
    /**
     * Index page. Display information
     */
    public function index()
    {
		return new Status(404);
    }
    
    /**
     * Fetch last users' data
     */
    public function user_all()
    {
    	
        return;
    }

    /**
     * Fetch a specific user's data
     * 
     * @param int
     */
    public function user_by_id($id__int)
    {
    	
        return;
    }

    /**
     * Fetch a specific user's data
     * 
     * @param string
     */
    public function user_by_name($username)
    {
    	
        return;
    }
    
    /**
     * Fetch last documents' data
     */
    public function document_all()
    {
    	
    	return;
    }
    
    /**
     * Fetch a specific document's data
     * 
     * @route document
     */
    public function document_by_id($id)
    {
    	
    	return;
    }
}
?>