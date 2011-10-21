<?php
class DirectTestModelFixture extends CakeTestFixture {
	public $name = 'DirectTestModel';
	public $table = 'direct_test_models';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id'		=> array('type' => 'integer', 'key' => 'primary'),
		'name'	=> array('type' => 'string', 'length' => 10, 'null' => true)
	);

/**
 * records property
 *
 * @var array
 */
	public $records = array(
		array('name' => 'test-001'),
		array('name' => 'test-002'),
		array('name' => 'test-003'),
		array('name' => 'test-004'),
		array('name' => 'test-005'),
		array('name' => 'test-006'),
		array('name' => 'test-007'),
		array('name' => 'test-008'),
		array('name' => 'test-009'),
		array('name' => 'test-010'),
		array('name' => 'test-011'),
	);
}
