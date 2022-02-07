<?php
/**
 * File contain Wrapper for a WC APIs Client Library for PHP
 *
 * @since 1.0.0
 *
 * @package HOKO
 * @subpackage HOKO/includes
 */

defined( 'ABSPATH' ) || exit;

define("APP_ROOT", realpath(dirname(__FILE__) ."/../../../../"));

class WC_HOKO_Necoyoad_WC_Api {

	private $p; 

	public function __construct(int $id = null) {
		$this->p = $id ? new WC_Product($id) : new WC_Product();
	}

	public function addProduct(Array $data) {
		/**
		 * validate data 
		 * check if is product simple or with variations 
		 * create product 
		 * 
		 * process images 
		 * 	- validate 
		 *  - download 
		 *  - upload
		 *  - save
		 *  - bind to product 
		 * 
		 * process stocks and prices 
		 *  - get price options to add or substract from hoko prices 
		 *  - calculate prices 
		 *  - apply filters 
		 *  - update product 
		 * 
		 * save
		 * return response 
		 * */

		$this->p->set_name( $data['title'] );
		$this->p->set_status("publish"); 
		$this->p->set_catalog_visibility("visible"); 
		$this->p->set_description( $data['description'] );
		$this->p->set_sku( $data['hoko_product_id'] ); 
		$this->p->set_price($data['price_by_unit']); 
		$this->p->set_regular_price($data['price_by_unit']); 
		$this->p->set_manage_stock(true); 
		$this->p->set_stock_quantity(1);
		$this->p->set_stock_status('instock');

		$product_id = $this->p->save();

		$this->addImages( $data['images'] ); 
		
		unset($data['title']);
		unset($data['description']);
		unset($data['price_by_unit']);
		unset($data['images']);

		foreach ($data as $k=>$v) {
			if (!$v) continue;
			
			$value = is_array($v) ? wp_slash( serialize( $v )) : $v;

			$k == 'hoko_product_id' ? 
				add_post_meta( $product_id, $k, $value, true ) :
				add_post_meta( $product_id, "hoko_". $k, $value, true );
		}


		//TODO: use singleton and global hoko api wrapper 
		$options = get_option( 'plugin_hoko_options' );

		$hoko_api_username = !empty( $options['hoko_api_username'] ) ? $options['hoko_api_username'] : '';
		$hoko_api_password = !empty( $options['hoko_api_password'] ) ? $options['hoko_api_password'] : '';

		$hoko_api = new WC_HOKO_Necoyoad_Hoko_Api($hoko_api_username, $hoko_api_password);

		add_post_meta( $product_id, "hoko_checksum", $hoko_api->generateChecksum( $data['hoko_product_id'] ), true );
		
		return $product_id;
	}
 
	public function checkExists() {
	   return (get_post_meta( $this->p->get_id(), 'hoko_checksum', true ));
	}

	public function addImages(Array $images) {
	    $images_array = [];
	    
	    foreach ($images as $k => $image_url) {
	        $file_uploaded = wc_rest_upload_image_from_url(esc_url_raw($image_url));
			
	        if (is_wp_error($file_uploaded)) {
	          	if (!apply_filters('woocommerce_rest_suppress_image_upload_error', false, $file_uploaded, $this->p->get_id(), $images)) {
	            	throw new WC_REST_Exception('woocommerce_product_image_upload_error', $file_uploaded->get_error_message(), 400);
	          	} else {
	            	continue;
	          	}
	        }
        	$attachment_id = wc_rest_set_uploaded_image_as_attachment($file_uploaded, $this->p->get_id());

	      	if ($k == 0) {
	        	$this->p->set_image_id($attachment_id);
	      	} else {
	        	array_push($images_array, $attachment_id);
	      	}
	    }

	    if (!empty($images_array)) {
	      	$this->p->set_gallery_image_ids($images_array);
	    }

	    $this->p->save();
	}

