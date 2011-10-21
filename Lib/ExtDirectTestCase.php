<?php
/**
 * ExtDirectTestCase class
 *
 * @package default
 * @author 渡辺 一宏
 **/
class ExtDirectTestCase extends CakeTestCase
{
	private 	$extjs = null;
	private 	$controlller = null;
	private 	$tid = 0;
	protected $Model = null;
	
	/**
	 * ExtDirectのリクエストを生成する
	 *
	 * @return void
	 * @author 渡辺 一宏
	 **/
	public function direct_request($method, $params=null, $controller=null)
	{
		if ( !isset($this->Model) ) {
			throw new Exception('Model undefined.');
		}
		
		return $this->_direct_request($this->Model, $this->Model->alias, $method, $params, $controller);
	}

	/**
	 * ExtDirectのフォームリクエストを生成する
	 *
	 * @return void
	 * @author 渡辺 一宏
	 **/
	public function direct_formrequest($method, $params=null, $controller=null)
	{
		if ( !isset($this->Model) ) {
			throw new Exception('Model undefined.');
		}
		
		$requestparams = array('form'=>true,'data'=>$params);
		return $this->_direct_request($this->Model, $this->Model->alias, $method, $requestparams, $controller);
	}
	
	/**
	 * ExtDirectのリクエストを生成する
	 *
	 * @return void
	 * @author 渡辺 一宏
	 **/
	public function _direct_request(&$Model, $action, $method, $params=null, $controller=null)
	{
		$this->loadExtjsComponent($controller);
		
		$request = new stdClass();
		
		$request->type		= 'rpc';
		$request->tid			= ++$this->tid;
		$request->action	= $action;
		$request->method	= $method;
		$request->form 		= false;
		if ( isset($params['data']) ) {
			$request->data = $params['data'];
		}
		
		if ( isset($params['form']) ) {
			$request->form = $params['form'];
		}
		
		return $this->extjs->_invoke($request);
	}
	
	/**
	 * ページング用パラメータを作成
	 *
	 * @return void
	 * @author 渡辺 一宏
	 **/
	public function paginate($page=1, $limit=25, $sort=null)
	{
		$sort_default = array(array('property'=>'id','direction'=>'ASC'));
		if ( !is_array($sort) ) {
			$sort = $sort_default;
		}
		$result = new stdClass();
		
		$result->page = $page;
		$result->start = ($page-1)*$limit;
		$result->limit = $limit;
		$result->sort = json_decode(json_encode($sort));
		
		return $result;
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 渡辺 一宏
	 **/
	public function makeResponse(&$result)
	{
		$defaults = array(
			'type' => 'rpc',
			'tid' => $this->tid,
		);
		
		if ( isset($this->Model) ) {
			$defaults['action'] = get_class($this->Model);
		}
		return array_merge($defaults, $result);
	}
	/**
	 * Extjs.Direct コンンポーネントを生成し初期化する
	 *
	 * @return void
	 * @author 渡辺 一宏
	 **/
	private function loadExtjsComponent($controller)
	{
		if ( !is_null($this->extjs) ) {
			return ;
		}

		if ( !is_null($controller) ) {
			$this->Controller = $controller;
		}
		
		App::import('Component', 'Extjs.Direct');
		$this->extjs =& $this->Controller->Direct;
		
		$this->extjs->initialize($this->Controller, array());
	}
} // END class ExtDirectTestCase extends CakeTestCase