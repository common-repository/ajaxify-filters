<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* widget to show actives filters
*
* @class    CED_CAF_Widget_AJAX_Active_Filters
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
class CED_CAF_Widget_AJAX_Active_Filters extends CED_CAF_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'ccas_ajax_active_filters';
		$this->widget_description = __( 'Shows active filters so users can see and deactivate them.', 'ajaxify-filters' );
		$this->widget_id          = 'ccas_ajax_active_filters';
		$this->widget_name        = __( 'AJAX Active Filters', 'ajaxify-filters' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Remove Active Filters', 'ajaxify-filters' ),
				'label' => __( 'Title', 'ajaxify-filters' )
			)
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 */
	public function widget( $args, $instance ) {
		global $_chosen_attributes;
		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}

		$filtersHtmlUlStart = '';
		$filtersHtmlUlEnd = '';
		$filtersHtmlLi = '';
		$liRemoveAllFilters = '';
		// Price
		$min_price = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : 0;
		$max_price = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : 0;
		
		$this->widget_start( $args, $instance );
		
		if ( 0 < count( $_chosen_attributes ) || 0 < $min_price || 0 < $max_price || isset( $_GET['filter_tag'] ) ) {

			$filtersHtmlUlStart .= '<ul>';
			
			/*
			 * adding Remove all-filter at once link :: start
			 */
			$activeFiltersCount = 0;
			if( $min_price )
				$activeFiltersCount++;
			if( $max_price )
				$activeFiltersCount++;
			if( isset( $_GET[ 'filter_tag' ] ) ) {
				$tempArray = explode( ',', $_GET['filter_tag'] );
				$activeFiltersCount += count($tempArray);
			}
			if(is_array($_chosen_attributes) && !empty($_chosen_attributes)){

			if ( array_key_exists( 'pa_color', $_chosen_attributes ) ) {
				if ( count( $_chosen_attributes[ 'pa_color' ][ 'terms' ] ) > 1 or ( $_chosen_attributes[ 'pa_color' ][ 'terms' ] == 1 and $activeFiltersCount ) ) {
					$activeFiltersCount++;
				}
			}
		}

			$activeFiltersCount += count( $_chosen_attributes );
			
			/*
			 * adding Remove all-filter at once link :: end
			 */
			
			
			// Attributes
			if ( ! is_null( $_chosen_attributes ) ) {
				foreach ( $_chosen_attributes as $taxonomy => $data ) {
					foreach ( $data['terms'] as $term_id ) {
						$term = get_term( $term_id, $taxonomy );

						if ( ! isset( $term->name ) ) {
							continue;
						}

						$taxonomy_filter = str_replace( 'pa_', '', $taxonomy );
						
						$current_filter  = ! empty( $_GET[ 'filter_' . $taxonomy_filter ] ) ? $_GET[ 'filter_' . $taxonomy_filter ] : '';
						
						// code to convert term_id to slug :: start
						$term_slug = get_term_by('id',$term_id,$taxonomy,ARRAY_A)['slug'];
						// code to convert term_id to slug :: end
						
						$new_filter      = explode( ',', $current_filter );
						
						$new_filter      = array_diff( $new_filter, array($term_slug) );
						
						$link = remove_query_arg( array( 'add-to-cart', 'filter_' . $taxonomy_filter ) );

						if ( sizeof( $new_filter ) > 0 ) 
						{
							$link = add_query_arg( 'filter_' . $taxonomy_filter, implode( ',', $new_filter ), $link );
						}

						$filtersHtmlLi .= '<li class="ccas_chosen">';
							$filtersHtmlLi .= '<a class="ccas_ajax_attribute_filter_anchor_class" title="' . esc_attr__( 'Remove filter', 'ajaxify-filters' ) . '" href="' . esc_url( $link ) . '">' . $term->name . '</a>';
						$filtersHtmlLi .= '</li>';
					}
				}
			}

			if ( $min_price ) {
				$link = remove_query_arg( 'min_price' );
				$filtersHtmlLi .= '<li class="ccas_chosen">';
					$filtersHtmlLi .= '<a class="ccas_ajax_attribute_filter_anchor_class" title="' . esc_attr__( 'Remove filter', 'ajaxify-filters' ) . '" href="' . esc_url( $link ) . '">' . __( 'Min Price', 'ajaxify-filters' ) . ' ' . wc_price( $min_price ) . '</a>';
				$filtersHtmlLi .= '</li>';
			}

			if ( $max_price ) {
				$link = remove_query_arg( 'max_price' );
				$filtersHtmlLi .= '<li class="ccas_chosen">';
					$filtersHtmlLi .= '<a class="ccas_ajax_attribute_filter_anchor_class" title="' . esc_attr__( 'Remove filter', 'ajaxify-filters' ) . '" href="' . esc_url( $link ) . '">' . __( 'Max Price', 'ajaxify-filters' ) . ' ' . wc_price( $max_price ) . '</a>';
				$filtersHtmlLi .= '</li>';
			}
			
			/*** code checking for active product-category :: start ***/
			$cat_filter_applied = isset( $_GET[ 'filter_cat' ] ) ? esc_attr( $_GET[ 'filter_cat' ] ) : NULL;
			if( $cat_filter_applied != NULL and !empty( $cat_filter_applied ) ) {
				$appliedCats = explode( ',', $cat_filter_applied );

				if ( $activeFiltersCount == 1 ) {
					if ( ! array_key_exists( 'product_cat', $_chosen_attributes ) ) {
						if ( count( $appliedCats ) > 1 ) {
							$activeFiltersCount++;		
						}
					}
					
					if ( count( $appliedCats ) > 1 ) {
						$activeFiltersCount++;
					}
				}

				if ( !empty( $appliedCats ) and is_array( $appliedCats ) ) {
					foreach ( $appliedCats as $catSlug ) {
						if ( empty( $catSlug ) ) {
							continue;
						}

						$catName = get_term_by( 'slug', $catSlug, 'product_cat', ARRAY_A )[ 'name' ];
						$link = remove_query_arg( 'filter_cat' );
						$cat_slug_to_use = $catSlug;
						if ( isset( $_GET ['filter_cat'] ) ) {
							$applied_filter_cats = explode( ',', $_GET ['filter_cat'] ) ;
							if ( ! empty( $applied_filter_cats ) ) {
								if ( ! in_array( $catSlug, $applied_filter_cats ) ) {
									$cat_slug_to_use = $_GET [ 'filter_cat' ] . ',' . $cat_slug_to_use;
								} else {
									/**
									 * If already assigned then remove this from link.
									 */
									$currentCatKey = array_search( $catSlug, $applied_filter_cats );
									unset( $applied_filter_cats[ $currentCatKey ] );
									$cat_slug_to_use = implode( ',', $applied_filter_cats );
								}
							}
						}
						if ( !empty( $cat_slug_to_use ) ) {
							$link = add_query_arg( 'filter_cat', $cat_slug_to_use, $link );
						}

						$filtersHtmlLi .= '<li class="ccas_chosen">';
							$filtersHtmlLi .= '<a class="ccas_ajax_attribute_filter_anchor_class" title="' . esc_attr__( 'Remove filter', 'ajaxify-filters' ) . '" href="' . esc_url( $link ) . '">' . $catName . '</a>';
						$filtersHtmlLi .= '</li>';
					}
				}
			}	
			/*** code checking for active product-category :: end ***/
			
			/*** code to add product-tag to anchor if product-tag is present in URL ***/
			if( isset( $_GET[ 'filter_tag' ] ) ) {
				$link = remove_query_arg( 'filter_tag' );
				
				$activeTags = explode( ',', sanitize_text_field( $_GET[ 'filter_tag' ] ) );
				foreach( $activeTags as $tag ) {
					$activeTagsArr = array_diff( $activeTags, array( $tag ) );
					if( is_array( $activeTagsArr ) && !empty( $activeTagsArr ) ) {
						$link = add_query_arg( 'filter_tag', implode( ',', $activeTagsArr ), $link );
					}

					$filtersHtmlLi .= '<li class="ccas_chosen">';
						$filtersHtmlLi .= '<a class="ccas_ajax_attribute_filter_anchor_class" title="' . esc_attr__( 'Remove filter', 'ajaxify-filters' ) . '" href="' . esc_url( $link ) . '">' . $tag . '</a>';
					$filtersHtmlLi .= '</li>';
				}
			}
			
			if( $activeFiltersCount >= 2 ) {
				$liRemoveAllFilters .= '<li class="ccas_chosen">';
					$liRemoveAllFilters .= '<a class="ccas_ajax_attribute_filter_anchor_class" title="' . esc_attr__( 'Remove filter', 'ajaxify-filters' ) . '" href="' . esc_url(get_permalink( wc_get_page_id( 'shop' ) )) . '">Remove All Filter</a>';
				$liRemoveAllFilters .= '</li>';
			}	
			$filtersHtmlUlEnd .= '</ul>';
			
			echo $filtersHtmlUlStart . $liRemoveAllFilters . $filtersHtmlLi . $filtersHtmlUlEnd;
		}
		$this->widget_end( $args );
	}
}
