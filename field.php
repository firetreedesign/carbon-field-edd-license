<?php
/**
 * Carbon Fields Easy Digital Downloads License Key Field
 *
 * @package Carbon_Fields_EDD_License
 */

use Carbon_Fields\Carbon_Fields;
use Carbon_Field_EDD_License\EDD_License_Field;

define( 'Carbon_Field_EDD_License\\DIR', __DIR__ );

require_once __DIR__ . '/core/EDD_License_Field.php';

Carbon_Fields::extend(
	EDD_License_Field::class,
	function( $container ) {
		return new EDD_License_Field(
			$container['arguments']['type'],
			$container['arguments']['name'],
			$container['arguments']['label']
		);
	}
);
