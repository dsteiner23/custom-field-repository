<?php
namespace DSteiner23\Custom_Field_Repository\Field;

use Alchemy\Component\Annotations\Annotations;
use DSteiner23\Custom_Field_Repository\Provider\Provider_Manager;

/**
 * @package DSteiner23\Custom_Field_Repository\Field
 */
class Field_Generator_Factory {
	static function create( array $field_groups ) {

		$annotations      = new Annotations();
		$provider_manager = new Provider_Manager( $annotations, $field_groups[0] ); //Todo!!

		return new Field_Generator( $field_groups, $provider_manager->getProvider(), $annotations );
	}
}