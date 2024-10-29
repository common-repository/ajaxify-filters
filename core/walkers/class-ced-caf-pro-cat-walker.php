<?php
/**
 * class for implementing walker for product category
 *
 * @class    CED_CAF_Pro_Cat_Walker
 * @version  1.0.0
 * @category Class
 * @author   CedCommerce
 */
if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly
}

if (! class_exists ( 'CCAS_Pro_Cat_Walker' )) :
	class CED_CAF_Pro_Cat_Walker extends Walker {
		
		/**
		 * What the class handles.
		 */
		public $tree_type = 'product_cat';
		
		/**
		 * DB fields to use.
		 */
		public $db_fields = array (
			'parent' => 'parent',
			'id' => 'term_id',
			'slug' => 'slug' 
		);
		
		/**
		 * Starts the list before the elements are added.
		 */
		public function start_lvl(&$output, $depth = 0, $args = array()) {
			if ('list' != $args ['style'])
				return;
			
			$indent = str_repeat ( "\t", $depth );
			$output .= "$indent<ul class='children'>\n";
		}
		
		/**
		 * Ends the list of after the elements are added.
		 */
		public function end_lvl(&$output, $depth = 0, $args = array()) {
			if ('list' != $args ['style'])
				return;
			
			$indent = str_repeat ( "\t", $depth );
			$output .= "$indent</ul>\n";
		}
		
		/**
		 * Start the element output.
		 */
		public function start_el( &$output, $cat, $depth = 0, $args = array(), $current_object_id = 0 ) {
			$link = $this->makeFilteringLink ( $cat );
			$activeClassForCheckbox = "";
			if ( isset( $_GET ['filter_cat'] )) {
				$tempArray = explode ( ",", sanitize_text_field ( $_GET ['filter_cat'] ) );
				if ( in_array ( $cat->slug, $tempArray ) ) {
					$activeClassForCheckbox = "checked";
				}
			}
			
			$output .= '<li class="cat-item cat-item-' . $cat->term_id;
			
			if ( $args ['current_category'] == $cat->term_id ) {
				$output .= 'current-cat';
			}
			
			if ( $args ['has_children'] && $args ['hierarchical'] ) {
				$output .= 'ccas-cat-parent';
			}
			
			if ( $args ['current_category_ancestors'] && $args ['current_category'] && in_array ( $cat->term_id, $args ['current_category_ancestors'] ) ) {
				$output .= 'ccas-current-cat-parent';
			}
			
			// added
			global $activeProCat;
			if (! $activeProCat) {
				$activeIdentifier = 'class="ccas_ajax_attribute_filter_anchor_class"';
			} else {
				$activeIdentifier = 'class="ccas_ajax_attribute_filter_anchor_class"';
			}
			// added
			
			$output .= '">';
			
			$output .= '<a href="' . $link . '"' . __ ( $activeIdentifier, 'ajaxify-filters' ) . '>';
				$output .= '<input type="checkbox"' . $activeClassForCheckbox . '>' . __ ( $cat->name, 'ajaxify-filters' );
			$output .= '</a>';
			
			if ( $args[ 'show_count' ] ) {
				$output .= ' <span class="count">(' . $cat->count . ')</span>';
			}
		}
		
		/*
		 * function to make href on anchor for categories :: start
		 */
		function haveCommonProducts( $cat ) {
			// fetching category product ids
			$catProIds = get_posts ( array (
					'product_cat' => $cat->slug,
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => '-1',
					'fields' => 'ids' 
			) );
			
			// fetching currently filtered product ids
			global $ccas_filtered_pro_ids;
			
			if (! empty ( $ccas_filtered_pro_ids )) {
				$commonProIds = array_intersect ( $ccas_filtered_pro_ids, $catProIds );
				if (empty ( $commonProIds )) {
					return false;
				}
				return true;
			}
			return true;
		}
		
		/*
		 * function to make link for the category
		 */
		function makeFilteringLink( $cat ) {
			global $activeProCat;
			$activeProCat = true;
			
			// general shop path
			// Base Link decided by current page
			if (defined ( 'SHOP_IS_ON_FRONT' )) {
				$link = home_url ();
			} elseif (is_post_type_archive ( 'product' ) || is_page ( wc_get_page_id ( 'shop' ) )) {
				$link = get_post_type_archive_link ( 'product' );
			} else {
				$link = get_term_link ( get_query_var ( 'term' ), get_query_var ( 'taxonomy' ) );
			}
			
			// Post Type Arg
			if (isset ( $_GET ['post_type'] )) {
				$link = add_query_arg ( 'post_type', $_GET ['post_type'], $link );
			}
			
			// fetching currently active filters
			global $_chosen_attributes;
			if (! empty( $_chosen_attributes ) ) {
				foreach ( $_chosen_attributes as $key => $arr ) {
					if ( $key != 'product_cat' ) {
						$tempSlugArray = array ();
						foreach ( $arr ['terms'] as $term ) {
							$tempSlugArray[] = get_term_by ( 'id', $term, $key, ARRAY_A )[ 'slug' ];
						}
						$link = add_query_arg ( str_replace ( 'pa_', 'filter_', $key ), implode ( ',', $tempSlugArray ), $link );
					} else {
						foreach ( $arr ['terms'] as $term ) {
							$tempSlugElement = get_term_by ( 'slug', $term, $key, ARRAY_A ) ['slug'];
							if ( $tempSlugElement == $cat->slug ) {
								$activeProCat = false;
								break;
							}
						}
					}
				}
				if ( $activeProCat ) {
					// updated
					if ( isset( $_GET['filter_cat'] ) && $_GET['filter_cat'] == $cat->slug ) {
						$link = $link;
					} else {
						$cat_slug_to_use = $cat->slug;
						if ( isset( $_GET ['filter_cat'] ) ) {
							$applied_filter_cats = explode( ',', $_GET ['filter_cat'] ) ;
							if ( ! empty( $applied_filter_cats ) ) {
								if ( ! in_array( $cat->slug, $applied_filter_cats ) ) {
									$cat_slug_to_use = $_GET [ 'filter_cat' ] . ',' . $cat_slug_to_use;
								} else {
									/**
									 * If already assigned then remove this from link.
									 */
									$currentCatKey = array_search( $cat->slug, $applied_filter_cats );
									unset( $applied_filter_cats[ $currentCatKey ] );
									$cat_slug_to_use = implode( ',', $applied_filter_cats );
								}
							}
						}
						$link = add_query_arg( 'filter_cat', $cat_slug_to_use, $link );
					}
				}
			} else {
				$link = add_query_arg ( 'filter_cat', $cat->slug, $link );
			}
			
			// checking for price
			$min_price = isset ( $_GET ['min_price'] ) ? esc_attr ( $_GET ['min_price'] ) : 0;
			$max_price = isset ( $_GET ['max_price'] ) ? esc_attr ( $_GET ['max_price'] ) : 0;
			
			if ( $min_price ) {
				$link = add_query_arg ( 'min_price', $min_price, $link );
			}
			if ( $max_price ) {
				$link = add_query_arg ( 'max_price', $max_price, $link );
			}
			
			// checking for product-tag
			if ( isset( $_GET ['filter_tag'] ) ) {
				$link = add_query_arg ( 'filter_tag', sanitize_text_field ( $_GET ['filter_tag'] ), $link );
			}
			
			return $link;
		}
		
		/**
		 * Ends the element output, if needed.
		 */
		public function end_el(&$output, $cat, $depth = 0, $args = array()) {
			$output .= "</li>\n";
		}
		
		/**
		 * Traverse elements to create list from elements.
		 */
		public function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
			if (! $element || 0 === $element->count) {
				return;
			}
			parent::display_element ( $element, $children_elements, $max_depth, $depth, $args, $output );
		}
	}


endif;
