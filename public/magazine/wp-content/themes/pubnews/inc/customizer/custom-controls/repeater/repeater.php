<?php
/**
 * Custom WP Repeater Control
 * 
 * @package Pubnews
 * @since 1.0.0
 */
class Pubnews_WP_Custom_Repeater extends \WP_Customize_Control {
    /**
     * Arguments passed to this class
     * 
     * 
     */
    private $args;
    
    /**
     * Default values passed when control is registered
     * 
     * 
     */
    private $defaults;

    /**
     * Row label key value
     * 
     * 
     */
    private $row_label;

    /**
     * Add new button label
     * 
     * 
     */
    private $add_new_label;

    /**
     * Main function
     * 
     * Pubnews_WP_Custom_Repeater class name 
     */
    public function __construct($manager, $id, $args = array()) {
        $this->args = $args;
        $this->row_label = $args['row_label'];
        $this->add_new_label = $args['add_new_label'];
        parent::__construct( $manager, $id, $args );
        $this->defaults = $this->setting->default;
    }
    
    /**
     * Enqueue Scripts
     * 
     */
    function enqueue() {
        wp_enqueue_media();
        wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/assets/lib/fontawesome/css/all.min.css', array(), '6.4.2', 'all' );
        wp_enqueue_style( 'pubnews-wp-repeater', get_template_directory_uri() . '/inc/customizer/custom-controls/repeater/repeater.css', array(), PUBNEWS_VERSION, 'all' );
        wp_enqueue_script( 'pubnews-wp-repeater', get_template_directory_uri() . '/inc/customizer/custom-controls/repeater/repeater.js', array( 'jquery' ), PUBNEWS_VERSION, true );
    }

