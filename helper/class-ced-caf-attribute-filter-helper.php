<?php
/**
* class helps to fetch product by attributes.
*
* @class    CED_CAF_Attribute_Filter_Helper
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CED_CAF_Attribute_Filter_Helper
{
		/**
		 * This function helps to fetch product by attributes'.
		 * @name ced_caf_ajax_layered_nav_init()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link: http://cedcommerce.com/
		 */
	static function ced_caf_ajax_layered_nav_init() 
	{
		if ( apply_filters( 'ced_caf_is_ajax_layered_nav_active', is_active_widget( false, false, 'ccas_ajax_layered_nav', true ) ) && ! is_admin() )
		{
			global $_chosen_attributes;
			$_chosen_attributes = array();
			
			global $ccas_filtered_pro_ids;
			$ccas_filtered_pro_ids = array();
			
	
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( $attribute_taxonomies ) 
			{
				foreach ( $attribute_taxonomies as $tax ) 
				{
					$attribute       = wc_sanitize_taxonomy_name( $tax->attribute_name );
					$taxonomy        = wc_attribute_taxonomy_name( $attribute );
					$name            = 'filter_' . $attribute;
					$query_type_name = 'query_type_' . $attribute;
	
					if ( ! empty( $_GET[ $name ] ) && taxonomy_exists( $taxonomy ) ) 
					{
						
 						$_chosen_attributes[ $taxonomy ]['terms'] = explode( ',', sanitize_text_field($_GET[ $name ] ));

	
						foreach ($_chosen_attributes[ $taxonomy ]['terms'] as $key => $termSlug)
						{
							$_chosen_attributes[ $taxonomy ]['terms'][$key] = get_term_by( 'slug', $termSlug, $taxonomy, ARRAY_A )['term_id'];
						}
						if ( empty( $_GET[ $query_type_name ] ) || ! in_array( strtolower( $_GET[ $query_type_name ] ), array( 'and', 'or' ) ) )
							$_chosen_attributes[ $taxonomy ]['query_type'] = apply_filters( 'woocommerce_layered_nav_default_query_type', 'and' );
							else
								$_chosen_attributes[ $taxonomy ]['query_type'] = strtolower( $_GET[ $query_type_name ] );
	
					}
				}
			}
			
			$ccas_filtered_pro_ids =  CED_CAF_Attribute_Filter_Helper::ced_caf_layered_nav_query_function();
			return $ccas_filtered_pro_ids;
			
		
		}
	}	
	
	/*
	* helper function 
	*/
	static function ced_caf_layered_nav_query_function( $filtered_posts = array() ) {
		global $_chosen_attributes;
		
		if ( sizeof( $_chosen_attributes ) > 0 ) {
	
			$matched_products   = array(
					'and' => array(),
					'or'  => array()
			);
			$filtered_attribute = array(
					'and' => false,
					'or'  => false
			);
	
			foreach ( $_chosen_attributes as $attribute => $data ) {
				$matched_products_from_attribute = array();
				$filtered = false;
	
				if ( sizeof( $data['terms'] ) > 0 ) {
					foreach ( $data['terms'] as $value ) {
	
						$args = array(
								'post_type' 	=> 'product',
								'numberposts' 	=> -1,
								'post_status' 	=> 'publish',
								'fields' 		=> 'ids',
								'no_found_rows' => true,
								'tax_query' => array(
										array(
												'taxonomy' 	=> $attribute,
												'terms' 	=> $value,
												'field' 	=> 'term_id'
										)
								)
						);
						
						$post_ids = get_posts( $args );
						
						if ( ! is_wp_error( $post_ids ) ) {
	
							if ( sizeof( $matched_products_from_attribute ) > 0 || $filtered ) {
								$matched_products_from_attribute = $data['query_type'] == 'or' ? array_merge( $post_ids, $matched_products_from_attribute ) : array_intersect( $post_ids, $matched_products_from_attribute );
							} else {
								$matched_products_from_attribute = $post_ids;
							}
	
							$filtered = true;
						}
					}
				}
				
				if ( sizeof( $matched_products[ $data['query_type'] ] ) > 0 || $filtered_attribute[ $data['query_type'] ] === true ) {
					$matched_products[ $data['query_type'] ] = ( $data['query_type'] == 'or' ) ? array_merge( $matched_products_from_attribute, $matched_products[ $data['query_type'] ] ) : array_intersect( $matched_products_from_attribute, $matched_products[ $data['query_type'] ] );
				} else {
					$matched_products[ $data['query_type'] ] = $matched_products_from_attribute;
				}
	
				$filtered_attribute[ $data['query_type'] ] = true;
	
			}
	
			// Combine our AND and OR result sets
			if ( $filtered_attribute['and'] && $filtered_attribute['or'] )
				$results = array_intersect( $matched_products[ 'and' ], $matched_products[ 'or' ] );
				else
					$results = array_merge( $matched_products[ 'and' ], $matched_products[ 'or' ] );
	
					if ( $filtered ) {
						
						
						WC()->query->layered_nav_post__in   = $results;
						WC()->query->layered_nav_post__in[] = 0;
						
						if ( sizeof( $filtered_posts ) == 0 ) {
							$filtered_posts   = $results;
							$filtered_posts[] = 0;
						} else {
							$filtered_posts   = array_intersect( $filtered_posts, $results );
							$filtered_posts[] = 0;
						}
	
					}
		}
		return (array) $filtered_posts;
	}
	
}

?>