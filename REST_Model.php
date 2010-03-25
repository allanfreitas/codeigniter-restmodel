<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Version 1.2 2010-03-23

// DRY'd up the class
// Added decode support for JSON and XML

class REST_Model extends Model
{	
		
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	
	protected $authentication = 'none';		// Can be 'none', 'basic' or 'digest'
	protected $username = FALSE;
	protected $password = FALSE;
	protected $key = FALSE;
	
	
	protected $format = 'html';
	protected $supported_formats = array(
		'html'		=> 'text/html',
		'php' 		=> 'text/plain',
		'json' 		=> 'application/json',
		'xml' 		=> 'application/xml'
	);
	
		
	function __construct()
	{
		parent::Model();
	}
		
   
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// REDIRECT THE REQUESTS TO THE $this->curl FUNCTION AS THEY COME IN
   	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 	


	function get ($url, $parameters = '')
	{
		$this->curl("GET", $url, $parameters);
	}
    
    
	//---------------------------------------------------
    
    
	function post ($url, $parameters = '')
	{
		$this->curl("POST", $url, $parameters);
	}
		
		
	//---------------------------------------------------
		
		
	function delete ($url, $parameters = '')
	{
	  	$this->curl("DELETE", $url, $parameters);
	}

		
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// SET CURL OPTIONS AND EXECUTE THE REQUEST
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 	
		
    
	function curl ($method, $url, $parameters)
	{	    	
    
    	//---------------------------------------------------
    	// Initialise cURL handle and setup a few standard options
    	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array ("Accept: {$this->supported_formats[$this->format]}"));
				
				
		//---------------------------------------------------
		// Set authentication options if needed
		
		if ($this->authentication === 'basic')
   		{
   			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   			curl_setopt($ch,CURLOPT_USERPWD,"{$this->username}:{$this->password}"); 
   		}
   		elseif ($this->authentication === 'digest')
   		{
   			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
   			curl_setopt($ch,CURLOPT_USERPWD,"{$this->username}:{$this->password}"); 
   		}
   		   		
   		   		
   		//---------------------------------------------------
		// Set parameters and any other remaining options
    	
		if (is_array($parameters))
		{
			$parameters = http_build_query($parameters);
		}
    	
		switch ($method) 
		{
			case 'GET':
				if ($parameters !== '')
				{
					curl_setopt($ch, CURLOPT_URL, $url.'?'.$parameters);
				}
				break;
			
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
				break;
			
			case 'DELETE':
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
				break;	
		}
    	
    	
		//---------------------------------------------------
		// Execute!
    	    	
		$data = curl_exec($ch);
		curl_close($ch);
    	
		if($data === FALSE)
		{
			return FALSE;
		}
        
                
		//---------------------------------------------------
		// Let's decode the content into a PHP array before returning
        
		if(method_exists($this, 'decode_'.$this->format))
		{
			$data = $this->{'decode_'.$this->format}($data);
		}
		
		return $data;
	}
    

   	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   	// DECODE FUNCTIONS
   	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 	
   	
   	
   	function decode_json($data)
   	{
   		return json_decode(trim($data));
   	}
   	
   	
	//---------------------------------------------------
   	
   	
   	function decode_xml($data)
    {
    	return (array) simplexml_load_string($data);
    }
   	

}

/* End of file REST_Model.php */
