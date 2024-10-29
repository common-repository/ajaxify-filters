jQuery( document ).ready( function( $ ) {
	var currenySymbol = $('#ccas_ajax_hidden_currency_symbol').text();
	var minPrice = parseInt($('#ccas_ajax_hidden_min_price').text());
	var maxPrice = parseInt($('#ccas_ajax_hidden_max_price').text());
	var minPriceRange = parseInt($('#ccas_ajax_hidden_min_price_range').text());
	var maxPriceRange = parseInt($('#ccas_ajax_hidden_max_price_range').text());
	
	$( "#sliderOfPriceWidget" ).slider({
		range:true,
		min: minPrice,
		max: maxPrice,
		values: [ minPriceRange, maxPriceRange ],
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
			
			var newURL = baseURL+"?";
			newURL += 'min_price='+ui.values[ 0 ]+'&max_price='+ui.values[ 1 ];
			
			if(jQuery.type(restURL) != 'undefined')
			{
				var getArray = restURL.split('&');
				
				var count = 0;
				for(count=0;count<getArray.length;count++)
				{
					var test1 = "min_price".localeCompare(getArray[count].split('=')[0]);
					var test2 = "max_price".localeCompare(getArray[count].split('=')[0]);
	
					if(test1 === 0 || test2 === 0)
					{
					}
					else
					{
						newURL += '&'+getArray[count];
					}	

				}	
			}
			/*
			 * URL making code ends here
			 */
			
			// pushing the new URL to nrowser history and calling core ajax function
			window.history.pushState('','',newURL);
			ajaxifyShop( newURL );

		},
	});
	
	$( "#price" ).val( currenySymbol + $( "#sliderOfPriceWidget" ).slider( "values", 0 ) +
			" - " + currenySymbol + $( "#sliderOfPriceWidget" ).slider( "values", 1 ) );
	
});
