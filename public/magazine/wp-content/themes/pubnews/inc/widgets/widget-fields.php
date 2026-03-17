<?php
/**
 * Includes widget fields
 * 
 * @package Pubnews
 * @since 1.0.0
 */

function pubnews_widget_fields( $instance, $args, $field_value ) {
    echo '<div class="pubnews-widget-field pubnews-' .esc_html( $args['type'] ). '-field">';
        switch( $args['type'] ) {
            case 'number' : ?>
                            <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                            <?php
                                if( isset( $args['description'] ) ) {
                                    echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                                }
                            ?>
                            <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="number" value="<?php echo esc_attr( $field_value ); ?>"/>
                <?php
                break;
            case 'heading' : ?>
                        <div class="heading"><?php echo esc_html( $args['label'] ); ?></div>
            <?php
            break;
            case 'text' : ?>
                            <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                            <?php
                                if( isset( $args['description'] ) ) {
                                    echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                                }
                            ?>
                            <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="text" value="<?php echo esc_attr( $field_value ); ?>"/>
                <?php
                break;
            case 'icon-text' : $field_value_formatted = json_decode( $field_value );
                               $icons = pubnews_get_tabbed_icon_classes();
             ?>
                        <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                        <?php
                            if( isset( $args['description'] ) ) {
                                echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                            }
                        ?>
                        <div class="field-group-wrap">
                            <div class="icon-field" data-value="<?php echo esc_attr( $field_value_formatted->icon ); ?>">
                                <span class="icon-selector"><i class="<?php echo esc_attr( $field_value_formatted->icon ); ?>"></i></span>
                            </div>
                            <div class="text-field">
                                <input type="text" value="<?php echo esc_attr( $field_value_formatted->title ); ?>">
                            </div>
                            <span class="icon-selector-wrap">
                                <?php
                                    foreach( $icons as $icon ) :
                                        echo '<i class="' .esc_attr( $icon ). '"></i>';
                                    endforeach; 
                                ?>
                            </span>
                        </div>
                        <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="hidden" value="<?php echo esc_attr( $field_value ); ?>"/>
            <?php
            break;
            case 'url' : ?>
                            <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                            <?php
                                if( isset( $args['description'] ) ) {
                                    echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                                }
                            ?>
                            <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="url" placeholder="<?php esc_attr_e( 'Add url here . .', 'pubnews' ); ?>" value="<?php echo esc_url( $field_value ); ?>" />
                <?php
                break;
            case 'textarea' : ?>
                            <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                            <?php
                                if( isset( $args['description'] ) ) {
                                    echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                                }
                            ?>
                            <textarea class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" ><?php echo wp_kses_post( $field_value ); ?></textarea>
                <?php
                break;
            case 'checkbox' : ?>
                    <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="checkbox" value="<?php echo esc_attr( $field_value ); ?>" <?php checked( $field_value, true ); ?> />
                    <label for="<?php echo $instance->get_field_id( $args['name'] ); ?>"><?php echo esc_html( $args['title'] ); ?></label>
                    <?php
                        if( isset( $args['description'] ) ) {
                            echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                        }
                break;
            case 'upload' : ?>
                            <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                            <?php
                                if( isset( $args['description'] ) ) {
                                    echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                                }
                            ?>
                            <div class="upload-trigger<?php if( $field_value ) echo ' selected'; ?>">
                                <span><?php esc_html_e( 'Add image', 'pubnews' ); ?></span>
                            </div>
                            <div class="upload-buttons<?php if( ! $field_value ) echo ' not-selected'; ?>">
                                <img class="image-holder <?php if( ! $field_value ) echo 'nothasImage'; ?>" src="<?php echo esc_url( $field_value ); ?>">
                                <button class="button button-link-delete remove-image"><?php esc_html_e( 'Remove image', 'pubnews' ); ?></button>
                            </div>
                            <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="hidden" value="<?php echo esc_attr( $field_value ); ?>" />
                <?php
                break;
            case 'multicheckbox' :
                    $options = $args['options'];
            ?>
                    <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                    <?php
                        if( isset( $args['description'] ) ) {
                            echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                        }
                    ?>
                    <div class="multicheckbox-content">
                        <?php
                        if( empty( $field_value ) ) {
                            $field_value = [];
                        } else {
                            $field_value = json_decode( $field_value, true );
                        }
                            foreach( $options as $option_key => $option_value ) :
                        ?>
                                <div class="multicheckbox-single-item">
                                    <input type="checkbox" id="<?php echo $instance->get_field_name( $args['name'] ).'['.$option_key.']'; ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php if( is_array( $field_value ) ) if( in_array( $option_key, $field_value ) ) echo 'checked'; ?>>
                                    <label for="<?php echo $instance->get_field_name( $args['name'] ).'['.$option_key.']'; ?>"><?php echo esc_html( $option_value ); ?></label>
                                </div>
                        <?php
                            endforeach;
                        ?>
                    </div>
                    <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="hidden" value=<?php echo json_encode( $field_value ); ?> />
            <?php
                break;
            case 'repeater' :
                $fields = $args['fields'];
            ?>
                    <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                    <?php
                        if( isset( $args['description'] ) ) {
                            echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                        }
                    ?>
                    <div class="repeater-field-content-area">
                        <?php
                        if( empty( $field_value ) ) {
                            $field_value = [];
                        } else {
                            $field_value = json_decode( $field_value, true );
                        }
                        
                        if( ! empty( $field_value ) ) :
                            foreach( $field_value as $single_key => $single_value ) :
                            ?>
                                <div class="repeater-single-item">
                                    <div class="single-item-heading">
                                        <span class="heading-text"><?php esc_html_e( 'Item', 'pubnews' ); ?></span><span class="heading-icon"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                    <div class="single-item-edit-area">
                                        <?php
                                            foreach( $single_value as $single_value_inner_key => $single_value_inner_value ) {
                                                switch( $fields[$single_value_inner_key]['type'] ) {
                                                    case 'url' : ?>
                                                            <div class="single-field">
                                                                <label><?php echo esc_html( $fields[$single_value_inner_key]['label'] ); ?></label>
                                                                <?php
                                                                    if( isset( $args['description'] ) ) {
                                                                        echo '<p class="description">' .esc_html( $fields[$single_value_inner_key]['description'] ). '</p>';
                                                                    }
                                                                ?>
                                                                <input type="url" class="single-field-form-field" value="<?php echo esc_attr( $single_value_inner_value ); ?>" data-name="<?php echo esc_attr( $single_value_inner_key ); ?>">
                                                            </div>
                                                        <?php
                                                            break;
                                                        case 'image' : 
                                                            $image_url = ( $single_value_inner_value ) ? wp_get_attachment_url($single_value_inner_value) : '';
                                                        ?>
                                                                <div class="single-field image-field">
                                                                    <label><?php echo esc_html( $fields[$single_value_inner_key]['label'] ); ?></label>
                                                                    <?php
                                                                        if( isset( $args['description'] ) ) {
                                                                            echo '<p class="description">' .esc_html( $fields[$single_value_inner_key]['description'] ). '</p>';
                                                                        }
                                                                    ?>
                                                                    <div class="image-preview">
                                                                        <img src="<?php echo esc_url( wp_get_attachment_url($single_value_inner_value) ); ?>" alt="" class="image-preview-element">
                                                                        <button class="upload-image <?php if( ! empty( $image_url ) ) echo esc_attr('hide'); ?>"><?php echo esc_html__( 'Upload Image', 'pubnews' ); ?></button>
                                                                        <button class="remove-image <?php if( empty( $image_url ) ) echo esc_attr('hide'); ?>"><?php echo esc_html__( 'Remove Image', 'pubnews' ); ?></button>
                                                                    </div>
                                                                    <input type="hidden" class="single-field-form-field" value="<?php echo esc_attr( $single_value_inner_value ); ?>" data-name="<?php echo esc_attr( $single_value_inner_key ); ?>">
                                                                </div>
                                                            <?php
                                                                break;
                                                    default: esc_html__( 'Invalid field', 'pubnews' );
                                                }
                                            }
                                        ?>
                                        <div class="single-item-actions"><button class="remove-item"><?php echo esc_html__( 'Remove item', 'pubnews' ) ?></button></div>
                                    </div>
                                </div>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <input class="widefat" id="<?php echo $instance->get_field_id( $args['name'] ); ?>" name="<?php echo $instance->get_field_name( $args['name'] ); ?>" type="hidden" value='<?php echo json_encode( $field_value ); ?>'>
                    <div class="field-actions"><button class="add-item"><?php echo esc_html__( 'Add item', 'pubnews' ) ?></button></div>
            <?php
                break;
            case 'select' :
                $options = $args['options'];
        ?>
                <h2 class="title"><?php echo esc_html( $args['title'] ); ?></h2>
                <?php
                    if( isset( $args['description'] ) ) {
                        echo '<p class="description">' .esc_html( $args['description'] ). '</p>';
                    }
                    echo '<select class="widefat" id="' .$instance->get_field_id( $args['name'] ). '" name="' .$instance->get_field_name( $args['name'] ). '">';
                        foreach( $options as $option_key => $option_value ) :
                ?>
                            <option value="<?php echo esc_attr( $option_key ); ?>" <?php if( $option_key === $field_value ) echo 'selected'; ?>><?php echo esc_html( $option_value ); ?></option>
                <?php
                        endforeach;
                    echo '</select>';
                    ?>
                <?php
                break;
            default : esc_html( 'Undefined control field', 'pubnews' );
                break;
        }
    echo '</div>';
}

