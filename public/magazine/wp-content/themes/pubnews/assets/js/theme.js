/**
 * Handles theme general events
 * 
 * @package Pubnews
 * @since 1.0.0
 */
jQuery(document).ready(function($) {
    "use strict"
    var ajaxUrl = pubnewsObject.ajaxUrl, _wpnonce = pubnewsObject._wpnonce, sttOption = pubnewsObject.stt, query_vars = pubnewsObject.query_vars, paged = pubnewsObject.paged

    if( ! pubnewsObject.is_customizer ) {
        setTimeout(function() {
            $('body .pubnews_loading_box').hide();
        }, 2000);
    } else {
        $('body .pubnews_loading_box').hide();
    }

    var nrtl = false
    var ndir = "left"
    if ($('body').hasClass("rtl")) {
        nrtl = true;
        ndir = "right";
    };
    
    // theme trigger modal close
    function pubnewsclosemodal( elm, callback ) {
        $(document).mouseup(function (e) {
            var container = $(elm);
            if (!container.is(e.target) && container.has(e.target).length === 0) callback();
        });
    }

    // ticker news slider events
    var tc = $( ".ticker-news-wrap" );
    if( tc.length ) {
        var tcSpeed = tc.data("speed")
        var tcM = tc.find( ".ticker-item-wrap" ).marquee({
            duration: tcSpeed,
            gap: 0,
            delayBeforeStart: 0,
            direction: ndir,
            duplicated: true,
            startVisible: true,
            pauseOnHover: true,
        });
        tc.on( "click", ".pubnews-ticker-pause", function() {
            $(this).find( "i" ).toggleClass( "fa-pause fa-play" )
            tcM.marquee( "toggle" );
        })
    }

    // top date time
    var timeElement = $( ".top-date-time .time" )
    if( timeElement.length > 0 ) {
        setInterval(function() {
            timeElement.html(new Date().toLocaleTimeString())
        },1000);
    }
    
    // search form and off canvas trigger
    $( "#masthead" ).on( "click", ".off-canvas-trigger", function() {
        $(this).addClass('slideshow');
        $('body').addClass('off-canvas-active');
    });
    $( "#masthead" ).on( "click", ".off-canvas-trigger.slideshow, .sidebar-toggle .off-canvas-close, .search_close_btn", function() {
        $('.off-canvas-trigger').removeClass('slideshow');
        $('body').removeClass('off-canvas-active');
        $("#masthead .search-wrap").find(".search-results-wrap").remove()
        $("#masthead .search-wrap form").removeClass( 'results-loaded' )
    });

     // search form 
    $( "#masthead" ).on( "click", ".search-trigger", function() {
        $(this).next().slideDown();
        $( '#masthead .search-wrap input[type="search"]' ).focus()
        $(this).addClass('slideshow');
        $('body').addClass('bodynoscroll');
    });

    $( "#masthead" ).on( "click", ".search-trigger.slideshow", function() {
        $(this).next().slideUp();
        $(this).removeClass('slideshow');
        $('body').removeClass('bodynoscroll');
    });

    $( ".search-popup--style-three #masthead" ).on( "click", ".search-trigger", function() {
        $(this).parents( '#masthead' ).find( '.search-form-wrap' ).slideDown();
        $(this).parents( '#masthead' ).find( '.search-form-wrap input[type="search"]' ).focus()
        $(this).addClass('slideshow');
        $('body').addClass('bodynoscroll');
    });

    $( ".search-popup--style-three #masthead" ).on( "click", ".search_close_btn", function() {
        $(this).parents( '.main-header' ).find( '.search-form-wrap' ).slideUp();
        $(this).siblings().removeClass('slideshow');
        $('body').removeClass('bodynoscroll');
    });

    $( "body.search-popup--style-one #masthead" ).on( "click", ".search_close_btn", function() {
        $(this).prev().fadeOut();
        $(this).prev().prev().removeClass('slideshow');
        $('body').removeClass('bodynoscroll');
    });

    pubnewsclosemodal( $( ".search-wrap, .search-form-wrap" ), function () {
        $( ".search-wrap .search-trigger" ).removeClass( "slideshow" );
        $( ".search-form-wrap" ).slideUp();
        $('body').removeClass('bodynoscroll');
    }); // trigger search close


    pubnewsclosemodal( $( ".sidebar-toggle-wrap" ), function () {
        $( ".sidebar-toggle-wrap .off-canvas-trigger" ).removeClass( "slideshow" );
        $('body').removeClass('off-canvas-active');
    }); // trigger htsidebar close

    // top header ticker news slider events
    var thtn = $( ".top-ticker-news" );
    if( thtn.length ) {
        var thtnitems = thtn.find( ".ticker-item-wrap" )
        var thtDir = thtnitems.data("dir")
        var thtAuto = thtnitems.data("auto")
        thtnitems.slick({
            dots: false,
            infinite: true,
            rtl: nrtl,
            vertical: ( ! thtDir ),
            arrows: true,
            autoplay: thtAuto,
            nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
            prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
        });
    }

    // main banner slider events
    var bc = $( "#main-banner-section" );
    if( bc.length ) {
        var bic = bc.find( ".main-banner-slider" )
        var bAuto = bic.data( "auto" )
        var bArrows = bic.data( "arrows" )
        var bDots = bic.data( "dots" )
        var bSpeed = bic.data( "speed" )
        bic.slick({
            dots: bDots,
            infinite: true,
            rtl: nrtl,
            arrows: bArrows,
            autoplay: bAuto,
            speed: bSpeed,
            nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
            prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
        });
    }

    // main banner popular posts slider events
    var bpc = bc.find( ".popular-posts-wrap" );
    if( bpc.length ) {
        var bpcAuto = bpc.data( "auto" )
        var bpcArrows = bpc.data( "arrows" )
        var bpcVertical = bpc.data( "vertical" );
        if( bpcVertical) {
            bpc.slick({
                vertical: bpcVertical,
                slidesToShow: 5,
                dots: false,
                infinite: true,
                arrows: bpcArrows,
                autoplay: bpcAuto,
                nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
            })
        } else {
            bpc.slick({
                dots: false,
                infinite: true,
                arrows: bpcArrows,
                rtl: nrtl,
                draggable: true,
                autoplay: bpcAuto,
                nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
            })
        }  
    }

    // news carousel events
    var nc = $( ".pubnews-section .news-carousel .news-carousel-post-wrap" );
    if( nc.length ) {
        nc.each(function() {
            var _this = $(this)
            var multiColumnSection = _this.parents('.pubnews-multi-column-section')
            var ncDots= _this.data("dots") == '1'
            var ncLoop= _this.data("loop") == '1'
            var ncArrows= _this.data("arrows") == '1'
            var ncAuto  = _this.data("auto") == '1'
            var ncColumns  = _this.data("columns")
            var ncColumnsTablet = 2
            if( multiColumnSection.length > 0 ) {
                ncColumnsTablet = 1
                ncColumns = 1
            }
            _this.slick({
                dots: ncDots,
                infinite: ncLoop,
                arrows: ncArrows,
                autoplay: ncAuto,
                rtl: nrtl,
                slidesToShow: ncColumns,
                nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
                responsive: [
                  {
                    breakpoint: 910,
                    settings: {
                      slidesToShow: ncColumnsTablet,
                    },
                  },
                  {
                    breakpoint: 640,
                    settings: {
                      slidesToShow: 1,
                    },
                  }
                ]
            });
        })
    }

    // Filter posts
     $( ".pubnews-section .news-filter" ).each(function() {
        var $scope = $(this), $scopeOptions = $scope.data("args"), newTabs = $scope.find( ".filter-tab-wrapper" ), newTabsContent = $scope.find( ".filter-tab-content-wrapper" );
        newTabs.on( "click", ".tab-title", function() {
          var a = $(this), aT = a.data("tab")
          a.addClass( "isActive" ).siblings().removeClass( "isActive" );
          if( newTabsContent.find( ".tab-content.content-" + aT ).length < 1 ) {
            $scopeOptions.category_id = aT
            $.ajax({
                method: 'get',
                url: ajaxUrl,
                data: {
                    action: 'pubnews_filter_posts_load_tab_content',
                    options : JSON.stringify( $scopeOptions ),
                    _wpnonce: _wpnonce
                },
                beforeSend: function() {
                    $scope.addClass( 'retrieving-posts' );
                },
                success : function(res) {
                    if( res.data.loaded ) {
                        newTabsContent.append( res.data.posts )
                        $scope.removeClass( 'retrieving-posts' );
                    }
                },
                complete: function() {
                    newTabsContent.find( ".tab-content.content-" + aT ).show().siblings().hide()
                }
            })
          } else {
            newTabsContent.find( ".tab-content.content-" + aT ).show().siblings().hide()
          }
        })
    })

    if(pubnewsObject.ajaxPostsLoad == '1') {
        $( "#primary .pagination .ajax-load-more" ).on( "click", function() {
            var _this = $(this), $parent = _this.parents(".primary-content")
            $.ajax({
                method: 'post',
                url: ajaxUrl,
                data: {
                    action: 'pubnews_posts_content',
                    _wpnonce: _wpnonce,
                    query_vars: JSON.stringify(query_vars),
                    paged: paged + 1
                },
                beforeSend: function() {
                    $parent.addClass( 'retrieving-posts' );
                    _this.text( "Retrieving posts" )
                },
                success : function(res) {
                    if( res.data.loaded ) {
                        if( $parent.find(" > div:first-child").length > 0 ) {
                            $parent.find(" > div:first-child").append( res.data.posts )
                        } else {
                            $parent.find(".pagination").before( res.data.posts )
                        }
                        if( res.data.continue ) {
                            paged++;
                        } else {
                            _this.remove()
                        }
                    } else {
                        _this.remove()
                    }
                },
                complete: function() {
                    _this.text( "Load More" )
                    $parent.removeClass( 'retrieving-posts' );
                }
            })
        })
    }

    // popular posts widgets
    var ppWidgets = $( ".pubnews-widget-popular-posts" )
    ppWidgets.each(function() {
        var _this = $(this), parentWidgetContainerId = _this.parents( ".widget.widget_pubnews_popular_posts_widget" ).attr( "id" ), parentWidgetContainer = $( "#" + parentWidgetContainerId )
        var ppWidget = parentWidgetContainer.find( ".popular-posts-wrap" );
        if( ppWidget.length > 0 ) {
            var ppWidgetAuto = ppWidget.data( "auto" )
            var ppWidgetArrows = ppWidget.data( "arrows" )
            var ppWidgetLoop = ppWidget.data( "loop" )
            var ppWidgetVertical = ppWidget.data( "vertical" )
            if( ppWidgetVertical == 'vertical' ) {
                ppWidget.slick({
                    vertical: true,
                    slidesToShow: 4,
                    dots: false,
                    infinite: ppWidgetLoop,
                    arrows: ppWidgetArrows,
                    autoplay: ppWidgetAuto,
                    nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                    prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`
                })
            } else {
                ppWidget.slick({
                    dots: false,
                    infinite: ppWidgetLoop,
                    rtl: nrtl,
                    arrows: ppWidgetArrows,
                    autoplay: ppWidgetAuto,
                    nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                    prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`
                })
            }  
        }
    })

    // carousel posts widgets
    var cpWidgets = $( ".pubnews-widget-carousel-posts" )
    cpWidgets.each(function() {
        var _this = $(this), parentWidgetContainerId = _this.parents( ".widget.widget_pubnews_carousel_widget" ).attr( "id" ), parentWidgetContainer
        if( typeof parentWidgetContainerId != 'undefined' ) {
            parentWidgetContainer = $( "#" + parentWidgetContainerId )
            var ppWidget = parentWidgetContainer.find( ".carousel-posts-wrap" );
        } else {
            var ppWidget = _this;
        }
        if( ppWidget.length > 0 ) {
            var ppWidgetAuto = ppWidget.data( "auto" )
            var ppWidgetArrows = ppWidget.data( "arrows" )
            var ppWidgetLoop = ppWidget.data( "loop" )
            var ppWidgetVertical = ppWidget.data( "vertical" )
            if( ppWidgetVertical == 'vertical' ) {
                ppWidget.slick({
                    vertical: true,
                    dots: false,
                    infinite: ppWidgetLoop,
                    arrows: ppWidgetArrows,
                    autoplay: ppWidgetAuto,
                    nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                    prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`
                })
            } else {
                ppWidget.slick({
                    dots: false,
                    infinite: ppWidgetLoop,
                    rtl: nrtl,
                    arrows: ppWidgetArrows,
                    autoplay: ppWidgetAuto,
                    adaptiveHeight: true,
                    nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
                    prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`
                })
            }  
        }
    })

    // tabbed posts
    var tabpWidgets = $( ".pubnews-tabbed-widget-tabs-wrap" )
    tabpWidgets.each(function() {
        var _this = $(this), parentWidgetContainerId = _this.parents( ".widget.widget_pubnews_tabbed_posts_widget" ).attr( "id" ), parentWidgetContainer
        if( typeof parentWidgetContainerId != 'undefined' ) {
            parentWidgetContainer = $( "#" + parentWidgetContainerId )
            var tabpWidget = parentWidgetContainer.find( ".pubnews-tabbed-widget-tabs-wrap" );
        } else {
            var tabpWidget = _this;
        }
        if( tabpWidget.length > 0 ) {
            tabpWidget.on( "click", ".tabbed-widget-tabs li.tabbed-widget", function() {
                var _this = $(this), tabItem = _this.attr( "tab-item" );
                _this.addClass( "active" ).siblings().removeClass( "active" );
                tabpWidget.find( '.widget-tabs-content div[tab-content="' + tabItem + '"]' ).addClass( "active" ).siblings().removeClass( "active" );
            })
        }
    })

    // news filter tabbed posts
    var nftabpWidgets = $( ".pubnews-news-filter-tabbed-widget-tabs-wrap" )
    nftabpWidgets.each(function() {
        var _this = $(this), parentWidgetContainerId = _this.parents( ".widget.widget_pubnews_news_filter_tabbed_widget" ).attr( "id" ), parentWidgetContainer
        if( typeof parentWidgetContainerId != 'undefined' ) {
            parentWidgetContainer = $( "#" + parentWidgetContainerId )
            var nftabpWidget = parentWidgetContainer.find( ".pubnews-news-filter-tabbed-widget-tabs-wrap" );
        } else {
            var nftabpWidget = _this;
        }
        if( nftabpWidget.length > 0 ) {
            nftabpWidget.on( "click", ".widget-tabs li.widget-tab", function() {
                var _this = $(this), tabItem = _this.attr( "tab-item" );
                _this.addClass( "active" ).siblings().removeClass( "active" );
                nftabpWidget.find( '.tabs-content-wrap div.tabs-content.' + tabItem ).addClass( "show" ).siblings().removeClass( "show" );
            })
        }
    })

    // check for dark mode drafts
    if( localStorage.getItem( "themeMode" ) != null ) {
        if( localStorage.getItem("themeMode") == "dark" ) {
            $('body').addClass( 'pubnews_dark_mode' ).removeClass('pubnews_main_body')
            $('body .blaze-switcher-button').addClass( 'active' )
        } else {
            $('body').addClass( 'pubnews_main_body' ).removeClass('pubnews_dark_mode')
            $('body .blaze-switcher-button').removeClass( 'active' )
        }
    }
    
    // header - theme mode
    var themeModeContainer = $('.blaze-switcher-button')
    if( themeModeContainer.length > 0 ) {
        themeModeContainer.on( 'click', function(){
            var _this = $(this), bodyElement = _this.parents('body')
            if( bodyElement.hasClass('pubnews_dark_mode') ) {
                localStorage.setItem( 'themeMode', 'light' )
                bodyElement.removeClass('pubnews_dark_mode').addClass('pubnews_main_body')
                $('body .blaze-switcher-button').removeClass( 'active' )
            } else {
                localStorage.setItem( 'themeMode', 'dark' )
                bodyElement.removeClass('pubnews_main_body').addClass('pubnews_dark_mode')
                $('body .blaze-switcher-button').addClass( 'active' )
            }
        })
    }

    // back to top script
    if( sttOption && $( "#pubnews-scroll-to-top" ).length ) {
        var scrollContainer = $( "#pubnews-scroll-to-top" );
        $(window).scroll(function() {
            if ( $(this).scrollTop() > 800 ) {
                scrollContainer.addClass('show');
            } else {
                scrollContainer.removeClass('show');
            }
        });
        scrollContainer.click(function(event) {
            event.preventDefault();
            // Animate the scrolling motion.
            $("html, body").animate({scrollTop:0},"slow");
        });
    }

    // category archive hide featured post in list
    var featuredPost = $( ".archive.category .featured-post.is-sticky" )
    if( featuredPost.length > 0 ) {
        var postHide = "#post-" + featuredPost.data("id")
        $(postHide).addClass( "sticky-hide" );
    }

    // ads slide block
    var adsSliderContainer = $(".ads-banner.ads-banner-slider")
    if( adsSliderContainer.length > 0 ) {
        adsSliderContainer.slick({
            dots: false,
            infinite: true,
            rtl: nrtl,
            vertical: false,
            arrows: true,
            autoplay: true,
            nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
            prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
        })
    }

    // ads slider widget
    var adsSliderWidget = $(".widget_pubnews_ads_slider_widget .pubnews-advertisement-block")
    if( adsSliderWidget.length > 0 ) {
        adsSliderWidget.slick({
            dots: false,
            infinite: true,
            rtl: nrtl,
            vertical: false,
            arrows: true,
            autoplay: true,
            nextArrow: `<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>`,
            prevArrow: `<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>`,
        })
    }

    // post format - gallery
    var gallery = $('.wp-block-gallery')
    if( gallery.length > 0 ) {
        gallery.each(function(){
            var _this = $(this)
            var findImageSrc = _this.find('.wp-block-image img')
            var srcArgs = []
            findImageSrc.each(function(){
                srcArgs.push({
                    src: $(this).attr('src'),
                    type: 'image'
                })
            })
            _this.magnificPopup({
                items: srcArgs,
                gallery: {
                    enabled: true
                },
                type: 'image'
            })
        })
    }

    // news filter burger
    var newsFilterContainer = $('.news-filter')
    if( newsFilterContainer.length > 0 ) {
        newsFilterContainer.each(function(){
            $(this).on('click', '.pubnews-burger', function(){
                var _this = $(this)
                _this.siblings().toggleClass('isactive')
            })
        })
    }

     // cursor animation
     var cursorContainer = $('.pubnews-cursor')
     if( cursorContainer.length > 0 ) {
         $(document).on( 'mousemove', function( event ){
             cursorContainer[0].style.top = 'calc('+ event.pageY +'px - 15px)'
             cursorContainer[0].style.left = 'calc('+ event.pageX +'px - 15px)'
         })
         var selector = 'a, button, input[type="submit"], #pubnews-scroll-to-top .icon-text, #pubnews-scroll-to-top .icon-holder, .thumb-video-highlight-text .thumb-controller, .pagination .ajax-load-more, .blaze-switcher-button, .pubnews-canvas-menu .canvas-menu-icon, .pubnews-table-of-content .toc-fixed-icon'
         $( selector ).on( 'mouseover', function(){
             $( cursorContainer ).addClass( 'isActive' )
         })
         $( selector ).on( 'mouseout', function(){
             $( cursorContainer ).removeClass( 'isActive' )
         })
     }
})