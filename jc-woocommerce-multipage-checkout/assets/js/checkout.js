// multistep checkout scriptage
jQuery(function($){
	
	if($('body').hasClass('woocommerce-order-pay') || $('body').hasClass( 'woocommerce-order-received' ) ){
		return;
	}

	var msg = {
		'validateRequired': jcmc['text']['validation_required'],
		'validateEmail': jcmc['text']['validation_email']
	};

	$checkout_form = $( 'form.checkout' );

	var sections = jcmc.checkout_steps;

	var page_numbers = true;
	// var order_review_index = 2;
	var tabs = '';
	var outside_tabs = '';
	var active = 0;
	var page = 0;
	var style = ''; //"jcmc-arrows";
	var wrapper_style = ''
	var button_style = '';

	// load in settings
	if(jcmc['tab_style'] == "arrows" || jcmc['tab_style'] == "progress"){
		style += " jcmc-" + jcmc['tab_style'];
	}else{
		style += " jcmc-tabs-default";
	}
	if(jcmc['tab_style'] != "progress"){
		style += " jcmc-blocks";
	}

	if(jcmc['tab_size'] == "sm" || jcmc['tab_size'] == "lg"){
		style += " jcmc-" + jcmc['tab_size'];
	}

	if(jcmc['hide_step_numbers'] == "yes"){
		style += " jcmc-no-numbers";
	}

	// only left / right works if block style
	// todo: future make work with arrows
	if( ( jcmc['tab_alignment'] == "left" || jcmc['tab_alignment'] == "right") && jcmc['tab_style'] == "block" ){
		wrapper_style += " jcmc-tabs-" + jcmc['tab_alignment'];
	}else{
		wrapper_style += " jcmc-tabs-top";
	}

	if(jcmc['tab_full_width'] == 'yes'){
		style += " jcmc-wide";
	}

	if(jcmc['button_size'] == "sm" || jcmc['button_size'] == "lg"){
		button_style += " jcmc-" + jcmc['button_size'];
	}

	$checkout_form.find(' > *').hide();

	// wrap
	$checkout_form.wrap('<div id="jcmc-wrap" class="'+wrapper_style+'">');
	$checkout_form.wrap('<div  id="jcmc-tab-panels" class="jcmc-tab-wrapper">');

	$.each(sections, function(index, item){

		var classes = '';
		var link_classes = '';
		var before = '';
		var after = '';
		var page_number = '';

		if(page_numbers){
			page_number = '<span class="jcmc-number">' + ( index + 1 ) + '.</span>';
		}

		if(active == index){
			classes = 'jcmc-active-tab';
			link_classes = 'jcmc-active-link';
		}

		if(active >= index){
			link_classes += ' jcmc-enabled';
		}

		// link_classes as odd even
		if( ( index % 2 ) == 1 ){
			link_classes += ' jcmc-even';
		}else{
			link_classes += ' jcmc-odd';
		}

		tabs += '<li class="' + link_classes + '"><a href="#jcmc-tab-' + index + '" id="jcmc-trigger-' + index + '"><span class="jcmc-tab-span">'+page_number+item.name+'</span></a></li>';

		var current_tab = $('<div id="jcmc-tab-' + index + '" class="jcmc-tab ' + classes + '" />');
		if(item.in_form != undefined && item.in_form == 'no'){
			$( current_tab ).appendTo('#jcmc-wrap .jcmc-tab-wrapper');
		}else{
			$( current_tab ).appendTo('#jcmc-wrap .jcmc-tab-wrapper > form');
		}

		$.each(item.selector_parts, function(index, value){
			current_tab.append($(value).show());
		});

		current_tab.append('<div style="clear:both;"></div>');

		if(item.text_before !== undefined){
			current_tab.prepend(item.text_before);
		}

		if(item.text_after !== undefined){
			current_tab.append(item.text_after);
		}
	});

	$('#jcmc-wrap').prepend('<ul id="jcmc-tabs" class="jcmc-tabs '+ style +'">' + tabs + '</ul>');
	$('#jcmc-wrap').append('<div class="jcmc-buttons ' + button_style + '"><a href="#" class="jcmc-button jcmc-nextprev jcmc-prev">'+jcmc['text']['btn_prev']+'</a> <a href="#" class="jcmc-button jcmc-nextprev jcmc-next">'+jcmc['text']['btn_next']+'</a> <button type="button" class="jcmc-button jcmc-nextprev jcmc-order alt">'+jcmc['text']['btn_order']+'</a></div>');

	// setup, now display
	$('#jcmc-wrap').show();

	$checkout_form.on('jcmc_change_page', function(event, tab_link){

		// set current index and page
		active = $('.jcmc-tabs a').index(tab_link);
		if(page < active){
			$('.jcmc-tabs li:eq(' + active + ')').addClass('jcmc-enabled');
			page = active;
		}


		if(active == sections.length-1){
			// last section
			$('.jcmc-next').hide();
			$('.jcmc-order').show();
			$('.jcmc-prev').show();

		}else if(active == 0){
			// first section
			$('.jcmc-prev').hide();
			$('.jcmc-order').hide();
			$('.jcmc-next').show();
		}else{
			// default sections
			$('.jcmc-next').show();
			$('.jcmc-order').hide();
			$('.jcmc-prev').show();
		}

		$('.jcmc-tab').removeClass('jcmc-active-tab');
		$('.jcmc-tabs li').removeClass('jcmc-active-link');
		tab_link.parent().addClass('jcmc-active-link');
		$(tab_link.attr('href')).addClass('jcmc-active-tab');

		$('html, body').animate({
	        scrollTop: $("#jcmc-wrap").offset().top
	    }, 500);
	});

	// generate validation messages
	$checkout_form.find('.validate-required').append('<p class="jcmc-required"></p>');
	$checkout_form.find('.validate-required .jcmc-required').text(msg.validateRequired);
	$checkout_form.find('.validate-email .jcmc-required').text(msg.validateEmail);

	// tabs
	$('.jcmc-tabs a').on('click', function(event){

		event.preventDefault();

		// limit to section reached + 1
		if(page + 1 < $('.jcmc-tabs a').index($(this))){
			return false;
		}

		// trigger form validation
		$('.jcmc-active-tab .input-text, .jcmc-active-tab select').trigger('change');
		$('.jcmc-active-tab').trigger('jcmc_validation', page);

		if( ( $('.jcmc-active-tab .woocommerce-invalid-required-field:visible').length == 0 && !$('.jcmc-active-tab').hasClass('jcmc-invalid') ) || page > $('.jcmc-tabs a').index($(this)) ){

			$checkout_form.trigger('jcmc_change_page', [$(this)]);
		}else{

			// scroll up and show the validation errors
			$('html, body').animate({
		        scrollTop: $("#jcmc-wrap").offset().top
		    }, 500);
		}
	});

	var timeouts = false;

	// disable order button on update_checkout
	$( document.body ).bind( 'update_checkout', function(){
		$('.jcmc-order').prop('disabled', true);
	});

	// enable order button after updated_checkout
	$( document.body ).bind( 'updated_checkout', function(){

		if(timeouts){
			window.clearTimeout(timeouts);
		}

		timeouts = window.setTimeout(function(){
			if($('#place_order').is(':disabled')){
				$('.jcmc-order').prop('disabled', true);
			}else{
				$('.jcmc-order').prop('disabled', false);
			}
		}, 100);
	});

	// next/prev buttons
	$('.jcmc-nextprev').on('click', function(event){

		if($(this).hasClass('jcmc-next')){

			$('.jcmc-active-link').next().find('a').trigger('click');
		}else if($(this).hasClass('jcmc-order')){

			$checkout_form.submit();
		}else{

			// avoid validation
			if($('.jcmc-active-link').prev().length > 0){
				$checkout_form.trigger('jcmc_change_page', [ $('.jcmc-active-link').prev().find('a') ]);
			}
			//
			// $('.jcmc-active-link').prev().find('a').trigger('click');
		}

		event.preventDefault();
	});

	// on basket update show basket screen if that far through fields, removed@05/02/16 due to payment section on
	// last tab causing redirect every time if order details arn't on same page
	// $('body').on('update_checkout', function(){
	// 	if(active > order_review_index){
	// 		$checkout_form.trigger('jcmc_change_page', [$('.jcmc-tabs a:eq('+order_review_index+')')]);
	// 	}
	// });

	// set complete order button to dynamic button value
	$checkout_form.on('click', '.payment_methods input.input-radio', function(){
		$('.jcmc-order').text($('#place_order').val())
	});
	$('.jcmc-order').text($('#place_order').val())

	// hide payment button
	$checkout_form.find('#payment .place-order').hide();

	// open first tab
	$checkout_form.trigger('jcmc_change_page', [$('.jcmc-tabs a:eq(0)')]);

});