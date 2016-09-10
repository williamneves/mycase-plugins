

jQuery(function($  ){
   
   
   /* ============= setting prices dynamically on product page ============= */
	$(".nm-productmeta-box").find('select,input:checkbox,input:radio').on('click', function(){
		
		
		//disabling add to cart button for a while
		$('button[type="submit"]').prop('disabled', true);
		var option_prices = [];
				
		$(".nm-productmeta-box").find('select').each(function(i, item){
			option_price = $("option:selected", this).attr('data-price');
			fixedfee = $(this).attr('data-onetime');
			fixedfee_taxable = $(this).attr('data-onetime-taxable');
			
			//console.log($(this).attr('id')+' '+$(this).closest('div').css('display'));
			if(option_price != undefined && option_price != '' && $(this).closest('div').css('display') != 'none'){
				option_prices.push({option: $(this).val(), price: option_price, isfixed: fixedfee, fixedfeetaxable:fixedfee_taxable});
			}				
			
			
		});
		
		$(".nm-productmeta-box").find('input:checkbox').each(function(i, item){
			option_price = $(this).attr('data-price');
			option_label = ($(this).attr('data-title') == undefined) ? $(this).val() : $(this).attr('data-title');	// for image type
			
			if($(this).is(':checked') && option_price != undefined && option_price != '' && $(this).closest('div').css('display') != 'none'){
				option_prices.push({option: option_label, price: option_price});
			}
							
		});
		
		$(".nm-productmeta-box").find('input:radio').each(function(i, item){
			option_price = $(this).attr('data-price');
			option_label = ($(this).attr('data-title') == undefined) ? $(this).val() : $(this).attr('data-title');	// for image type
			fixedfee = $(this).attr('data-onetime');
			fixedfee_taxable = $(this).attr('data-onetime-taxable');
			
			if($(this).is(':checked') && option_price != undefined && option_price != '' && $(this).closest('div').css('display') != 'none'){
				option_prices.push({option: option_label, price: option_price, isfixed: fixedfee, fixedfeetaxable: fixedfee_taxable});
			}
							
		});
		
		//console.log(fixedfees);
		if ($(".single_variation .price .amount").length > 0){
			$price = $(".single_variation .price");
			
			var base_amount = $(".single_variation .price .amount").html();
			base_amount = base_amount.replace ( /[^\d.]/g, '' );
			//enabling add to cart button
			
			
		}else{
			var base_amount = $("#_product_price").val();
		}
		
		
		//var product_base_price = $('#_product_price').val();
		var price_matrix = jQuery("#_pricematrix").val();
		var productmeta_id = jQuery("#_productmeta_id").val();
		var variation_id = jQuery(this).closest('form').find('input[name=variation_id]').val();
		
		var post_data = {action: 'nm_personalizedproduct_get_option_price', 
						optionprices:option_prices,
						baseprice:base_amount,
						pricematrix: price_matrix,
						productmeta_id: productmeta_id,
						variation_id: variation_id,
						qty: jQuery('input[name="quantity"]').val()
						};
		
		$.post(nm_personalizedproduct_vars.ajaxurl, post_data, function(resp){
			//console.log(resp);
			
			$(".amount-options").remove();
			if(resp.option_total > 0){
				var html = '<div class="amount-options">';
				html += resp.prices_html;
				html += '</div>';
			}			
			
			$('input[name="woo_option_price"]').val(resp.option_total);
			$('input[name="woo_onetime_fee"]').val(JSON.stringify( resp.onetime_meta ));
			
			//console.log(resp.display_price_hide);
			if (resp.display_price_hide !== 'yes'){
				$price.append(html);	
			}
			
			
			//enabling add to cart button
			$('button[type="submit"]').removeAttr('disabled');
			
		}, 'json');
		
	});
	
});