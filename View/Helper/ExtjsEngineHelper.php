<?php
/**
 * ExtJS Engine Helper for JsHelper
 *
 * Provides ExtJS specific Javascript for JsHelper.
 *
 * PHP5
 *
 * @copyright       Copyright 2010-2011, KazWatanabe.
 * @link            http://d.hatena.ne.jp/kaz_29/
 * @package         ExtjsPlugin
 * @subpackage      ExtjsPlugin.View.Helper
 */
App::uses('AppHelper', 'View/Helper');
App::uses('JsBaseEngineHelper', 'View/Helper');

class ExtjsEngineHelper extends JsBaseEngineHelper
{
/**
 * Option mappings for ExtJS
 *
 * @var array
 */
	protected $_optionMap = array(
		'request' => array(
			'error' => 'failure',
			'data' => 'params',
		),
	);

/**
 * callback arguments lists
 *
 * @var string
 */
	protected $_callbackArguments = array(
		'request' => array(
			'beforeSend' => 'XMLHttpRequest',
			'error' => 'XMLHttpRequest, textStatus, errorThrown',
			'success' => 'data, textStatus',
			'complete' => 'XMLHttpRequest, textStatus',
			'xhr' => ''
		)
	);
	
	/**
	 * undocumented class variable
	 *
	 * @var string
	 **/
	private $_model=null;
	
