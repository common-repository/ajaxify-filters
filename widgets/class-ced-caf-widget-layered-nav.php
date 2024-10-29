<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* widget to fecth products by attributes
*
* @class    CED_CAF_Widget_Layered_Nav
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
class CED_CAF_Widget_Layered_Nav extends CED_CAF_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'ccas_ajax_layered_nav_widget_id';
		$this->widget_description = __( 'Shows a custom attribute in a widget which lets you search products based on attributes.', 'ajaxify-filters' );
		$this->widget_id          = 'ccas_ajax_layered_nav';
		$this->widget_name        = __( 'AJAX Product Attributes', 'ajaxify-filters' );

		parent::__construct();
	}

	/**
	 * Updates a particular instance of a widget.
	 */
	public function update( $new_instance, $old_instance ) 
	{   
		print_r($old_instance);
		return $new_instance;
	}

	/**
	 * Outputs the settings update form.
	 */
	public function form( $instance ) {
		
		$this->init_settings();

		parent::form( $instance );
		
		if(isset($instance["display_type"]))
		{
			$thisRef =  $this->get_field_name("");
			$thisRef = explode("]",$thisRef )[0];
			$thisRef = $thisRef.']';
		
			$instanceRef = $instance;
			$selectedOpt = $instance["display_type"];
			$selectedAttribute = $instance['attribute'];
			$terms = get_terms ( 'pa_' . $selectedAttribute );		
		
			echo '<div class="dynamic-hidden-div">';
			if ($selectedOpt == "label") {
				require_once CED_CAF_PLUGIN_DIR_PATH.'/core/labelLook.php';
			} else if ($selectedOpt == "picker") {
				require_once CED_CAF_PLUGIN_DIR_PATH.'/core/pickerLook.php';
			}
			echo '</div>';	
		}
		else
		{
			//an empty div to to filled by ajax-data-fetch request (dynamically)
			echo '<div class="dynamic-hidden-div" style="display: none;"></div>';
		}
			
		echo '<div class="instance-ref-div" style="display: none;">'.json_encode($instance).'</div>';
	}

	/**
	 * Init settings after post types are registered.
	 */
	public function init_settings() {
		$attribute_array      = array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
					$attribute_array[ $tax->attribute_name ] = $tax->attribute_name;
				}
			}
		}

		$this->settings = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Filter By', 'ajaxify-filters' ),
				'label' => __( 'Title', 'ajaxify-filters' )
			),
			'multiple_filter' => array( //updated
				'type'  => 'checkbox',
				'std'   => 0,
				'class' => 'ccas_multiple_filter',
				'label' => __( 'Enable Multiple Filter', 'ajaxify-filters' )
			),
			'query_type' => array(
				'type'    => 'select',
				'std'     => 'and',
				'class' => 'ccas_query_type',
				'label'   => __( 'Query type', 'ajaxify-filters' ),
				'options' => array(
					'and' => __( 'AND', 'ajaxify-filters' ),
					'or'  => __( 'OR', 'ajaxify-filters' )
				)
			),
			'count' => array( //updated
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show product counts', 'ajaxify-filters' )
			),
			'attribute' => array(
				'type'    => 'select_attribute',
				'std'     => '',
				'label'   => __( 'Attribute', 'ajaxify-filters' ),
				'options' => $attribute_array
			),
			'display_type' => array(
				'type'    => 'select_display_type',
				'std'     => 'list',
				'class' => 'ccas_attribute_urgent',
				'label'   => __( 'Display type', 'ajaxify-filters' ),
				'options' => array(
					'list'     => __( 'List', 'ajaxify-filters' ),
					'checkbox'     => __( 'Checkbox', 'ajaxify-filters' ),
					'label'     => __( 'Size/Amount Selector', 'ajaxify-filters' ),
					'picker'     => __( 'Color Selector', 'ajaxify-filters' ),
				)
			),
		);
	}

	/**
	 * Output widget.
	 */
	public function widget( $args, $instance ) {
		global $_chosen_attributes;
		global $ccas_filtered_pro_ids;

		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}

		$current_term = is_tax() ? get_queried_object()->term_id : '';
		
		$current_tax  = is_tax() ? get_queried_object()->taxonomy : '';
		$taxonomy     = isset( $instance['attribute'] ) ? wc_attribute_taxonomy_name( $instance['attribute'] ) : $this->settings['attribute']['std'];
		$query_type   = isset( $instance['query_type'] ) ? $instance['query_type'] : $this->settings['query_type']['std'];
		$display_type = isset( $instance['display_type'] ) ? $instance['display_type'] : $this->settings['display_type']['std'];

		$isShowCount  = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];//updated
		$is_multiple_filter  = isset( $instance['multiple_filter'] ) ? $instance['multiple_filter'] : $this->settings['multiple_filter']['std'];//updated

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$get_terms_args = array( 'hide_empty' => '1' );
		$orderby = wc_attribute_orderby( $taxonomy );
		
		switch ( $orderby ) {
			case 'name' :
				$get_terms_args['orderby']    = 'name';
				$get_terms_args['menu_order'] = false;
			break;
			case 'id' :
				$get_terms_args['orderby']    = 'id';
				$get_terms_args['order']      = 'ASC';
				$get_terms_args['menu_order'] = false;
			break;
			case 'menu_order' :
				$get_terms_args['menu_order'] = 'ASC';
			break;
		}

		$terms = get_terms( $taxonomy, $get_terms_args );
		
		if ( 0 < count( $terms ) ) {
			
			//ob_start();
			
			$found = false;

			$this->widget_start( $args, $instance );
			
			// Force found when option is selected - do not force found on taxonomy attributes
			if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
				
				$found = true;
			}
			
			if($display_type == "picker")
				{
					echo '<ul class="caf_picker_class">';
				}
				else if($display_type == "label")
				{
					echo '<ul class="caf_label_class">';
				}
				else 
				{
					echo '<ul>';
				}	
				
				foreach ( $terms as $term ) {

					// Get count based on current view - uses transients
					$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
					
					$option_is_set  = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

					// skip the term for the current archive
					if ( $current_term == $term->term_id ) {
						continue;
					}

					// If this is an AND query, only show options with count > 0
					if ( 'and' == $query_type ) {


						/*
						 * WC()->query->filtered_product_ids <==> $ccas_filtered_pro_ids 
						 */
						
						if( isset($ccas_filtered_pro_ids) && !empty($ccas_filtered_pro_ids)) // runs when our custom global variable contains filtered pro-ids
						{
							$count = sizeof( array_intersect( $_products_in_term,  $ccas_filtered_pro_ids) );
						}
						else // runs when no attribute is selected : means runs by default
						{
							if(isset(WC()->query->filtered_product_ids))
							{
								$count = sizeof( array_intersect( $_products_in_term,  WC()->query->filtered_product_ids) );
							}
							else
							{
								$count = count($_products_in_term);//changed
							}	
						}
						
						if ( 0 < $count && $current_term !== $term->term_id ) {
							$found = true;
						}

						if ( 0 == $count && ! $option_is_set ) {
							continue;
						}

					// If this is an OR query, show all options so search can be expanded
					} else {

						if(isset(WC()->query->unfiltered_product_ids))
						{
							$count = sizeof( array_intersect( $_products_in_term, WC()->query->unfiltered_product_ids ) );
						}
						else
						{
							$count =  count($_products_in_term);
						}	
						if ( 0 < $count ) {
							$found = true;
						}
					}

					$arg = 'filter_' . sanitize_title( $instance['attribute'] );

					$current_filter = ( isset( $_GET[ $arg ] ) ) ? explode( ',', $_GET[ $arg ] ) : array();

					if ( ! is_array( $current_filter ) ) {
						$current_filter = array();
					}

					$current_filter = array_map( 'esc_attr', $current_filter );

					
					//changed by me to use name in place of slug
					if ( ! in_array( $term->slug, $current_filter ) ) {
						$current_filter[] = $term->slug;
					}

					// Base Link decided by current page
					if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
						$link = home_url();
					} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ) ) {
						$link = get_post_type_archive_link( 'product' );
					} else {
						$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
					}

					// All current filters
					if ( $_chosen_attributes ) {
						foreach ( $_chosen_attributes as $name => $data ) {
							
							$tempSlugArray=array();
							if( $name == 'product_cat' ) {
								foreach ( $data[ 'terms' ] as $key => $val ) {
									$tempSlugArray[] = get_term_by('slug',$val,$name,ARRAY_A)['slug'];
								}
							} else {	
								// custom code to convert array of ids to array of slug :: start
								foreach ( $data['terms'] as $key => $val ) {
									$tempSlugArray[] = get_term_by('id',$val,$name,ARRAY_A)['slug'];
								}	
								// custom code to convert array of ids to array of slug :: end
							}					
							
							if ( $name !== $taxonomy ) {

								// Exclude query arg for current term archive term
								while ( in_array( $current_term, $data['terms'] ) ) {
									$key = array_search( $current_term, $data );
									unset( $data['terms'][$key] );
								}
								
								if($name == 'product_cat') //code added by meeeee
								{
									$filter_name = sanitize_title( str_replace( 'product_', '', $name ) );
								}
								else
								{	
									// Remove pa_ and sanitize
									$filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );
								}
								
								if ( ! empty( $data['terms'] ) ) {
									// used to make link when some attributes are already selected
									$link = add_query_arg( 'filter_' . $filter_name, implode( ',', $tempSlugArray ), $link ); 
								}
								
								if ( 'or' == $data['query_type'] ) {
									$link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
								}
							}
						}
					}
					
					
					/*
					 * code to add Min/Max price to anchor if Min/Max price is present in URL
					 */
					if ( isset( $_GET['min_price'] ) ) {
						$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
					}

					if ( isset( $_GET['max_price'] ) ) {
						$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
					}

					/*
					 * code to add Orderby to anchor if Orderby is present in URL
					 */
					if ( isset( $_GET['orderby'] ) ) {
						$link = add_query_arg( 'orderby', $_GET['orderby'], $link );
					}

					/*
					 * code to add filter_tag to anchor if filter_tag is present in URL
					 */
					if(isset($_GET['filter_tag']))
					{
						$link = add_query_arg( 'filter_tag', sanitize_text_field($_GET['filter_tag']), $link );
					}
					
					
					// Current Filter = this widget
					if ( isset( $_chosen_attributes[ $taxonomy ] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) {

						$class = 'class="ccas_chosen"';

						// Remove this term is $current_filter has more than 1 term filtered
						if ( sizeof( $current_filter ) > 1 ) 
						{
							$current_filter_without_this = array_diff( $current_filter, array( $term->slug ) );
							$link = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
							if(!$is_multiple_filter)
							{
								$link = add_query_arg( $arg, $term->slug , $link );
							}
						}
					} else {
						$class = '';
						$link = add_query_arg( $arg, implode( ',', $current_filter ), $link );
						if(!$is_multiple_filter)
						{
							$link = add_query_arg( $arg, $term->slug , $link );
						}
					}
					
					// Search Arg
					if ( get_search_query() ) {
						$link = add_query_arg( 's', get_search_query(), $link );
					}

					// Post Type Arg
					if ( isset( $_GET['post_type'] ) ) {
						$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
					}

					// Query type Arg
					if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[ $taxonomy ]['terms'] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) ) {
						$link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );
					}

					/*
					 * code to handle display on front-end :: start
					 */
					if( $display_type == "checkbox" ) {

						if( $class == 'class="ccas_chosen"' ) {
							$class = 'checked';
						}
						
						echo '<li>';
						
						echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( $link ) . '" class="ccas_ajax_attribute_filter_anchor_class">' : '<span>';
						
						echo '<input type="checkbox"'.$class.'>';
						
						echo $term->name;
						
						echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';
						
						if( $isShowCount ) {
							echo ' <span class="count">(' . $count . ')</span></li>';
						} else {
							echo '</li>';
						}
						
					} else if( $display_type == "label" ) {
						echo '<li ' . $class . '>';
						echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( $link ) . '" class="ccas_ajax_attribute_filter_anchor_class">' : '<span>';
						
						if( $isShowCount ) {
							echo $instance[$term->name].'<span class="count">(' . $count . ')</span>';
						} else {
							echo $instance[$term->name];
						}
						echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';
						echo '</li>';
					} else if( $display_type == "picker" ) {
						echo '<li ' . $class . '>';
						
						echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( $link ) . '" class="ccas_ajax_attribute_filter_anchor_class" style="background: '. $instance[$term->name.'_colorVal'] .'">' : '<span>';

						echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

						if( $isShowCount ) {
							echo '<span class="count">' . $count . '</span>';
						}
						echo '</li>';
					} else {
						echo '<li ' . $class . '>';
						
						echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( $link ) . '" class="ccas_ajax_attribute_filter_anchor_class">' : '<span>';
						
						echo $term->name;
						
						echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';
						
						if( $isShowCount ) {
							echo ' <span class="count">(' . $count . ')</span></li>';
						} else {
							echo '</li>';
						}
					}
					/** code to handle display on front-end :: end */
				}
			echo '</ul>';
			echo '<div style="clear:both;"></div>';
			$this->widget_end( $args );

			if ( ! $found ) {
				ob_end_clean();
			} else {
				echo ob_get_clean();
			}
		}
	}

	public function ced_caf_get_required_svg( $color = '#000000', $checked = false ) {
		$checkMarkColor = $color;
		if ( $checked ) {
			$checkMarkColor = '#FFFFFF';
		}

		$svg = '<svg version="1.1" id="ccas_attribute_color" width="60" height="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" style="enable-background:new 0 0 507.2 507.2;" xml:space="preserve">';
			$svg .= '<circle style="fill: '. $color .';" cx="253.6" cy="253.6" r="253.6"/>';
			$svg .= '<g>';
				$svg .= '<path style="fill: '. $checkMarkColor .';" d="M260,310.4c11.2,11.2,11.2,30.4,0,41.6l-23.2,23.2c-11.2,11.2-30.4,11.2-41.6,0L93.6,272.8 c-11.2-11.2-11.2-30.4,0-41.6l23.2-23.2c11.2-11.2,30.4-11.2,41.6,0L260,310.4z"/>';
				$svg .= '<path style="fill: '. $checkMarkColor .';" d="M348.8,133.6c11.2-11.2,30.4-11.2,41.6,0l23.2,23.2c11.2,11.2,11.2,30.4,0,41.6l-176,175.2 c-11.2,11.2-30.4,11.2-41.6,0l-23.2-23.2c-11.2-11.2-11.2-30.4,0-41.6L348.8,133.6z"/>';
			$svg .= '</g>';
		$svg .= '</svg>';
		return $svg;
	}
}
