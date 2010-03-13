<?php
/**
 * ExtJS Engine Helper for JsHelper
 *
 * Provides ExtJS specific Javascript for JsHelper.
 *
 * PHP versions 4 and 5
 *
 * @copyright       Copyright 2010, KazWatanabe.
 * @link            http://d.hatena.ne.jp/kaz_29/
 * @package         extjsplugin
 * @subpackage      extjsplugin.view.helpers
 */
App::import('Helper', 'Js');

class ExtjsEngineHelper extends JsBaseEngineHelper
{
/**
 * Option mappings for ExtJS
 *
 * @var array
 */
	var $_optionMap = array(
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
	var $_callbackArguments = array(
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
	function event($type, $callback, $options = array())
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
	function domReady($functionBody)
	{
		return $this->event('onReady', $functionBody, array('stop' => false));
	}
	
/**
 * Create javascript selector for a CSS rule
 *
 * @param string $selector The selector that is targeted
 * @return object instance of $this. Allows chained methods.
 */
	function get($selector)
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
	function each($callback) 
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
	function effect($name, $options = array()) {
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
	function request($url, $options = array()) {
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
}