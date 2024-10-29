<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* widget to fetch products by tag.
*
* @class    CED_CAF_Widget_Pro_Tag_Cloud
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
class CED_CAF_Widget_Pro_Tag_Cloud extends CED_CAF_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'widget_product_tag_cloud';
		$this->widget_description = __( 'Shows a Product tags in a widget which lets you search products based on tag.', 'ajaxify-filters' );
		$this->widget_id          = 'ccas_ajax_product_tag_cloud';
		$this->widget_name        = __( 'AJAX Product Tags', 'ajaxify-filters' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Product Tags', 'ajaxify-filters' ),
				'label' => __( 'Title', 'ajaxify-filters' )
			)
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 */
	public function widget( $args, $instance ) {

		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}
		
		$current_taxonomy = 'product_tag';
		if ( empty( $instance['title'] ) ) {
			$taxonomy = get_taxonomy( $current_taxonomy );
			$instance['title'] = $taxonomy->labels->name;
		}

		$this->widget_start( $args, $instance );

		$tagTerms = get_terms('product_tag');
		
		
		echo '<div class="ccas_ajax_tagcloud">';
		echo '<ul>';

		$link = $this->makeFilteringLink();
		$linkCopy = $link;

		foreach ($tagTerms as $tag)
		{
			$classToBeAppended = "";

			$link = $linkCopy;
			
			$temp = get_posts(array(
					'product_tag'		=>	$tag->slug,
					'post_type'			=>	'product',
					'post_status'		=>	'publish',
					'posts_per_page'	=>	'-1'
			));
			
			$proIds = array();
			foreach($temp as $product)
			{
				$proIds[] = $product->ID;
			}
			
			global $ccas_filtered_pro_ids;
			if(!empty($ccas_filtered_pro_ids))
			{
				$commonProIds = array_intersect($ccas_filtered_pro_ids, $proIds);
				if(!is_array($commonProIds) || empty($commonProIds) || count($commonProIds)<=0)
				{
					continue;
				}
			}
			
			
			if(!isset($_GET['filter_tag']))
			{
				$link = add_query_arg( 'filter_tag', $tag->slug , $link );
			}
			else 
			{
				$activeTags = explode(',',sanitize_text_field($_GET['filter_tag']));
				
				if(in_array($tag->slug, $activeTags))
				{
					$activeTags = array_diff($activeTags,array($tag->slug));
					$classToBeAppended = "ccas_chosen";
				}
				else 
				{
					$activeTags[] = $tag->slug;
				}	
				
				if(is_array($activeTags) && !empty($activeTags))
				{
					$link = add_query_arg( 'filter_tag', implode( ',', $activeTags ), $link );
				}
					
			
			}	
			echo "<li class='".$classToBeAppended."'>";
			echo '<a class="ccas_ajax_attribute_filter_anchor_class" title="'.count($temp).' products" href="'.$link.'">'.$tag->name.'</a>';
			echo '</li>';

		}	

		echo '</ul>';
		echo '</div>';
		
		
		$this->widget_end( $args );

	}
	
	/*
	*function to make link
	*/
	function makeFilteringLink()
	{
		global $activeProCat;
		$activeProCat = true;
	
		/**
		 * general shop path
		 * Base Link decided by current page
		 */
		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ))
		{
			$link = get_post_type_archive_link( 'product' );
		} else {
			$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
		}

		// Post Type Arg
		if ( isset( $_GET['post_type'] ) ) {
			$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
		}
	
		//fetching currently active filters
		global $_chosen_attributes;
	
		if(!empty($_chosen_attributes))
		{
			foreach ($_chosen_attributes as $key => $arr)
			{
				if($key != 'product_cat')
				{
					$tempSlugArray = array();
					foreach ($arr['terms'] as $term)
					{
						$tempSlugArray[] = get_term_by('id',$term,$key,ARRAY_A)['slug'];
					}
					$link = add_query_arg( str_replace( 'pa_', 'filter_', $key ), implode( ',', $tempSlugArray ), $link );
				}
				else
				{
					foreach ($arr['terms'] as $term)
					{
						$link = add_query_arg( 'filter_cat', $_GET['filter_cat'], $link );
						break;
					}
				}
			}
		}
		
		//checking for price
		$min_price = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : 0;
		$max_price = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : 0;
		
		if($min_price)
		{
			$link = add_query_arg( 'min_price', $min_price, $link );
		}
		if($max_price)
		{
			$link = add_query_arg( 'max_price', $max_price, $link );
		}
		
		return $link;
		
	}
	

}
