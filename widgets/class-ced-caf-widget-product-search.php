<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* widget to fetch product by typing name
*
* @class    CED_CAF_Widget_Product_Search
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
class CED_CAF_Widget_Product_Search extends CED_CAF_Widget 
{
	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		$this->widget_cssclass    = 'widget_product_search';
		$this->widget_description = __( 'Shows a search-box in a widget which lets you search products based text entered.', 'ajaxify-filters' );
		$this->widget_id          = 'ccas_ajax_product_search';
		$this->widget_name        = __( 'AJAX Product Search', 'ajaxify-filters' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Search Products', 'ajaxify-filters' ),
				'label' => __( 'Title', 'ajaxify-filters' )
			)
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 */
	function widget( $args, $instance ) 
	{
		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}
		
		$this->widget_start( $args, $instance );

		?>
		<div class="ccas_pro_search_div">
			<input type="text" id="search-box_ccas_" placeholder="Product Name" />
			<span class="ccas_pro_cross_class">
			<img  class="" src="<?php echo CED_CAF_PLUGIN_DIR_URL.'images/cancel.png'?>">
			</span>
			<img  class="ccas_ajax_pro_search_loader" src="<?php echo CED_CAF_PLUGIN_DIR_URL.'images/ajax-loader.gif'?>" style="display: none;">
			<div id="suggesstion-box_ccas_" style="display: none;"></div>
			<div style="clear:both;"></div>
		</div>
		<?php 
		$this->widget_end( $args );
	}
}
?>