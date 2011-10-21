<?php
App::uses('Router', 'Routing');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ExtDirectTestCase', 'Extjs.Lib');
App::uses('ExtDirectException', 'Extjs.Lib');
App::uses('ExtjsAppModel', 'Extjs.Model');

/**
 * ExtDirectTestController class
 */
class ExtDirectTestController extends Controller {

	public $components = array('Extjs.Direct');
	public $name = 'Direct';
	public $uses = null;
}

class DirectTestModel extends ExtjsAppModel {
	public $table = 'direct_test_models';
	public $actsAs = array('Extjs.Direct');
	public $directSettings = array(
		'allow' => array('index', 'add', 'view', 'edit', 'del'),
		'form'	=> array('add', 'edit', 'del'),
	);
	public $validate = array(
		'name' => array(
			'between' => array(
				'rule'		=> array('between', 3, 20),
				'last' 		=> true,
				'message'	=> 'name length error'
			),
		)
	);
	
	public function index($params)
	{
		$conditions = array();
		if ( isset($params->name) && !empty($params->name)) {
			$conditions['name LIKE'] = $this->escapeLike($params->name);
		}

		$count = $this->find('count', array('conditions'=>$conditions));
		$orders = array();
		foreach( $params->sort as $sort ) {
			$orders["{$this->alias}.{$sort->property}"] = $sort->direction;
		}
		
		$results = $this->find('all', array('offset' => $params->start, 'limit'=>$params->limit, 'order'=>$orders, 'conditions' => $conditions));		
		$data = array();
		foreach( $results as $value ) {
			$data[] = $value[$this->alias];
		}

		$result = $this->makeDirectResponce(
			true, 
			array(
				'total' => $count, 
				'datas' => $data
			)
		);

		return $result;
	}

	public function view($id, $escape=true)
	{
		if ( empty($id) ) {
			$result = $this->makeDirectResponce(false,array('message'=> __('Parameter Error')));
		}

		$result = $this->read(null, $id);
		if ( empty($result) ) {
			$result = $this->makeDirectResponce(false, array('message'=> __('Could not read data')));
		} else {			
			$result = $this->makeDirectResponce(true, array('data'=>$result[$this->alias]), $escape);
		}
		
		return $result ;
	}
	
	public function edit($data)
	{
		$this->create();
		$this->set($data);
		if ( !$this->validates() ) {
			$result = $this->makeDirectResponce(false, array('errors'=>$this->validationErrors));
			return $result;
		}
		if ($this->save($data)) {
			$result = array('id' => $this->id, 'message'=>__('Saved'));
			$result = $this->makeDirectResponce( true, $result );
		} else {
			$result = $this->makeDirectResponce(false, array('message'=>__('Save Error')));
		}
		return $result;
	}	
	public function add($data)
	{		
		$this->create();
		$this->set($data);
		if ( !$this->validates() ) {
			return $this->makeDirectResponce(false, array('errors'=> $this->validationErrors));
		}
		if ($this->save($data)) {
			$result = array('id' => $this->id, 'message'=>__('Created'));
			$result = $this->makeDirectResponce( true, $result );
		} else {
			$result = $this->makeDirectResponce(false,array('message'=>__('Save Error')));
		}
		return $result;
	}
}

class DirectComponentTestCase extends ExtDirectTestCase {
	public $fixtures = array(
		'plugin.extjs.direct_test_model',
	);
/**
 * start test
 *
 * @return void
 */
	function startTest() {
		Configure::write('Cache.disable', true);
		
		$this->_server = $_SERVER;
		Router::reload();
		Router::connect('/:plugin/:controller/:action/*');
	}

	private function _init($url='extjs/direct/router')
	{
		$this->Model =& ClassRegistry::init('DirectTestModel');

		$this->response = $this->getMock('CakeResponse');
		$request = new CakeRequest($url, false);
		$params = Router::parse($request->url);
		$request->addParams($params);

		$this->Controller = new ExtDirectTestController($request);
		$this->Controller->constructClasses();
		$this->Controller->Components->trigger('initialize', array(&$this));
		$this->Controller->beforeFilter();

		$this->Direct =& $this->Controller->Direct;
		$this->Direct->request = $request;
		$this->Direct->setOption('return', true) ;
	}
	
/**
 * end test
 *
 * @return void
 */
	function endTest() {
		$_SERVER = $this->_server;
		unset($this->Direct);
		ClassRegistry::flush();
	}
	
