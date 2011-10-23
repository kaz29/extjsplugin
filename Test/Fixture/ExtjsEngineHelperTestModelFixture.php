<?php
class ExtjsEngineHelperTestModelFixture extends CakeTestFixture {
	public $name = 'ExtjsEngineHelperTestModel';
	public $table = 'extjs_engine_helper_test_models';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id'				=> array('type' => 'integer', 'key' => 'primary'),
		'name'			=> array('type' => 'string', 'length' => 10, 'null' => true),
		'opendate'	=> array('type' => 'date', 'null' => false),
	);

/**
 * records property
 *
 * @var array
 */
	public $records = array(
		array('name' => 'test-001', 'opendate' => '2011-10-21'),
		array('name' => 'test-002', 'opendate' => '2011-10-22'),
		array('name' => 'test-003', 'opendate' => '2011-10-23'),
		array('name' => 'test-004', 'opendate' => '2011-10-24'),
		array('name' => 'test-005', 'opendate' => '2011-10-25'),
		array('name' => 'test-006', 'opendate' => '2011-10-26'),
		array('name' => 'test-007', 'opendate' => '2011-10-27'),
		array('name' => 'test-008', 'opendate' => '2011-10-28'),
		array('name' => 'test-009', 'opendate' => '2011-10-29'),
		array('name' => 'test-010', 'opendate' => '2011-10-30'),
		array('name' => 'test-011', 'opendate' => '2011-10-31'),
	);
}