	/**
	 * undocumented class variable
	 *
	 * @var string
	 **/
	private $_schema=null;
	
/**
 * Add an event to the script cache. Operates on the currently selected elements.
 *
 * ### Options
 *
 * - 'wrap' - Whether you want the callback wrapped in an anonymous function. (defaults true)
 * - 'stop' - Whether you want the event to stopped. (defaults true)
 *
 * @param string $type Type of event to bind to the current dom id
 * @param string $callback The Javascript function you wish to trigger or the function literal
 * @param array $options Options for the event.
 * @return string completed event handler
 */
	public function event($type, $callback, $options = array())
	{
		$defaults = array('wrap' => true, 'stop' => true);
		$options = array_merge($defaults, $options);

		$function = 'function () {%s}';  	
		if ($options['wrap'] && $options['stop']) {
			$callback .= "\nreturn false;";
		}
		if ($options['wrap']) {
			$callback = sprintf($function, $callback);
		}
		
		if ( $type === 'onReady' ) {
		  return sprintf('Ext.%s(%s);', $type, $callback);
    } else {
		  return sprintf('%s.on("%s", %s);', $this->selection, $type, $callback) ;
    }
	}

/**
 * Create a domReady event. This is a special event in many libraries
 *
 * @param string $functionBody The code to run on domReady
 * @return string completed domReady method
 */
	public function domReady($functionBody)
	{
		return $this->event('onReady', $functionBody, array('stop' => false));
	}
	
/**
 * Create javascript selector for a CSS rule
 *
 * @param string $selector The selector that is targeted
 * @return object instance of $this. Allows chained methods.
 */
	public function get($selector)
	{
		if ($selector == 'window' ) {
	    $this->selection = 'Ext.select('.$selector.')' ;
		} else if ( $selector == 'document') {
	    $this->selection = 'Ext.get('.$selector.')' ;
	  } else if ( $selector[0] === '#' ) {
	    $this->selection = 'Ext.get("'.substr($selector,1).'")' ;
	  } else {
	    $this->selection = 'Ext.select("'.$selector.'")' ;
	  }
	  
	  return $this ;
	}	

/**
 * Create an iteration over the current selection result.
 *
 * @param string $method The method you want to apply to the selection
 * @param string $callback The function body you wish to apply during the iteration.
 * @return string completed iteration
 */
	public function each($callback) 
	{
		return $this->selection.'.each(function (el) {' . $callback . '});';
	}
	
/**
 * Trigger an Effect.
 *
 * @param string $name The name of the effect to trigger.
 * @param array $options Array of options for the effect.
 * @return string completed string with effect.
 * @see JsBaseEngineHelper::effect()
 */
	public function effect($name, $options = array()) {
		$speed = .5;
		if (isset($options['speed'])) {
			$speed = $options['speed'];
		}
		$anchor = 'r';
		if (isset($options['anchor']) && in_array($options['anchor'], array('r', 'l', 't', 'b'))) {
			$anchor = $options['anchor'];
		}
		$color = 'FF0000';
		if (isset($options['color'])) {
			$color = $options['color'];
		}
		$count = 3;
		if (isset($options['count'])) {
			$count = $options['count'];
		}
		
		$effect = '';
		switch ($name) {
			case 'slideIn':
				$effect = ".$name('$anchor', {duration: {$speed}});";
			break ;
			case 'slideOut':
				$effect = ".$name('$anchor', {duration: {$speed}});";
			break ;
			case 'slideDown':
				$effect = ".slideOut('b', {duration: {$speed}});";
			break ;
			case 'slideUp':
				$effect = ".slideOut('t', {duration: {$speed}});";
			break ;
			case 'puff':
			case 'switchOff':
 			case 'fadeIn':
			case 'fadeOut':
				$effect = ".$name({duration: {$speed}});";
			break ;
			case 'frame':
				$effect = ".$name('$color', $count, {duration: {$speed}});";
			break ;
			case 'highlight':
				$effect = ".$name('$color', {duration: {$speed}});";
			break ;
		}
		return $this->selection.$effect;
	}
	
/**
 * Create an $.ajax() call.
 *
 * If the 'update' key is set, success callback will be overridden.
 *
 * @param mixed $url
 * @param array $options
 * @return string The completed ajax call.
 */
	public function request($url, $options = array()) {
		$url = $this->url($url);
		$options = $this->_mapOptions('request', $options);
		if (isset($options['params']) && is_array($options['params'])) {
			$options['params'] = $this->_toQuerystring($options['params']);
		}
		$options['url'] = $url;
		if (isset($options['update'])) {
		  $update = ($options['update'][0] === '#')? substr($options['update'],1): $options['update'] ;
			$wrapCallbacks = isset($options['wrapCallbacks']) ? $options['wrapCallbacks'] : true;
			if ($wrapCallbacks) {
				$success = 'Ext.get("' . $update . '").update(data.responseText);';
			} else {
				$success = 'function (data, textStatus) {Ext.get("' . $update . '").update(data.responseText);}';
			}
			$options['success'] = $success;
			unset($options['update']);
		}
		$callbacks = array('success', 'failure', 'callback');
		if (isset($options['dataExpression'])) {
			$callbacks[] = 'params';
			unset($options['dataExpression']);
		}
		$options = $this->_prepareCallbacks('request', $options);
		$options = $this->_parseOptions($options, $callbacks);
		return 'Ext.Ajax.request({' . $options .'});';
  }

/**
 * Create a draggable element.  Works on the currently selected element.
 * Additional options may be supported by the library implementation.
 *
 * ### Options
 *
 * - `handle` - selector to the handle element.
 * - `snapGrid` - The pixel grid that movement snaps to, an array(x, y)
 * - `container` - The element that acts as a bounding box for the draggable element.
 *
 * ### Event Options
 *
 * - `start` - Event fired when the drag starts
 * - `drag` - Event fired on every step of the drag
 * - `stop` - Event fired when dragging stops (mouse release)
 *
 * @param array $options Options array see above.
 * @return string Completed drag script
 */
	public function drag($options = array())
	{
	}

/**
 * Create a droppable element. Allows for draggable elements to be dropped on it.
 * Additional options may be supported by the library implementation.
 *
 * ### Options
 *
 * - `accept` - Selector for elements this droppable will accept.
 * - `hoverclass` - Class to add to droppable when a draggable is over.
 *
 * ### Event Options
 *
 * - `drop` - Event fired when an element is dropped into the drop zone.
 * - `hover` - Event fired when a drag enters a drop zone.
 * - `leave` - Event fired when a drag is removed from a drop zone without being dropped.
 *
 * @param array $options Array of options for the drop. See above.
 * @return string Completed drop script
 */
	public function drop($options = array())
	{
	}
	
/**
 * Create a sortable element.
 * Additional options may be supported by the library implementation.
 *
 * ### Options
 *
 * - `containment` - Container for move action
 * - `handle` - Selector to handle element. Only this element will start sort action.
 * - `revert` - Whether or not to use an effect to move sortable into final position.
 * - `opacity` - Opacity of the placeholder
 * - `distance` - Distance a sortable must be dragged before sorting starts.
 *
 * ### Event Options
 *
 * - `start` - Event fired when sorting starts
 * - `sort` - Event fired during sorting
 * - `complete` - Event fired when sorting completes.
 *
 * @param array $options Array of options for the sortable. See above.
 * @return string Completed sortable script.
 */
	public function sortable($options = array())
	{
	}
/**
 * Create a slider UI widget.  Comprised of a track and knob.
 * Additional options may be supported by the library implementation.
 *
 * ### Options
 *
 * - `handle` - The id of the element used in sliding.
 * - `direction` - The direction of the slider either 'vertical' or 'horizontal'
 * - `min` - The min value for the slider.
 * - `max` - The max value for the slider.
 * - `step` - The number of steps or ticks the slider will have.
 * - `value` - The initial offset of the slider.
 *
 * ### Events
 *
 * - `change` - Fired when the slider's value is updated
 * - `complete` - Fired when the user stops sliding the handle
 *
 * @param array $options Array of options for the slider. See above.
 * @return string Completed slider script
 */
	public function slider($options = array())
	{
	}

/**
 * Serialize the form attached to $selector.
 * Pass `true` for $isForm if the current selection is a form element.
 * Converts the form or the form element attached to the current selection into a string/json object
 * (depending on the library implementation) for use with XHR operations.
 *
 * ### Options
 *
 * - `isForm` - is the current selection a form, or an input? (defaults to false)
 * - `inline` - is the rendered statement going to be used inside another JS statement? (defaults to false)
 *
 * @param array $options options for serialization generation.
 * @return string completed form serialization script
 */
	public function serializeForm($options = array())
	{
	}
	
