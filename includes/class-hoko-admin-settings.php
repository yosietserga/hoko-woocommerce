<?php
/**
 * Plugin settings
 *
 * @since 1.0.0
 *
 * @package HOKO
 * @subpackage HOKO/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin settings
 *
 * @since 1.0.0
 */
class WC_HOKO_Admin_Settings {

	/**
	 * Construcotr for admin settings
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init amdin settings
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action(
			'admin_init',
			array( $this, 'plugin_admin_init_settings' )
		);
	}

	/**
	 * Add submenu to woocommerce admin menu
	 *
	 * @since 1.0.0
	 */
	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Hoko', 'wc-hoko' ),
			esc_html__( 'Hoko Settings', 'wc-hoko' ),
			'manage_options',
			'hoko_menu',
			array( $this, 'settings_form' )
		);
	}

	/**
	 * Add main plugin setings form
	 *
	 * @since 1.0.0
	 */
	public function settings_form() {
		include_once WC_HOKO_URI_ABSPATH . 'views/html-admin-settings-form.php';
	}

	/**
	 * Init plugin settings
	 *
	 * @since 1.0.0
	 */
	public function plugin_admin_init_settings() {
		register_setting(
			'plugin_hoko_options',
			'plugin_hoko_options',
			array( $this, 'set_options' )
		);

		add_settings_section(
			'plugin_main',
			'',
			array( $this, 'plugin_section_text' ),
			'plugin2'
		);

		add_settings_section(
			'plugin_main',
			'',
			array( $this, 'hoko_products_list' ),
			'plugin_hoko'
		);

	}

	/**
	 * Function callback for a add_settings_section
	 *
	 * @since 1.0.0
	 */
	public function plugin_section_text() {
		echo '<p></p>';
	}

	/**
	 * Function callback for a add_settings_field
	 *
	 * @since 1.0.0
	 */
	public function hoko_products_list() {
		$options = get_option( 'plugin_hoko_options' );
		if (isset($_GET['hoko_action']) && $_GET['hoko_action'] == 'products_list') {
			$hoko_api_username = !empty( $options['hoko_api_username'] ) ? $options['hoko_api_username'] : '';
			$hoko_api_password = !empty( $options['hoko_api_password'] ) ? $options['hoko_api_password'] : '';

				try {
					$hoko = new WC_HOKO_Necoyoad_Hoko_Api($hoko_api_username, $hoko_api_password);
					$page = isset($_GET['spage']) && (int)$_GET['spage'] ? (int)$_GET['spage'] : 1;
					$data = $hoko->getProducts(["page"=>$page]);
					$products = $data->data;
					$page = $data->current_page;
				} catch( Exception $e ) {
					echo $e->getMessage();
				}

			include_once WC_HOKO_URI_ABSPATH . 'views/html-admin-settings-form-hoko-products.php';
		}
	}

	/**
	 * Function callback for a add_settings_field
	 *
	 * @since 1.0.0
	 */
	public function display_settings() {
		$options = get_option( 'plugin_hoko_options' );

		if (!isset($_GET['hoko_action']) || $_GET['hoko_action'] === 'settings') {

			$hoko_subscription_key = !empty( $options['hoko_subscription_key'] ) ? $options['hoko_subscription_key'] : '';
			$hoko_api_username = !empty( $options['hoko_api_username'] ) ? $options['hoko_api_username'] : '';
			$hoko_api_password = !empty( $options['hoko_api_password'] ) ? $options['hoko_api_password'] : '';

			$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
			$size       = size_format( $bytes );

			if (!empty($hoko_subscription_key) && !empty($hoko_api_username) && !empty($hoko_api_password)) {
				try {
					$hoko = new WC_HOKO_Necoyoad_Hoko_Api($hoko_subscription_key, $hoko_api_username, $hoko_api_password);
					$hoko_connected = $hoko->getAccessToken();
				} catch( Exception $e ) {
					echo $e->getMessage();
				}
			}
			include_once WC_HOKO_URI_ABSPATH . 'views/html-admin-settings-form-options.php';
		}
	}

	/**
	 * Set options
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Option input value.
	 *
	 * @return array
	 */
	public function set_options( $input ) {
		$valid_input = $this->validate_options( $input );

		return $valid_input;
	}

	/**
	 * Validate user input
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Option input value.
	 *
	 * @return array
	 */
	public function validate_options( $input ) {
		$valid_input['hoko_subscription_key'] =  $input['hoko_subscription_key'];
		$valid_input['hoko_api_username']     =  $input['hoko_api_username'];
		$valid_input['hoko_api_password']     =  $input['hoko_api_password'];

		return $valid_input;
	}

	/**
	 * Get plugin options
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_plugin_options() {
		$options = get_option( 'plugin_hoko_options' );

		$options['hoko_subscription_key'] = wp_specialchars_decode( $options['hoko_subscription_key'], ENT_QUOTES );
		$options['hoko_api_username']     = wp_specialchars_decode( $options['hoko_api_username'], ENT_QUOTES );
		$options['hoko_api_password']     = wp_specialchars_decode( $options['hoko_api_password'], ENT_QUOTES );

		return $options;
	}

}

new WC_HOKO_Admin_Settings();
