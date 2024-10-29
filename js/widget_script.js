	
	jQuery(document).ready(function() {

	//setting for first time
	jQuery('.ccas_extract_display_type').each(function (i, e) {

		if(jQuery(this).val() == "label" || jQuery(this).val() == "picker")
		{
			var temp = jQuery(this).attr("name").split("]");
			temp=temp[0]+']'; 
			var thisRef=temp;
			
			var instanceRef=jQuery(this).parent('p').siblings('div.instance-ref-div').text();
			instanceRef=jQuery.parseJSON(jQuery.makeArray(instanceRef));

			var selectedAttribute=jQuery(this).parent('p').prev().find('select').val();
			
			makeDynamicHTML(instanceRef,thisRef,jQuery(this).val(),this,selectedAttribute);
		}

	});
	
	});
	


	//handling attribute change
	jQuery(document.body).on('change','.ccas_extract_current_attribute',function (){

		var temp = jQuery(this).attr("name").split("]");
		temp=temp[0]+']'; 
		
		var showType=jQuery(this).parent('p').next().find('select.ccas_extract_display_type').val();

		if(showType == "label" || showType == "picker")
		{
			var instanceRef=jQuery(this).parent('p').siblings('div.instance-ref-div').text();
			instanceRef=jQuery.parseJSON(jQuery.makeArray(instanceRef));

			var thisRef=temp;
			makeDynamicHTML(instanceRef,thisRef,showType,this,jQuery(this).val());
		}	
		
	});	


	//handling show option change
	jQuery(document.body).on('change','.ccas_extract_display_type',function (){
		
		var temp = jQuery(this).attr("name").split("]");
		temp=temp[0]+']'; 
		
	    if(jQuery(this).val() == "label" || jQuery(this).val() == "picker")
		{
			var instanceRef=jQuery(this).parent('p').siblings('div.instance-ref-div').text();
			instanceRef=jQuery.parseJSON(jQuery.makeArray(instanceRef));

			var thisRef=temp;
			
			var selectedAttribute=jQuery(this).parent('p').prev().find('select').val();
			
			makeDynamicHTML(instanceRef,thisRef,jQuery(this).val(),this,selectedAttribute);

		}
		else
		{
			jQuery(this).parent('p').siblings('div.dynamic-hidden-div').html("");
			jQuery(this).parent('p').siblings('div.dynamic-hidden-div').hide();
		}	

	});


	//main ajax function for getting dynamic HTML
	function makeDynamicHTML(instanceRef,thisRef,showType,currentRef,selectedAttribute)
	{
		jQuery.ajax({
			url : widget_script_ajax.ajax_url,
			type : 'post',
			data : {
						action : 'ccas_dynamicHTML',
						instanceRef : instanceRef,
						thisRef : thisRef,
						selectedOpt : showType,
						selectedAttribute : selectedAttribute
					   },
			success : function( data ) 
			{
				jQuery(currentRef).parent('p').siblings('div.dynamic-hidden-div').html(data);
				jQuery(currentRef).parent('p').siblings('div.dynamic-hidden-div').show();
			}
		});
		
	}
