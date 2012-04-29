<?php
	config('ext_direct');
	$ext_direct_models = Configure::read('ext_direct_models');
  
	foreach($ext_direct_models as $name => $params):
	  if ($name === '-') 
	    continue ;
	    
	  if (isset($params['autoload']) && $params['autoload'] !== true)
	    continue ;
	  
	  if (isset($params['controller']) && isset($params['action'])) {
      $this->Html->script(Router::url(
        array(
          'controller'  => Inflector::tableize($params['controller']),
          'action'      => $params['action']
        )
      ), array('inline' => false));

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