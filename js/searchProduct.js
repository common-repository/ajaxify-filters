
		var currentRequest = null;
		
		jQuery(document.body).on('keyup',"#search-box_ccas_",function(){
			
			if(jQuery("#search-box_ccas_").val() == "")
			{
				return false;
			}
			
			jQuery(".ccas_ajax_pro_search_loader").show();
		
			currentRequest = jQuery.ajax({
				url : searchProduct_ajax.ajax_url,
				type : 'post',
				data : {
							action : 'searchProductAjaxify',
							term : jQuery(this).val()
						},
				beforeSend : function()    
				{           
					if(currentRequest != null) 
					{
			            currentRequest.abort();
			        }
			    },		
				success : function( data ) 
				{	
					jQuery(".ccas_ajax_pro_search_loader").hide();
					
					jQuery("#suggesstion-box_ccas_").show();
					jQuery("#suggesstion-box_ccas_").html(data);
				}
				
			});
			
			if(jQuery(this).val() == '')
			{
				jQuery("#suggesstion-box_ccas_").hide();
				jQuery("#suggesstion-box_ccas_").html('');
			}
			
		});

		jQuery(document.body).on('click','span.ccas_pro_cross_class',function(){
			jQuery("#suggesstion-box_ccas_").hide();
			jQuery("#suggesstion-box_ccas_").html('');
			jQuery("#search-box_ccas_").val("");
		});
		