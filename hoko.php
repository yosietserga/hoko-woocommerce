<?php
/**
 * The plugin bootstrap file
 *
 * @since 1.0.0
 *
 * Plugin Name:  HOKO
 * Plugin URI:   https://necoyoad.com/
 * Description:  Sync your data with HOKO 
 * Version:      1.0.0
 * Author:       Necoyoad
 * Author URI:   https://necoyoad.com/
 * License:      GPL-3.0+
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:  wc-hoko
 * Domain Path:  /languages
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Plagin Class.
 *
 * @since 1.0.0
 */
final class WC_HOKO_Plugin {
	/**
	 * The single instance of the class.
	 *
	 * @var    WC_HOKO_Plugin
	 * @access protected
	 * @since  1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main plugin instance.
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Plagin - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WC_HOKO_Plugin Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';

		// Check if woocommerce is already active.
		$woocommerce_check = hoko_is_plugin_active(
			'Hoko',
			'WooCommerce',
			'woocommerce/woocommerce.php',
			'3.1.0'
		);

		if ( $woocommerce_check ) {
			// Add woocommerce activation checkup.
			$this->define_constants();
			$this->init_hooks();
		}
	}

	/**
	 * Define plugin constants.
	 *
	 * @since 1.0.0
	 */
	private function define_constants() {
		define( 'WC_HOKO_PLUGIN_FILE', __FILE__ );
		define( 'WC_HOKO_URI', plugins_url( '', WC_HOKO_PLUGIN_FILE ) );
		define( 'WC_HOKO_URI_ABSPATH', dirname( __FILE__ ) . '/' );
		define( 'WC_HOKO_WC_ABSPATH', WP_PLUGIN_DIR . '/woocommerce/' );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'admin_init', array( $this, 'init_frontend' ), 0 );
		add_filter(
			'plugin_action_links_' . plugin_basename( __FILE__ ),
			array( $this, 'set_plugin_action_links' ),
			10,
			1
		);
		add_filter(
			'woocommerce_screen_ids',
			array( $this, 'add_woocommerce_screen_ids' ),
			10,
			1
		);

		//ajax add product 
		add_action( 'wp_ajax_nopriv_hoko_insert_product', array( $this, 'add_product' ) );
		add_action( 'wp_ajax_hoko_insert_product',  array( $this, 'add_product' ) );
		
		//hook after order created and paid sucessfully
		add_action( 'woocommerce_thankyou', 'make_order', 10, 1);

		//ajax add product 
		add_action( 'wp_ajax_nopriv_hoko_insert_product_images', array( $this, 'add_product_images' ) );
		add_action( 'wp_ajax_hoko_insert_product_images',  array( $this, 'add_product_images' ) );

