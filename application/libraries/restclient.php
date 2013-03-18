<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Rest Client
 * 
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package Generic
 * @author André Gonçalves
 * @copyright Copyright (c) 2012 Reimagine
 * @link reimagine.cc
 * @version 1.0
 * @since 03 março, 2013
 *
 */

// ------------------------------------------------------------------------

/**
 * RestClient
 *
 * @package Generic
 * @subpackage Libraries
 * @category Libraries
 * @author André Gonçalves
 *
 */
class RestClient {
	
	/**
	 * Version.
	 */
	const VERSION = '1.0.0';
	
	/**
	 * 
	 * @var array
	 */
	protected static $METHOD = array('GET', 'POST', 'PUT', 'DELETE');
	
	/**
	 * The URL we’ll be requesting against
	 * @var string
	 */
	protected $url;
	/**
	 * The type of request we’ll be making (what verb to use)
	 * @var string
	 */
	protected $verb;
	/**
	 * The request body we’ll send with PUT and POST requests
	 * @var string
	 */
	protected $requestBody;
	/**
	 * An internally used variable for PUT requests
	 * @var int
	 */
	protected $requestLength;
	/**
	 * The username to use for this request
	 * @var string
	 */
	protected $username;
	/**
	 * I’ll let you guess
	 * @var string
	 */
	protected $password;
	/**
	 * The body of our response
	 * @var unknown_type
	 */
	protected $responseBody;
	/**
	 * All the other goodness from our response (status code, etc.)
	 * @var string
	 */
	protected $responseInfo;
	
	/**
	 * Headers of http
	 * @var $headers
	 */
	protected $headers = array();
	
	/**
	 * construct
	 * @access public
	 * @param array $config
	 */
	public function __construct($config = array()) {
		$this->config($config);
		log_message('info', "Load Class RestClient success.");
	}
	
	/**
	 * config
	 * Config the vars params
	 * @access public
	 * @param array $config
	 */
	public function config($config = array()) {
		$this->url = array_key_exists('url', $config)? $config['url'] : null;
		
		$verb	= array_key_exists('verb', $config)? $config['verb'] : 'GET';
		$this->setVerb($verb);
		
		$this->requestBody = array_key_exists('requestBody', $config)? $config['requestBody'] : null;
		$this->requestLength = 0;
		$this->username	= array_key_exists('username', $config)? $config['username'] :null;
		$this->password	= array_key_exists('password', $config)? $config['password'] :null;
		$this->responseBody = null;
		$this->responseInfo = null;
		
		$this->headers = array_key_exists('headers', $config)? $config['headers'] : array('Accept: application/json');
		if ($this->requestBody !== null && $this->verb != 'PUT') {
			$this->buildPostBody();
		}
	}
	
	/**
	 * getResponseBody
	 * @access public
	 * @return string
	 */
	public function getResponseBody() {
		return $this->responseBody;
	}
	
	/**
	 * getResponseInfo
	 * @access public
	 * @return string
	 */
	public function getResponseInfo() {
		return $this->responseInfo;
	}
	
	/**
	 * getUsername
	 * @access public
	 * @return string
	 */
	public function getUsername () {
		return $this->username;
	}
	
	/**
	 * setUsername
	 * @access public
	 * @param string $username
	 */
	public function setUsername ($username) {
		$this->username = $username;
	}
	
	/**
	 * getPassword
	 * @access public
	 * @return string
	 */
	public function getPassword () {
		return $this->password;
	}
	
	/**
	 * setPassword
	 * @access public
	 * @param string $password
	 */
	public function setPassword ($password) {
		$this->password = $password;
	}
	
	/**
	 * getHeaders
	 * @access public
	 * @return array
	 */
	public function getHeaders () {
		return $this->Headers;
	}
	
	/**
	 * setHeaders
	 * @access public
	 * @param array $headers
	 */
	public function setHeaders ($headers) {
		if(is_array($headers)) {
			$this->headers = $headers;
		} else {
			$this->headers = array('Accept: application/json');
		}
	}
	
	/**
	 * getVerb
	 * @access public
	 * @return string
	 */
	public function getVerb () {
		return $this->verb;
	}
	
	/**
	 * setVerb
	 * @access public
	 * @param string $verb
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public function setVerb ($verb) {
		try {
			$this->verb = in_array($verb, self::$METHOD)? $verb : 'GET';
			if(!in_array($verb, self::$METHOD)) {
				throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
				log_message('error', "Method do not exist. Set default to GET.");
			}
		} catch (InvalidArgumentException $e) {
			throw $e;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * getUrl
	 * @access public
	 * @return string
	 */
	public function getUrl () {
		return $this->url;
	}
	