	function wpsuploadMedia(Array $images) {
	    require_once(APP_ROOT . 'wp-admin/includes/image.php');
	    require_once(APP_ROOT . 'wp-admin/includes/file.php');
	    require_once(APP_ROOT . 'wp-admin/includes/media.php');

	    $media = media_sideload_image($images, 0);

	    $attachments = get_posts(array(
	        'post_type' => 'attachment',
	        'post_status' => null,
	        'post_parent' => 0,
	        'orderby' => 'post_date',
	        'order' => 'DESC'
	    ));

	    return $attachments[0]->ID;
	}

/**
 * Save a new product attribute from his name (slug).
 *
 * @since 3.0.0
 * @param string $name  | The product attribute name (slug).
 * @param string $label | The product attribute label (name).
 */
function save_product_attribute_from_name( $name, $label='', $set=true ){
    if( ! function_exists ('get_attribute_id_from_name') ) return;

    global $wpdb;

    $label = $label == '' ? ucfirst($name) : $label;
    $attribute_id = get_attribute_id_from_name( $name );

    if( empty($attribute_id) ){
        $attribute_id = NULL;
    } else {
        $set = false;
    }
    $args = array(
        'attribute_id'      => $attribute_id,
        'attribute_name'    => $name,
        'attribute_label'   => $label,
        'attribute_type'    => 'select',
        'attribute_orderby' => 'menu_order',
        'attribute_public'  => 0,
    );


    if( empty($attribute_id) ) {
        $wpdb->insert(  "{$wpdb->prefix}woocommerce_attribute_taxonomies", $args );
        set_transient( 'wc_attribute_taxonomies', false );
    }

    if( $set ){
        $attributes = wc_get_attribute_taxonomies();
        $args['attribute_id'] = get_attribute_id_from_name( $name );
        $attributes[] = (object) $args;
        //print_r($attributes);
        set_transient( 'wc_attribute_taxonomies', $attributes );
    } else {
        return;
    }
}

/**
 * Get the product attribute ID from the name.
 *
 * @since 3.0.0
 * @param string $name | The name (slug).
 */
function get_attribute_id_from_name( $name ){
    global $wpdb;
    $attribute_id = $wpdb->get_col("SELECT attribute_id
    FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
    WHERE attribute_name LIKE '$name'");
    return reset($attribute_id);
}
/**
 * Create a product variation for a defined variable product ID.
 *
 * @since 3.0.0
 * @param int   $product_id | Post ID of the product parent variable product.
 * @param array $variation_data | The data to insert in the product.
 */

function create_product_variation( $product_id, $variation_data ){
    // Get the Variable product object (parent)
    $product = wc_get_product($product_id);

    $variation_post = array(
        'post_title'  => $product->get_name(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );

    // Creating the product variation
    $variation_id = wp_insert_post( $variation_post );

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );

    // Iterating through the variations attributes
    foreach ($variation_data['attributes'] as $attribute => $term_name )
    {
        $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

        // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
        if( ! taxonomy_exists( $taxonomy ) ){
            register_taxonomy(
                $taxonomy,
               'product_variation',
                array(
                    'hierarchical' => false,
                    'label' => ucfirst( $attribute ),
                    'query_var' => true,
                    'rewrite' => array( 'slug' => sanitize_title($attribute) ), // The base slug
                ),
            );
        }

        // Check if the Term name exist and if not we create it.
        if( ! term_exists( $term_name, $taxonomy ) )
            wp_insert_term( $term_name, $taxonomy ); // Create the term

        $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

        // Get the post Terms names from the parent variable product.
        $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

        // Check if the post term exist and if not we set it in the parent variable product.
        if( ! in_array( $term_name, $post_term_names ) )
            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

        // Set/save the attribute data in the product variation
        update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
    }

    ## Set/save all other data

    // SKU
    if( ! empty( $variation_data['sku'] ) )
        $variation->set_sku( $variation_data['sku'] );

    // Prices
    if( empty( $variation_data['sale_price'] ) ){
        $variation->set_price( $variation_data['regular_price'] );
    } else {
        $variation->set_price( $variation_data['sale_price'] );
        $variation->set_sale_price( $variation_data['sale_price'] );
    }
    $variation->set_regular_price( $variation_data['regular_price'] );

    // Stock
    if( ! empty($variation_data['stock_qty']) ){
        $variation->set_stock_quantity( $variation_data['stock_qty'] );
        $variation->set_manage_stock(true);
        $variation->set_stock_status('');
    } else {
        $variation->set_manage_stock(false);
    }
    
    $variation->set_weight(''); // weight (reseting)

    $variation->save(); // Save the data
}

/*
create_product_variation( array(
    'author'        => '', // optional
    'title'         => 'Woo special one',
    'content'       => '<p>This is the product content <br>A very nice product, soft and clear…<p>',
    'excerpt'       => 'The product short description…',
    'regular_price' => '16', // product regular price
    'sale_price'    => '', // product sale price (optional)
    'stock'         => '10', // Set a minimal stock quantity
    'image_id'      => '', // optional
    'gallery_ids'   => array(), // optional
    'sku'           => '', // optional
    'tax_class'     => '', // optional
    'weight'        => '', // optional
    // For NEW attributes/values use NAMES (not slugs)
    'attributes'    => array(
        'Attribute 1'   =>  array( 'Value 1', 'Value 2' ),
        'Attribute 2'   =>  array( 'Value 1', 'Value 2', 'Value 3' ),
    ),
) );
*/
/**
 * Create a new variable product (with new attributes if they are).
 * (Needed functions:
 *
 * @since 3.0.0
 * @param array $data | The data to insert in the product.
 */

function create_product_variable( $data ){
    if( ! function_exists ('save_product_attribute_from_name') ) return;

    $postname = sanitize_title( $data['title'] );
    $author = empty( $data['author'] ) ? '1' : $data['author'];

    $post_data = array(
        'post_author'   => $author,
        'post_name'     => $postname,
        'post_title'    => $data['title'],
        'post_content'  => $data['content'],
        'post_excerpt'  => $data['excerpt'],
        'post_status'   => 'publish',
        'ping_status'   => 'closed',
        'post_type'     => 'product',
        'guid'          => home_url( '/product/'.$postname.'/' ),
    );

    // Creating the product (post data)
    $product_id = wp_insert_post( $post_data );

    // Get an instance of the WC_Product_Variable object and save it
    $product = new WC_Product_Variable( $product_id );
    $product->save();

    ## ---------------------- Other optional data  ---------------------- ##
    ##     (see WC_Product and WC_Product_Variable setters methods)

    // THE PRICES (No prices yet as we need to create product variations)

    // IMAGES GALLERY
    if( ! empty( $data['gallery_ids'] ) && count( $data['gallery_ids'] ) > 0 )
        $product->set_gallery_image_ids( $data['gallery_ids'] );

    // SKU
    if( ! empty( $data['sku'] ) )
        $product->set_sku( $data['sku'] );

    // STOCK (stock will be managed in variations)
    $product->set_stock_quantity( $data['stock'] ); // Set a minimal stock quantity
    $product->set_manage_stock(true);
    $product->set_stock_status('');

    // Tax class
    if( empty( $data['tax_class'] ) )
        $product->set_tax_class( $data['tax_class'] );

    // WEIGHT
    if( ! empty($data['weight']) )
        $product->set_weight(''); // weight (reseting)
    else
        $product->set_weight($data['weight']);

    $product->validate_props(); // Check validation

    ## ---------------------- VARIATION ATTRIBUTES ---------------------- ##

    $product_attributes = array();

    foreach( $data['attributes'] as $key => $terms ){
        $taxonomy = wc_attribute_taxonomy_name($key); // The taxonomy slug
        $attr_label = ucfirst($key); // attribute label name
        $attr_name = ( wc_sanitize_taxonomy_name($key)); // attribute slug

        // NEW Attributes: Register and save them
        if( ! taxonomy_exists( $taxonomy ) )
            save_product_attribute_from_name( $attr_name, $attr_label );

        $product_attributes[$taxonomy] = array (
            'name'         => $taxonomy,
            'value'        => '',
            'position'     => '',
            'is_visible'   => 0,
            'is_variation' => 1,
            'is_taxonomy'  => 1
        );

        foreach( $terms as $value ){
            $term_name = ucfirst($value);
            $term_slug = sanitize_title($value);

            // Check if the Term name exist and if not we create it.
            if( ! term_exists( $value, $taxonomy ) )
                wp_insert_term( $term_name, $taxonomy, array('slug' => $term_slug ) ); // Create the term

            // Set attribute values
            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );
        }
    }
    update_post_meta( $product_id, '_product_attributes', $product_attributes );
    $product->save(); // Save the data
}

}