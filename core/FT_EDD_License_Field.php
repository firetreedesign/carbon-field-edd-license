<?php

namespace Carbon_Field_FT_EDD_License;

use Carbon_Fields\Field\Field;
use EDD_SL_Plugin_Updater;

class FT_EDD_License_Field extends Field {

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
	protected $verson = '';

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
	 * Field name
	 *
	 * @var string
	 */
	protected static $field_name;

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
		$dir    = \Carbon_Field_FT_EDD_License\DIR . '/languages/';
		$locale = get_locale();
		$path   = $dir . $locale . '.mo';
		load_textdomain( 'carbon-field-ft-edd-license', $path );
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
		$root_uri = \Carbon_Fields\Carbon_Fields::directory_to_url( \Carbon_Field_FT_EDD_License\DIR );

		// Enqueue field styles.
		wp_enqueue_style( 'carbon-field-ft-edd-license', $root_uri . '/build/bundle.css', array(), '1.0.0' );

		// Enqueue field scripts.
		wp_enqueue_script( 'carbon-field-ft-edd-license', $root_uri . '/build/bundle.js', array( 'carbon-fields-core' ), '1.0.0', false );

		wp_localize_script(
			'carbon-field-ft-edd-license',
			'ft_edd_license',
			array(
				'nonce' => wp_create_nonce( self::$field_name . '_nonce' ),
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
		$field_data = parent::to_json( $load );
		$field_data = array_merge(
			$field_data,
			array(
				'author'      => $this->author,
				'beta'        => $this->beta,
				'item_id'     => $this->item_id,
				'plugin_file' => $this->plugin_file,
				'store_url'   => $this->store_url,
				'version'     => $this->version,
			)
		);
		return $field_data;
	}

	/**
	 * Admin init
	 *
	 * @return void
	 */
	public function admin_init() {

		self::$field_name = $this->name;

		$this->activate_license();
		$this->deactivate_license();

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			include realpath( __DIR__ ) . '../lib/EDD_SL_Plugin_Updater.php';
		}

		new EDD_SL_Plugin_Updater(
			$this->store_url,
			$this->plugin_file,
			array(
				'license' => $this->get_value(),
				'version' => $this->version, // Current version number.
				'item_id' => $this->item_id, // ID of the product.
				'author'  => $this->author, // Author of the product.
				'beta'    => $this->beta, // Receive beta updates.
			)
		);

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
	private function activate_license() {

		// Listen for our activate button to be clicked.
		if ( ! isset( $_POST[ "{$this->name}_activate_license" ] ) ) {
			return;
		}

		// Run a quick security check.
		if ( ! check_admin_referer( "{$this->name}_nonce", "{$this->name}_nonce" ) ) {
			return; // Get out if we didn't click the Activate button.
		}

		// Retrieve the license key.
		$license_key = trim( $this->get_value() );

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

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			$this->error_message = $message;
			return;
		} else {
			$this->error_message = '';
		}

		update_option( "{$this->name}_status", $license_data->license );
	}

	/**
	 * Deactivate the license
	 *
	 * @return void
	 */
	private function deactivate_license() {

		// Listen for our deactivate button to be clicked.
		if ( ! isset( $_POST[ "{$this->name}_deactivate_license" ] ) ) {
			return;
		}

		// Run a quick security check.
		if ( ! check_admin_referer( "{$this->name}_nonce", "{$this->name}_nonce" ) ) {
			return; // Get out if we didn't click the Activate button.
		}

		// Retrieve the license key.
		$license_key = trim( $this->get_value() );

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

		// $license_data->license will be either "deactivated" or "failed".
		if ( 'deactivated' === $license_data->license ) {
			delete_option( "{$this->name}_status" );
		}
	}
}
