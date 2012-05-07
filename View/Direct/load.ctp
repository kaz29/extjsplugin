<?php 
	config('ext_direct');
	$ext_direct_models = Configure::read('ext_direct_models');
	$ext_default_controller = Configure::read('ext_default_controller');
	$controllers = array();
	foreach($ext_direct_models as $name => $params):
  	if (is_numeric($name)) 
  	  continue;
	  
	  if (isset($params['noconvert']) && $params['noconvert'] === true) {
      $controllers[] = strtolower($name);
	  } else {
      $controllers[] = Inflector::tableize($name);
    }
  endforeach;
?>
Ext.onReady(function() {
  Ext.app.init('');
  var controllers = <?php echo json_encode($controllers); ?>;

  Ext.History.init();
	Ext.getCmp('basic-panel').expand();

  Ext.History.on('change', function(token) {
    for( var i in controllers) {
      if (token == '!/'+controllers[i] || token == '%21%2F'+controllers[i]) {
        Ext.app[controllers[i]].load();
        break ;
      }
    }
  });
  
  var find=false;
  for( var i in controllers) {
    if (location.hash == '#!/'+controllers[i] || location.hash == '#%21%2F'+controllers[i]) {
      Ext.app[controllers[i]].load();
	    Ext.util.History.add('#!/'+controllers[i]);
      find = true;
      break ;
    }
  }
  
  if (!find) {
    Ext.app.<?php echo Inflector::tableize($ext_default_controller);?>.load();
    Ext.util.History.add('#!/<?php echo Inflector::tableize($ext_default_controller);?>');
    document.title='<?php echo $ext_default_controller;?>';
  }
});
