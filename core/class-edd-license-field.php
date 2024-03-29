<?php
/**
 * Carbon Fields Easy Digital Downloads License Field
 *
 * @package edd-license-field
 */

namespace Carbon_Field_EDD_License;

use Carbon_Fields\Field\Field;
use CF_EDD_SL_Plugin_Updater;
use stdClass;

/**
 * Carbon Fields Easy Digital Downloads License Field
 */
class EDD_License_Field extends Field {

	/**
	 * File
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Item ID
	 *
	 * @var int
	 */
	protected $item_id;

	/**
	 * Version
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Store URL
	 *
	 * @var string
	 */
	protected $store_url = '';

	/**
	 * Author
	 *
	 * @var string
	 */
	protected $author = '';

	/**
	 * Beta
	 *
	 * @var boolean
	 */
	protected $beta = false;

	/**
	 * Error Message
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * Prepare the field type for use.
	 * Called once per field type when activated.
	 *
	 * @static
	 * @access public
	 *
	 * @return void
	 */
	public static function field_type_activated() {
		$dir    = \Carbon_Field_EDD_License\DIR . '/languages/';
		$locale = get_locale();
		$path   = $dir . $locale . '.mo';
		load_textdomain( 'carbon-field-edd-license', $path );
	}

	/**
	 * Enqueue scripts and styles in admin.
	 * Called once per field type.
	 *
	 * @static
	 * @access public
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {
		$root_uri = \Carbon_Fields\Carbon_Fields::directory_to_url( \Carbon_Field_EDD_License\DIR );

		// Enqueue field styles.
		wp_enqueue_style( 'carbon-field-edd-license', $root_uri . '/build/bundle.css', array(), '1.0.0' );

		// Enqueue field scripts.
		wp_enqueue_script( 'carbon-field-edd-license', $root_uri . '/build/bundle.js', array( 'carbon-fields-core' ), '1.0.0', false );

		wp_localize_script(
			'carbon-field-edd-license',
			'edd_license',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'edd-license' ),
			)
		);
	}

	/**
	 * Returns an array that holds the field data, suitable for JSON representation.
	 *
	 * @param bool $load Should the value be loaded from the database or use the value from the current instance.
	 * @return array
	 */
	public function to_json( $load ) {
		$status     = get_option( "{$this->name}_status" );
		$field_data = parent::to_json( $load );
		$field_data = array_merge(
			$field_data,
			array(
				'author'         => $this->author,
				'beta'           => $this->beta,
				'item_id'        => $this->item_id,
				'plugin_file'    => $this->plugin_file,
				'store_url'      => $this->store_url,
				'version'        => $this->version,
				'status'         => $status,
				'license_status' => $this->get_license_status( $status ),
				'nonce'          => wp_create_nonce( "{$this->name}_nonce" ),
				'nonce_name'     => "{$this->name}_nonce",
				'date_format'    => get_option( 'date_format' ),
				'license'        => $this->get_license_key(),
			)
		);
		return $field_data;
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		add_action( "wp_ajax_{$this->name}_activate", array( $this, 'activate_license' ) );
		add_action( "wp_ajax_{$this->name}_deactivate", array( $this, 'deactivate_license' ) );
	}

	/**
	 * Set field plugin file
	 *
	 * @param string $plugin_file Plugin file.
	 * @return this
	 */
	public function set_plugin_file( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		return $this;
	}

	/**
	 * Set field item id
	 *
	 * @param string $item_id Item ID.
	 * @return this
	 */
	public function set_item_id( $item_id ) {
		$this->item_id = $item_id;
		return $this;
	}

	/**
	 * Set field version
	 *
	 * @param string $version Version.
	 * @return this
	 */
	public function set_version( $version ) {
		$this->version = $version;
		return $this;
	}

	/**
	 * Set field store url
	 *
	 * @param string $store_url Store URL.
	 * @return this
	 */
	public function set_store_url( $store_url ) {
		$this->store_url = $store_url;
		return $this;
	}

	/**
	 * Set field author
	 *
	 * @param string $author Author.
	 * @return this
	 */
	public function set_author( $author ) {
		$this->author = $author;
		return $this;
	}

	/**
	 * Set field beta
	 *
	 * @param bool $beta Beta.
	 * @return this
	 */
	public function set_beta( $beta ) {
		$this->beta = $beta;
		return $this;
	}

