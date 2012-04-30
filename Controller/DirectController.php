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
	 * initialize 
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function init($id=null)
	{
		$this->layout = false;
		$this->response->type('js');
		if (!is_null($id)) {
		  if (strpos($id,'.') !== false) {
		    list($view, $ext) = explode('.', $id);
		  } else {
		    $view = $id;
		  }
		  
		  $this->render($view);
		}
	}
}