    /**
     * For The displaying the structure in Customizer
     */
    public function render_content() {
        $fields = json_decode( json_encode( $this->args['fields'] ) );
        $control_values = $this->value();
        $control_values = ( ! empty( $control_values ) ) ? json_decode( $control_values ) : json_decode( json_encode( array( $fields ) ) );
        $item_count = 1;
        ?>
        <div class="pubnews-repeater-control">
            <label class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
            <?php if( $this->description ) { ?>
                <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
            <?php } ?>
            <div class="pubnews-repeater-control-inner">
                <?php 
                $row_label = $this->row_label;
                 foreach( $control_values as $control_value_key => $control_value ) :
                    if( str_contains( $this->row_label, 'inherit' ) )  {
                        $label_array = explode( "-", $this->row_label );
                        if( $label_array[1] == 'icon_class' ) {
                            $newlabel_array = explode( "-", $control_value->{$label_array[1]} );
                            $row_label = $newlabel_array[1];
                        } else {
                            $row_label = $control_value->{$label_array[1]};
                        }
                    }
                  ?>
                    <div class="pubnews-repeater-item <?php if( $control_value->item_option === 'show' ) : echo 'visible'; else : echo 'not-visible'; endif; ?>">
                        <div class="item-heading-wrap">
                            <span class="item-heading"><?php echo esc_html( $row_label ); ?></span>
                            <span class="dashicons-arrow-down-alt2 dashicons settings-icon"></span>
                            <span class="display-icon dashicons dashicons-<?php if( $control_value->item_option === 'show' ) : echo 'visibility'; else : echo 'hidden'; endif; ?>"></span>
                        </div>
                        <div class="item-control-fields isHidden">
                            <?php
                                foreach( $fields as $field_key => $field_val ) :
                                    if( $field_key != 'item_option' ) {
                                        $this->render_control( $field_key, $field_val, $control_value );
                                    } else {
                                        echo '<input type="hidden" class="repeater-field-value-holder" data-default="' .esc_attr($field_val). '" data-key="' .esc_attr( $field_key ). '" value="' .esc_attr( $control_value->$field_key ). '">';
                                    }
                                endforeach;
                            ?>
                        </div>
                    </div>
                <?php $item_count++; endforeach; ?>
                <div class="buttons-wrap">
                    <input class="repeater-control-value-holder" type="hidden" <?php echo esc_attr( $this->link() ); ?> value="<?php echo esc_attr(json_encode( $control_values )); ?>"/>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render control field w.r.t paramater 
     * 
     */
    function render_control( $field_key, $field, $current_item_value ) {
        $type = $field->type;
        $label = isset( $field->label ) ? $field->label : esc_html__( 'Item Label', 'pubnews' );
        $description = isset( $field->description ) ? $field->description : false;
        $default_value = isset( $field->default ) ? $field->default : '';
        $families = isset( $field->families ) ? $field->families : '';
        // filter the contrrol type parameter
        switch( $type ) {
            case 'text' : ?>
                <div class="single-control text-field">
                    <h2 class="control--item-label"><?php echo esc_html( $label ); ?></h2>
                    <?php if( $description ) { ?>
                        <span class="control--item-description"><?php echo esc_html( $description ); ?></span>
                    <?php } ?>
                    <input class="repeater-field-value-holder" data-default="<?php echo esc_attr( $default_value ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>" type="text" value="<?php echo esc_attr( $current_item_value->$field_key ); ?>">
                </div>
            <?php
            break;
            case 'image' : ?>
                <div class="single-control image-field">
                    <h2 class="control--item-label"><?php echo esc_html( $label ); ?></h2>
                    <?php if( $description ) { ?>
                        <span class="control--item-description"><?php echo esc_html( $description ); ?></span>
                    <?php } ?>
                    <div class="image-holder">
                        <div class="image-element <?php if( ! $current_item_value->$field_key ) echo 'no-image'; ?>">
                            <?php
                                if( $current_item_value->$field_key ) :
                                    if( wp_get_attachment_image_url( $current_item_value->$field_key ) ) :
                                        echo '<img src="' .wp_get_attachment_image_url( $current_item_value->$field_key ). '">';
                                    endif;
                                else :
                                    echo '<img>';
                                endif;
                            ?>
                            <span class="remove-image dashicons dashicons-trash"></span>
                        </div>
                        <div class="add-image-trigger <?php if( $current_item_value->$field_key ) echo 'no-trigger'; ?>"><?php echo esc_html__( 'Upload Image', 'pubnews'); ?></div>
                        <input class="repeater-field-value-holder" data-default="<?php echo esc_attr( $default_value ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>" type="hidden" value="<?php echo esc_attr( $current_item_value->$field_key ); ?>"/>
                    </div>
                </div>
            <?php
                break;
            case 'fontawesome-icon-picker' : $icons_list = pubnews_get_all_fontawesome_icons( $families ); ?>
                <div class="single-control fontawesome-icon-picker">
                    <h2 class="control--item-label"><?php echo esc_html( $label ); ?></h2>
                    <?php if( $description ) { ?>
                        <span class="control--item-description"><?php echo esc_html( $description ); ?></span>
                    <?php } ?>
                    <div class="icon-holder">
                        <div class="icon-header">
                            <div class="active-icon"><i class="<?php echo esc_attr( $current_item_value->$field_key ); ?>"></i></div>
                            <div class="icon-list-trigger"><i class="fas fa-angle-down"></i></div>
                        </div>
                        <div class="icons-list" style="display:none;">
                            <input class="icon-search-input" type="text" placeholder="<?php echo esc_attr__( "Type to search", "pubnews" ); ?>"/>
                            <?php
                                foreach( $icons_list as $icon ) :
                                ?>
                                    <i class="<?php echo esc_attr( $icon ); ?> <?php if( $icon === $current_item_value->$field_key ) echo 'selected'; ?>"></i>
                                <?php
                                endforeach;
                            ?>
                        </div>
                        <input class="repeater-field-value-holder" data-default="<?php echo esc_attr( $default_value ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>" type="hidden" value="<?php echo esc_attr( $current_item_value->$field_key ); ?>"/>
                    </div>
                </div>
            <?php
                break;
            case 'textarea' : ?>
                <div class="single-control textarea-field">
                    <h2 class="control--item-label"><?php echo esc_html( $label ); ?></h2>
                    <?php if( $description ) { ?>
                        <span class="control--item-description"><?php echo esc_html( $description ); ?></span>
                    <?php } ?>
                    <textarea class="repeater-field-value-holder" data-default="<?php echo esc_attr( $default_value ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>" rows="5"><?php echo wp_kses_post( $current_item_value->$field_key ); ?></textarea>
                </div>
            <?php
            break;
            case 'url' : ?>
                <div class="single-control url-field">
                    <h2 class="control--item-label"><?php echo esc_html( $label ); ?></h2>
                    <?php if( $description ) { ?>
                        <span class="control--item-description"><?php echo esc_html( $description ); ?></span>
                    <?php } ?>
                    <input class="repeater-field-value-holder" data-default="<?php echo esc_attr( $default_value ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>" type="url" value="<?php echo esc_attr( $current_item_value->$field_key ); ?>"/>
                </div>
            <?php
            break;
            default: echo 'Not set';
        }   
    }
}