<?php
/**
* Core class wrapping all plugin functionality.
*
* @class    CED_CAF_Core_Class
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CED_CAF_Core_Class {
	public function __construct() {
		$this->ced_caf_include_plugin_files_to_be_used();

		// Add cutom wrapper for showing the messages
		add_action( 'woocommerce_before_main_content', array( $this, 'ced_caf_result_count' ), 35.9 );

		//filter to add things on plugin listing page
		add_filter( 'plugin_row_meta', array( $this, 'ced_caf_add_plugin_row_meta' ), 10, 4 );

		//hook to register text-domain
		add_action( 'plugins_loaded', array ( $this, 'ced_caf_ajax_shop_load_textdomain') );
		
		//hooks to be used on shop page
		add_action('woocommerce_before_main_content',array( $this, 'ced_caf_ajax_shop_page_content_wrapper_start_func'),9.999);
		

		//hook to filter product-ids
		add_action( 'woocommerce_product_query', array( $this, 'ced_caf_ajax_shop_alter_product_query' ), 10, 2 );

		//adding widgets
		add_action( 'widgets_init', array( $this, 'ced_caf_ajax_widgets_registration_function' ) );

		//frontEnd enqueue script
		add_action( 'wp_enqueue_scripts', array( $this, 'ced_caf_custom_script_enqueue_function' ) );

		//admin side script
		add_action( 'admin_enqueue_scripts', array( $this, 'ced_caf_admin_script_enqueue_function' ) );

		//custom hooks for ajax functions to run :: adds dynamic HTML to widgets on backEnd
		add_action( 'wp_ajax_nopriv_ccas_dynamicHTML', array( $this, 'ced_caf_dynamicHTML' ) );
		add_action( 'wp_ajax_ccas_dynamicHTML', array( $this, 'ced_caf_dynamicHTML' ) );

		//custom hooks to fetch products using Ajax from search widget
		add_action( 'wp_ajax_nopriv_searchProductAjaxify', array( $this, 'ced_caf_searchProductAjaxify' ) );
		add_action( 'wp_ajax_searchProductAjaxify', array( $this, 'ced_caf_searchProductAjaxify' ) );
		
		//custom hooks to save active filters
		add_action( 'wp_ajax_nopriv_setCookieForSavingFilters', array( $this, 'setCookieForSavingFilters' ) );
		add_action( 'wp_ajax_setCookieForSavingFilters', array( $this, 'setCookieForSavingFilters' ) );

		do_action( CED_CAF_PREFIX.'add_more_hooks_and_filters' );
	}

		/**
		 * This function includes neccessary files required.
		 * @name ced_caf_include_plugin_files_to_be_used()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link: http://cedcommerce.com/
		 */
	function ced_caf_include_plugin_files_to_be_used() {
		//including helper classes
		require_once CED_CAF_PLUGIN_DIR_PATH.'helper/class-ced-caf-attribute-filter-helper.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'helper/class-ced-caf-price-filter-helper.php';

		//including widget classes
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/abstract-ced-caf-widget.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-pro-cat.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-widget-pro-tag-cloud.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-widget-layered-nav.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-widget-price-filter.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-widget-active-filters.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-widget-product-search.php';
		require_once CED_CAF_PLUGIN_DIR_PATH.'widgets/class-ced-caf-widget-save-active-filters.php';

		do_action( CED_CAF_PREFIX . 'include_more_files' );
	}

	function ced_caf_result_count() {
		if ( !is_shop() ) {
			return false;
		}

		$wrapper = '<div class="ccas_items_info_wrapper_parent ccas_hidden_item">';
			$wrapper .= '<div class="ccas_items_info_wrapper woocommerce-info">';
				$wrapper .= __( 'No products were found matching your selection.', CED_CAF_TXTDOMAIN );
			$wrapper .= '</div>';
		$wrapper .= '</div>';
		echo $wrapper;
	}

	/**
	 * This function to extra links to plugin listing page'.
	 * @name ced_caf_add_plugin_row_meta()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link: http://www.cedcommerce.com/
	 */
	function ced_caf_add_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if( CED_CAF_PLUGIN_BASE_FILE == $plugin_file ) {
			$plugin_meta[] = '<a href="http://demo.cedcommerce.com/woocommerce/ajaxify-filter/wp-admin" target="_blank">' . __( 'Demo : Backend', 'ajaxify-filters' ) . '</a>';
			$plugin_meta[] = '<a href="http://demo.cedcommerce.com/woocommerce/ajaxify-filter/" target="_blank">' . __( 'Demo : Frontend', 'ajaxify-filters' ) . '</a>';
			$plugin_meta[] = '<a href="http://demo.cedcommerce.com/woocommerce/ajaxify-filter/doc/index.html" target="_blank">' . __( 'Plugin Documentation', 'ajaxify-filters' ) . '</a>';
			$plugin_meta[] = '<a href="http://cedcommerce.com/woocommerce-extensions/" target="_blank">' . __( 'More Plugins By CedCommerce', 'ajaxify-filters' ) . '</a>';
		}
		return $plugin_meta;
	}
	
	/**
	 * This function to load text-domain of the plugin'.
	 * @name ced_caf_ajax_shop_load_textdomain()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link: http://cedcommerce.com/
	 */
	function ced_caf_ajax_shop_load_textdomain() {
		$domain = "ajaxify-filters";
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, plugins_url().'/'.CED_CAF_PLUGIN_NAME.'/language/'.$domain.'-' . $locale . '.mo' );
		load_plugin_textdomain( 'ajaxify-filters', false, plugin_basename( dirname( __FILE__ ) ) . '/language' );
	}
	
	/*
	 * function for saving cookie to save active filters
	 */
	function setCookieForSavingFilters() {
		$changesMade = "true_new";
		$cookie_name = 'ced_caf_saved_filters';
		$cookieArray = array();
		if(isset($_COOKIE[$cookie_name])) {
			$cookieArray = $_COOKIE[$cookie_name];
			$cookieArray = stripslashes($cookieArray);
			$cookieArray = json_decode($cookieArray, true);
		}
		
		if(!is_array($cookieArray)) {
			$temp[] 		= $cookieArray;
			$cookieArray 	= $temp;
		}
		
		$cookieArray[] 	= $_POST['currentURL'];
		$preCount 		= count($cookieArray);
		$cookieArray 	= array_unique($cookieArray);
		$postCount 		= count($cookieArray);
		
		if( $preCount == $postCount + 1 ) {
			$changesMade = "false";
		}	
		
		if( ! empty( $cookieArray ) && is_array( $cookieArray ) && count( $cookieArray ) > 5 ) {
			array_shift($cookieArray);
			$changesMade = "true_replace";
		}
		
		$cookieArray = json_encode($cookieArray);
		setcookie($cookie_name,$cookieArray, time() + (86400 * 30 * 5), "/"); // 86400 = 1 day
		
		$responseArray = array(
			'changesMade' => $changesMade,
			'cookieArray' => $cookieArray
		);
		echo json_encode( $responseArray );
		wp_die();
	}
	
	/*
	*	function to wrap shop page used to replace content::start
	*/
	function ced_caf_ajax_shop_page_content_wrapper_start_func() {
		echo '<div class="ccas_ajax_shop_loading_div" style="display:none;">';
			echo '<img src="'.CED_CAF_PLUGIN_DIR_URL.'images/hourglass.gif">';
			echo  '<br>';
			echo '<div class="blink_me">Fetching Products ... </div>';
		echo '</div>';
	}
	
	/**
	* This function registers widgets.
	* @name ced_caf_ajax_widgets_registration_function()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link: http://cedcommerce.com/
	*/
	function ced_caf_ajax_widgets_registration_function() {
		$widgetsToBeRegistered = array(
			'CED_CAF_Widget_Pro_Categories',
			'CED_CAF_Widget_Pro_Tag_Cloud',
			'CED_CAF_Widget_Layered_Nav',
			'CED_CAF_Widget_Price_Filter',
			'CED_CAF_Widget_AJAX_Active_Filters',
			'CED_CAF_Widget_Product_Search',
			'CED_CAF_Widget_Save_Active_Filters'	
		);

		$widgetsToBeRegistered = apply_filters( CED_CAF_PREFIX . 'alter_widget_array', $widgetsToBeRegistered );
		
		foreach ( $widgetsToBeRegistered as $widget ) {
			register_widget( $widget );
		}
		do_action( CED_CAF_PREFIX . 'add_widgets_hook' );
	}

	/*
	*this function changes the product query to fecth data accordingly
	*/
	function ced_caf_ajax_shop_alter_product_query( $query, $ref ) {
		if( count( $_GET ) == 1 && isset( $_GET['orderby'] ) ) {
			return;
		}

		global $ccas_filtered_pro_ids;
		if(! empty( $_GET ) ) {
			$post_ids = CED_CAF_Attribute_Filter_Helper::ced_caf_ajax_layered_nav_init();
			
			if( isset( $post_ids ) && is_array( $post_ids ) && !empty( $post_ids )) {
				$query->set( 'post__in', array_merge( array( 0 ), $post_ids ) );
			}
			
			$price_filter_pro_ids = CED_CAF_Price_Filter_Helper::ced_caf_price_filter();
			
			if( ! empty( $price_filter_pro_ids ) ) {
				$query->set( 'post__in', array_merge( array(0), $price_filter_pro_ids ) );
			}	
		}	
		
		/*
		 * code to filter product according to category :: start
		 */
		if( isset( $_GET['filter_cat'] ) ) {
			global $ccas_filtered_pro_ids, $_chosen_attributes;
			
			$temp = get_posts(
				array(
					'product_cat'		=>	$_GET['filter_cat'],
					'post_type'			=>	'product',
					'post_status'		=>	'publish',
					'posts_per_page'	=>	'-1',
					'fields'			=>	'ids'
				)
			);
			
			if(!empty($ccas_filtered_pro_ids)) {
				$filteredResult = array_intersect( $ccas_filtered_pro_ids, $temp );
			} else {
				$filteredResult = $temp ;
			}	
			
			$query->set( 'post__in', $filteredResult );
			
			$ccas_filtered_pro_ids = $filteredResult;
			
			$_chosen_attributes['product_cat']['terms'][0] = sanitize_text_field(($_GET['filter_cat']));
			$_chosen_attributes['product_cat']['query_type'] = 'and';
		}
		/*
		 * code to filter product according to category :: end
		 */

		/*
		 * code to filter product according to filter_tag :: start
		 */
		if( isset( $_GET['filter_tag'] ) ) {
			$filterTagArr = explode( ",", sanitize_text_field( $_GET['filter_tag'] ) );
			
			$filteredProIds = array();
			foreach( $filterTagArr as $currentTag ) {
				$args = array(
					'post_type' 	=> 'product',
					'numberposts' 	=> -1,
					'post_status' 	=> 'publish',
					'fields' 		=> 'ids',
					'product_tag' 	 => $currentTag
				);
				$post_ids = get_posts( $args );
				
				if( empty( $filteredProIds ) ) {
					$filteredProIds = $post_ids;
				} else  {	
					$filteredProIds = array_intersect( $filteredProIds, $post_ids );
				}
			}
			
			global $ccas_filtered_pro_ids;
			if( ! empty( $ccas_filtered_pro_ids ) ) {
				$filteredResult = array_intersect( $ccas_filtered_pro_ids, $filteredProIds );
			} else {
				$filteredResult = $filteredProIds ;
			}
			
			$query->set( 'post__in', $filteredResult );
			
			$ccas_filtered_pro_ids = $filteredResult;
		}
		/*
		 * code to filter product according to filter_tag :: end
		 */

	}

	/**
	 * This function enqueue scripts on client side.
	 * @name ced_caf_custom_script_enqueue_function()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link: http://cedcommerce.com/
	 */
	function ced_caf_custom_script_enqueue_function() {
		if( ! is_shop() ) {
			return;
		}

		wp_enqueue_style ( 'pro_cat', CED_CAF_PLUGIN_DIR_URL . '/css/pro_cat.min.css', '', CED_CAF_VERSION, 'all' );
		wp_enqueue_style ( 'jquery_ui_css', CED_CAF_PLUGIN_DIR_URL .'/css/price-slider/jquery_ui.css', '', CED_CAF_VERSION, 'all' );

		wp_enqueue_script( 'ccas-ajaxify-functions', CED_CAF_PLUGIN_DIR_URL."/js/ajaxify-functions.js", array( 'jquery' ), CED_CAF_VERSION, true );
		wp_enqueue_script( 'pro_cat_script', CED_CAF_PLUGIN_DIR_URL . '/js/pro-cat-script.min.js', array( 'jquery', 'ccas-ajaxify-functions' ), CED_CAF_VERSION, true );
		wp_enqueue_script( 'jquery_ui_js', CED_CAF_PLUGIN_DIR_URL .'/js/price-slider/jquery_ui.min.js', array( 'jquery', 'ccas-ajaxify-functions' ), CED_CAF_VERSION, true );
		wp_enqueue_script( 'searchProduct', CED_CAF_PLUGIN_DIR_URL .'/js/searchProduct.min.js', array( 'jquery', 'ccas-ajaxify-functions' ), CED_CAF_VERSION, true );
		wp_localize_script( 
			'searchProduct', 
			'searchProduct_ajax', 
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);

		wp_enqueue_script( 'priceWidgetScript', CED_CAF_PLUGIN_DIR_URL . '/js/priceWidgetScript.min.js', array( 'jquery', 'ccas-ajaxify-functions' ), CED_CAF_VERSION, true );
		wp_localize_script( 
			'pro_cat_script', 
			'pro_cat_script_ajax', 
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	/**
	 * This function enqueue script on admin side.
	 * @name ced_caf_admin_script_enqueue_function()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link: http://cedcommerce.com/
	 */
	function ced_caf_admin_script_enqueue_function() {
		if(strpos( $_SERVER[ 'REQUEST_URI' ], "wp-admin/widgets.php" ) || strpos( $_SERVER[ 'REQUEST_URI' ], "wp-admin/customize.php" ) ) {


			wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style ( 'extra_added', plugins_url().'/'.CED_CAF_PLUGIN_NAME.'/colorpicker/extra_added.min.css');

        wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'widget_script', plugins_url() .'/'. CED_CAF_PLUGIN_NAME . '/js/widget_script.min.js', array( 'jquery' ), '', true );
		wp_localize_script( 
			'widget_script', 
			'widget_script_ajax', 
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);

		wp_enqueue_script('colorPickerDynamic', plugins_url().'/'.CED_CAF_PLUGIN_NAME.'/js/colorPickerDynamic.min.js',array( 'jquery', 'wp-color-picker' ),'',true);
			
		}
		

		
	}

	/*
	*This function makes dynamic HTML for widgets
	*/
	function ced_caf_dynamicHTML()
	{
		if(isset($_POST ['instanceRef']))
		{
			$instanceRef = $_POST ['instanceRef'];
		}	
		$thisRef = $_POST ['thisRef'];
		$selectedOpt = $_POST ['selectedOpt'];
		$selectedAttribute = $_POST ['selectedAttribute'];
		$terms = get_terms ( 'pa_' . $selectedAttribute );
		if ($selectedOpt == "label") {
			require_once 'labelLook.php';
		} else if ($selectedOpt == "picker") {
			require_once 'pickerLook.php';
		}
		
		wp_die ();
	}

	/*
	*This function search products using ajax on typing the product name
	*/
	function ced_caf_searchProductAjaxify( $x = '', $post_types = array( 'product' ) )
	{
		global $wpdb;
		
		ob_start();
		
		$term = (string) wc_clean( stripslashes( $_POST['term'] ) );
		
		if ( empty( $term ) ) {
			die();
		}
		
		$like_term = '%' . $wpdb->esc_like( $term ) . '%';
		
		if ( is_numeric( $term ) ) {
			$query = $wpdb->prepare( "
					SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
					WHERE posts.post_status = 'publish'
					AND (
					posts.post_parent = %s
					OR posts.ID = %s
					OR posts.post_title LIKE %s
					OR (
					postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
					)
					)
					", $term, $term, $term, $like_term );
		} else {
			$query = $wpdb->prepare( "
					SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
					WHERE posts.post_status = 'publish'
					AND (
					posts.post_title LIKE %s
					or posts.post_content LIKE %s
					OR (
					postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
					)
					)
					", $like_term, $like_term, $like_term );
		}
		
		$query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "')";
		
		if ( ! empty( $_GET['exclude'] ) ) {
			$query .= " AND posts.ID NOT IN (" . implode( ',', array_map( 'intval', explode( ',', $_GET['exclude'] ) ) ) . ")";
		}
		
		if ( ! empty( $_GET['include'] ) ) {
			$query .= " AND posts.ID IN (" . implode( ',', array_map( 'intval', explode( ',', $_GET['include'] ) ) ) . ")";
		}
		
		if ( ! empty( $_GET['limit'] ) ) {
			$query .= " LIMIT " . intval( $_GET['limit'] );
		}
		
		$posts          = array_unique( $wpdb->get_col( $query ) );
		$found_products = array();
		
		global $product;
		
		$proHTML = '';
		if ( ! empty( $posts ) ) {
			
			$proHTML .= '<ul class="products ccas_searched_product_ul">';
			
			foreach ( $posts as $post ) {
				$product = wc_get_product( $post );
		
				if ( ! $product || ( $product->is_type( 'variation' ) && empty( $product->parent ) ) ) {
					continue;
				}
		
				$productIds[] = $post;
				$found_products[ $post ] = rawurldecode( $product->get_formatted_name() );
				
				/*
				 * code to generate HTML for product listing
				 */
				
				$proImg = wc_placeholder_img_src();
				if(has_post_thumbnail($post))
				{
					$proImg = wp_get_attachment_url( get_post_thumbnail_id($post) );
				}
				
				$proHTML .= '<li class="ccas_searched_pro_list">';
				$proHTML .= '<a href="' . get_the_permalink($post) . '">';
				$proHTML .= '<img src="' . $proImg . '" alt="' . esc_attr__( 'Product Available Image', 'woocommerce' ) . '" width="' . esc_attr( 80 ) . '" class="woocommerce-placeholder wp-post-image" height="' . esc_attr( 80 ) . '" />';
				$proHTML .= '<span class="ccas_disign_adjust"><div>' . get_the_title($post) . '</div>';
				$proHTML .= '<div class="price">'.$product->get_price_html().'</div></span>';
				$proHTML .= '</a>';
				$proHTML .= '</li>';
			
			}
			
			$proHTML .= '</ul>';
		}
		else
		{
			$proHTML .= '<ul class="woocommerce-error ccas_searched_product_ul"><li class="ccas_searched_pro_list"><strong>'. __( 'No Matches Found', CED_CAF_TXTDOMAIN ) .'</strong><br/></li></ul>';
		}	
		
		echo $proHTML;
		wp_die();
	}
}
new CED_CAF_Core_Class();