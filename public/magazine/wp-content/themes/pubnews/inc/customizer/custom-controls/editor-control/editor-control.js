/**
 * Editor control JS to handle the editor rendering within customize control.
 *
 * @package Pubnews
 * @since 1.0.0
 */
 wp.customize.controlConstructor[ 'pubnews-editor' ] = wp.customize.Control.extend( {
	ready : function () {
		'use strict';
		var control = this,
		    id      = 'editor_' + control.id;

		if ( wp.editor && wp.editor.initialize ) {
			wp.editor.initialize( id, {
				tinymce      : {
					wpautop : false,
					forced_root_block : "",
				},
				quicktags    : true,
				mediaButtons : true
			} );
		}
	},

	onChangeActive : function ( active, args ) {
		'use strict';
		var control = this,
		    id      = 'editor_' + control.id,
		    element = control.container.find( 'textarea' ),
		    editor;

		editor = tinyMCE.get( id );

		if ( editor ) {

			editor.onChange.add( function ( ed ) {
				var content;

				ed.save();
				content = editor.getContent();
				element.val( content ).trigger( 'change' );
				wp.customize.instance( control.id ).set( content );
			} );

		}
	}
});
