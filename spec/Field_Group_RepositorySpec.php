<?php

namespace spec\DSteiner23\Custom_Field_Repository;

use DSteiner23\Custom_Field_Repository\Client\Client_Interface;
use DSteiner23\Custom_Field_Repository\Examples\Sales_Report;
use DSteiner23\Custom_Field_Repository\Field_Group_Repository;
use DSteiner23\Custom_Field_Repository\Lazy_Load_Ghost_Proxy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Field_Group_RepositorySpec extends ObjectBehavior {
	function it_is_initializable() {
		$this->shouldHaveType( Field_Group_Repository::class );
	}

	function it_finds_field_group() {
		$this->find(
			Sales_Report::class,
			1
		)->shouldReturnAnInstanceOf( Lazy_Load_Ghost_Proxy::class );
	}

	function it_persists( Client_Interface $client, Lazy_Load_Ghost_Proxy $proxy ) {
		$proxy->get_client()->willReturn( $client );
		$proxy->get_changes()->willReturn( [
			'report',
			'author'
		] );

		$proxy->get_property_value( Argument::exact( 'report' ) )->shouldBeCalled();
		$proxy->get_property_value( Argument::exact( 'author' ) )->shouldBeCalled();
		$proxy->get_property_path( Argument::exact( 'report' ) )->shouldBeCalled();
		$proxy->get_property_path( Argument::exact( 'author' ) )->shouldBeCalled();
		$proxy->get_id()->shouldBeCalledTimes( 2 );

		$client->set_value(
			Argument::any(),
			Argument::any(),
			Argument::any()
		)->shouldBeCalledTimes( 2 );

		$this->persist( $proxy );
	}
}