		//product publish
		add_action( 'transition_post_status', 'add_custom_meta_on_publish_product', 9999, 3 );
	}

	function make_order( $order_id ) {

	    if ( !$order_id ) return;

	    // Getting an instance of the order object
	    $order = wc_get_order( $order_id );

	    if($order->is_paid()) {

		    // iterating through each order items (getting product ID and the product object) 
		    // (work for simple and variable products)
		    foreach ( $order->get_items() as $item_id => $item ) {

		        if( $item['variation_id'] > 0 ){
		            $product_id = $item['variation_id']; // variable product
		        } else {
		            $product_id = $item['product_id']; // simple product
		        }

		        // Get the product object
		        $product = wc_get_product( $product_id );

		    }

		}
	}

	public function link_product($id) {
		global $wpdb;

	}

	public function responseSuccess(Array $data = []) {
	  	wp_reset_postdata();
	  	wp_send_json([
	  		"status"=>"OK",
	  		"data"=>$data
	  	]);
	  	wp_die("Hoko ajax reponse success");
	}

	public function responseError(String $error = "") {
	  	wp_reset_postdata();
	  	wp_send_json([
	  		"status"=>"Fail",
	  		"error"=>"Something went wrong. Error: ". $error
	  	]);
	  	wp_die("Hoko ajax reponse error");
	}

	/**
	 * Include required plugin core files
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		if ( $this->is_request( 'admin' ) ) {
			include_once WC_HOKO_URI_ABSPATH . 'lib/autoload.php';
			include_once WC_HOKO_URI_ABSPATH . 'includes/class-hoko-admin-settings.php';
			include_once WC_HOKO_URI_ABSPATH . 'includes/class-hoko-wrapper-api.php';
			include_once WC_HOKO_URI_ABSPATH . 'includes/class-woocommerce-wrapper-api.php';
		}
	}

	/**
	 * Init plugin when WordPress Initialises.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Before init action.
		do_action( 'before_hoko_init' );
		// Set up localisation.
		$this->load_plugin_textdomain();
		// After init action.
		do_action( 'hoko_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones
	 * if the same translation is present.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wc-hoko',
			false,
			WC_HOKO_URI_ABSPATH . '/languages'
		);
	}

	/**
	 * Init frontend files.
	 *
	 * @since 1.0.0
	 */
	public function init_frontend() {
		wp_register_style(
			'hoko_admin_style',
			WC_HOKO_URI . '/assets/css/hoko.css',
		);
		wp_register_script(
			'hoko_admin',
			WC_HOKO_URI . '/assets/js/hoko.js',
			array( 'jquery' ),
			true,
			true
		);

		wp_register_style(
			'owl.carousel',
			'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
		);
		wp_register_style(
			'owl.carousel_theme',
			'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css',
		);
		wp_register_script(
			'owl.carousel',
			'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
			array( 'jquery' ),
			true,
			true
		);

		wp_register_style(
			'fancyboxv4',
			'https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css',
		);
		wp_register_script(
			'fancyboxv4',
			'https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js',
			array( 'jquery' ),
			true,
			true
		);

		$params = array(
			'urls'    => array(
				'import_products_from_hoko' =>
					current_user_can( 'import' )
					? esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer_hoko' ) )
					: null,
			),
			'strings' => array(
				'import_products_from_hoko' =>
				esc_html__( 'Importar Desde HOKO', 'wc-hoko' ),
			),
		);

		wp_localize_script(
			'hoko_admin',
			'woocommerce_hoko_admin_screen',
			$params
		);

		$settings = new WC_HOKO_Admin_Settings();

		wp_enqueue_style( 'owl.carousel' );
		wp_enqueue_style( 'owl.carousel_theme' );
		wp_enqueue_style( 'fancyboxv4' );
		wp_enqueue_style( 'hoko_admin_style' );

		wp_enqueue_script( 'owl.carousel' );
		wp_enqueue_script( 'fancyboxv4' );
		wp_enqueue_script( 'hoko_admin' );
	}

	public function add_product() {
		$data = [];
		$data['hoko_product_id'] = isset($_POST["hoko_product_id"]) ? sanitize_text_field($_POST["hoko_product_id"]) : "";
		$data['code'] = isset($_POST["code"]) ? sanitize_text_field($_POST["code"]) : "";
		$data['title'] = isset($_POST["title"]) ? sanitize_text_field($_POST["title"]) : "";
		$data['description'] = isset($_POST["description"]) ? sanitize_text_field($_POST["description"]) : "";
		$data['reference'] = isset($_POST["reference"]) ? sanitize_text_field($_POST["reference"]) : "";
		$data['kind'] = isset($_POST["kind"]) ? sanitize_text_field($_POST["kind"]) : "";
		$data['created_at'] = isset($_POST["created_at"]) ? sanitize_text_field($_POST["created_at"]) : "";
		$data['updated_at'] = isset($_POST["updated_at"]) ? sanitize_text_field($_POST["updated_at"]) : "";
		$data['deleted_at'] = isset($_POST["deleted_at"]) ? sanitize_text_field($_POST["deleted_at"]) : "";

		$data['min_sale_price'] = isset($_POST["min_sale_price"]) ? sanitize_text_field($_POST["min_sale_price"]) : "";
		$data['price_by_unit'] = isset($_POST["price_by_unit"]) ? sanitize_text_field($_POST["price_by_unit"]) : "";
		$data['price_by_amount'] = isset($_POST["price_by_amount"]) ? sanitize_text_field($_POST["price_by_amount"]) : "";
		$data['price_dropshipping'] = isset($_POST["price_dropshipping"]) ? sanitize_text_field($_POST["price_dropshipping"]) : "";
		$data['minimal_price'] = isset($_POST["minimal_price"]) ? sanitize_text_field($_POST["minimal_price"]) : "";
		$data['tax'] = isset($_POST["tax"]) ? sanitize_text_field($_POST["tax"]) : "";
		$data['cost'] = isset($_POST["cost"]) ? sanitize_text_field($_POST["cost"]) : "";

		$data['periodicity'] = isset($_POST["periodicity"]) ? sanitize_text_field($_POST["periodicity"]) : "";
		$data['allowCombo'] = isset($_POST["allowCombo"]) ? sanitize_text_field($_POST["allowCombo"]) : "";
		$data['store_id'] = isset($_POST["store_id"]) ? sanitize_text_field($_POST["store_id"]) : "";
		$data['video'] = isset($_POST["video"]) ? sanitize_text_field($_POST["video"]) : "";
		$data['warranty'] = isset($_POST["warranty"]) ? sanitize_text_field($_POST["warranty"]) : "";
		$data['url_qr'] = isset($_POST["url_qr"]) ? sanitize_text_field($_POST["url_qr"]) : "";
		$data['url_code_bar'] = isset($_POST["url_code_bar"]) ? sanitize_text_field($_POST["url_code_bar"]) : "";

		$data['images'] = isset($_POST["images"]) ? $_POST["images"] : [];
		
		$p = new WC_HOKO_Necoyoad_WC_Api();

		$product_id = $p->addProduct( $data );

		$this->responseSuccess([
  			"id"=>$product_id
  		]);
	}

	/**
	 * Define constant if not already set.
	 *
	 * @since 1.0.0
	 *
	 * @param  string      $name contant name.
	 * @param  string|bool $value constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @since 1.0.0
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Add plugin provided screen to woocommerce admin area
	 *
	 * @since 1.0.0
	 *
	 * @param array $screen_ids all screen ids.
	 *
	 * @return array $screen_ids
	 */
	public function add_woocommerce_screen_ids( $screen_ids ) {
		$screen_ids[] = 'woocommerce_hoko_admin_screen';

		return $screen_ids;
	}

	/**
	 * Set additional links on a plugin admin dashbord page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links all links.
	 *
	 * @return array
	 */
	public function set_plugin_action_links( $links ) {
		return array_merge(
			array(
				'<a href="' .
				admin_url( 'admin.php?page=hoko_menu&hoko_action=settings' ) .
				'">' .
				esc_html__( 'Settings', 'wc-hoko' ) .
				'</a>',
			),
			$links
		);
	}
}

