<?php

namespace Test;

use Alchemy\Component\Annotations\Annotations;
use DSteiner23\Custom_Field_Repository\Client\Client_Interface;
use DSteiner23\Custom_Field_Repository\Examples\Sales_Report;
use DSteiner23\Custom_Field_Repository\Lazy_Load_Ghost_Proxy;
use DSteiner23\Custom_Field_Repository\ProxyException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class Lazy_Load_Ghost_ProxyTest extends TestCase {
	/**
	 * @var Sales_Report
	 */
	private $report;

	/**
	 * @var ObjectProphecy
	 */
	private $client;


	public function setUp() {
		parent::setUp();

		$this->client = $this->prophesize(
			Client_Interface::class
		);

		$this->report = new Lazy_Load_Ghost_Proxy(
			$this->client->reveal(),
			new Annotations(),
			new Sales_Report(),
			1
		);
	}

	public function test_call_unknown_function() {
		self::expectExceptionMessage( ProxyException::class );
		self::expectExceptionMessage( 'Invalid function call get_test' );
		$this->report->get_test();
	}

	public function test_multi_param_function() {
		$this->report->create_report_meta('Author', 'This is a test');
		self::assertSame('Author', $this->report->get_author());
		self::assertSame('This is a test', $this->report->get_description());
	}

	public function test_get_custom_field_value() {
		$this->client->get_value(Argument::exact('report'), Argument::exact(1))->shouldBeCalled();

		$this->report->get_report();
	}

	public function test_set_custom_field_value() {

		$this->client->set_value(Argument::any(), Argument::any())->shouldNotBeCalled();

		$this->report->set_report('somevalue');
		self::assertSame('somevalue', $this->report->get_report());
	}

	/**
	 * @dataProvider changeSetProvider
	 *
	 * @param $property
	 * @param $expected
	 */
	public function test_set_custom_field_value_adds_change($property, $expected) {
		$this->report->set_report('somevalue');

		self::assertSame($expected, $this->report->is_changed($property));
	}

	/**
	 * @return array
	 */
	public function changeSetProvider() {
		return [
			['report', true],
			['author', false],
		];
	}

}
