<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Rest Client
 *
 * @package generic
 * @author André Gonçalves
 * @copyright Copyright (c) 2013 Reimagine
 * @link reimagine.cc
 * @since Version 1.0
 * @version 1.0
 *
 */

// ------------------------------------------------------------------------

/**
 * Main
 *
 * @package generic
 * @subpackage Controllers
 * @category Controllers
 * @author André Gonçalves
 *
 */
Class RestClient extends  CI_Controller {
	
	private $data;
	
	public function __construct() {
		parent::__construct();
		$this->data = array();
		setlocale(LC_MONETARY, 'pt_BR');
	}
	
	/**
	 * query
	 */
	public function query() {
		$return = array();
		$headers = array('Content-Type: text/xml');
		$url = 'http://www.example.com';
		$config = array(
				'url' => $url
				,'verb' => 'PUT'
				,'requestBody' => 'my data'
				,'headers' => $headers
			);
		$this->load->library('restclient', $config);
		$this->restclient->execute();
		$result = $this->restclient->getResponseBody();
		
		$xml = simplexml_load_string($result);
		if(is_object($xml)) {
			...
		}
	}
}