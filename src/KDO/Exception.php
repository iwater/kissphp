<?php
class KISS_KDO_Exception extends Exception {
	private $extMsg = '';
	public function __construct($message, $code = 0, $extMsg='') {
		$this->extMsg = $extMsg;
		parent::__construct($message, $code);
	}
	
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]{$this->extMsg}: {$this->message}\n";
	}
}
?>