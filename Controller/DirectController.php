<?php
/**
 * undocumented class
 *
 * @package default
 * @author Kaz Watanabe
 **/
class DirectController extends AppController
{
	public $uses		= array();
	public $layout = null;
	public $components = array(
		'Extjs.Direct',
	);

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function init()
	{
		$this->layout = false;
		$this->response->type('js');
	}
}