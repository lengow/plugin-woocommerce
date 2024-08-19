<?php
declare(strict_types=1);

class Test_Lengow_Toolbox extends WP_UnitTestCase
{
	function test_get_data() {
		$data = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_ALL );
		$this->assertNotEmpty($data['checklist']);
		$this->assertNotEmpty($data['plugin']);
		$this->assertNotEmpty($data['synchronization']);
		$this->assertNotEmpty($data['cms_options']);
		$this->assertNotEmpty($data['shops']);
		$this->assertNotEmpty($data['checksum']);
		$this->assertNotEmpty($data['logs']);
	}
}
