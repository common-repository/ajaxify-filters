<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* widget to save active filters.
*
* @class    CED_CAF_Widget_Save_Active_Filters
* @version  1.0.0
* @category Class
* @author   CedCommerce
*/
class CED_CAF_Widget_Save_Active_Filters extends CED_CAF_Widget 
{

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'ccas_ajax_save_active_filters';
		$this->widget_description = __( 'Shows a widget which lets you save applied filter to be used later.', 'ajaxify-filters' );
		$this->widget_id          = 'ccas_ajax_save_active_filters';
		$this->widget_name        = __( 'AJAX Save Active Filters', 'ajaxify-filters' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Save Active Filters', 'ajaxify-filters' ),
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
		global $_chosen_attributes;
		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) 
		{
			return;
		}
		$cookie_name = 'ced_caf_saved_filters';
		
		if(isset($_COOKIE[$cookie_name]))
		{
			$cookieArray = $_COOKIE[$cookie_name];
			$cookieArray = stripslashes($cookieArray);
			$cookieArray = json_decode($cookieArray, true);
		}
		$title="Saved Filter";
		$counter=1; 
		
		$this->widget_start( $args, $instance );
		echo '<span class="ced_caf_saced_filter_div">';
		if(isset($cookieArray) && is_array($cookieArray))
		{
			foreach ($cookieArray as $cookie)
			{
				echo '<a href="'.$cookie.'" >'.$title.'-'.$counter.'</a>';
				echo '<br/>';
				$counter++;
			}
		}
		echo '</span>';
		echo '<button id="ced_caf_save_active_filters" >'. __( 'Save', CED_CAF_TXTDOMAIN ) .'</button>';
		
		$this->widget_end( $args );
	}
}
