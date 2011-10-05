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
 * @package         extjsplugin
 * @subpackage      extjsplugin.view.helpers
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
}