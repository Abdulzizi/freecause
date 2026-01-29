(function ($) {
	// dom ready
	$(function () {
		$(".content-pic a").fancybox({
			'titleShow': false
		});
		$("ul.tabs li").click(function () {
			on_click_tab_button($(this));
		});
		$("ul.tabs").each(function (i, DOMelem) {
			on_click_tab_button($(this).find("li").eq(2));
		});

		function on_click_tab_button($li) {
			var $ul = $li.parent();
			$ul.find("li.selected").removeClass("selected");
			$li.addClass("selected");
			$ul.nextAll(".mrw-tab").hide();
			var id2show = $li.attr("rel");
			$("#" + id2show).show();
		}

		// input search hint
		$('input[title!=""]').hint();
		// countries selector, more topics selector - START
		$("a#countrybutton").click(function () {
			$("#boxcountries").toggle();
			return false;
		});
		$("a.moretopics").click(function () {
			$("#moretopics").toggle();
			return false;
		});
		$("html").click(function () {
			if ($("#boxcountries").is(":visible")) {
				$("#boxcountries").hide();
			}
			if ($("#moretopics").is(":visible")) {
				$("#moretopics").hide();
			}
		});
		$("#boxcountries, #moretopics").click(function (e) {
			e.stopPropagation();
		});
		
		/*
		$(".customscrolling").mCustomScrollbar({
			theme: "dark",
			scrollButtons: {
				enable: false
			}
		});
		*/
		
		$(".tiptip").tipTip({
			defaultPosition: "top",
			delay: 0,
			fadeIn: 400,
			fadeOut: 400
		});

		let $headerCountryList = $('.header-country-list').eq(0);
		
		$('.header-country-switcher').on('click', function (e) {
			e.preventDefault();
			let $thisDiv = $(this); // the clicked element (div)
			//$headerCountryList.toggleClass('pressed');
			// no need --- (window.getSelection ? window.getSelection() : document.selection).empty()
			$thisDiv.toggleClass('clicked');
			$headerCountryList.toggle(500);
		});

		// new by dev below 
		
		$(".lang-icon").click(function () {
			$(".toggle-it").stop().slideToggle(500); // 500ms smooth slide effect
		});
		
		$('.dc-slider').slick({
			dots: false,
			infinite: true,
			speed: 300,
			arrows: true,
			slidesToShow: 4,
			slidesToScroll: 4,
			responsive: [
				{
					breakpoint: 1024,
					settings: {
						slidesToShow: 3,
						slidesToScroll: 3,
						infinite: true,
						dots: true
					}
				},
				{
					breakpoint: 600,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
		
			]
		});
		
		//   popular campaigns 
		$('.pc-slider').slick({
			dots: false,
			infinite: true,
			speed: 300,
			arrows: true,
			slidesToShow: 3,
			slidesToScroll: 1,
			responsive: [
				{
					breakpoint: 1024,
					settings: {
						slidesToShow: 3,
						slidesToScroll: 3,
						infinite: true,
						dots: true
					}
				},
				{
					breakpoint: 600,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}
		
			]
		});
		
		//   main slider 
		$('.main-slider').slick({
			infinite: true,
			slidesToShow: 1,
			slidesToScroll: 1
		});
	}); // end of dom ready
})($);