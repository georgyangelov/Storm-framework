<?php

/**
 * Restful API component
 * 
 * @package slides.bg
 * @author Stormbreaker
 * @version 0.1
 * @access public
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
			)
		));
	}
	
    public function _call($method, $params)
    {
        layout::Unload();
        
        echo 'Called: '. $method .'('. implode(', ', $params) .');';
    }
    
    /**
     * Index page. Display information
     */
    public function index($var)
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
    public function user_by_id($id)
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