<?php
App::uses('HtmlHelper', 'View/Helper');
App::uses('JsHelper', 'View/Helper');
App::uses('ExtjsEngineHelper', 'Extjs.View/Helper');
App::uses('DirectComponent', 'Extjs.Controller/Component');
App::uses('View', 'View');
App::uses('ExtjsAppModel', 'Extjs.Model');

class ExtjsEngineHelperTestModel extends ExtjsAppModel {
//	public $table = 'ExtjsEngineHelperTestModel';
	public $actsAs = array('Extjs.Direct');
	public $directSettings = array(
		'allow' => array('index', 'add', 'view', 'edit', 'del'),
		'form'	=> array('add', 'edit', 'del'),
	);
}

class ExtjsEngineHelperTestCase extends CakeTestCase {
	public $fixtures = array(
		'plugin.extjs.extjs_engine_helper_test_model',
	);
/**
 * startTest
 *
 * @return void
 */
	function startTest() {
		$controller = null;
		$this->View = $this->getMock('View', array('addScript'), array(&$controller));
		$this->Extjs = new ExtjsEngineHelper($this->View);
	}

/**
 * end test
 *
 * @return void
 */
	function endTest() {
		unset($this->Extjs);
	}
	
/**
 * test selector method
 *
 * @return void
 */
	function testSelector() {
		$result = $this->Extjs->get('#content');
		$this->assertEqual($result, $this->Extjs);
		$this->assertEqual($this->Extjs->selection, 'Ext.get("content")');

		$result = $this->Extjs->get('document');
		$this->assertEqual($result, $this->Extjs);
		$this->assertEqual($this->Extjs->selection, 'Ext.get(document)');

		$result = $this->Extjs->get('window');
		$this->assertEqual($result, $this->Extjs);
		$this->assertEqual($this->Extjs->selection, 'Ext.select(window)');

		$result = $this->Extjs->get('ul');
		$this->assertEqual($result, $this->Extjs);
		$this->assertEqual($this->Extjs->selection, 'Ext.select("ul")');
	}
	
/**
 * test event binding
 *
 * @return void
 */
	function testEvent() {
		$this->Extjs->get('#myLink');
		$result = $this->Extjs->event('click', 'doClick', array('wrap' => false));
		$expected = 'Ext.get("myLink").on("click", doClick);';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->event('click', 'this.show();', array('stop' => false));
		$expected = 'Ext.get("myLink").on("click", function () {this.show();});';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->event('click', 'this.hide();');
		$expected = 'Ext.get("myLink").on("click", function () {this.hide();'."\n".'return false;});';
		$this->assertEqual($result, $expected);
	}
	
/**
 * test dom ready event creation
 *
 * @return void
 */
	function testDomReady() {
		$result = $this->Extjs->domReady('foo.name = "bar";');
		$expected = 'Ext.onReady(function () {foo.name = "bar";});';
		$this->assertEqual($result, $expected);
	}

/**
 * test Each method
 *
 * @return void
 */
	function testEach() {
		$this->Extjs->get('#foo');
		$result = $this->Extjs->each('el.hide();');
		$expected = 'Ext.get("foo").each(function (el) {el.hide();});';
		$this->assertEqual($result, $expected);
		
		$this->Extjs->get('foo');
		$result = $this->Extjs->each('el.hide();');
		$expected = 'Ext.select("foo").each(function (el) {el.hide();});';
		$this->assertEqual($result, $expected);
	}
/**
 * test Effect generation
 *
 * @return void
 */
	function testEffect() {
		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('slideIn');
		$expected = 'Ext.get("foo").slideIn(\'r\', {duration: 0.5});';
		$this->assertEqual($result, $expected);
		
		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('slideOut');
		$expected = 'Ext.get("foo").slideOut(\'r\', {duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('slideDown');
		$expected = 'Ext.get("foo").slideOut(\'b\', {duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('slideUp');
		$expected = 'Ext.get("foo").slideOut(\'t\', {duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('puff');
		$expected = 'Ext.get("foo").puff({duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('switchOff');
		$expected = 'Ext.get("foo").switchOff({duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('fadeIn');
		$expected = 'Ext.get("foo").fadeIn({duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('fadeOut');
		$expected = 'Ext.get("foo").fadeOut({duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('frame');
		$expected = 'Ext.get("foo").frame(\'FF0000\', 3, {duration: 0.5});';
		$this->assertEqual($result, $expected);

		$this->Extjs->get('#foo');
		$result = $this->Extjs->effect('highlight');
		$expected = 'Ext.get("foo").highlight(\'FF0000\', {duration: 0.5});';
		$this->assertEqual($result, $expected);
	}
/**
 * Test Request Generation
 *
 * @return void
 */
	function testRequest() {
		$result = $this->Extjs->request(array('controller' => 'posts', 'action' => 'view', 1));
		$expected = 'Ext.Ajax.request({url:"\\/posts\\/view\\/1"});';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->request(array('controller' => 'posts', 'action' => 'view', 1), array(
			'update' => '#content',
			'wrapCallbacks' => true,
		));
		$expected = 'Ext.Ajax.request({success:function (data, textStatus) {Ext.get("content").update(data.responseText);}, url:"\/posts\/view\/1"});';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->request('/people/edit/1', array(
			'method' => 'post',
			'success' => 'doSuccess',
			'error' => 'handleError',
			'data' => array('name' => 'jim', 'height' => '185cm'),
			'wrapCallbacks' => false
		));
		$expected = 'Ext.Ajax.request({failure:handleError, method:"post", params:"name=jim&height=185cm", success:doSuccess, url:"\\/people\\/edit\\/1"});';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->request('/people/edit/1', array(
			'update' => '#updated',
			'success' => 'doFoo',
			'method' => 'post',
			'wrapCallbacks' => false
		));
		$expected = 'Ext.Ajax.request({method:"post", success:function (data, textStatus) {Ext.get("updated").update(data.responseText);}, url:"\\/people\\/edit\\/1"});';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->request('/people/edit/1', array(
			'update' => '#updated',
			'success' => 'doFoo',
			'method' => 'post',
			'dataExpression' => true,
			'data' => 'Ext.Ajax.serializeForm(Ext.get("someId"))',
			'wrapCallbacks' => false
		));
		$expected = 'Ext.Ajax.request({method:"post", params:Ext.Ajax.serializeForm(Ext.get("someId")), success:function (data, textStatus) {Ext.get("updated").update(data.responseText);}, url:"\\/people\\/edit\\/1"});';
		$this->assertEqual($result, $expected);

		$result = $this->Extjs->request('/people/edit/1', array(
			'success' => 'doFoo',
			'method' => 'post',
			'dataExpression' => true,
			'data' => 'Ext.Ajax.serializeForm(Ext.get("someId"))',
		));
		$expected = 'Ext.Ajax.request({method:"post", params:Ext.Ajax.serializeForm(Ext.get("someId")), success:function (data, textStatus) {doFoo}, url:"\\/people\\/edit\\/1"});';
		$this->assertEqual($result, $expected);
	}
	
/**
 * Test Namespace Generation
 *
 * @return void
 */
	function testNamespace() {
		$result = $this->Extjs->ns('User');
		$this->assertEqual("Ext.ns('Ext.app.users');", $result) ;

		$result = $this->Extjs->ns('users');
		$this->assertEqual("Ext.ns('Ext.app.users');", $result) ;
	}
	
/**
 * Test ExtDirect Action Generation
 *
 * @return void
 */
	function testCreateAction(){
		$result = $this->Extjs->actions('ExtjsEngineHelperTestModel');
		$expected =<<<EOT
'actions':{
'ExtjsEngineHelperTestModel':[
{'name':'index','len':1,'formHandler':false},
{'name':'add','len':1,'formHandler':true},
{'name':'view','len':2,'formHandler':false},
{'name':'edit','len':1,'formHandler':true},
{'name':'del','len':1,'formHandler':true}
]}
EOT;
		$this->assertEqual($expected, $result) ;
	}
	
/**
 * Test ExtModel Generation
 *
 * @return void
 */
	function testCreateModel(){
		$result = $this->Extjs->getmodel('ExtjsEngineHelperTestModel');
		$expected =<<<EOT
Ext.define('ExtjsEngineHelperTestModel',{
extend:'Ext.data.Model',
fields:[
'id',
'name'
]})
EOT;
		$this->assertEqual($expected, $result) ;
	}

/**
 * Test DataStore Generation
 *
 * @return void
 */
	function testCreateStore(){
		$result = $this->Extjs->store('ExtjsEngineHelperTestModel');
		$expected =<<<EOT
Ext.create('Ext.data.JsonStore',
{"model":"ExtjsEngineHelperTestModel","remoteSort":true,"autoLoad":true,"sorters":[{"property":"id","direction":"DESC"}],"proxy":{"type":"direct","directFn":ExtjsEngineHelperTestModel.index,"reader":{"type":"json","root":"datas"},"listeners":{"exception":function(proxy, response, operation) {
	if ( !response.result.success ) {
		Ext.Msg.alert(response.result.message);
	}
}}}}
)
EOT;
		$this->assertEqual($expected, $result) ;
	}

/**
 * Test Grid Columns Generation
 *
 * @return void
 */
	function testCreateColumns(){
		$result = $this->Extjs->columns('ExtjsEngineHelperTestModel');
		$expected =<<<EOT
[{
	text:'name',
	dataIndex:'name',
	flex:1,
	sortable:true,
	hideable:true
}]
EOT;
		$this->assertEqual($expected, $result) ;
	}
	
/**
 * Test FormPanel Generation
 *
 * @return void
 */
	function testFormPanel(){
		$options = array(
			'api' => array('ExtjsEngineHelperTestModel.add'),
		);
		$result = $this->Extjs->form('ExtjsEngineHelperTestModel', $options);
/*
		$expected =<<<EOT
Ext.create('Ext.form.Panel', {
	id: 'extjs_engine_helper_test_models-form-id',
	frame: true,
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
	api: {$api},
	paramOrder: ['escape']
EOT;
*/
//		$this->assertEqual($expected, $result) ;
	}
}