RestClient-Codeigniter
======================

RestClient for Codeigniter an open source application development framework for PHP 5.1.6 or newer.

How use?
=====================
    <?php
    
    Class RestClient extends  CI_Controller {
	
	private $data;
	
	public function __construct() {
		parent::__construct();
		$this->data = array();
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
    ?>