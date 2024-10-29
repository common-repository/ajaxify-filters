<?php
/**
* class helps to fetch product by price.
*
* @class    CED_CAF_Price_Filter_Helper
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CED_CAF_Price_Filter_Helper
{
		/**
		 * This function helps to fetch product by price'.
		 * @name ced_caf_ajax_layered_nav_init()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link: http://cedcommerce.com/
		 */
	static function ced_caf_price_filter( $filtered_posts = array() ) 
	{
		if ( apply_filters( 'ccas_is_price_filter_active', is_active_widget( false, false, 'ccas_ajax_price_filter', true ) ) && ! is_admin() )
		{
		
			//added by me
			global $ccas_filtered_pro_ids;
			
			global $wpdb;
	
			if ( isset( $_GET['max_price'] ) || isset( $_GET['min_price'] ) ) 
			{
				$matched_products = array();
				$min              = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0;
				$max              = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : 9999999999;
	
				// If displaying prices in the shop including taxes, but prices don't include taxes..
				if ( function_exists('wc_tax_enabled') && wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) 
				{
					$tax_classes = array_merge( array( '' ), WC_Tax::get_tax_classes() );
	
					foreach ( $tax_classes as $tax_class ) 
					{
						$tax_rates = WC_Tax::get_rates( $tax_class );
						$min_class = $min - WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $min, $tax_rates ) );
						$max_class = $max - WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $max, $tax_rates ) );
	
						$matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare( "
							SELECT DISTINCT ID, post_parent, post_type FROM {$wpdb->posts}
							INNER JOIN {$wpdb->postmeta} pm1 ON ID = pm1.post_id
							INNER JOIN {$wpdb->postmeta} pm2 ON ID = pm2.post_id
							WHERE post_type IN ( 'product', 'product_variation' )
							AND post_status = 'publish'
							AND pm1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
							AND pm1.meta_value BETWEEN %f AND %f
							AND pm2.meta_key = '_tax_class'
							AND pm2.meta_value = %s
						", $min_class, $max_class, sanitize_title( $tax_class ) ), OBJECT_K ), $min_class, $max_class );
	
						if ( $matched_products_query ) 
						{
							foreach ( $matched_products_query as $product ) 
							{
								if ( $product->post_type == 'product' ) 
								{
									$matched_products[] = $product->ID;
								}
								if ( $product->post_parent > 0 ) 
								{
									$matched_products[] = $product->post_parent;
								}
							}
						}
					}
				} 
				else 
				{
					$matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare( "
						SELECT DISTINCT ID, post_parent, post_type FROM {$wpdb->posts}
						INNER JOIN {$wpdb->postmeta} pm1 ON ID = pm1.post_id
						WHERE post_type IN ( 'product', 'product_variation' )
						AND post_status = 'publish'
						AND pm1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
						AND pm1.meta_value BETWEEN %d AND %d
					", $min, $max ), OBJECT_K ), $min, $max );
	
					if ( $matched_products_query ) 
					{
						foreach ( $matched_products_query as $product ) 
						{
							if ( $product->post_type == 'product' ) 
							{
								$matched_products[] = $product->ID;
							}
							if ( $product->post_parent > 0 ) 
							{
								$matched_products[] = $product->post_parent;
							}
						}
					}
				}
	
				$matched_products = array_unique( $matched_products );
	
				// Filter the id's
				if ( 0 === sizeof( $filtered_posts ) ) {
					$filtered_posts = $matched_products;
				} else {
					$filtered_posts = array_intersect( $filtered_posts, $matched_products );
				}
				$filtered_posts[] = 0;
				
				if(!empty($ccas_filtered_pro_ids))
				{
					$filtered_posts = array_intersect( $filtered_posts, $ccas_filtered_pro_ids );
				}
				
				$ccas_filtered_pro_ids = $filtered_posts;
			}
	
			return $ccas_filtered_pro_ids;
		}
		else
		{
			return array();
		}	
	}
}

?>