// Sanitize widget fields
function pubnews_sanitize_widget_fields( $widget_field, $new_instance ) {
    if( $widget_field['type'] === 'text' || $widget_field['type'] === 'select' || $widget_field['type'] === 'multicheckbox' ) {
        return sanitize_text_field( $new_instance[$widget_field['name']] );
    } else if( $widget_field['type'] === 'checkbox' ) {
        return ( isset($new_instance[$widget_field['name']]) && $new_instance[$widget_field['name']] ) ? true : false;
    } else if( $widget_field['type'] === 'number' ) {
        return absint( $new_instance[$widget_field['name']] );
    } else if( $widget_field['type'] === 'textarea' ) {
        return wp_kses_post( $new_instance[$widget_field['name']] );
    } else if( $widget_field['type'] === 'upload' ) {
        return absint( $new_instance[$widget_field['name']] );
    } else if ( $widget_field['type'] === 'repeater' ) {
        $repeater_instance = $new_instance[$widget_field['name']];
        if( is_array( $repeater_instance ) && ! empty( $repeater_instance ) ) {
            $repeater = [];
            $test = json_decode( $repeater_instance, true );
            foreach( $test as $repeater_key => $repeater_value ) :
                foreach( $repeater_value as $field_key => $field_value ) :
                    if( is_numeric( $field_value ) ) {
                        $repeater[$field_key] = absint( $field_value );
                    } else {
                        $repeater[$field_key] = sanitize_text_field( $field_value );
                    }
                endforeach;
            endforeach;
            return $repeater;
        } else {
            return ( $repeater_instance );
        }
    } else {
        if( isset($new_instance[$widget_field['name']]) ) {
            return sanitize_text_field( $new_instance[$widget_field['name']] );
        } else {
            return;
        }
    }
}