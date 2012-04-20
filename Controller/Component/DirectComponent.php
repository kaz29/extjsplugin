<?php
App::uses('Component', 'Controller');
App::uses('Router', 'Routing');
App::uses('Security', 'Utility');
App::uses('Debugger', 'Utility');
App::uses('ExtDirectAction', 'Extjs.Lib');
App::uses('ExtDirectException', 'Extjs.Lib');

/**
 * ExtDirect Component Class
 *
 * PHP5
 *
 * @copyright       Copyright 2010-2011, KazWatanabe.
 * @link            http://d.hatena.ne.jp/kaz_29/
 * @package         ExtjsPlugin
 * @subpackage      ExtjsPlugin.Controller.Compoenent
 **/
class DirectComponent extends Component
{
	/**
	 * undocumented class variable
	 *
	 * @var string
	 **/
	public $components = array('Session');

	/**
	 * ExtDirect Settings
	 *
	 * @access	private
	 * @var string
	 **/
	public $settings = array();

  private $Controller = null ;
  private $params = array();
  private $data = array();

  public function initialize(&$controller, $settings=array())
  {
		$defaults = array(
			'action'				=> 'router',
			'return'				=> false,
			'content_type'	=> 'application/json; charset=UTF-8',
		);
		$this->Controller =& $controller;
		$this->settings = array_merge($defaults,$settings) ;
		$this->params =& $this->Controller->request->params;
		$this->data =& $this->Controller->request->data;
  }
  
	public function startup(&$controller)
	{
		if ( $controller->action === $this->settings['action'] ) {
			$this->direct_response = $this->router();
			
			if ( $this->settings['return'] !== true ) {
				header("Content-Type: {$this->settings['content_type']}");
				header("X-Content-Type-Options: nosniff");
				
				echo $this->direct_response;
				exit ;
			}
			
			return true;
		}
		return false;
  }

