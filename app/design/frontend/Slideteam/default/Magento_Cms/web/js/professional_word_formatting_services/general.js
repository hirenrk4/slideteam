require(['jquery','owl_carausel','magnific_popup'], function ($)
{
	$(document).ready(function ()
	{
		$.fn.extend(
		{
			equalHeights: function () {
				var top = 0;
				var row = [];
				var classname = ('equalHeights' + Math.random()).replace('.', '');
				$(this).each(function () {
					var thistop = $(this).offset().top;
					if (thistop > top) {
						$('.' + classname).removeClass(classname);
						top = thistop;
					}
					$(this).addClass(classname);
					$(this).height('auto');
					var h = (Math.max.apply(null, $('.' + classname).map(function () {
						return $(this).outerHeight();
					}).get()));
					$('.' + classname).height(h);
				}).removeClass(classname);
			}
		});

		$('a.grouped_elements').magnificPopup(
		{
			type: 'image',
			mainClass: 'formatting-modal',
			tLoading: '',
			fixedContentPos: false,
			gallery: 
			{
				enabled: true,
				tCounter: '%curr% / %total%',
				navigateByImgClick: true,
			},
			callbacks: 
			{
				buildControls: function () 
				{
					// re-appends controls inside the main container
					this.contentContainer.append(this.arrowLeft.add(this.arrowRight));
				},
				resize: changeImgSize,
				imageLoadComplete: changeImgSize,
				change: changeImgSize,
			},
			image: 
			{
				verticalFit: true,
				markup: '<div class="mfp-figure">' +
				'<div class="mfp-close"></div>' +
				'<div class="mfp-img"></div>' +
				'<div class="mfp-bottom-bar">' +
				'<div class="mfp-counter"></div>' + '</div>' + '</div>', // Popup HTML markup. `.mfp-img` div will be replaced with img tag, `.mfp-close` by close button
			},
			closeMarkup: '<button title="Close (Esc)" type="button" class="mfp-close"></button>',
		});

		if($(".sample-work-carousel").length>0)
		{
			$('.sample-work-carousel').owlCarousel({
				loop:true,
				margin:1,
				responsiveClass:true,
				autospeed:4000,
				nav: true,
				dots: false,
				responsive:{
					0:{
						items:1,
					},
					600:{
						items:1,
					},
					1000:{
						items:1,
						loop:false
					}
				}
			});
		}
		proEqualheight();
	});

	$(window).resize(function()
	{
		proEqualheight();
	});

	function changeImgSize() 
	{
		var appVersion = navigator.appVersion;
		var wrapper = $(".mfp-wrap");

		var img = this.content.find('img');
		var popupH;

		if ((/iphone|ipad|ipod/gi).test(appVersion) || $(window).height() < 500) 
		{
			wrapper.css("height", $(window).height() / 3);
			popupH = $(window).height() * 0.65;
		} 
		else 
		{
			popupH = $(window).height() * 0.80;
		}
		img.css("max-height", popupH);
	}

	function proEqualheight() 
	{
		if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) 
		{
			if ($(window).width() <= 575) 
			{
				$('.layout-format-height').height("auto");
				$('.layout-height').height("auto");
			} else 
			{
				$('.layout-format-height').equalHeights();
				$('.layout-height').equalHeights();
			}
		} else 
		{
			$('.layout-format-height').equalHeights();
			$('.layout-height').equalHeights();
		}
	};
}); 