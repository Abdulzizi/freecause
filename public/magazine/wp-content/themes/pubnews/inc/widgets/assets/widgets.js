/**
 * Hjandles widgets scripts
 * 
 * @package Pubnews
 * @since 1.0.0
 */
jQuery(document).ready( function($) {

    // repeater field handler
        // on form field change
        $(document).on( "change", ".pubnews-repeater-field .single-field-form-field", function(event) {
            event.preventDefault()
            var _thisParent = $(this).parents(".pubnews-repeater-field")
            $(this).val( event.target.value )
            renderRepeaterValue(_thisParent)
        })

        // on item edit area toggle
        $(document).on( "click", ".pubnews-repeater-field .single-item-heading", function(event) {
            event.preventDefault()
            var _thisHeading = $(this), parentItem = _thisHeading.parent()
            _thisHeading.find(".heading-icon i").toggleClass("fa-chevron-up fa-chevron-down")
            _thisHeading.next().toggle()
            parentItem.siblings().find(".single-item-heading .heading-icon i").removeClass("fa-chevron-up").addClass("fa-chevron-down")
            parentItem.siblings().find(".single-item-edit-area").slideUp()
        })

        // on item remove
        $(document).on( "click", ".pubnews-repeater-field .single-item-actions .remove-item", function(event) {
            event.preventDefault()
            var removeItemButton = $(this)
            var _thisParent = removeItemButton.parents(".pubnews-repeater-field")
            removeItemButton.parents(".repeater-single-item").remove()
            renderRepeaterValue(_thisParent)
        })

        // trigger image upload
        $(document).on( "click", ".pubnews-repeater-field .image-field .upload-image", function(event) {
            event.preventDefault();
            var uploadButton = $(this)
            var _thisParent = uploadButton.parents(".pubnews-repeater-field")
            if ( frame ) {
                frame.open();
                return;
            }
            var frame = wp.media({
                title: 'Select or Upload Image',
                button: {
                    text: 'Add Image'
                },
                multiple: false
            });
            frame.open();
            frame.on( 'select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                uploadButton.addClass("hide")
                uploadButton.next().removeClass("hide")
                uploadButton.prev().attr( "src", attachment.url );
                uploadButton.parent().next().val( attachment.id );
                renderRepeaterValue(_thisParent)
                frame.close();
            })
        })

        $(document).on( "click", ".pubnews-repeater-field .image-field .remove-image", function(event) {
            event.preventDefault();
            var removeButton = $(this)
            var _thisParent = removeButton.parents(".pubnews-repeater-field")
            removeButton.addClass("hide")
            removeButton.prev().removeClass("hide")
            removeButton.parent().find("img").attr("src","")
            removeButton.parent().next().val(0);
            renderRepeaterValue(_thisParent)
        })

        // on add item
        $(document).on( "click", ".pubnews-repeater-field .add-item", function(event) {
            event.preventDefault()
            var addFieldButton = $(this)
            var _thisParent = addFieldButton.parents(".pubnews-repeater-field")
            var parentContainer = addFieldButton.parents(".pubnews-repeater-field"), newItemToRender = parentContainer.find( ".repeater-field-content-area" ), newItem = parentContainer.find( ".repeater-field-content-area .repeater-single-item" ).last().clone()
            parentContainer.find( ".repeater-field-content-area .repeater-single-item .single-item-heading .heading-icon i" ).removeClass("fa-chevron-up").addClass("fa-chevron-down")
            parentContainer.find( ".repeater-field-content-area .repeater-single-item .single-item-edit-area" ).slideUp()
            newItem.find(".single-field-form-field").val("")
            newItem.find(".image-field .remove-image").addClass("hide")
            newItem.find(".image-field .upload-image").removeClass("hide")
            newItem.find(".image-field img").attr("src","")
            newItem.find(".single-item-heading .heading-icon i").removeClass("fa-chevron-down").addClass("fa-chevron-up")
            newItem.find(".single-item-edit-area").slideDown()
            newItemToRender.append(newItem)
            renderRepeaterValue(_thisParent)
        })
        // update repeater fields value
        function renderRepeaterValue(parentContainer) {
            var elementToRenderValue = parentContainer.find(".widefat"), items = parentContainer.find(".repeater-single-item"), newRepeaterValue = []
            if( items.length > 0 ) {
                items.each(function() {
                    var _thisItem = $(this), fields = _thisItem.find(".single-field-form-field"), newItemValue = {}
                    if( fields.length > 0 ) {
                        fields.each(function() {
                            var _thisField = $(this), fieldName = _thisField.data("name"), fieldValue = _thisField.val()
                            newItemValue[fieldName] = fieldValue
                        })
                    }
                    newRepeaterValue.push(newItemValue)
                })
                elementToRenderValue.val(JSON.stringify(newRepeaterValue)).trigger("change")
            }
        }

    function pubnews_widgets_handler() {
        // multicheckbox field
        $( ".pubnews-multicheckbox-field" ).on( "click, change", ".multicheckbox-content input", function() {
            var _this = $(this), parent = _this.parents( ".pubnews-multicheckbox-field" ), currentVal, currentFieldVal = parent.find( ".widefat" ).val();
            currentFieldVal = JSON.parse( currentFieldVal )
            currentVal = _this.val();
            if( _this.is(":checked") ) {
                if( currentFieldVal != 'null' ) {
                    currentFieldVal.push(currentVal)
                }
            } else {
                if( currentFieldVal != 'null' ) {
                    currentFieldVal.splice( $.inArray( currentVal, currentFieldVal ), 1 );
                }
            }
            parent.find( ".widefat" ).val(JSON.stringify(currentFieldVal))
        })

        // checkbox field
        $( ".pubnews-checkbox-field" ).on( "click, change", "input", function() {
            var _this = $(this)
            if( _this.is(":checked") ) {
                _this.val( "1" )
            } else {
                _this.val( "0" )
            }
        })

        // upload field
        $( ".pubnews-upload-field" ).on( "click", ".upload-trigger", function(event) {
            event.preventDefault();
            if ( frame ) {
                frame.open();
                return;
            }
            var _this = $(this), frame = wp.media({
                title: 'Select or Upload Author Image',
                button: {
                    text: 'Add Author Image'
                },
                multiple: false
            });
            frame.open();
            frame.on( 'select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                _this.toggleClass( "selected not-selected" );
                _this.next().toggleClass( "selected not-selected" );
                _this.next().find("img").attr( "src", attachment.url ).toggleClass( "nothasImage" );
                _this.siblings(".widefat").val( attachment.id ).trigger("change");
            })
        })
        // remove image
        $( ".pubnews-upload-field" ).on( "click", ".upload-buttons .remove-image", function(event) {
            event.preventDefault();
            var _this = $(this);
            _this.prev().attr( "src", "" ).toggleClass( "nothasImage" );
            _this.parent().toggleClass( "selected not-selected" ).prev().toggleClass( "selected not-selected" );
            _this.parent().next().val( "" ).trigger("change");
        })

        // icon text handler
        var iconTextContainer = $( ".pubnews-icon-text-field" )
        iconTextContainer.each(function() {
            var _this = $(this), iconSelector = _this.find( ".icon-selector-wrap" ), iconField = _this.find( ".icon-field" ), textField = _this.find( ".text-field input" )
            iconSelector.on( "click", "i", function() {
                var newIcon = $(this).attr( "class" )
                iconField.data( "value", newIcon )
                iconField.find( ".icon-selector i" ).removeClass().addClass(newIcon)
                setIconTextFieldValue(_this,iconField,textField)
            })
            textField.on( "change", function() {
                setIconTextFieldValue(_this,iconField,textField)
            })
            iconField.each(function(){
                $(this).on( "click", function() {
                    var innerThis = $(this)
                    innerThis.siblings(".icon-selector-wrap" ).toggleClass('isactive')
                })
            })
        })

        function setIconTextFieldValue(el,iconEl,txtEl) {
            el.find( 'input.widefat[type="hidden"]' ).val(JSON.stringify( {icon: iconEl.data( "value" ), title: txtEl.val()} )).trigger("change")
        }

        
    }
    pubnews_widgets_handler();
    // run on widgets added and updated
    $( document ).on( 'load widget-added widget-updated', function() {
        pubnews_widgets_handler();
    });
})