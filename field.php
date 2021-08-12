<?php
/**
 * Carbon Fields Easy Digital Downloads License Key Field
 *
 * @package Carbon_Fields_EDD_License
 */

namespace CF_EDD_License_Field;

use Carbon_Fields\Carbon_Fields;
use Carbon_Field_EDD_License\EDD_License_Field;
use CF_EDD_SL_Plugin_Updater;

define( 'Carbon_Field_EDD_License\\DIR', __DIR__ );

/**
 * Register the field with Carbon Fields
 *
 * @return void
 */
function register_field() {
	require_once __DIR__ . '/core/class-edd-license-field.php';

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
}
add_action( 'carbon_fields_loaded', __NAMESPACE__ . '\\register_field' );

/**
 * Register the updater
 *
 * @return void
 */
function register_updater() {
	$license_data = \get_option( 'cf_edd_license_data' );

	if ( ! $license_data ) {
		return;
	}

	$license_data = \json_decode( $license_data );

	foreach ( $license_data as $field => $data ) {
		if ( ! \class_exists( 'CF_EDD_SL_Plugin_Updater' ) ) {
			include_once \realpath( __DIR__ ) . '/lib/CF_EDD_SL_Plugin_Updater.php';
		}
		$results = new CF_EDD_SL_Plugin_Updater(
			$data->store_url,
			$data->plugin_file,
			array(
				'license' => $data->license,
				'version' => $data->version, // Current version number.
				'item_id' => $data->item_id, // ID of the product.
				'author'  => $data->author, // Author of the product.
				'beta'    => $data->beta, // Receive beta updates.
			)
		);
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_updater' );