	/**
	 * Get ExtDirect response string
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function getDirectResponse()
	{
		return $this->direct_response ;
	}

	/**
	 * Get ExtDirect response string
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function setOption($key, $value)
	{
		return $this->settings[$key] = $value ;
	}
	
	/**
	 * ExtDirect routing
	 *
	 * @param void
	 * @access private
	 * @return void
	 * @author Kaz Watanabe
	 **/
	private function router()
	{
    Configure::write('debug',0) ;
    $isUpload = (Set::extract($this->data, 'extUpload')=='true');

		try {
     if (!$isUpload && 
			    (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
					 $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') ) {
				throw new ExtDirectException(__('Invalid Request'), array(), 32001);
			}
			
			if ($isUpload) {
			  $this->settings['content_type'] = 'text/html; charet=UTF-8';
			}
	      /**
	       * read request body
	       */
	    if( isset($GLOBALS['HTTP_RAW_POST_DATA']) ) {
	      $requests = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
				if ( function_exists('json_last_error') ) {
					$result = json_last_error();
					if ( $result !== JSON_ERROR_NONE ) {
						throw new ExtDirectException(__('Invalid Request'), array(), 32002);
					}
				}
			
				if ( is_array($requests) ) {
					foreach( $requests as &$request ) {
		      	$request->form   = false;
					}
				} else {
	      	$requests->form   = false;
				}
        $requests->upload = $isUpload;
	    } else if (isset($this->data['extAction'])) {
	      $request = new ExtDirectAction;
              
	      $requests->type   	= Set::extract($this->data, 'extType');
	      $requests->action 	= Set::extract($this->data, 'extAction');
	      $requests->method 	= Set::extract($this->data, 'extMethod');
	      $requests->tid    	= Set::extract($this->data, 'extTID');
				if ( isset($this->params['form']) && count($this->params['form']) > 0 ) {
	      	$requests->data   = $this->params['form'];
				} else {
					$requests->data = array();
					foreach($this->data as $key => $value) {
						if ( strncmp($key, 'ext', 3 ) === 0 )
							continue ;
						$requests->data[$key] = $value;
					}
				}
			
	      $requests->form   = true;
	      $requests->upload = $isUpload ;
	    } else {
				throw new ExtDirectException(__('Invalid Request'), array(), 32003);
	    }
    
	    return $this->_dispatch($requests) ;
		} catch(ExtDirectException $e) {
			return json_encode(array(
				'success' => false, 
				'message' => $e->getMessage(),
				'code'		=> $e->getCode()
			));
		}
	}

  /**
   * Analyze ExtDirect requests and execute each request.
   *
   * @param request object $data
   * @return string response string(JSON Format)
   * @author Kaz Watanabe
   **/
  private function _dispatch($requests)
  {
    if (is_array($requests)) {
      $response = array();
      foreach($requests as $request) {
        $result = $this->_invoke($request) ;
				$this->afterExtDirectRequest($requests, $result);
				$response[] = $result;
      }
    } else {
      $response = $this->_invoke($requests) ;
			$this->afterExtDirectRequest($requests, $response);
    }
    
		$escape = true;
		if ( isset($response['result']['escape']) ) {
			$escape = $response['result']['escape'] ;
			unset($response['result']['escape']);
		}
		
		if ( $escape === false ) {
    	$out = json_encode($response, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
    } else {
    	$out = json_encode($this->h($response), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
		}
    return $out ;
  }
  
  /**
   * Execute ExtDirect request
   *
   * @param request object $data
   * @return array reeposne data
   * @author Kaz Watanabe
   **/
  public function _invoke($data)
  {
    $response = array(
      'type' => $data->type,
      'tid' => $data->tid,
      'action' => $data->action,
      'method' => $data->method,
    ) ;
    		
    $Model =& $this->_loadModel($data) ;
		if ( is_null($Model) ) {
	    $response['result'] = array(
				'success'=>false, 
				'message'=> sprintf(__("Could not load model(%s)"), $data->action),
			);
      return $response ;
		}
				
    if ( $this->checkDirectPermission($Model, $data->method) !== true ) {
	    $response['result'] = array('success'=>false, 'message'=>__("Forbidden."));
      return $response ;
		}
		
    if ( !is_callable(array($Model, $data->method)) ) {
	    $response['result'] = array(
				'success'=>false, 
				'message'=> sprintf(__("Unfined method(%$1s)."), $data->method),
			);
      return $response ;
    }

		$result = $this->beforeExtDirectRequest($data, $Model, $data->method);
		if ( $result === true ) {
	    if ( isset($data->data) && is_array($data->data) ) {
	      if ( $data->form ) {
					$modeldata = $this->createModelData($Model, $data);
				} else {
					$modeldata = $data->data;
				}
			
		    $result = call_user_func_array(array($Model,$data->method), $modeldata) ;
	    } else {
	     	$result = call_user_func(array($Model,$data->method)) ;
	    }
		}

    $response['result'] = $result;
		if ( isset($this->Session) && isset($result['flash']) ) {
			$class = ($result['success'] === true)?'notice':'error';
			$this->Session->setFlash($result['flash'], 'default', array('class'=>$class));
		}
    
		return $response ;
  }
	
  /**
   * Create model for request
   *
   * @param request object $data
   * @return Model Object
   * @author Kaz Watanabe
   **/
  private function &_loadModel($data)
  {
		$modelname = null ;
		if ( strpos($data->action, '_') ) {
			list($pluginname, $classname) = explode('_', $data->action, 2);
			$classname = Inflector::classify($classname);
	    $modelname = Inflector::classify($pluginname) . '.' . $classname ;
		} else {
			$classname = Inflector::classify($data->action);
	    $modelname = "{$classname}" ;
    }

    App::import('Model', $modelname) ;
    $Model =& ClassRegistry::init($modelname) ;
		if ( !is_a($Model, $classname) ) {
			$Model = null;
			return $Model;
		}

    return $Model ;
  }

	/**
	 * Create data for model request.
	 *
	 * @param Model object	&$Model
	 * @param mixed 				$resuqest
	 * @access public
	 * @return void
	 * @author Kaz Watanabe
	 **/
	private function createModelData(&$Model, $request)
	{
		if ( isset($request->data[$Model->alias]) ) {
     	$result = $request->data ;
     } else {
			$result = array($Model->alias => array());
			foreach($request->data as $key => $value) {
				if ( $key == 'data' ) 
					continue ;
					
				if ( strncmp($key, 'ext', 3 ) === 0 )
					continue ;
				
				$result[$Model->alias][$key] = $value;
			}
		}
		
		return $result;
	}
	
	/**
	 * Call before filter method
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	private function beforeExtDirectRequest(&$Model, $method, $data=null)
	{
		$result = true ;
		if ( is_callable(array($this->Controller, 'beforeExtDirectRequest')) ) {
			$result = $this->Controller->beforeExtDirectRequest($Model, $method, $data);
			if ( $result !== true ) {
				if ( is_array($result) ) {
					if ( !isset($result['success']) ) {
						$result = array_merge(array('success'=>false), $result);
					}
				} else {
					$result = array('success'=>false);
				}
			}
		}
		
		return $result ;
	}
	
	/**
	 * Call after filter method
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	private function afterExtDirectRequest(&$request, &$response)
	{
		if ( is_callable(array($this->Controller, 'afterExtDirectRequest')) ) {
			$this->Controller->afterExtDirectRequest($request, $response);
		}
	}
	
	private function h($text, $charset = null) {
		if (is_array($text)) {
			return array_map(array($this, 'h'), $text);
		}

		static $defaultCharset = false;
		if ($defaultCharset === false) {
			$defaultCharset = Configure::read('App.encoding');
			if ($defaultCharset === null) {
				$defaultCharset = 'UTF-8';
			}
		}
		
		if ( is_bool($text) ) {
			return (bool)$text;
		} else if ( is_int($text) ) {
			return (int)$text;
		} else if ( is_float($text) ) {
				return (float)$text;
		} else {
			if ($charset) {
				return htmlspecialchars($text, ENT_QUOTES, $charset);
			} else {
				return htmlspecialchars($text, ENT_QUOTES, $defaultCharset);
			}
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	private function checkDirectPermission(&$Model, $action)
	{
		if ( !isset($Model->directSettings) || 
				 !isset($Model->directSettings['allow']) || 
				 !is_array($Model->directSettings['allow']) )
			return false;
		
		return in_array($action, $Model->directSettings['allow']);
	}
} // END class DirectComponent extends Component