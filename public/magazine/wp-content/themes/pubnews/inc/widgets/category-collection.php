<?php
/**
 * Adds Pubnews_Category_Collection_Widget widget.
 * 
 * @package Pubnews
 * @since 1.0.0
 */
class Pubnews_Category_Collection_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'pubnews_category_collection_widget',
            esc_html__( 'Pubnews : Category Collection', 'pubnews' ),
            array( 'description' => __( 'A collection of post categories.', 'pubnews' ) )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $widget_title = isset( $instance['widget_title'] ) ? $instance['widget_title'] : '';
        $posts_categories = isset( $instance['posts_categories'] ) ? $instance['posts_categories'] : '';
        $categories_title = isset( $instance['categories_title'] ) ? $instance['categories_title'] : true;
        $categories_count = isset( $instance['categories_count'] ) ? $instance['categories_count'] : true;
        $news_text = isset( $instance['news_text'] ) ? $instance['news_text'] : '';
        $widget_layout = isset( $instance['widget_layout'] ) ? $instance['widget_layout'] : 'layout-one';
        $delay = 0;
        echo wp_kses_post($before_widget);
            if ( ! empty( $widget_title ) ) {
                echo $before_title . $widget_title . $after_title;
            }
    ?>
            <div class="categories-wrap <?php echo esc_attr( $widget_layout ); ?>">
                <?php
                if( $posts_categories != '[]' ) {
                    $postCategories = get_categories( array( 'include' => json_decode( $posts_categories ) ) );
                } else {
                    $postCategories = get_categories(['number'  => 4]);
                }
                    foreach( $postCategories as $cat ) :
                        $cat_count = $categories_count ? $cat->count : '';
                        $cat_id = $cat->cat_ID;
                        $cat_query = array( 
                            'cat'    => absint( $cat_id ),
                            'posts_per_page' => 1,
                            'meta_query' => array(
                                array(
                                 'key' => '_thumbnail_id',
                                 'compare' => 'EXISTS'
                                ),
                            ),
                            'ignore_sticky_posts'    => true
                        );
                        $widget_post = new WP_Query( apply_filters( 'pubnews_query_args_filter', $cat_query ) );
                        $thumbnail_url = '';
                        if( $widget_post->have_posts() ) :
                            while( $widget_post->have_posts() ) : $widget_post->the_post();
                                $thumbnail_url = get_the_post_thumbnail_url();
                            endwhile;
                            wp_reset_postdata();
                        endif;
                ?>
                        <div class="post-thumb post-thumb category-item pubnews-card cat-<?php echo esc_attr( $cat_id ); ?>" <?php echo esc_attr(pubnews_get_block_animation_attr($delay)); ?>>
                            <a class="cat-meta-wrap" href="<?php echo esc_url( get_term_link( $cat_id ) ); ?>">
                                <?php
                                    $figureClass = 'thumbnail-wrap';
                                    $figureClass .= ( $thumbnail_url ) ? ' has-thumbnail' : ' no-feat-img';
                                ?>
                                <figure class="<?php echo esc_attr( $figureClass ); ?>">
                                        <?php if( $thumbnail_url ) : ?>
                                            <img src="<?php echo esc_url( $thumbnail_url ); ?>" loading="<?php pubnews_lazy_load_value(); ?>">
                                        <?php endif; ?>
                                    </figure>
                                <?php
                                    $category_title_html = $categories_title ? '<span class="category-name">'. esc_html( $cat->name ) .'</span>' : '';
                                    $category_count_html = $categories_count ? '<div class="icon-count-wrap"><span class="category-icon"><i class="fa solid fa-arrow-right-long"></i></span><span class="category-count">'. absint( $cat->count ) .' posts</span></div>' : '';
                                    if( $category_title_html || $category_count_html ) echo '<div class="cat-meta pubnews-post-title">';
                                        echo sprintf( '%1s%2s', $category_title_html, $category_count_html );
                                    if( $category_title_html || $category_count_html ) echo '</div>';
                                ?>
                            </a>
                        </div>
                <?php
                    $delay += 100;
                    endforeach;
                ?>
            </div>
    <?php
        echo wp_kses_post($after_widget);
    }

    /**
     * Widgets fields
     * 
     */
    function widget_fields() {
        $postCategories = get_categories();
        $categories_options[''] = esc_html__( 'Select category', 'pubnews' );
        foreach( $postCategories as $category ) :
            $categories_options[$category->term_id] = $category->name. ' (' .$category->count. ') ';
        endforeach;
        return array(
                array(
                    'name'      => 'widget_title',
                    'type'      => 'text',
                    'title'     => esc_html__( 'Widget Title', 'pubnews' ),
                    'description'=> esc_html__( 'Add the widget title here', 'pubnews' ),
                    'default'   => esc_html__( 'Category Collection', 'pubnews' )
                ),
                array(
                    'name'      => 'posts_categories',
                    'type'      => 'multicheckbox',
                    'title'     => esc_html__( 'Post Categories', 'pubnews' ),
                    'description'=> esc_html__( 'Choose the categories to display', 'pubnews' ),
                    'options'   => $categories_options
                ),
                array(
                    'name'      => 'news_text',
                    'type'      => 'text',
                    'title'     => esc_html__( 'News Text', 'pubnews' ),
                    'description'=> esc_html__( 'Add the News Text here', 'pubnews' ),
                    'default'   => esc_html__( 'News', 'pubnews' )
                ),
                array(
                    'name'      => 'widget_layout',
                    'type'      => 'select',
                    'title'     => esc_html__( 'Layouts', 'pubnews' ),
                    'options'   => array(
                        'layout-one'    => esc_html__( 'Layout One', 'pubnews' ),
                        'layout-two'    => esc_html__( 'Layout Two', 'pubnews' )
                    )
                )
            );
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $widget_fields = $this->widget_fields();
        foreach( $widget_fields as $widget_field ) :
            if ( isset( $instance[ $widget_field['name'] ] ) ) {
                $field_value = $instance[ $widget_field['name'] ];
            } else if( isset( $widget_field['default'] ) ) {
                $field_value = $widget_field['default'];
            } else {
                $field_value = '';
            }
            pubnews_widget_fields( $this, $widget_field, $field_value );
        endforeach;
    }
 
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $widget_fields = $this->widget_fields();
        if( ! is_array( $widget_fields ) ) {
            return $instance;
        }
        foreach( $widget_fields as $widget_field ) :
            $instance[$widget_field['name']] = pubnews_sanitize_widget_fields( $widget_field, $new_instance );
        endforeach;

        return $instance;
    }
 
} // class Pubnews_Category_Collection_Widget