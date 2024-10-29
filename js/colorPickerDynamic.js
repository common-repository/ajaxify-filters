// updated
jQuery( document ).ready( function( $ ) {
	$( document ).find( 'div[id*="ccas_ajax_layered_nav"]' ).ajaxComplete( function() {
		applyColorPicker();
	});

	function applyColorPicker() {
		var params = { 
			change: function(e, ui) {
				$( e.target ).val( ui.color.toString() );
$( e.target ).trigger('change'); // enable widget "Save" button
  },
}

$('.ccas_colorpicker_input').wpColorPicker( params );

};

$( 'input.ccas_multiple_filter' ).each(function() {
	if( $( this ).is( ':checked' ) ) {
		$( this ).parent().next( 'p' ).show();
	} else {
		$( this ).parent().next( 'p' ).hide();
	}
});

$( document.body ).on( 'change', 'input.ccas_multiple_filter', function( e ) {
	if( $( this ).is( ':checked' ) ) {
		$( this ).parent().next( 'p' ).show();
	} else {
		$( this ).parent().next('p').hide();
		$( this ).parent().next('p').find( 'select.ccas_query_type' ).val( "and" ).attr( "selected", "selected" );
	}
 });
});
