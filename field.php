<?php
/**
 * Carbon Fields Easy Digital Downloads License Key Field
 *
 * @package Carbon_Fields_FT_EDD_License
 */

use Carbon_Fields\Carbon_Fields;
use Carbon_Field_FT_EDD_License\FT_EDD_License_Field;

define( 'Carbon_Field_FT_EDD_License\\DIR', __DIR__ );

Carbon_Fields::extend(
	FT_EDD_License_Field::class,
	function( $container ) {
		return new FT_EDD_License_Field(
			$container['arguments']['type'],
			$container['arguments']['name'],
			$container['arguments']['label']
		);
	}
);