	/**
	 * Create model namespace
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function ns($modelname)
	{
	  list($plugin,$name) = pluginSplit($modelname);
		$tablename = Inflector::tableize($name);
		return "Ext.ns('Ext.app.{$tablename}');";
	}
	
	/**
	 * Create ExtDirect Action Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function actions($modelname, $options=array())
	{
		$defaults = array(
			'actions' => true,
			'action_params' => array(),
		);
		
		$options = array_merge($defaults, $options) ;
		$this->_model = ClassRegistry::init($modelname);
		$settings = $this->_model->getDirectSettings();
		
		list($plugin, $name) = pluginSplit($modelname);
		if (!empty($plugin)) {
  		$out = "'{$plugin}_{$this->_model->alias}':[\n";
  	} else {
  		$out = "'{$this->_model->alias}':[\n";
  	}
  	
		$n = 0;
		foreach($settings['allow'] as $action) {
			$paramnum = ($action=='view')?2:1;
		  if (array_key_exists($action, $options['action_params'])) {
		    $paramnum = $options['action_params'][$action];
		  }
			$form = (in_array($action, $settings['form']))?'true':'false';
			$out .= "{'name':'{$action}','len':{$paramnum},'formHandler':{$form}}";
			$out .= (++$n >= count($settings['allow']))?"\n":",\n";
		}
		$out .= "]";
		
		if ( isset($options['actions']) && $options['actions'] === true ) {
			return "'actions':{\n$out}";
		} else {
			return $out;
		}
	}

	/**
	 * Create ExtDirect Action Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function getmodel($modelname, $options=array())
	{
		$defaults = array(
			'actions' => true,
			'fields'  => array(),
		);
		
		$options = array_merge($defaults, $options) ;
		$this->_model = ClassRegistry::init($modelname);
		$this->_schema = $this->_model->schema();
		
		list($plugin, $name) = pluginSplit($modelname);
		if (!empty($plugin)) {
		  $out = "Ext.define('{$plugin}_{$this->_model->alias}',{\nextend:'Ext.data.Model',\nfields:[\n";
		} else {
		  $out = "Ext.define('{$this->_model->alias}',{\nextend:'Ext.data.Model',\nfields:[\n";
		}
		$n = 0;
		
		$schema = $this->_schema;
		foreach($options['fields'] as $field) {
		  $schema[$field] = array();
		}
		
		foreach($schema as $name => $prop) {
			$out .= "'{$name}'";
			$out .= (++$n >= count($schema))?"\n":",\n";
		}
		$out .= "]})";
		
		return $out;
	}
	
	/**
	 * Create Ext.data.Store Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function store($modelname, $options=array())
	{
		$exception_function =<<<EOT
function(proxy, response, operation) {
	if ( !response.result.success ) {
		Ext.Msg.alert(response.result.message);
	}
}
EOT;
		list($plugin, $name) = pluginSplit($modelname);
		if (!empty($plugin)) {
      $tmpmodelname = "{$plugin}_{$name}";
    } else {
      $tmpmodelname = $modelname;
    }
			// TODO: Listenerをカスタマイズする方法を実装
		$defaults = array(
			'model'				=> $tmpmodelname,
			'remoteSort' 	=> true,
			'autoLoad'		=> true,
			'sorters'			=> array(array('property'=>'id', 'direction'=>'DESC')),
			'proxy'				=> array(
				'type'				=> 'direct',
				'directFn'		=> '___DIRECT_FUNCTION___',
				'reader'			=> array('type'=>'json', 'root'=>'datas'),
				'listeners'		=> array(
					'exception'	=> '___EXCEPTION_LISTENER___',
				)
			)
		);
		
		$options = array_merge($defaults, $options);
		
		$tmpstr = json_encode($options);
		$tmpstr = str_replace('"___EXCEPTION_LISTENER___"', $exception_function, $tmpstr);
		$tmpstr = str_replace('"___DIRECT_FUNCTION___"', "{$tmpmodelname}.index", $tmpstr);
		return "Ext.create('Ext.data.JsonStore',\n{$tmpstr}\n)";
	}
	
	/**
	 * Create Ext.data.Store column Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function columns($modelname, $options=array())
	{
		$defaults = array(
			'hidden' => array('id'),
			'order'  => null,
		);
		$options = array_merge($defaults, $options);
		$Model = ClassRegistry::init($modelname);
		$schema = $Model->schema();
		
		if (is_array($options['order'])) {
		  $orderd = array();
		  foreach($options['order'] as $name) {
		    if (isset($schema[$name])) {
		      $orderd[$name] = $schema[$name];
		    } else {
		      $orderd[$name] = array();
		    }
		  }
		  
		  $schema = $orderd;
		}
		
		$out = '[';
		foreach($schema as $name => $prop) {		    
			if ( in_array($name, $options['hidden']) ) {
				continue ;
			}
		  if (strlen($out) > 1)
		    $out .= ",";
			
			$title        = (isset($options[$name]['title']))?$options[$name]['title']:__($name);
			$width_type		= (isset($options[$name]['width_type']))?$options[$name]['width_type']:'flex'; // flex or size
			$width				= (isset($options[$name]['width']))?$options[$name]['width']:1;
			$sortable			= (isset($options[$name]['sortable']))?$options[$name]['sortable']:'true';
			$hideable			= (isset($options[$name]['hideable']))?$options[$name]['hideable']:'true';
			$align  			= (isset($options[$name]['align']))?$options[$name]['align']:'left';
			
			$option_fields = '';
			
			if (isset($options[$name]['renderer'])) {
			  $option_fields .= ",\n	renderer: {$options[$name]['renderer']}";
			}
$out .=<<<EOT
{
	text:'{$title}',
	dataIndex:'{$name}',
	{$width_type}:{$width},
	sortable:{$sortable},
	hideable:{$hideable},
	align:'{$align}'{$option_fields}
}
EOT;
		}
		$out .= ']';
		
		return $out;
	}
	
	/**
	 * Create Ext.direct remoting provider Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function addProvidor($modelname, $options=array())
	{
		$defaults = array(
			'router' => '/extjs/direct/router',
			'action_params' => array(),
		);
		$options = array_merge($defaults, $options) ;
		
		$url = (is_array($options['router']))?Router::url($options['router']):$options['router'];
		$actions = $this->actions($modelname, array('action_params'=>$options['action_params']));
$out =<<<EOT
Ext.direct.Manager.addProvider({
	"url":"{$url}",
	"type":"remoting",
	{$actions}
});
EOT;
		return $out;
	}
	
	/**
	 * Create Ext.form.Panel Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function form($modelname, $options=array())
	{
	  list($plugin, $name) = pluginSplit($modelname);
		$tablized_modelname =  Inflector::tableize($name);

		$defaults = array(
			'id'			=> null,
			'frame'		=> true,
			'border'	=> false,
			'bodyPadding'	=> 5,
			'autoScroll'	=> true,
			'fieldDefaults'			=> array(
				'labelAlign'			=> 'left',
				'labelWidth'			=> 150,
				'labelSeparator'	=> ':',
				'anchor'					=> '100%',
				'allowBlank'			=> false,
				'msgTarget'				=> 'side',
			),
			'defaultType'	=> 'textfield',
			'api'					=> array(),
			'paramOrder'	=> array('id','escape'),
			'items'       => null,
			'dockedItems'	=> array(
				'xtype'			=> 'toolbar',
				'dock'			=> 'bottom',
				'ui'				=> 'footer',
				'items'			=> array(
					'->',
					array(
						'xtype'			=> 'button',
						'text'			=> __('Save'),
						'minWidth'	=> 80,
						'handler'		=> null,
						'validMessage'				=> __('Invalid Data.'),
						'saveConfirmMessage'	=> __(sprintf('Do you want to create the %s?', $tablized_modelname)),
						'confirmTitle'				=> __('Confirm'),
						'progressTitle'				=> __('Saving...'),
					),
					array(
						'xtype'			=> 'button',
						'text'			=> __('Cancel'),
						'minWidth'	=> 80,
						'handler'		=> null,
					)
				)
			)
		);
		
		$defaults['id'] = "{$tablized_modelname}-form-id";
		$options = array_merge($defaults, $options);

		$options['frame']				= $options['frame']?'true':'false';
		$options['border']			= $options['border']?'true':'false';
		$options['autoScroll']	= $options['autoScroll']?'true':'false';
		$options['fieldDefaults']['allowBlank']	= $options['fieldDefaults']['allowBlank']?'true':'false';

		if (isset($options['dockedItems']['items'][1]) && 
		    is_null($options['dockedItems']['items'][1]['handler'])) {
		  $options['dockedItems']['items'][1]['handler'] =<<<EOT
function() {
	var form = this.up('form').getForm()
			win = this.up('window');

	if (!form.isValid()) {
		Ext.Msg.alert('{$options['dockedItems']['items'][1]['validMessage']}');
		return ;
	}
	Ext.Msg.confirm(
		'{$options['dockedItems']['items'][1]['confirmTitle']}', 
		'{$options['dockedItems']['items'][1]['saveConfirmMessage']}', 
		function(btn) {
			if (btn == "yes") {
				Ext.app.loading.show('{$options['dockedItems']['items'][1]['progressTitle']}');
				form.submit({
					clientValidation: true,
					success: function(form, action) {	
					  if (typeof Ext.app.{$tablized_modelname} != "undefined" &&
					      typeof Ext.app.{$tablized_modelname}.store != "undefined") {
						  Ext.app.{$tablized_modelname}.store.load();	
						}											
						Ext.app.loading.hide();
						form.reset();
						win.close();
					},
					failure: function(form, action) {
						Ext.app.loading.hide();
						Ext.Msg.alert(action.result.message);
					}
				});
			}
		}, 
		window
	);
}
EOT;
    }
    
		if (isset($options['dockedItems']['items'][2]) && 
		    is_null($options['dockedItems']['items'][2]['handler'])) {
      $options['dockedItems']['items'][2]['handler'] =<<<EOT
function(){
	this.up('form').getForm().reset();
	this.up('window').close();
}
EOT;
    }
    
    $api = '';
    if (isset($options['api'])) {
  			// Create API function list
  		$api = 'api: {';
  		$n = 0;
  		foreach($options['api'] as $index => $value) {
  		  if (!empty($plugin)) {
  		    $api_action = "{$plugin}_{$value}";
  		  } else {
  		    $api_action = $value;
  		  }
		  
  			if ( $index > 0 ) 
  				$api .= ',';
  			$api .= "{$index}:{$api_action}";
  			$api .= (++$n >= count($options['api']))?"":",";
  		}
  		$api .= '},';
		}
			// Create Form Items
		$items = '';
		if (is_null($options['items'])) {
  		$this->load_model($modelname) ;
  		foreach($this->_schema as $name => $prop) {
  			$result = $this->input($name);
  			if ( $result === false ) 
  				continue ;
			
  		  if (strlen($items) > 0) 
  		    $items .= ',';
  			$items .= $result ;	
  		}
  	} else {
  		foreach($options['items'] as $name => $prop) {
  			$result = $this->input($name, $prop);
  			if ( $result === false ) 
  				continue ;
			
  		  if (strlen($items) > 0) 
  		    $items .= ',';
  			$items .= $result ;	
  		}
  	}
		
		$paramOrder = '';
		$n = 0;
		foreach($options['paramOrder'] as $param) {
			$paramOrder .= "'{$param}'";
			$paramOrder .= (++$n >= count($options['paramOrder']))?"":",";
		}
			// Create dockItems
		$dockItems = '';
		$n = 0;
		foreach($options['dockedItems']['items'] as $item) {
  		  if (strlen($dockItems) > 0) 
  		    $dockItems .= ',';

			if ( is_string($item) ) {
				$result = "'{$item}'";
			} else {
			  $result =<<<EOT
{
	xtype: '{$item['xtype']}',
	text: '{$item['text']}',
	minWidth: {$item['minWidth']},
	handler: {$item['handler']}
}
EOT;
			}
			
			$dockItems .= $result;
//			$dockItems .= (++$n >= count($options['dockedItems']['items']))?"\n":",\n";
		}
		
		$out =<<<EOT
Ext.create('Ext.form.Panel', {
	id: '{$options['id']}',
	frame: {$options['frame']},
	border: {$options['border']},
	bodyPadding: {$options['bodyPadding']},
	autoScroll: {$options['autoScroll']},
	fieldDefaults: {
		labelAlign: '{$options['fieldDefaults']['labelAlign']}',
		labelWidth: {$options['fieldDefaults']['labelWidth']},
		labelSeparator: '{$options['fieldDefaults']['labelSeparator']}',
		anchor: '{$options['fieldDefaults']['anchor']}',
		allowBlank:{$options['fieldDefaults']['allowBlank']},
		msgTarget: '{$options['fieldDefaults']['msgTarget']}'
	},
	defaultType: '{$options['defaultType']}',
	activeItem:0,
	{$api} // カンマなしで正解
	paramOrder: [{$paramOrder}],
	items: [{$items}],
dockedItems: [{
	xtype: '{$options['dockedItems']['xtype']}',
	dock: '{$options['dockedItems']['dock']}',
	ui: '{$options['dockedItems']['ui']}',
	items: [{$dockItems}]
}]
})
EOT;
		return $out;
	}
	
	/**
	 * Create Form Item Definition
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function input($fieldname, $options=array())
	{
		$xtype = null;

		$defaults = array(
			'xtype'					=> $xtype,
			'fieldLabel'		=> __(Inflector::classify($fieldname)),
			'allowBlank'		=> true,
			'selectOnFocus'	=> true,
			'name'					=> Inflector::singularize($fieldname),
		);
	
		$options = array_merge($defaults, $options) ;
		if ( is_null($options['xtype']) ) {
		  if (!isset($this->_schame[$fieldname])) {
  			$options['xtype'] = 'textfield';
	    } else {
  			if (isset($this->_schema[$fieldname]['key']) && $this->_schema[$fieldname]['key'] === 'primary') {
  				$options['xtype'] = 'hiddenfield';
  			} else if ( $fieldname === 'created' || $fieldname === 'modified' ) {
  				return false;
  			} else if ( $this->_schema[$fieldname]['type'] === 'text' ) {
  				$options['xtype'] = 'textarea';
  				$options['height'] = 100;
  			} else if ( $this->_schema[$fieldname]['type'] === 'date' ) {
  				$options['xtype'] = 'datefield';
  				$options['format'] = 'Y/m/d';
  				$options['editable'] = false;
  				unset($options['fieldLabel']);
  				unset($options['selectOnFocus']);
  			}
  		}
		}
		
		$out = "{\n";
		$n = 0 ;
		foreach($options as $name => $value) {
			if ( $name == 'xtype' && is_null($value) ) {
				$n++;
				continue;
			}
			
			if ($name === 'items') {
  			$out .= "\t{$name}: [{$value}]";
			} else {
  			if ( is_string($value) ) {
  				$out .= "\t{$name}: '{$value}'";
  			} else if ( is_bool($value) ) {
  				$out .= "\t{$name}: ".(($value)?'true':'false');
  			} else {
  				$out .= "\t{$name}: {$value}";
  			}
  		}
			$out .= (++$n >= count($options))?"\n":",\n";
		}
		$out .= "}";
		
		if ( $options['xtype'] === 'datefield' ) {
			$fieldlabel = __(Inflector::classify($fieldname));
			$out =<<<EOT
{
	xtype: 'fieldcontainer',
	fieldLabel: '{$fieldlabel}',
	defaults: {
		hideLabel: true
	},
	items:[{$out}]
}
EOT;
		}
		
		return $out;
	}
	
	/**
	 * Load Model data
	 *
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function load_model($modelname)
	{
		$this->_model = ClassRegistry::init($modelname);
		$this->_schema = $this->_model->schema();
	}
}