<?php
/**
* PayJunction API response class
**/
class payjunction_response {
	private $options;

	/** constructor */
	public function __construct( $options ) {
		$this->options = (array) $options;
	}

	/**
	* Return whether or not the request was successful
	**/
	public function success() {
		
		if (isset($this->options['dc_response_code']) && (strcmp($this->options['dc_response_code'], "00")==0 || strcmp($this->options['dc_response_code'], "85")==0)) return true;
		
		return false;
	}

	/**
	* Get declined message
	**/
	public function get_error() {

		// Long error
		if (isset($this->options['dc_response_message'])) return $this->options['dc_response_message'];
	}
	
	/**
	* Get transaction id
	**/
	public function get_transaction_id() {
		if (isset($this->options['dc_transaction_id'])) return $this->options['dc_transaction_id'];
	}
}
?>