	function testExtDirectException() {
		try {
			throw new ExtDirectException('testExtDirectException');
			$this->assertTrue(false) ;
		} catch(ExtDirectException $e) {
			$this->assertEqual('testExtDirectException', $e->getMessage()) ;
		}
	}
	
	function testInvalidRequest_UndefinedAction() {
		$this->_init('extjs/router/index');
		$this->assertFalse($this->Direct->startup($this->Controller));
	}
	
	function testInvalidRequest_NoAjaxCall() {
		$this->_init();
		
		$this->assertTrue($this->Direct->startup($this->Controller));
		$expected = json_encode(array(
			'success' => false,
			'message'	=> __('Invalid Request'),
			'code'		=> 32001,
		));
		$this->assertEqual($expected, $this->Direct->getDirectResponse());
	}

	function testInvalidRequest_NoRequestBody() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->_init();
		
		$this->assertTrue($this->Direct->startup($this->Controller));
		$expected = json_encode(array(
			'success' => false,
			'message'	=> __('Invalid Request'),
			'code'		=> 32003,
		));
		$this->assertEqual($expected, $this->Direct->getDirectResponse());
	}

	function testInvalidRequest_InvalidRequestBody() {
		$GLOBALS['HTTP_RAW_POST_DATA']		= 'foobar';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->_init();
		
		$this->assertTrue($this->Direct->startup($this->Controller));
		$expected = json_encode(array(
			'success' => false,
			'message'	=> __('Invalid Request'),
			'code'		=> 32002,
		));
		$this->assertEqual($expected, $this->Direct->getDirectResponse());
	}
	
	function testForbiddenRequest() {
		$this->_init();

		$params = array('data' => array($this->paginate(1,5)));
		$result = $this->direct_request('hoge', $params);
		$expected = array(
			'type'		=> 'rpc',
			'tid'			=> 1,
			'action'	=> 'DirectTestModel',
			'method'	=> 'hoge',
			'result'	=> array(
				'success'	=> false,
				'message'	=> __('Forbidden.'),
			)
		);
		$this->assertEqual($expected, $result) ;
	}

	function testStoreRequest() {
		$this->_init();
		
		$params = array('data' => array($this->paginate(1,5)));
		$result = $this->direct_request('index', $params);
		$expected = array(
			'type'		=> 'rpc',
			'tid'			=> 1,
			'action'	=> 'DirectTestModel',
			'method'	=> 'index',
			'result'	=> array(
				'success'	=> true,
				'escape'	=> true,
				'total'		=> 11,
				'datas'		=> array(
					array(
						'id'		=> 1,
						'name'	=> 'test-001',
					),
					array(
						'id'		=> 2,
						'name'	=> 'test-002',
					),
					array(
						'id'		=> 3,
						'name'	=> 'test-003',
					),
					array(
						'id'		=> 4,
						'name'	=> 'test-004',
					),
					array(
						'id'		=> 5,
						'name'	=> 'test-005',
					),
				)
			)
		);
		$this->assertEqual($expected, $result) ;
		
		$params = array('data' => array($this->paginate(2,5)));
		$result = $this->direct_request('index', $params);
		$expected = array(
			'type'		=> 'rpc',
			'tid'			=> 2,
			'action'	=> 'DirectTestModel',
			'method'	=> 'index',
			'result'	=> array(
				'success'	=> true,
				'escape'	=> true,
				'total'		=> 11,
				'datas'		=> array(
					array(
						'id'		=> 6,
						'name'	=> 'test-006',
					),
					array(
						'id'		=> 7,
						'name'	=> 'test-007',
					),
					array(
						'id'		=> 8,
						'name'	=> 'test-008',
					),
					array(
						'id'		=> 9,
						'name'	=> 'test-009',
					),
					array(
						'id'		=> 10,
						'name'	=> 'test-010',
					),
				)
			)
		);
		$this->assertEqual($expected, $result) ;
		
		$params = array('data' => array($this->paginate(3,5)));
		$result = $this->direct_request('index', $params);
		$expected = array(
			'type'		=> 'rpc',
			'tid'			=> 3,
			'action'	=> 'DirectTestModel',
			'method'	=> 'index',
			'result'	=> array(
				'success'	=> true,
				'escape'	=> true,
				'total'		=> 11,
				'datas'		=> array(
					array(
						'id'		=> 11,
						'name'	=> 'test-011',
					),
				)
			)
		);
		$this->assertEqual($expected, $result) ;

		$data = $this->paginate(1,5);
		$data->name = '01';
		$param = array('data' => array($data));
		$result = $this->direct_request('index', $param);
		$expected = array(
			'type'		=> 'rpc',
			'tid'			=> 4,
			'action'	=> 'DirectTestModel',
			'method'	=> 'index',
			'result'	=> array(
				'success'	=> true,
				'escape'	=> true,
				'total'		=> 3,
				'datas'		=> array(
					array(
						'id'		=> 1,
						'name'	=> 'test-001',
					),
					array(
						'id'		=> 10,
						'name'	=> 'test-010',
					),
					array(
						'id'		=> 11,
						'name'	=> 'test-011',
					),
				)
			)
		);
		$this->assertEqual($expected, $result) ;
	}

	function testViewRequest() {
		$this->_init();
		
		$param = array('data' => array(10));
		$result = $this->direct_request('view', $param);
		$expected = array(
			'method' => 'view',
			'result' => array(
				'success' => true,
				'escape' => true,
				'data' => array(
					'id'		=> 10,
					'name'	=> 'test-010',
				)
			)
		);
		$expected = $this->makeResponse($expected);
		$this->assertEqual($expected, $result) ;

		$param = array('data' => array(999));
		$result = $this->direct_request('view', $param);
		$expected = array(
			'method' => 'view',
			'result' => array(
				'success' => false,
				'escape' 	=> true,
				'message'	=> __('Could not read data'),
			)
		);
		$expected = $this->makeResponse($expected);
		$this->assertEqual($expected, $result) ;
	}
	
	function testEditRequest() {
		$this->_init();

		$data = array(
			'id'	 => 1,
			'name' => '1',
		);
		
		$result = $this->direct_formrequest('edit', $data);
		$expected = array(
			'method' => 'edit',
			'result' => array(
				'success' => false,
				'escape' => true,
				'errors' => array(
					'name' => array('name length error'),
				),
			)
		);
		$expected = $this->makeResponse($expected);
		$this->assertEqual($expected, $result);

		$data = array(
			'id' => 1,
			'name' => '1111',
		);
		
		$result = $this->direct_formrequest('edit', $data);
		$expected = array(
			'method' => 'edit',
			'result' => array(
				'success' => true,
				'escape' 	=> true,
				'id'			=> 1,
				'message' => 'Saved',
			)
		);
		$expected = $this->makeResponse($expected);
		$this->assertEqual($expected, $result);
	}

	function testAddRequest() {
		$this->_init();

		$data = array(
			'name' => '1',
		);
		
		$result = $this->direct_formrequest('add', $data);
		$expected = array(
			'method' => 'add',
			'result' => array(
				'success' => false,
				'escape' => true,
				'errors' => array(
					'name' => array('name length error'),
				),
			)
		);
		$expected = $this->makeResponse($expected);
		$this->assertEqual($expected, $result);

		$data = array(
			'name' => '99999',
		);
		
		$result = $this->direct_formrequest('add', $data);
		$expected = array(
			'method' => 'add',
			'result' => array(
				'success' => true,
				'escape' 	=> true,
				'id'			=> Set::extract($result,'result.id'),
				'message' => 'Created',
			)
		);
		$expected = $this->makeResponse($expected);
		$this->assertEqual($expected, $result);
	}
}