<?php
/**
 * ExtDirectException Class
 *
 * @package default
 * @author Kaz Watanabe
 **/
class ExtDirectException extends RuntimeException
{
	/**
	 * Option data
	 *
	 * @var string
	 **/
	private $optioins=array();
	
	public function __construct($message, $options=array(), $code = 500) {
		$this->options = $options;
		parent::__construct($message, $code);
	}
	
	public function getOptions()
	{
		return $this->options;
	}
} // END class ExtDirectAction
