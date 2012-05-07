<?php
	config('ext_direct');
	$ext_direct_models = Configure::read('ext_direct_models');
  
	foreach($ext_direct_models as $name => $params):
	  if (is_numeric($name)) 
	    continue ;
	    
	  if (isset($params['autoload']) && $params['autoload'] !== true)
	    continue ;
	  
	  if (isset($params['controller']) && isset($params['action'])) {
	    if (isset($params['noconvert']) && $params['noconvert'] === true) {
        $this->Html->script(Router::url(
          array(
            'controller'  => $params['controller'],
            'action'      => 'script'
          )
        ), array('inline' => false));
	    } else {
        $this->Html->script(Router::url(
          array(
            'controller'  => Inflector::tableize($params['controller']),
            'action'      => 'script'
          )
        ), array('inline' => false));
      } 
	  } else {
      $this->Html->script(Router::url(
        array(
          'controller'  => Inflector::tableize($name),
          'action'      => 'script'
        )
      ), array('inline' => false));
    }
  endforeach;
?>