	/**
	 * Activate the license
	 *
	 * @return void
	 */
	public function activate_license() {
		check_ajax_referer( 'edd-license', '_wpnonce' );

		// Retrieve the license key.
		$license_key = trim( $this->get_license_key() );

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_id'    => rawurlencode( $this->item_id ),
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			$this->store_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure thre response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again' );
			}
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired':
						$message = sprintf(
							// translators: the placeholder is for a date.
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'disabled':
					case 'revoked':
						$message = __( 'Your license key has been disabled.' );
						break;
					case 'missing':
						$message = __( 'Invalid license' );
						break;
					case 'invalid':
					case 'site_inactive':
						$message = __( 'Your license is not active for this URL.' );
						break;
					case 'item_name_mismatch':
						$message = __( 'This appears to be an invalid license key for this product' );
						break;
					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.' );
						break;
					default:
						$message = __( 'An error occurred, please try again.' );
						break;
				}
			}
		}

		$license_status = '';

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			$this->error_message = $message;
		} else {
			// Reset the error message.
			$this->error_message = null;

			// Save the license status.
			update_option( "{$this->name}_status", $license_data );

			// Get the license status text.
			$license_status = $this->get_license_status( $license_data );

			// Save the item info.
			$this->save_item_info();
		}

		wp_send_json(
			array(
				'error'  => $this->error_message,
				'status' => $license_status,
			)
		);
	}

	/**
	 * Deactivate the license
	 *
	 * @return void
	 */
	public function deactivate_license() {
		check_ajax_referer( 'edd-license', '_wpnonce' );

		// Retrieve the license key.
		$license_key = trim( $this->get_license_key() );

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license_key,
			'item_id'    => rawurlencode( $this->item_id ),
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			$this->store_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure thre response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again' );
			}
		}

		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			$this->error_message = $message;
			return;
		} else {
			$this->error_message = null;
		}

		// $license_data->license will be either "deactivated" or "failed".
		if ( is_object( $license_data ) && isset( $license_data->license ) && 'deactivated' === $license_data->license ) {
			update_option( "{$this->name}_status", $license_data );
			$this->delete_item_info();
		}

		$license_status = $this->get_license_status( $license_data );

		wp_send_json(
			array(
				'error'  => $this->error_message,
				'status' => $license_status,
			)
		);
	}

	/**
	 * Get the license key
	 */
	private function get_license_key() {
		switch ( $this->context ) {
			case 'theme_options':
				return \carbon_get_theme_option( $this->base_name );
			default:
				return '';
		}
	}

	/**
	 * Get the license status in text format
	 *
	 * @param object $license_data License data.
	 * @return string
	 */
	private function get_license_status( $license_data ) {
		$license_status = '';

		if ( // Lifetime license.
			isset( $license_data->license ) &&
			'valid' === $license_data->license &&
			isset( $license_data->expires ) &&
			'lifetime' === $license_data->expires
		) {
			$license_status = __( 'Your license key never expires.' );
		}

		if (
			isset( $license_data->license ) &&
			'valid' === $license_data->license &&
			isset( $license_data->expires )
		) {
			if ( 'lifetime' === $license_data->expires ) {
				$license_status = __( 'Your license key never expires.' );
			} else {
				$license_status = sprintf(
					'Your license key expires on %s.',
					date(
						get_option( 'date_format' ),
						strtotime( $license_data->expires )
					)
				);
			}
		}

		return $license_status;
	}

	/**
	 * Save the item info to the options.
	 *
	 * @return void
	 */
	private function save_item_info() {
		$licenses = \get_option( 'cf_edd_license_data' );

		if ( ! $licenses ) {
			$licenses = new stdClass();
		} else {
			$licenses = \json_decode( $licenses );
		}

		$licenses->{$this->name } = array(
			'store_url'   => $this->store_url,
			'plugin_file' => $this->plugin_file,
			'license'     => $this->get_license_key(),
			'version'     => $this->version,
			'item_id'     => $this->item_id,
			'author'      => $this->author,
			'beta'        => $this->beta,
		);

		\update_option( 'cf_edd_license_data', \wp_json_encode( $licenses ) );
	}

	/**
	 * Delete the item info from the options.
	 *
	 * @return void
	 */
	private function delete_item_info() {
		$licenses = \get_option( 'cf_edd_license_data' );

		if ( ! $licenses ) {
			$licenses = new stdClass();
		} else {
			$licenses = \json_decode( $licenses );
		}

		if ( ! isset( $licenses->{ $this->name } ) ) {
			return;
		}

		unset( $licenses->{ $this->name } );

		\update_option( 'cf_edd_license_data', \wp_json_encode( $licenses ) );
	}
}
