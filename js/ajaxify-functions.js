/**
 * code jQuery function to fetch and replace products
 */
function ajaxifyShop( sourceURL ) {
	jQuery( 'div.ccas_ajax_shop_loading_div' ).show();
	jQuery.ajax({
		url : sourceURL,
		type : 'get',
		success : function( data ) {
			/**
			 * showing changed product content
			 */
			if( typeof( jQuery( 'ul.products', jQuery( data ) ).html() ) == "undefined" ) {
				jQuery( document ).find( 'ul.products' ).addClass( 'ccas_hidden_item' );
				jQuery( document ).find( 'form.woocommerce-ordering' ).addClass( 'ccas_hidden_item' );
				jQuery( document ).find( '.ccas_items_info_wrapper_parent' ).removeClass( 'ccas_hidden_item' );
			} else {
				if ( jQuery( document ).find( 'ul.products' ).hasClass( 'ccas_hidden_item' ) && jQuery( document ).find( '.ccas_items_info_wrapper_parent' ).hasClass( 'ccas_hidden_item' ) ) {
					window.location = '';
				} else if ( typeof( jQuery( 'ul.products' ).html() ) == "undefined" && jQuery( document ).find( '.ccas_items_info_wrapper_parent' ).hasClass( 'ccas_hidden_item' ) ) {
					window.location = '';
				}

				jQuery( document ).find( '.ccas_items_info_wrapper_parent' ).addClass( 'ccas_hidden_item' );
				jQuery( 'ul.products' ).removeClass( 'ccas_hidden_item' );
				jQuery( 'form.woocommerce-ordering' ).removeClass( 'ccas_hidden_item' );
				var pageContent = jQuery( 'ul.products', jQuery( data ) ).html();
				jQuery( 'ul.products' ).html( pageContent );
			}
			
			/**
			 * changing product count text
			 */
			var proCountText = jQuery( 'p.woocommerce-result-count', jQuery( data ) ).html();
			if( jQuery.type( proCountText ) != 'undefined' ) {
				jQuery( 'p.woocommerce-result-count' ).show();
				jQuery( 'p.woocommerce-result-count' ).html( proCountText );
			} else {
				jQuery( 'p.woocommerce-result-count' ).hide();
			}

			/**
			 * changing breadcrum
			 */
			var breadcrum = jQuery( 'nav.woocommerce-breadcrumb', jQuery( data ) ).html();
			if( jQuery.type( breadcrum ) != 'undefined' ) {
				jQuery( 'nav.woocommerce-breadcrumb' ).show();
				jQuery( 'nav.woocommerce-breadcrumb' ).html( breadcrum );
			} else {
				jQuery( 'nav.woocommerce-breadcrumb' ).hide();
			}
			
			/**
			 * changing pagination
			 */
			var pagination = jQuery( 'nav.woocommerce-pagination', jQuery( data ) ).html();
			if( jQuery.type( pagination ) != 'undefined' ) {
				jQuery( 'nav.woocommerce-pagination' ).show();
				jQuery( 'nav.woocommerce-pagination' ).html( pagination );
			} else {
				jQuery( 'nav.woocommerce-pagination' ).hide();
			}

			/*
			 * code for showing filters in sidebars::product attribute filters (1)
			 */
			var filterContentArray = [];
			var filterIdsArray = [];
			jQuery( '.ccas_ajax_layered_nav_widget_id', jQuery( data ) ).each( function() {
				filterContentArray.push( jQuery( this ).html() );
				filterIdsArray.push( jQuery( this ).attr( 'id' ) );
			});

			var counter = 0;
			jQuery( '.ccas_ajax_layered_nav_widget_id' ).each( function() {
				if( jQuery( this ).attr( 'id' ) == filterIdsArray[counter] ) {
					jQuery( this ).html( filterContentArray[counter] );
					jQuery( this ).show();
					counter++;
				} else {
					jQuery( this ).hide();
				}	
				
			});

			/*
			 * code for price filter (2)
			 */
			var priceFilterHTML = jQuery('.ccas_ajax_price_filter_widget',jQuery(data)).html();
			
			if( jQuery.type( priceFilterHTML ) != 'undefined' ) {
		      	jQuery( function( $ ) {
		      		var currenySymbol 	= $( '#ccas_ajax_hidden_currency_symbol', $( data ) ).text();
		      		var minPrice 		= parseInt( $( '#ccas_ajax_hidden_min_price', $( data ) ).text() );
		      		var maxPrice 		= parseInt( $( '#ccas_ajax_hidden_max_price', $( data ) ).text() );
		      		var minPriceRange 	= parseInt( $( '#ccas_ajax_hidden_min_price_range', $( data ) ).text() );
		      		var maxPriceRange 	= parseInt( $( '#ccas_ajax_hidden_max_price_range', $( data ) ).text() );
		      		
		      		$( "#sliderOfPriceWidget" ).slider({
		      			range 	:true,
		      			min 	: minPrice,
		      			max 	: maxPrice,
		      			values 	: [ minPriceRange, maxPriceRange ],
		      			slide: function( event, ui ) {
		      				$( "#price" ).val( currenySymbol + ui.values[ 0 ] + " - " + currenySymbol + ui.values[ 1 ] );
		      			},
		      			stop: function( event, ui ) {
		      				/*
		      				 * code for creating the new URL using the existing one and current price filter choosen
		      				 */
		      				var currentURL = window.location.href;
		      				var URLparts = currentURL.split("?");
		      				var baseURL = URLparts[0];
		      				var restURL = URLparts[1];
		      				
		      				var newURL = baseURL + "?";
		      				newURL += 'min_price=' + ui.values[ 0 ] + '&max_price=' + ui.values[ 1 ];
		      				
		      				if( $.type( restURL ) != 'undefined' ) {
		      					var getArray = restURL.split( '&' );
		      					
		      					for( var count = 0; count < getArray.length; count++ ) {
		      						var test1 = "min_price".localeCompare( getArray[count].split( '=' )[0] );
		      						var test2 = "max_price".localeCompare( getArray[count].split( '=' )[0] );
		      		
		      						if( test1 !== 0 && test2 !== 0 ) {
		      							newURL += '&' + getArray[count];
		      						}
		      					}	
		      				}
		      				/** URL making code ends here **/
		      				
		      				// pushing the new URL to nrowser history and calling core ajax function
		      				window.history.pushState( '', '', newURL );
		      				ajaxifyShop( newURL );
		      			}
		      		});
		      		var minPriceSelected = $( "#sliderOfPriceWidget" ).slider( "values", 0 ),
		      			maxPriceSelected = $( "#sliderOfPriceWidget" ).slider( "values", 1 );

		      		$( "#price" ).val( currenySymbol + minPriceSelected + " - " + currenySymbol + maxPriceSelected );
		      	});
				jQuery( '.ccas_ajax_price_filter_widget' ).show();
			} 
			
			/*
			 * code for active filters (3)
			 */
			var activeFilterHTML = jQuery( '.ccas_ajax_active_filters', jQuery( data ) ).html();
			
			if( jQuery( '.ccas_ajax_active_filters ul', jQuery( data ) ).length < 1 ) {
				jQuery( '.ccas_ajax_active_filters' ).hide();
			} else {
				jQuery( '.ccas_ajax_active_filters' ).html( activeFilterHTML );
				jQuery( '.ccas_ajax_active_filters' ).show();
			}	
		
			
			/***** 
			 * code for category filter (4)
			 *****/
			var categoryFilterHTML = jQuery( '.ccas_ajax_category_filter', jQuery( data ) ).html();
			if( jQuery.type( categoryFilterHTML ) != 'undefined' ) {
				jQuery( '.ccas_ajax_category_filter' ).html( categoryFilterHTML );
				jQuery( '.ccas_ajax_category_filter' ).show();
			} else {
				jQuery('.ccas_ajax_category_filter').hide();
			}
			/** code for type-n-search (5) :: no replacement needed **/

			/***** 
			 * code for tag filter (6)
			 *****/
			var tagFilterHTML = jQuery( '.widget_product_tag_cloud', jQuery( data ) ).html();
			if( jQuery.type( tagFilterHTML ) != 'undefined' ) {
				jQuery( '.widget_product_tag_cloud' ).html( tagFilterHTML );
				jQuery( '.widget_product_tag_cloud' ).show();
			} else {
				jQuery( '.widget_product_tag_cloud' ).hide();
			}
			jQuery( 'div.ccas_ajax_shop_loading_div' ).hide();
		}
	});
}
