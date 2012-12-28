<?php
/**
* PayJunction API request class - sends given POST data to PayJunction server via CURL extension
**/
class payjunction_request {
	private $url;

	/** constructor */
	public function __construct( $url ) {
		$this->url = $url;
	}

	/**
     * Create and send the request
     * @param array $options array of options to be send in POST request
	 * @return payjunction_response response object
     */
	public function send(array $options) {
		
		if (!empty($options)) {
			foreach($options AS $key => $val){
				$post .= urlencode($key) . "=" . urlencode($val) . "&";
			}
			$post_data = substr($post, 0, -1);
		} else {
			$post_data = '';
		}

		$result = $this->payjunction_request($post_data);

		$response = new payjunction_response($result);

		return $response;
	}
	
	/**
     * Run the curl request and send data to PayJunction
     */
	private function payjunction_request($post_data) {
	
		if(!function_exists('curl_init')) throw new Exception('CURL extension is not loaded.');

		// CURL request
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL 			=> $this->url,
			CURLOPT_VERBOSE 		=> 1,
			CURLOPT_SSL_VERIFYPEER 	=> 1,
			CURLOPT_SSL_VERIFYHOST 	=> 2,
			CURLOPT_CONNECTTIMEOUT	=> 60, //try to connect for x seconds
			CURLOPT_TIMEOUT 		=> 70, //whole operation must be finished in under x seconds
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_POST 			=> true,
			CURLOPT_CUSTOMREQUEST 	=> "POST",
			CURLOPT_POSTFIELDS 		=> $post_data,
		));

		if( !$output = curl_exec($curl) ) throw new Exception('CURL error: "' . curl_error($curl) . '"');

		if(strlen($output) == 0) throw new Exception('Empty PayJunction output.');

		// Parse response
		$output = explode(chr (28), $output); // The ASCII field seperator character is the delimiter
		foreach ($output as $key_value) {
			list ($key, $value) = explode("=", $key_value);
			$result[$key] = $value;
		}
		//return results of request
		return $result;
		
	}
}
?>