	/**
	 * setUrl
	 * @access public
	 * @param string $url
	 */
	public function setUrl ($url) {
		$this->url = $url;
	}
	
	/**
	 * flush
	 * Clear all vars
	 * @access public
	 */
	public function flush () {
		$this->requestBody		= null;
		$this->requestLength		= 0;
		$this->verb				= 'GET';
		$this->responseBody		= null;
		$this->responseInfo		= null;
	}
	
	/**
	 * execute
	 * @access public
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public function execute () {
		$ch = curl_init();
		$this->setAuth($ch);
		
		try {
			switch (strtoupper($this->verb)) {
				case 'GET':
					$this->executeGet($ch);
					break;
				case 'POST':
					$this->executePost($ch);
					break;
				case 'PUT':
					$this->executePut($ch);
					break;
				case 'DELETE':
					$this->executeDelete($ch);
					break;
				default:
					throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
			}
		}
		catch (InvalidArgumentException $e) {
			curl_close($ch);
			throw $e;
		}
		catch (Exception $e) {
			curl_close($ch);
			throw $e;
		}
	}
	
	/**
	 * buildPostBody
	 * This function will take an array and prepare it for being posted (or put as well):
	 * @access public
	 * @param string $data
	 */
	public function buildPostBody ($data = null) {
		$data = ($data !== null) ? $data : $this->requestBody;
		
		if (!is_array($data)) {
			throw new InvalidArgumentException('Invalid data input for postBody.  Array expected');
		}
		
		$data = http_build_query($data, '', '&');
		$this->requestBody = $data;
	}
	
	/**
	 * executeGet
	 * @access protected
	 * These requests are about as easy as they get. Since your params will be a part of the URL, there isn’t much to do outside of actually making the request.
	 * @param unknown_type $ch
	 */
	protected function executeGet ($ch) {
		$this->doExecute($ch);
	}
	
	/**
	 * executePost
	 * hese too are pretty easy to accomplish, and POSTing with Curl is well-documented. Nonetheless, here’s what our function looks like
	 * @access protected
	 * @param unknown_type $ch
	 */
	protected function executePost ($ch) {
		if (!is_string($this->requestBody)) {
			$this->buildPostBody();
		}
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$this->doExecute($ch);
	}
	
	/**
	 * executePut
	 * These are the big mystery. Admittedly, there are a few articles out there covering how to do them, but they’re not super-easy to come across. I did, however, manage to find a few and here’s what I came up with
	 * @access protected
	 * @param unknown_type $ch
	 */
	protected function executePut ($ch) {
		if (!is_string($this->requestBody)) {
			$this->buildPostBody();
		}
		
		$this->requestLength = strlen($this->requestBody);
		
		$fh = fopen('php://memory', 'rw');
		fwrite($fh, $this->requestBody);
		rewind($fh);
		
		curl_setopt($ch, CURLOPT_INFILE, $fh);
		curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
		curl_setopt($ch, CURLOPT_PUT, true);
		
		$this->doExecute($ch);
		
		fclose($fh);
	}
	
	/**
	 * executeDelete
	 * Deletes, when done as they’re intended (no post body), are easy. Remember, you’re not really supposed to send any body for a delete request, as you’re merely sending a command to a resource (i.e. /api/user/1)
	 * @access protected
	 * @param unknown_type $ch
	 */
	protected function executeDelete ($ch) {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		$this->doExecute($ch);
	}
	
	/**
	 * doExecute
	 * @access protected
	 * @param unknown_type $curlHandle
	 */
	protected function doExecute (&$curlHandle) {
		$this->setCurlOpts($curlHandle);
		$this->responseBody = curl_exec($curlHandle);
		$this->responseInfo	= curl_getinfo($curlHandle);
		
		curl_close($curlHandle);
	}
	
	/**
	 * setCurlOpts
	 * This function will take care of all the curl options common to all our requests:
	 * @access protected
	 * @param unknown_type $curlHandle
	 */
	protected function setCurlOpts (&$curlHandle) {
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
		curl_setopt($curlHandle, CURLOPT_URL, $this->url);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $this->headers);
	}
	
	/**
	 * setAuth
	 * If we’ve got a username and password set on the class, we’ll set up the auth options on the curl request with this function:
	 * @access protected
	 * @param unknown_type $curlHandle
	 */
	protected function setAuth (&$curlHandle) {
		if ($this->username !== null && $this->password !== null) {
			curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			curl_setopt($curlHandle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
	}
}

?>