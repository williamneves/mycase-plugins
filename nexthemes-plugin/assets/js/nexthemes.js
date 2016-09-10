/*!
 * NexThemes Plugin v1.0.0
 * Copyright 2015 Nexthemes
 */

(function($){
	"use strict";

	NexThemes._events = {
		prettyPhoto: function () {
			$("a[data-rel^='nth_prettyPhoto']").prettyPhoto({
				hook: 'data-rel',
				social_tools: false,
				theme: 'nth_pp_theme',//pp_default
				horizontal_padding: 20,
				opacity: 0.8,
				deeplinking: false
			});
		}
	}

	NexThemes.objs = {
		portfolio: {
			elements: $('.nth-portfolios-wrapper'),
			init: function(){
				this.elements.each(function(){
					var $this = $(this);
					var $grid = $this.find('.nth-portfolio-content').isotope({
						itemSelector: '.nth-portfolio-item',
						masonry: {}
					});

					$this.find('img').load(function(){
						$grid = $this.find('.nth-portfolio-content').isotope({
							itemSelector: '.nth-portfolio-item',
							masonry: {}
						});
					});
					
					$this.find('ul.nth-portfolio-filters li').on('click', function(){
						$(this).parent().find('li.active').removeClass( 'active' );
						$(this).addClass( 'active' );
						var filter_class = $(this).find('a').data('filter');
						$grid.isotope({ filter: filter_class });
					});

					$this.find('ul.nth-portfolio-filters.ajax_filter li').on('click', function( event ){
						event.preventDefault();

						var el = $(this);
						var container = el.parents('.nth-portfolio-container');

						el.trigger('nth_ajax_galleries_content', [container, $grid]);
					});

					$this.find('form.order-form').on('change', 'select', function(){
						var el = $(this);
						var container = el.parents('.nth-portfolio-container');
						el.trigger('nth_ajax_galleries_content', [container, $grid]);
					});
				});
				
			}
		},
		gridlistToggle: {
			elements: $('.nth-shop-meta-controls .gridlist-toggle'),
			init: function(){
				$('#grid').on('click', function(){
					$(this).addClass('active');
					$('#list').removeClass('active');
					$('#table').removeClass('active');
					$.cookie('gridcookie','grid', { path: '/' });
					var products = $(this).parents(".nth-shop-meta-controls").next(".row").find(".products");
					products.fadeOut(300, function() {
						$(this).addClass('grid').removeClass('table').removeClass('list').fadeIn(300);
					});
                    window.resetHeightProductInfo();
					return false;
				})

				$('#list').on('click', function() {
					$(this).addClass('active');
					$('#grid').removeClass('active');
					$('#table').removeClass('active');
					$.cookie('gridcookie','list', { path: '/' });
					var products = $(this).parents(".nth-shop-meta-controls").next(".row").find(".products");
					products.fadeOut(300, function() {
						$(this).removeClass('grid').removeClass('table').addClass('list').fadeIn(300);
					});
                   window.resetHeightProductInfo();
					return false;
				});

				$('#table').on('click', function() {
					$(this).addClass('active');
					$('#grid').removeClass('active');
					$('#list').removeClass('active');
					$.cookie('gridcookie','table', { path: '/' });
					var products = $(this).parents(".nth-shop-meta-controls").next(".row").find(".products");
					products.fadeOut(300, function() {
						$(this).removeClass('grid').removeClass('list').addClass('table').fadeIn(300);
                        window.resetHeightProductInfo();
                        /*window.setHeightProductInfo(false);*/
                        setTimeout(function(){ window.setHeightProductInfo(false); }, 400);
					});
                    /*window.resetHeightProductInfo();
                    setTimeout(function(){ window.setHeightProductInfo(false); }, 400);*/

					return false;
				});

				if ($.cookie('gridcookie')) {
					var products = $(".nth-shop-meta-controls").next(".row").find(".products");
					products.removeClass('table').removeClass('list').removeClass('grid').addClass($.cookie('gridcookie'));
				}

				if ($.cookie('gridcookie') == 'grid') {
					$('.gridlist-toggle #grid').addClass('active');
					$('.gridlist-toggle #list').removeClass('active');
					$('.gridlist-toggle #table').removeClass('active');
				}

				if ($.cookie('gridcookie') == 'list') {
					$('.gridlist-toggle #list').addClass('active');
					$('.gridlist-toggle #grid').removeClass('active');
					$('.gridlist-toggle #table').removeClass('active');
				}
				if ($.cookie('gridcookie') == 'table') {
					$('.gridlist-toggle #table').addClass('active');
					$('.gridlist-toggle #grid').removeClass('active');
					$('.gridlist-toggle #list').removeClass('active');
				}

				$('.gridlist-toggle a').click(function(event) {
					event.preventDefault();
				});

			}
		},
		ajaxseach: {
			elements: $('#header .nth-search-wrapper [name=s]'),
			init: function(){
				NexThemes.data.search = NexThemes.data.search || {};
				NexThemes.data.search.min_char = NexThemes.data.search.min_char || 3;
				NexThemes.data.search.result_limit = NexThemes.data.search.result_limit || 3;
				//icon-nth-loading
				var $s = this.elements;
				this.elements.autocomplete({
					minChars: NexThemes.data.search.min_char,
					triggerSelectOnValidInput: false,
					maxHeight: 'auto',
					serviceUrl: theshopier_data.ajax_url + '?action=nth_ajax_search_products',
					onSearchStart: function(){
						var loading_e = $s.parents('.nth-search-wrapper').find('.ajax-loading-icon');
						if( loading_e.length ) {
							loading_e.show();
						} else {
							var l_icon = '<span class="fa-spin ajax-loading-icon icon-nth-loading"></span>';
							$s.parents('.nth-search-wrapper').append(l_icon);
						}

					},
					onSearchComplete: function(query,suggestion){
						$('.autocomplete-suggestions .autocomplete-suggestion').each(function(i, e){
							var i = $(this).data("index");
							if(suggestion[i]['id'] == -1) return false;
							var title = $(this).html();
							if( $(this).find('.product-title').length <= 0 )
								$(this).html('<h3 class="suggestion-title">'+title+'</h3>');

							if( $(this).find('img.suggestion-thumbnail').length <= 0 )
								$(this).prepend(suggestion[i]['img']);

							if( $(this).find('.suggestion-cats').length <= 0 && typeof suggestion[i]['cats'] != "undefined" ) {
								$(this).append(suggestion[i]['cats']);
							}

							if( $(this).find('.suggestion-prices').length <= 0 && typeof suggestion[i]['price'] != "undefined" ) {
								$(this).append(suggestion[i]['price']);
							}
						});
						$s.parents('.nth-search-wrapper').find('.ajax-loading-icon').hide();
					},
					onSelect: function (suggestion) {
						if (suggestion.id != -1){
							window.location.href = suggestion.url;
						}
					}
				});
			}
		},
		gallery: {
			elements: $('.gallery-content'),
			init: function(){
				this.elements.each(function(){
					var $this = $(this);

					if( $this.hasClass('gallery-style-2') ) {

						var wrap = $this.find('.galleries-wrapper'),
							def = {
								fullscreen: {
									enabled: true,
									nativeFS: true
								},
								controlNavigation: 'thumbnails',
								autoScaleSlider: true,
								autoScaleSliderWidth: 1170,
								autoScaleSliderHeight: 670,
								loop: false,
								imageScaleMode: 'fit-if-smaller',
								navigateByClick: true,
								numImagesToPreload:2,
								arrowsNav:true,
								arrowsNavAutoHide: true,
								arrowsNavHideOnTouch: true,
								keyboardNavEnabled: true,
								fadeinLoadedSlide: true,
								globalCaption: false,
								globalCaptionInside: false,
								autoPlay: {
									enabled: true,
									pauseOnHover: true,
									delay: 5000
								},
								thumbs: {
									appendSpan: true,
									firstMargin: true,
									paddingBottom: 4
								}
							},
							ops = wrap.data('options'),
							conf = $.extend(def, ops);

						$('#nth_galleries').royalSlider(conf);
					}
				});

			}
		},
		masonry_gallery: {
			elements: $('.gallery-content'),
			gallery_grid: null,
			k: 0,
			init: function(){
				this.elements.each(function(){
					var $this = $(this);
					NexThemes.objs.masonry_gallery.gallery_grid = $this.find('.nth-isotope-1').isotope({
						percentPosition: true,
						itemSelector: '.gallery-image-item',
						masonry: {}
					});

					NexThemes.objs.masonry_gallery.gallery_grid.imagesLoaded().progress(function(){
						NexThemes.objs.masonry_gallery.gallery_grid.isotope('layout');
					});

					NexThemes.objs.masonry_gallery.k = parseInt($this.attr('data-k'));

					$this.on('click', '.nth_gallery_load_more', function(e){
						e.preventDefault();
						var btn_el = $(this);

						if( NexThemes.objs.masonry_gallery.k >= $this.data('max') ) return false;

						$.ajax({
							type: 'POST',
							url: NexThemes.ajax_url,
							dataType: 'json',
							data: {
								k: NexThemes.objs.masonry_gallery.k,
								l: $this.attr('data-number'),
								post_id: $this.attr('data-post_id'),
								action: 'nth_gallery_items'
							},
							beforeSend: function(){
								$.data(btn_el, 'txt_html', btn_el.html());
								btn_el.prop('disabled', true);
								btn_el.find('i.fa').addClass('fa-spin');
								var _txt = btn_el.data('loading_text') || 'Loading...';
								btn_el.find('span').html(_txt);
							},
							success: function (data) {
								var $item = $(data.element);
								NexThemes.objs.masonry_gallery.k = parseInt(data.k);
								NexThemes.objs.masonry_gallery.gallery_grid.append( $item ).isotope( 'appended', $item);

								NexThemes.objs.masonry_gallery.gallery_grid.imagesLoaded().progress(function(){
									NexThemes.objs.masonry_gallery.gallery_grid.isotope('layout');
								});

								NexThemes._events.prettyPhoto();

								btn_el.prop('disabled', false);
								btn_el.find('i.fa').removeClass('fa-spin');
								btn_el.html($.data(btn_el, 'txt_html'));

								if( data.k >= $this.data('max') ) btn_el.remove();
							}
						});
					});

				});
			}
		},
		instagram_shortcode: {
			elements: $('.instagram-media-ajax'),
			init: function(){
				this.elements.each(function(){
					var $this = $(this);
					$.ajax({
						type: 'POST',
						url: NexThemes.ajax_url,
						//dataType: 'json',
						data: {
							action: 'nth_instagram_get_media',
							limit: $this.data('limit'),
							thumbnail: $this.data('thumbnail')
						},
						beforeSend: function(){

						},
						success: function (data) {
							$this.html(data);

							var defaults = {
								loop: false,
								nav: true,
								autoplay: false,
								autoplayTimeout: 5000,
								dots: false,
								lazyLoad: false,
								navRewind: false,
								mouseDrag: false,
								onInitialized: function(event){
									$this.addClass('initialized').removeClass('nth-loading');
								}
							};
							if(typeof theshopier_data !== 'undefined' && theshopier_data.rtl == '1') {
								defaults.rtl = true;
							}

							var slider = $this.data('slider');
							var base = $this.data('base');

							var config = $.extend({}, defaults, $this.data("options"));

							if( typeof base !== "undefined" && base == 1 ) {
								config.responsiveBaseElement = $this;
							}

							if( typeof slider !== "undefined" && slider !== 'this' ) {
								var owl = $this.find(slider);
							} else {
								var owl = $this;
							}

							owl.owlCarousel(config);

						}
					});
				});
			}
		}
	}


	$(document).ready(function(){
		NexThemes._events.prettyPhoto();

		$.each( NexThemes.objs, function( key, value ){
			if(value.elements.length) {
				value.init();
			}
		} );

	});

	$(document).on('nth_ajax_galleries_content', function(event, container, grid){
		var termid = container.find('.nth-portfolio-filters li.active a').data('termid');
		var orderby = container.find('form.order-form select[name=orderby]').val();
		var order = container.find('form.order-form select[name=order]').val();
		var paged = container.find('ul.nth-portfolio-filters').data('paged');
		var data = {
			action: 'nth_gallery_content',
			term_id: termid,
			orderby: orderby,
			order: order,
			paged: paged
		};

		var appent = container.find('.nth-portfolio-content');

		$.ajax({
			type: 'POST',
			url: NexThemes.ajax_url,
			data: data,
			beforeSend: function(){
				appent.addClass('nth-loading');
			},
			success: function( res ){
				appent.removeClass('nth-loading').html(res);
				grid.isotope('destroy');
				appent.isotope({
					itemSelector: '.nth-portfolio-item',
					masonry: {}
				});
			}
		});
	});

    window.setHeightProductInfo = function (load) {
        var selector;
        if(load) {
             selector = $(".products.table .product-meta-wrapper");
        }
        else
            selector = $(".product-meta-wrapper");

       selector.each(function( ){
            var prod_info = $(this).find('.prod-meta-relative');
            var prod_price = $(this).find('.prod-meta-hover');
            var prod_info_height = $(prod_info).height();
            var prod_price_height = $(prod_price).height();

            if(prod_info_height > prod_price_height) {
                $(prod_price).height(prod_info_height);
            }
            else {
                $(prod_info).height(prod_price_height);
            }
        });
    }

    window.resetHeightProductInfo = function() {
        $(".product-meta-wrapper").each(function(){
            $(this).find('.prod-meta-relative').removeAttr('style');
            $(this).find('.prod-meta-hover').removeAttr('style');
        });
    }
	
})(jQuery);