WC_HOKO_Plugin::instance();


add_filter( 'manage_edit-product_columns', 'product_list_hoko_column', 20 );
add_filter( 'manage_edit-shop_order_columns', 'product_list_hoko_column', 20 );
function product_list_hoko_column( $columns_array ) {
	// I want to display Brand column just after the product name column
	return array_slice( $columns_array, 0, 3, true )
	+ array( 'hoko' => 'HOKO' )
	+ array_slice( $columns_array, 3, NULL, true );
}

add_action( 'manage_posts_custom_column', 'populate_hoko_column' );
function populate_hoko_column( $column_name ) {
	if( $column_name  == 'hoko' ) {
		$p = new WC_HOKO_Necoyoad_WC_Api( get_the_ID() );
		echo $p->checkExists() ? "Yes" : "No";
	}

}


add_action( 'post_submitbox_misc_actions', 'product_edit_actions_hoko', 20 );
function product_edit_actions_hoko() {
	$checksum = get_post_meta( get_the_ID(), 'hoko_checksum', true );
	
	$options = get_option( 'plugin_hoko_options' );

	$hoko_api_username = !empty( $options['hoko_api_username'] ) ? $options['hoko_api_username'] : '';
	$hoko_api_password = !empty( $options['hoko_api_password'] ) ? $options['hoko_api_password'] : '';

	$hoko_api = new WC_HOKO_Necoyoad_Hoko_Api($hoko_api_username, $hoko_api_password);

	echo $hoko_api->generateChecksum( get_post_meta( get_the_ID(), 'hoko_product_id', true ) ) != $checksum ? 
    	'<button class="hoko-button hoko-red button button-primary button-large" type="button" id="hokoSync">Actualizar desde Hoko</button>':
    	"";
}

/*
 * Tab
 */
add_filter('woocommerce_product_data_tabs', 'hoko_product_settings_tabs' );
function hoko_product_settings_tabs( $tabs ){
	$tabs['hoko'] = array(
		'label'    => 'HOKO',
		'target'   => 'hoko_product_data',
		'priority' => 21,
	);
	return $tabs;
}
 
/*
 * Tab content
 */
add_action( 'woocommerce_product_data_panels', 'hoko_product_panels' );
function hoko_product_panels(){
 
	echo '<div id="hoko_product_data" class="panel woocommerce_options_panel hidden">';
	$metas = [
		'checksum'=>'Hoko Product Checksum',
		'product_id'=>'Hoko Product ID',
		'code'=>'Code',
		'reference'=>'Referencia',
		'kind'=>'Tipo',
		'created_at'=>'Fecha Creado',
		'updated_at'=>'Fecha Actualizado',
		'deleted_at'=>'Fecha Eliminado',
		'min_sale_price'=>'Precio Min. de Venta',
		'price_by_unit'=>'Precio Unitario',
		'price_by_amount'=>'Precio Por Cantidad',
		'minimal_price'=>'Precio Min.',
		'tax'=>'Impuesto',
		'cost'=>'Costo',
		'periodicity'=>'Frecuencia',
		'allowCombo'=>'Combo Habilitado',
		'store_id'=>'Tienda ID',
		'video'=>'Video Url',
		'warranty'=>'Garantía',
		'url_qr'=>'QR Url',
		'url_code_bar'=>'Code Url',
	];
 		
 	echo '<table class="product_edit_table_hoko">';

 	foreach ($metas as $k=>$v) {
 		$value = get_post_meta( get_the_ID(), 'hoko_'. $k, true );
 		$value = in_array($k, ['created_at','updated_at','deleted_at']) ? date("d-m-Y", strtotime($value)) : $value;
 		echo '<tr id="hoko_'. $v .'">';
 		echo '<td style="width:30%">'. $v .'</td>';
 		echo '<td>'. $value .'</td>';
 		echo '</tr>';
	}

 	echo '</table>'; 
	echo '</div>';
 
}