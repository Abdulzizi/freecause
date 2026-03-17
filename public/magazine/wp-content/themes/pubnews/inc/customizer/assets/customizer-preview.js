/* global wp, jQuery */
/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

 ( function( $ ) {
	const themeContstants = {
		prefix: 'pubnews_',
		isHome: pubnewsPreviewObject.isHome,
		isArchive: pubnewsPreviewObject.isArchive,
	}
	const themeCalls = {
		pubnewsGenerateStyleTag: function( code, id ) {
			if( code ) {
				if( $( "head #" + id ).length > 0 ) {
					$( "head #" + id ).html( code )
				} else {
					$( "head" ).append( '<style id="' + id + '">' + code + '</style>' )
				}
			}
		},
		pubnewsGenerateLinkTag: function( action, id ) {
			$.ajax({
				method: "GET",
				url: pubnewsPreviewObject.ajaxUrl,
				data: ({
					action: action,
					_wpnonce: pubnewsPreviewObject._wpnonce
				}),
				success: function(response) {
					if( response ) {
						if( $( "head #" + id ).length > 0 ) {
							$( "head #" + id ).attr( "href", response )
						} else {
							$( "head" ).append( '<link rel="stylesheet" id="' + id + '" href="' + response + '"></link>' )
						}
					}
				}
			})
		},
		pubnewsGenerateTypoCss: function(selector,value) {
			var cssCode = ''
			if( value.font_family ) {
				cssCode += '.pubnews_font_typography { ' + selector + '-family: ' + value.font_family.value + '; } '
			}
			if( value.font_weight ) {
				cssCode += '.pubnews_font_typography { ' + selector + '-weight: ' + value.font_weight.value + '; } '
			}
			if( value.text_transform ) {
				cssCode += '.pubnews_font_typography { ' + selector + '-texttransform: ' + value.text_transform + '; } '
			}
			if( value.text_decoration ) {
				cssCode += '.pubnews_font_typography { ' + selector + '-textdecoration: ' + value.text_decoration + '; } '
			}
			if( value.font_size ) {
				if( value.font_size.desktop ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-size: ' + value.font_size.desktop + 'px; } '
				}
				if( value.font_size.tablet ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-size-tab: ' + value.font_size.tablet + 'px; } '
				}
				if( value.font_size.smartphone ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-size-mobile: ' + value.font_size.smartphone + 'px; } '
				}
			}
			if( value.line_height ) {
				if( value.line_height.desktop ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-lineheight: ' + value.line_height.desktop + 'px; } '
				}
				if( value.line_height.tablet ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-lineheight-tab: ' + value.line_height.tablet + 'px; } '
				}
				if( value.line_height.smartphone ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-lineheight-mobile: ' + value.line_height.smartphone + 'px; } '
				}
			}
			if( value.letter_spacing ) {
				if( value.letter_spacing.desktop ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-letterspacing: ' + value.letter_spacing.desktop + 'px; } '
				}
				if( value.letter_spacing.tablet ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-letterspacing-tab: ' + value.letter_spacing.tablet + 'px; } '
				}
				if( value.letter_spacing.smartphone ) {
					cssCode += '.pubnews_font_typography { ' + selector + '-letterspacing-mobile: ' + value.letter_spacing.smartphone + 'px; } '
				}
			}
			return cssCode
		},
	}

	// post title hover class
	wp.customize( 'post_title_hover_effects', function( value ) {
		value.bind( function(to) {
				$( "body" ).removeClass( "pubnews-title-none pubnews-title-three pubnews-title-ten" )
				$( "body" ).addClass( "pubnews-title-" + to )
		});
	});

	// website layout class
	wp.customize( 'website_layout', function( value ) {
		value.bind( function(to) {
				$( "body" ).removeClass( "site-boxed--layout site-full-width--layout" )
				$( "body" ).addClass( "site-" + to )
		});
	});

	// block title primary bk color
	wp.customize( 'block_title_primary_color', function( value ) {
		value.bind( function( to ) {
			var cssCode = 'body.block-title--layout-seven h2.pubnews-block-title:before, body.block-title--layout-seven h2.widget-title span:before, body.archive.block-title--layout-seven .page-header span:before, body.search.block-title--layout-seven .page-header span:before, body.archive.block-title--layout-seven .page-title:before, body.block-title--layout-seven h2.pubnews-widget-title span:before, body.block-title--layout-seven .pubnews-custom-title:before { background: '+ pubnews_get_color_format( to ) +'}'
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'block-title-primary-bk-color' )
		});
	});

	// block title secondary bk color
	wp.customize( 'site_block_title_secondary_color', function( value ) {
		value.bind( function( to ) {
			var cssCode = 'body.block-title--layout-seven h2.pubnews-block-title:after, body.block-title--layout-seven h2.widget-title span:after, body.archive.block-title--layout-seven .page-header span:after, body.search.block-title--layout-seven .page-header span:after, body.archive.block-title--layout-seven .page-title:after, body.block-title--layout-seven h2.pubnews-widget-title span:after, body.block-title--layout-seven .pubnews-custom-title:after { background: '+ pubnews_get_color_format( to ) +'}'
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'block-title-secondary-bk-color' )
		});
	});

	// image hover class
	wp.customize( 'site_image_hover_effects', function( value ) {
		value.bind( function(to) {
				$( "body" ).removeClass( "pubnews-image-hover--effect-none pubnews-image-hover--effect-four pubnews-image-hover--effect-eight" )
				$( "body" ).addClass( "pubnews-image-hover--effect-" + to )
		});
	});

	// main banner five trailing layout styles 
	wp.customize( 'main_banner_six_trailing_posts_layout', function( value ) {
		value.bind( function( to ) {
			$("#main-banner-section.banner-layout--six").removeClass( "layout--column layout--row" ).addClass( "layout--" + to )
		});
	});

	// theme color bind changes
	wp.customize( 'theme_color', function( value ) {
		value.bind( function( to ) {
			helperFunctions.generateStyle(to, 'theme-color-style', '--pubnews-global-preset-theme-color')
		});
	});

	// solid presets
	wp.customize( 'solid_presets', function( value ) {
		value.bind( function( to ) {
			if( Array.isArray( to ) ) {
				to.map(( color, index ) => {
					let count = ( index + 1 )
					helperFunctions.generateStyle( color, 'solid-preset-color-'+ count +'-style', '--pubnews-global-preset-color-' + count )
				})
			}
		});
	});

	// gradient presets
	wp.customize( 'gradient_presets', function( value ) {
		value.bind( function( to ) {
			if( Array.isArray( to ) ) {
				to.map(( color, index ) => {
					let count = ( index + 1 )
					helperFunctions.generateStyle( color, 'gradient-preset-color-'+ count +'-style', '--pubnews-global-preset-gradient-color-' + count )
				})
			}
		});
	});

	// header menu hover effect 
	wp.customize( 'header_menu_hover_effect', function( value ) {
		value.bind( function(to) {
			$( "#site-navigation" ).removeClass( "hover-effect--one hover-effect--none" )
			$( "#site-navigation" ).addClass( "hover-effect--" + to )
		});
	});

	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	});
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	});
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			} else {
				$( '.site-title' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			}
		} );
	});
	// blog description
	wp.customize( 'blogdescription_option', function( value ) {
		value.bind(function(to) {
			if( to ) {
				$( '.site-description' ).css( {
					clip: 'auto',
					position: 'relative',
				} );
			} else {
				$( '.site-description' ).css( {
					clip: 'rect(1px, 1px, 1px, 1px)',
					position: 'absolute',
				} );
			}
		})
	});

	// site description color
	wp.customize( 'site_description_color', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).css( {
				color: to,
			});
		} );
	});

	// site title typo
	wp.customize('site_title_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--site-title'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-site-title-typo' )
		})
	})

	// site tagline typo
	wp.customize('site_tagline_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--site-tagline'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-site-tagline-typo' )
		})
	})

	// side background color
	wp.customize( 'site_background_color', function( value ) {
		value.bind( function( to ) {
			var value = JSON.parse(to)
			var cssCode = ''
			var selector = '--site-bk-color'
			cssCode += ".pubnews_main_body { " + selector + ": " + pubnews_get_color_format( value[value.type] ) +  "}";
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'site-background-style' )
		})
	})

	// category colors text colors
	var parsedCats = pubnewsPreviewObject.totalCats
	if( parsedCats ) {
		parsedCats = Object.keys( parsedCats ).map(( key ) => { return parsedCats[key] })
		parsedCats.forEach(function(item) {
			wp.customize( 'category_' + item.term_id + '_color', function( value ) {
				value.bind( function(to) {
					var cssCode = ''
					if( to.color ) {
						cssCode += "body article:not(.pubnews-category-no-bk) .post-categories .cat-item.cat-" + item.term_id + " { background : " + pubnews_get_color_format( to.color ) + " } "
						cssCode += "body .pubnews-category-no-bk .post-categories .cat-item.cat-" + item.term_id + " a { color : " + pubnews_get_color_format( to.color ) + " } "
					}
					if( to.hover ) {
						cssCode += "body article:not(.pubnews-category-no-bk) .post-categories .cat-item.cat-" + item.term_id + ":hover { background : " + pubnews_get_color_format( to.hover ) + " } "
						cssCode += "body .pubnews-category-no-bk .post-categories .cat-item.cat-" + item.term_id + " a:hover { color : " + pubnews_get_color_format( to.hover ) + " } "
					}
					themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-category-' + item.term_id + '-style' )
				})
			})
		})
	}

	// global typography block title
	wp.customize('site_section_block_title_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--block-title'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'site-section-block-title-typo' )
		})
	})

	// global typography post title
	wp.customize('site_archive_post_title_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--post-title'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'site-archive-post-title-typo' )
		})
	})

	// global typography post meta
	wp.customize('site_archive_post_meta_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--meta'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'site-archive-post-meta-typo' )
		})
	})

	// global typography post content
	wp.customize('site_archive_post_content_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--content'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'site-archive-post-content-typo' )
		})
	})

	// global buttons typography
	wp.customize( 'global_button_typo', function( value ) {
		value.bind( function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--post-link-btn'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'global-buttons-typo' )
		})
	})

	// top header background
	wp.customize( 'top_header_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body .site-header.layout--default .top-header {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'top-header-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'top-header-background-color' )
			}
		});
	});

	// theme header general settings width layout
	wp.customize( 'header_width_layout', function( value ) {
		value.bind(function( to ){
			$('body').removeClass('header-width--full-width header-width--contain header-width--global').addClass( 'header-width--' + to )
		});
	});

	// theme header general settings vertical padding
	wp.customize( 'header_vertical_padding', function( value ) {
		value.bind(function( to ){
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body.pubnews_main_body { --header-padding: ' + to.desktop + 'px }';
			}
			if( to.tablet ) {
				cssCode += 'body.pubnews_main_body { --header-padding-tablet: ' + to.tablet + 'px }';
			}
			if( to.smartphone ) {
				cssCode += 'body.pubnews_main_body { --header-padding-smartphone: ' + to.smartphone + 'px }';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-header-vertical-padding' )
		})
	})

	// theme header general settings background
	wp.customize( 'header_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += 'body.pubnews_main_body .site-header.layout--default .site-branding-section {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-background-color' )
			}
		});
	});

	// newsletter background color
	wp.customize('header_newsletter_background_color', function( value ) {
		value.bind(function( to ){
			var parsedValue = JSON.parse(to)
			if( parsedValue ) {
				var cssCode = ''
				var selector = '--newsletter-bk-color'
				if( parsedValue.initial[parsedValue.initial.type] ) {
					cssCode += 'body { ' + selector + ' : ' + pubnews_get_color_format( parsedValue.initial[parsedValue.initial.type] ) +  ' } '
				}
				if( parsedValue.hover[parsedValue.hover.type] ) {
					cssCode += 'body { ' + selector + '-hover : ' + pubnews_get_color_format( parsedValue.hover[parsedValue.hover.type] ) +  ' } '
				}
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-header-newsletter-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( '', 'pubnews-header-newsletter-background-color' )
			}
		})
	})

	// menu options background
	wp.customize( 'header_menu_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body .site-header.layout--default .menu-section, .search-popup--style-three .site-header.layout--one .search-form-wrap {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-menu-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-menu-background-color' )
			}
		});
	});

	// menu options background
	wp.customize( 'header_sub_menu_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body .main-navigation ul.menu ul, .pubnews_main_body .main-navigation ul.nav-menu ul {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-sub-menu-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-sub-menu-background-color' )
			}
		});
	});

	// main menu typo
	wp.customize( 'header_menu_typo', function( value ) {
		value.bind( function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--menu'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-menu-typo' )
		})
	})

	// main menu typo
	wp.customize( 'header_sub_menu_typo', function( value ) {
		value.bind( function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--submenu'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'header-sub-menu-typo' )
		})
	})

	// off canvas sidebar position
	wp.customize('off_canvas_position', function( value ) {
		value.bind(function( to ){
			if( to == 'left' ) {
				$('body.pubnews_main_body').removeClass('off-canvas-sidebar-appear--right').addClass('off-canvas-sidebar-appear--left')
			} else {
				$('body.pubnews_main_body').removeClass('off-canvas-sidebar-appear--left').addClass('off-canvas-sidebar-appear--right')
			}
		})
	})

	// front sections full width section background
	wp.customize( 'full_width_blocks_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body #full-width-section {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-full-width-section-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-full-width-section-background-color' )
			}
		});
	});

	// front sections left content right sidebar section background
	wp.customize( 'leftc_rights_blocks_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body #leftc-rights-section {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-left-content-right-sidebar-section-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-left-content-right-sidebar-section-background-color' )
			}
		});
	});

	// front sections left sidebar right content section background
	wp.customize( 'lefts_rightc_blocks_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += 'body.pubnews_main_body #lefts-rightc-section {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-left-sidebar-right-content-section-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-left-sidebar-right-content-section-background-color' )
			}
		});
	});

	// front sections bottom full width section background
	wp.customize( 'bottom_full_width_blocks_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body #bottom-full-width-section {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-bottom-full-width-section-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-bottom-full-width-section-background-color' )
			}
		});
	});

	// front sections two column section background
	wp.customize( 'two_column_background_color_group', function( value ) {
		value.bind( function(to) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += '.pubnews_main_body .two-column-section {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-two-column-section-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-two-column-section-background-color' )
			}
		});
	});

	// blogs / archive image ratio
	wp.customize('archive_image_ratio', function( value ) {
		value.bind(function( to ){
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body.pubnews_main_body { --pubnews-archive-image-ratio: ' + to.desktop + ' }\n';
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 940px) { body.pubnews_main_body { --pubnews-archive-image-ratio-tab: ' + to.tablet + ' } }\n';
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { body.pubnews_main_body { --pubnews-archive-image-ratio-mobile: ' + to.smartphone + ' } }\n';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-archive-image-ratio' )
		})
	})

	// opinions vertical spacing top
	wp.customize('archive_vertical_spacing_top', function( value ) {
		value.bind(function( to ) {
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body.pubnews_font_typography { --archive-padding-top: ' + to.desktop + 'px }\n';
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 940px) { body.pubnews_font_typography { --archive-padding-top-tab: ' + to.tablet + 'px } }\n';
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { body.pubnews_font_typography { --archive-padding-top-mobile: ' + to.smartphone + 'px } }\n';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-archive-vertical-spacing-top' )
		})
	})

	// archive vertical spacing bottom
	wp.customize('archive_vertical_spacing_bottom', function( value ) {
		value.bind(function( to ) {
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body.pubnews_font_typography { --archive-padding-bottom: ' + to.desktop + 'px }';
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 940px) { body.pubnews_font_typography { --archive-padding-bottom-tab: ' + to.tablet + 'px } }';
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { body.pubnews_font_typography { --archive-padding-bottom-mobile: ' + to.smartphone + 'px } }';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-archive-vertical-spacing-bottom' )
		})
	})

	// single post show original image
	wp.customize('single_post_show_original_image_option', function( value ) {
		value.bind(function( to ) {
			if( to ) {
				$('body.pubnews_main_body.single main#primary .post-inner-wrapper .post-thumbnail').addClass('show-original-image')
			} else {
				$('body.pubnews_main_body.single main#primary .post-inner-wrapper .post-thumbnail').removeClass('show-original-image')
			}
		})
	})

	// single post related news title
	wp.customize('single_post_related_posts_title', function( value ) {
		value.bind(function( to ) {
			var parentElement = $('body.pubnews_main_body #theme-content .post-inner-wrapper .single-related-posts-section')
			if( parentElement.find( '.pubnews-block-title' ).length > 0 ) {
				parentElement.find( '.pubnews-block-title span' ).text( to )
			} else {
				parentElement.find( '.related_post_close' ).after('<h2 class="pubnews-block-title"><span>'+ to +'</span></h2>')
			}
		})
	})

	// single post image ratio
	wp.customize('single_post_image_ratio', function( value ) {
		value.bind(function( to ) {
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body.pubnews_font_typography { --pubnews-single-image-ratio: ' + to.desktop + ' }';
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 940px) { body.pubnews_font_typography { --pubnews-single-image-ratio-tab: ' + to.tablet + ' } }';
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { body.pubnews_font_typography { --pubnews-single-image-ratio-mobile: ' + to.smartphone + ' } }';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-image-ratio' )
		})
	})

	// single post post title typo
	wp.customize('single_post_title_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-title'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-title-typo' )
		})
	})

	// single post post meta typo
	wp.customize('single_post_meta_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-meta'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-meta-typo' )
		})
	})

	// single post post content typo
	wp.customize('single_post_content_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-typo' )
		})
	})

	// single post h1 typo
	wp.customize('single_post_content_h1_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content-h1'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-h1-typo' )
		})
	})

	// single post h2 typo
	wp.customize('single_post_content_h2_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content-h2'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-h2-typo' )
		})
	})

	// single post h3 typo
	wp.customize('single_post_content_h3_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content-h3'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-h3-typo' )
		})
	})

	// single post h4 typo
	wp.customize('single_post_content_h4_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content-h4'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-h4-typo' )
		})
	})

	// single post h5 typo
	wp.customize('single_post_content_h5_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content-h5'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-h5-typo' )
		})
	})

	// single post h6 typo
	wp.customize('single_post_content_h6_typo', function( value ) {
		value.bind(function( to ) {
			ajaxFunctions.typoFontsEnqueue()
			var cssCode = ''
			var selector = '--single-content-h6'
			cssCode = themeCalls.pubnewsGenerateTypoCss(selector,to)
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-single-post-content-h6-typo' )
		})
	})

	// single page show original image
	wp.customize('single_page_show_original_image_option', function( value ) {
		value.bind(function( to ) {
			if( to ) {
				$('body.pubnews_main_body.page main#primary .post-inner-wrapper .post-thumbnail').addClass('show-original-image')
			} else {
				$('body.pubnews_main_body.page main#primary .post-inner-wrapper .post-thumbnail').removeClass('show-original-image')
			}
		})
	})

	// page settings image ratio
	wp.customize('single_page_image_ratio', function( value ) {
		value.bind(function( to ) {
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body.pubnews_main_body { --pubnews-page-image-ratio: ' + to.desktop + ' }';
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 940px) { body.pubnews_main_body { --pubnews-page-image-ratio-tab: ' + to.tablet + ' } }';
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { body.pubnews_main_body { --pubnews-page-image-ratio-mobile: ' + to.smartphone + ' } }';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-page-settings-image-ratio' )
		})
	})

	// theme footer background
	wp.customize('footer_background_color_group', function( value ) {
		value.bind(function( to ) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += 'body.pubnews_main_body .site-footer .main-footer, body .dark_bk .posts-grid-wrap.layout-two .post-content-wrap {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-theme-footer-content-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-theme-footer-content-background-color' )
			}
		})
	})

	// bottom footer background
	wp.customize('bottom_footer_background_color_group', function( value ) {
		value.bind(function( to ) {
			var value = JSON.parse( to )
			if( value ) {
				var cssCode = ''
				cssCode += 'body.pubnews_main_body .site-footer .bottom-footer {' + pubnews_get_background_style( value ) + '}'
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-bottom-footer-background-color' )
			} else {
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-bottom-footer-background-color' )
			}
		})
	})

	// bottom footer menu option
	wp.customize( 'bottom_footer_menu_option', function( value ) {
		value.bind( function( to ) {
			if( to ) {
				$( '.bottom-footer .bottom-menu' ).show()
			} else {
				$( '.bottom-footer .bottom-menu' ).hide()
			}
		});
	});

	// pubnews_preloader_section

	// prloader handler
	wp.customize( 'preloader_type', function( value ) {
		value.bind( function( to ) {
			var loaderItem = $( 'body .pubnews_loading_box .loader-item' )
			$( "body .pubnews_loading_box" ).show()
			setTimeout( function() {
				$( "body .pubnews_loading_box" ).hide()
			}, 2000)
			loaderItem.removeClass();
			loaderItem.addClass( "loader-item loader-" + to )
		});
	});

	// single post related posts title
	wp.customize( 'single_post_related_posts_title', function( value ) {
		value.bind( function( to ) {
			$( '.single-related-posts-section .pubnews-block-title span' ).text( to );
		} );
	});

	// footer width option
	wp.customize( 'footer_section_width', function( value ) {
		value.bind( function( to ) {
			if( to == 'boxed-width' ) {
				$( 'footer .main-footer' ).removeClass( 'full-width' ).addClass( 'boxed-width' );
				$( 'footer .main-footer .footer-inner' ).removeClass( 'pubnews-container-fluid' ).addClass( 'pubnews-container' );
			} else {
				$( 'footer .main-footer' ).removeClass( 'boxed-width' ).addClass( 'full-width' );
				$( 'footer .main-footer .footer-inner' ).removeClass( 'pubnews-container' ).addClass( 'pubnews-container-fluid' );
			}
		});
	});

	// archive page layout
	wp.customize( 'archive_page_layout', function( value ) {
		value.bind( function( to ) {
			$('body').removeClass('post-layout--one post-layout--two post-layout--five')
			$('body').addClass( 'post-layout--' + to )
		});
	});

	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			var cssCode = 'header .site-title a { color: '+ to +' }'
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-site-title-color' )
		} );
	});

	// site title hover color
	wp.customize( 'site_title_hover_textcolor', function( value ) {
		value.bind( function( to ) {
			var color = pubnews_get_color_format( to )
			themeCalls.pubnewsGenerateStyleTag( 'header .site-title a:hover { color : ' + color + ' }', 'pubnews-site-title-hover-color' )
		})
	})

	// widget settings border
	wp.customize('widgets_styles_image_border', function( value ) {
		value.bind(function( to ) {
			var cssCode = '.widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts .post-thumb-wrap, .author-wrap.layout-two .post-thumb, .widget_pubnews_category_collection_widget .categories-wrap .category-item { border: '+ to.width +'px '+ to.type +' '+ pubnews_get_color_format( to.color ) +'}'
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-widget-border-control' )
		})
	})

	// widget border radius
	wp.customize('widgets_styles_image_border_radius', function( value ) {
		value.bind(function( to ) {
			var cssCode = ''
			var selector = '.widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts .post-thumb-wrap, .author-wrap.layout-two .post-thumb, .widget .pubnews-widget-carousel-posts.layout--two .slick-list, .widget_pubnews_category_collection_widget .categories-wrap .category-item'
			if( to.desktop ) {
				cssCode += selector + ' { border-radius: ' + to.desktop + 'px }';
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 940px) { '+ selector +' { border-radius: ' + to.tablet + 'px } }';
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { '+ selector +' { border-radius: ' + to.smartphone + 'px } }';
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-widget-border-radius' )
		})
	})

	// widgets settings box shadow
	wp.customize('widgets_styles_image_box_shadow', function( value ) {
		value.bind(function( to ) {
			var cssCode = ''
			if( to && to.option != 'none' ) {
				if( to.type == 'outset' ) {
					let boxShadowValue = to.hoffset + "px " + to.voffset + "px " + to.blur + "px " + to.spread + "px " + pubnews_get_color_format( to.color );
					cssCode += ".widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts.layout--two .slick-list, .author-wrap.layout-two .post-thumb, .widget_pubnews_category_collection_widget .categories-wrap .category-item { box-shadow: " + boxShadowValue + "; -webkit-box-shadow: "+ boxShadowValue + "; -moz-box-shadow:"+ boxShadowValue +" } "
				} else {
					let boxShadowValue = to.type + " " + to.hoffset + "px " + to.voffset + "px " + to.blur + "px " + to.spread + "px " + pubnews_get_color_format( to.color );
					cssCode += ".widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts.layout--two .slick-list, .author-wrap.layout-two .post-thumb, .widget_pubnews_category_collection_widget .categories-wrap .category-item { box-shadow: " + boxShadowValue + "; -webkit-box-shadow: "+ boxShadowValue + "; -moz-box-shadow:"+ boxShadowValue +" } "
				}
				themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-card-box-shadow' )
			} else {
				cssCode += ".widget .post_thumb_image, .widget .widget-tabs-content .post-thumb, .widget .popular-posts-wrap article .post-thumb, .widget.widget_pubnews_news_filter_tabbed_widget .tabs-content-wrap .post-thumb, .widget .pubnews-widget-carousel-posts.layout--two .slick-list, .author-wrap.layout-two .post-thumb, .widget_pubnews_category_collection_widget .categories-wrap .category-item { box-shadow: 0px 0px 0px 0px } "
				themeCalls.pubnewsGenerateStyleTag( '', 'pubnews-widget-box-shadow' )
			}
		})
	})

	// site logo width
	wp.customize( 'pubnews_site_logo_width', function( value ) {
		value.bind( function(to) {
			var cssCode = ''
			if( to.desktop ) {
				cssCode += 'body .site-branding img { width: ' + to.desktop +  'px} '
			}
			if( to.tablet ) {
				cssCode += '@media(max-width: 994px) { body .site-branding img { width: ' + to.tablet +  'px} } '
			}
			if( to.smartphone ) {
				cssCode += '@media(max-width: 610px) { body .site-branding img { width: ' + to.smartphone +  'px} } '
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-site-logo-width' )
		});
	});

	// post title hover class
	wp.customize( 'frontpage_sidebar_sticky_option', function( value ) {
		value.bind( function(to) {
			if( to ) {
				if( $('body').hasClass( 'home' ) && $('body').hasClass( 'page-template-default' ) ) $( "body" ).addClass( "sidebar-sticky" )
			} else {
				if( $('body').hasClass( 'home' ) && $('body').hasClass( 'page-template-default' ) )  $( "body" ).removeClass( "sidebar-sticky" )
			}
		});
	});

	// post title hover class
	wp.customize( 'archive_sidebar_sticky_option', function( value ) {
		value.bind( function(to) {
			if( to ) {
				if( ( $('body').hasClass( 'home' ) && $('body').hasClass( 'blog' ) ) || $('body').hasClass( 'archive' ) ) $( "body" ).addClass( "sidebar-sticky" )
			} else {
				if( ( $('body').hasClass( 'home' ) && $('body').hasClass( 'blog' ) ) || $('body').hasClass( 'archive' ) ) $( "body" ).removeClass( "sidebar-sticky" )
			}
		});
	});

	// post title hover class
	wp.customize( 'single_sidebar_sticky_option', function( value ) {
		value.bind( function(to) {
			if( to ) {
				if( $('body').hasClass( 'single' ) ) $( "body" ).addClass( "sidebar-sticky" )
			} else {
				if( $('body').hasClass( 'single' ) ) $( "body" ).removeClass( "sidebar-sticky" )
			}
		});
	});

	// post title hover class
	wp.customize( 'page_sidebar_sticky_option', function( value ) {
		value.bind( function(to) {
			if( to ) {
				if( $('body').hasClass( 'page' ) ) $( "body" ).addClass( "sidebar-sticky" )
			} else {
				if( $('body').hasClass( 'page' ) ) $( "body" ).removeClass( "sidebar-sticky" )
			}
		});
	});

	// background animation
	wp.customize( 'site_background_animation', function( value ) {
		value.bind( function( to ) {
			$('body').removeClass( 'background-animation--none background-animation--three' ).addClass( 'background-animation--' + to )
		});
	});

	// animation object color
	wp.customize( 'animation_object_color', function( value ) {
		value.bind( function(to) {
			helperFunctions.generateStyle(to, 'pubnews-animation-object-color', '--pubnews-animation-object-color')
		})
	})

	// sst responsive option
	wp.customize( 'stt_responsive_option', function( value ) {
		value.bind( function(to) {
			var cssCode = ''
			if( ! to.desktop ) {
				cssCode += 'body #pubnews-scroll-to-top.show { display: none }';
			}
			if( ! to.tablet ) {
				cssCode += '@media(max-width: 940px) { body #pubnews-scroll-to-top.show { display : none } }'
			} else {
				cssCode += '@media(max-width: 940px) { body #pubnews-scroll-to-top.show { display : block } }'
			}
			if( to.mobile ) {
				cssCode += '@media(max-width: 610px) { body #pubnews-scroll-to-top.show { display : block } }'
			} else {
				cssCode += '@media(max-width: 610px) { body #pubnews-scroll-to-top.show { display : none } }'
			}
			themeCalls.pubnewsGenerateStyleTag( cssCode, 'pubnews-scroll-to-top-responsive-option' )
		})
	})

	// check if string is variable and formats 
	function pubnews_get_color_format(color) {
		if( color == null ) return color
		if( color.indexOf( '--pubnews-global-preset' ) >= 0 ) {
			return( 'var( ' + color + ' )' );
		} else {
			return color;
		}
	}

	// returns css property and value of background
	function pubnews_get_background_style( control ) {
		if( control ) {
		 var cssCode = '', mediaUrl = '', repeat = '', position = '', attachment = '', size = ''
		 switch( control.type ) {
		 case 'image' : 
				  if( 'media_id' in control.image ) mediaUrl = 'background-image: url(' + control.image.media_url + ');'
				 if( 'repeat' in control ) repeat = " background-repeat: "+ control.repeat + ';'
				 if( 'position' in control ) position = " background-position: "+ control.position + ';'
				 if( 'attachment' in control ) attachment = " background-attachment: "+ control.attachment + ';'
				 if( 'size' in control ) size = " background-size: "+ control.size + ';'
				 return cssCode.concat( mediaUrl, repeat, position, attachment, size )
			 break;
		 default: 
		 if( 'type' in control ) return "background: " + pubnews_get_color_format( control[control.type] )
	   }
	 }
 }

	// constants
	const ajaxFunctions = {
		typoFontsEnqueue: function() {
			var action = themeContstants.prefix + "typography_fonts_url",id ="pubnews-customizer-typo-fonts-css"
			themeCalls.pubnewsGenerateLinkTag( action, id )
		}
	}

	// constants
	const helperFunctions = {
		generateStyle: function(color, id, variable) {
			if(color) {
				if( id == 'theme-color-style' ) {
					var styleText = 'body.pubnews_main_body, body.pubnews_dark_mode { ' + variable + ': ' + helperFunctions.getFormatedColor(color) + '}';
				} else {
					var styleText = 'body.pubnews_main_body { ' + variable + ': ' + helperFunctions.getFormatedColor(color) + '}';
				}
				if( $( "head #" + id ).length > 0 ) {
					$( "head #" + id).text( styleText )
				} else {
					$( "head" ).append( '<style id="' + id + '">' + styleText + '</style>' )
				}
			}
		},
		getFormatedColor: function(color) {
			if( color == null ) return
			if( color.includes('preset') ) {
				return 'var(' + color + ')'
			} else {
				return color
			}
		}
	}
}( jQuery ) );