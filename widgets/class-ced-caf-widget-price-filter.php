<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* widget to fecth product by price
*
* @class    CED_CAF_Widget_Price_Filter
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
class CED_CAF_Widget_Price_Filter extends CED_CAF_Widget 
{

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		$this->widget_cssclass    = 'ccas_ajax_price_filter_widget';
		$this->widget_description = __( 'Shows a price filter slider in a widget which lets you search products based on price.', 'ajaxify-filters' );
		$this->widget_id          = 'ccas_ajax_price_filter';
		$this->widget_name        = __( 'AJAX Price Filter', 'ajaxify-filters' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Filter By Price', 'ajaxify-filters' ),
				'label' => __( 'Title', 'ajaxify-filters' )
			)
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 */
	public function widget( $args, $instance )
	{
		global $_chosen_attributes, $wpdb, $wp;

		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) 
		{
			return;
		}

		if(isset(WC()->query->unfiltered_product_ids))//change made
		{
			if ( sizeof( WC()->query->unfiltered_product_ids ) == 0 ) 
			{
				return; // None shown - return
			}
		}	
		

		$min_price = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : '';
		$max_price = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : '';

	
		$fields = '';
		if ( $_chosen_attributes ) 
		{
			foreach ( $_chosen_attributes as $attribute => $data ) 
			{
				$taxonomy_filter = 'filter_' . str_replace( 'pa_', '', $attribute );

				$fields .= '<input type="hidden" name="' . esc_attr( $taxonomy_filter ) . '" value="' . esc_attr( implode( ',', $data['terms'] ) ) . '" />';

				if ( 'or' == $data['query_type'] ) 
				{
					$fields .= '<input type="hidden" name="' . esc_attr( str_replace( 'pa_', 'query_type_', $attribute ) ) . '" value="or" />';
				}
			}
		}

		
		/*
		 * WC()->query->layered_nav_product_ids replace by  $ccas_filtered_pro_ids by me
		 */
		
		global $ccas_filtered_pro_ids;
		
		if ( 0 === sizeof( $ccas_filtered_pro_ids ) )
		{
			$min = floor( $wpdb->get_var( "
				SELECT min(meta_value + 0)
				FROM {$wpdb->posts} as posts
				LEFT JOIN {$wpdb->postmeta} as postmeta ON posts.ID = postmeta.post_id
				WHERE meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price', '_min_variation_price' ) ) ) ) . "')
				AND meta_value != ''
			" ) );
			$max = ceil( $wpdb->get_var( "
				SELECT max(meta_value + 0)
				FROM {$wpdb->posts} as posts
				LEFT JOIN {$wpdb->postmeta} as postmeta ON posts.ID = postmeta.post_id
				WHERE meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
			" ) );
		} 
		else 
		{
			$min = floor( $wpdb->get_var( "
				SELECT min(meta_value + 0)
				FROM {$wpdb->posts} as posts
				LEFT JOIN {$wpdb->postmeta} as postmeta ON posts.ID = postmeta.post_id
				WHERE meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price', '_min_variation_price' ) ) ) ) . "')
				AND meta_value != ''
				AND (
					posts.ID IN (" . implode( ',', array_map( 'absint', $ccas_filtered_pro_ids ) ) . ")
					OR (
						posts.post_parent IN (" . implode( ',', array_map( 'absint', $ccas_filtered_pro_ids ) ) . ")
						AND posts.post_parent != 0
					)
				)
			" ) );
			$max = ceil( $wpdb->get_var( "
				SELECT max(meta_value + 0)
				FROM {$wpdb->posts} as posts
				LEFT JOIN {$wpdb->postmeta} as postmeta ON posts.ID = postmeta.post_id
				WHERE meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
				AND (
					posts.ID IN (" . implode( ',', array_map( 'absint', $ccas_filtered_pro_ids ) ) . ")
					OR (
						posts.post_parent IN (" . implode( ',', array_map( 'absint', $ccas_filtered_pro_ids ) ) . ")
						AND posts.post_parent != 0
					)
				)
			" ) );
		}

		if ( $min == $max  || $max-$min == 1 ) 
		{
			return;
		}

		
		$this->widget_start( $args, $instance );

		if ( '' == get_option( 'permalink_structure' ) ) 
		{
			$form_action = remove_query_arg( array( 'page', 'paged' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} 
		else 
		{
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}

		if(function_exists('wc_tax_enabled'))
		{
			if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) 
			{
				$tax_classes = array_merge( array( '' ), WC_Tax::get_tax_classes() );
				$min         = 0;

				foreach ( $tax_classes as $tax_class ) 
				{
					$tax_rates = WC_Tax::get_rates( $tax_class );
					$class_min = $min + WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min, $tax_rates ) );
					$class_max = $max + WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max, $tax_rates ) );

					if ( $min === 0 || $class_min < $min ) 
					{
						$min = $class_min;
					}
					if ( $class_max > $max ) 
					{
						$max = $class_max;
					}
				}
			}
			}
		$minPrice = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : $min;
		$maxPrice = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : $max;
		
		?>
		
		<!-- setting for fetching min and max price by jQuery -->
		<span id="ccas_ajax_hidden_price" style="display: none;">
			<p id="ccas_ajax_hidden_currency_symbol"><?php echo get_woocommerce_currency_symbol();?></p>
			<p id="ccas_ajax_hidden_min_price"><?php echo $min;?></p>
			<p id="ccas_ajax_hidden_max_price"><?php echo $max;?></p>
			<p id="ccas_ajax_hidden_min_price_range"><?php echo $minPrice;?></p>
			<p id="ccas_ajax_hidden_max_price_range"><?php echo $maxPrice;?></p>
		</span>
		
		<p>
         <label for="price">Price range:</label>
         <input type="text" id="price" 
            style="border:0; color:#b9cd6d; font-weight:bold;" readonly>
      	</p>
      	<div id="sliderOfPriceWidget"></div>
      	
      	<?php
      	$this->widget_end( $args );
	}
}
