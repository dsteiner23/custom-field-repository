<?php

namespace DSteiner23\Custom_Field_Repository;

use Alchemy\Component\Annotations\Annotations;
use DSteiner23\Custom_Field_Repository\Client\Client_Interface;

/**
 * Class Field_Generator
 * @package DSteiner23\Custom_Field_Repository
 */
class Field_Generator {

	const OPTION_TITLE = 'title';
	const OPTION_DEFAULT = 'default';
	const OPTION_TYPE = 'type';

	const TYPE_TEXT = 'text';
	const TYPE_BOOLEAN = 'true_false';

	/**
	 * @var Client_Interface
	 */
	private $client;
	/**
	 * @var array
	 */
	private $field_groups;

	/**
	 * @var Annotations
	 */
	private $annotations;

	private static $allowed_field_options = [self::OPTION_DEFAULT, self::OPTION_TYPE];
	private static $allowed_field_group_options = [self::OPTION_TITLE];
	private static $allowed_data_types = [self::TYPE_TEXT, self::TYPE_BOOLEAN];

	/**
	 * Field_Generator constructor.
	 *
	 * @param array $field_groups
	 * @param Client_Interface $client
	 * @param Annotations $annotations
	 */
	public function __construct( array $field_groups, Client_Interface $client, Annotations $annotations ) {
		$this->field_groups = $field_groups;
		$this->client       = $client;
		$this->annotations  = $annotations;
	}

	public function generate() {
		$files = [];
		foreach ( $this->field_groups as $field_group ) {
			$object = new $field_group;
				$files[] = $object;
				$this->create_field_group( $field_group );
		}

		return $files; //Todo: Macht das hier so Sinn?
	}

	/**
	 * @param $class
	 */
	private function create_field_group( $class ) {
		$annotations = $this->annotations->getClassAnnotations( $class );

		if ( is_array( $annotations ) && array_key_exists( 'name', $annotations['Field_Group'][0] ) ) {
			$field_group = $annotations['Field_Group'][0]['name'];
			$this->client->create_field_group( $field_group, $this->get_field_group_options( $annotations ) );

			$annotations = $this->annotations->getAllPropertyAnnotations( $class );

			foreach ( $annotations as $annotation ) {
				$this->create_field( $annotation, $field_group );
			}

		}
	}

	/**
	 * @param $annotation
	 * @param $field_group
	 */
	private function create_field( $annotation, $field_group ) {
			if ( is_array( $annotation ) && array_key_exists( 'Field', $annotation ) ) {
				$this->client->create_field(
					$annotation['Field'][0]['name'],
					$field_group,
					$this->get_field_options($annotation)
				);
			}
	}

	/**
	 * @param $annotations
	 *
	 * @return array
	 */
	private function get_field_group_options( $annotations ) {
		$options = [];
		foreach ($annotations['Field_Group'][0] as $key => $value) {
			if (in_array($key, self::$allowed_field_group_options)) {
				$options[$key] = $value;
			}
		}

		return $options;
	}

	private function get_field_options($annotations) {
		$options = [];
		foreach ($annotations['Field'][0] as $key => $value) {
			if (in_array($key, self::$allowed_field_options)) {

				if ($key == self::OPTION_TYPE) {
					if (!$this->validate_data_types($value)) {
						throw new \Exception(
							sprintf('Type %s not supported', $value)
						);
					}
				}

				$options[$key] = $value;
			}
		}

		return $options;
	}

	/**
	 * @param $data_type
	 *
	 * @return bool
	 */
	private function validate_data_types($data_type) {
		return (in_array($data_type, self::$allowed_data_types));
	}
}