( function( $ ) {
	//code for saving fiters in Save Active filter plugin
	$( document.body ).on( 'click', '#ced_caf_save_active_filters' ,function() {
		var currentURL = window.location.href;
		$.ajax({
			url : pro_cat_script_ajax.ajax_url,
			type : 'post',
			data : {
				action : 'setCookieForSavingFilters',
				currentURL : currentURL
			},
			success : function( data ) {
				data = JSON.parse( data );
				var changesMade = data.changesMade;
				
				data = JSON.parse( data.cookieArray );
				
				var count = 0;
				$( 'span.ced_caf_saced_filter_div' ).html( '' );
				for ( var key in data ) {
					count++;
					$( 'span.ced_caf_saced_filter_div' ).append( '<a href="'+ data[key] +'" >Saved Filter-'+(count)+'</a><br/>' );
				}
				
				if( changesMade == "true_new" ) {
					alert( "Your curent combination of filters has been saved successfully." );
				} else if( changesMade == "true_replace" ) {
					alert( "Your curent combination of filters has been saved successfully. Your oldest combination of filters has been removed as you are only allowed to save 5." );
				} else {
					alert( "This combination of filters is already saved. Try another one." );
				}	
			}
		});
	});

	/*
	 * script for clicking on cancel button also
	 */
	$( document.body ).on( 'click', 'li.ccas_chosen', function( e ) {
		var hrefToBeUsed = $( this ).find( "a" ).first().attr( 'href' );

		//preventing the anchor tag to open the link
		e.preventDefault();

		//pushing the href to browser history
		window.history.pushState('', '', hrefToBeUsed );
		ajaxifyShop( hrefToBeUsed );
		window.scrollTo( 0, 0 );
	});



	/*
	 * script for fetching product text blink
	 */
	(function blink() { 
	    $('.blink_me').fadeOut(500).fadeIn(500, blink); 
	})();



	/*
	 * script to work with orderby without page-load
	 */
	$( 'select.orderby' ).addClass( 'orderbydev' );
	$( 'select.orderby' ).removeClass( 'orderby' );

	$( document.body ).on( 'change', '.woocommerce-ordering select.orderbydev', function( e ) {
		/*
		 * code for creating the new URL using the existing one and current price filter choosen
		 */
		var currentURL = window.location.href;
		var URLparts = currentURL.split("?");
		var baseURL = URLparts[0];
		var restURL = URLparts[1];
		
		
		var newURL = baseURL + "?";
		if( $.type( restURL ) == 'undefined' ) {
			newURL += 'orderby=' + this.value;
		} else if( $.type( restURL ) != 'undefined' ) {
			newURL += 'orderby=' + this.value;
			var getArray = restURL.split( '&' );
			var count = 0;
			for( count = 0; count < getArray.length; count++ ) {
				var test1 = "orderby" . localeCompare( getArray[count] . split( '=' )[0]);
				if( test1 !== 0 ) {
					newURL += '&' + getArray[count];
				}
			}	
		}

		/*
		 * URL making code ends here
		 */
		window.history.pushState( '', '', newURL );
		ajaxifyShop( newURL );
		window.scrollTo( 0, 0 );
	});



	/**
	 * using same class and script for category filter widget
	 * using same class and script for active filter widget
	 * script for handling the click on anchor-tags of attribte filters
	 */
	$( document.body ).on( 'click', 'a.ccas_ajax_attribute_filter_anchor_class', function( e ) {
		//preventing the anchor tag to open the link
		e.preventDefault();

		//pushing the href to browser history
		window.history.pushState( '', '', $( this ).attr( 'href' ) );
		ajaxifyShop( $( this ).attr( 'href' ) );
		window.scrollTo( 0, 0 );
	});


	/**
	 * script for handling the click on woocommerce-pagination 
	 */
	$( document.body ).on( 'click', 'nav.woocommerce-pagination li a', function( e ) {
		//preventing the anchor tag to open the link
		e.preventDefault();

		//pushing the href to browser history
		window.history.pushState( '', '', $( this ).attr( 'href' ) );
		ajaxifyShop( $( this ).attr( 'href' ) );
		window.scrollTo( 0, 0 );
	});


	//on browsers back button press
	$( document ).ready( function( $ ) {
		if ( window.history && window.history.pushState ) {
			$( window ).on( 'popstate', function() {
				ajaxifyShop( window.location.href );
				window.scrollTo( 0, 0 );
			});
		}
	});

	


	/*
	 * hiding the activeFilter section if there is nothing to show on page load/ or for first time
	 */
	if( $('.ccas_ajax_active_filters ul').length < 1 )
	{
		$('.ccas_ajax_active_filters').hide();
	}
})( jQuery );