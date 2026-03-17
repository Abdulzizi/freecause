<?php
/**
 * Adds Pubnews_Tabbed_Posts_Widget widget.
 * 
 * @package Pubnews
 * @since 1.0.0
 */
class Pubnews_Tabbed_Posts_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'pubnews_tabbed_posts_widget',
            esc_html__( 'Pubnews : Tabbed Posts', 'pubnews' ),
            array( 'description' => __( 'A collection of tabbed posts from specific category.', 'pubnews' ) )
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
        $latest_tab_title = isset( $instance['latest_tab_title'] ) ? json_decode( $instance['latest_tab_title'], true ) : [];
        $latest_tab_title['icon'] = isset($latest_tab_title['icon']) ? $latest_tab_title['icon'] : 'far fa-clock';
        $latest_tab_title['title'] = isset($latest_tab_title['title']) ? $latest_tab_title['title'] : esc_html__( 'Latest', 'pubnews' );
        $latest_tab_count = isset( $instance['latest_tab_count'] ) ? $instance['latest_tab_count'] : 4;
        $popular_tab_title = isset( $instance['popular_tab_title'] ) ? json_decode( $instance['popular_tab_title'], true ) : [];
        $popular_tab_title['icon'] = isset($popular_tab_title['icon']) ? $popular_tab_title['icon'] : 'fas fa-fire';
        $popular_tab_title['title'] = isset($popular_tab_title['title']) ? $popular_tab_title['title'] : esc_html__( 'Popular', 'pubnews' );
        $popular_tab_count = isset( $instance['popular_tab_count'] ) ? $instance['popular_tab_count'] : 4;
        $popular_tab_category = isset( $instance['popular_tab_category'] ) ? $instance['popular_tab_category'] : '';
        $comments_tab_title = isset( $instance['comments_tab_title'] ) ? json_decode( $instance['comments_tab_title'], true ) : [];
        $comments_tab_title['icon'] = isset($comments_tab_title['icon']) ? $comments_tab_title['icon'] : 'far fa-comments';
        $comments_tab_title['title'] = isset($comments_tab_title['title']) ? $comments_tab_title['title'] : esc_html__( 'Comments', 'pubnews' );
        $comments_tab_count = isset( $instance['comments_tab_count'] ) ? $instance['comments_tab_count'] : 4;
        $tab_order = isset( $instance['tab_order'] ) ? $instance['tab_order'] : 'latest-popular-comments';
        $tabbed_posts_tab_section_order = explode( '-', $tab_order );

        echo wp_kses_post($before_widget);
            ?>
            <div class="pubnews-tabbed-widget-tabs-wrap">
                <ul class="tabbed-widget-tabs">
                    <?php
                        $tab_count = 0;
                        foreach( $tabbed_posts_tab_section_order as $tab_key => $tab_section_order ) :
                        switch( $tab_section_order ) {
                            case 'latest': ?> <li class="tabbed-widget latest-tab<?php if( $tab_count == 0 ) echo ' active'; ?>" tab-item="latest">
                                        <?php if( $latest_tab_title['icon'] != 'fas fa-ban' ) { ?><i class="<?php echo esc_attr( $latest_tab_title['icon'] ); ?>"></i><?php } ?>
                                        <?php echo esc_html( $latest_tab_title['title'] ); ?></li>
                                    <?php
                                    $tab_count++;
                                        break;
                            case 'popular': ?> <li class="tabbed-widget popular-tab<?php if( $tab_count == 0 ) echo ' active'; ?>" tab-item="popular">
                                                <?php if( $popular_tab_title['icon'] != 'fas fa-ban' ) { ?><i class="<?php echo esc_attr( $popular_tab_title['icon'] ); ?>"></i><?php } ?><?php echo esc_html( $popular_tab_title['title'] ); ?></li>
                                            <?php
                                            $tab_count++;
                                                break;
                            case 'comments': ?> <li class="tabbed-widget comments-tab<?php if( $tab_count == 0 ) echo ' active'; ?>" tab-item="comments">
                                            <?php if( $comments_tab_title['icon'] != 'fas fa-ban' ) { ?><i class="<?php echo esc_attr( $comments_tab_title['icon'] ); ?>"></i><?php } ?><?php echo esc_html( $comments_tab_title['title'] ); ?></li>
                                            <?php
                                            $tab_count++;
                                                break;
                        }
                    endforeach; ?>
                </ul>
                <div class="widget-tabs-content">
                    <?php
                        $tab_ccount = 0;
                        foreach( $tabbed_posts_tab_section_order as $tab_key => $tab_section_order ) :
                            ?>
                            <div class="tab-item<?php if( $tab_ccount == 0 ) echo ' active'; ?>" tab-content="<?php echo esc_attr( $tab_section_order ); ?>">
                            <?php
                                switch( $tab_section_order ) {
                                    case 'latest': $latest_tab_posts = get_posts( apply_filters( 'pubnews_query_args_filter', [ 'numberposts' => absint( $latest_tab_count ) ] ) );
                                                    if( $latest_tab_posts ) :
                                                        $delay = 0;
                                                        foreach( $latest_tab_posts as $latest_tab_post ) :
                                                            $latest_tab_id  = $latest_tab_post->ID;
                                                        ?>
                                                            <article class="post-item pubnews-category-no-bk pubnews-card <?php if(!has_post_thumbnail($latest_tab_id)){ echo esc_attr('no-feat-img');} ?>" <?php echo esc_attr(pubnews_get_block_animation_attr($delay)); ?>>
                                                                <figure class="post-thumb">
                                                                    <?php if( has_post_thumbnail($latest_tab_id) ): ?>
                                                                    <a href="<?php echo esc_url(get_the_permalink($latest_tab_id));?>"><img src="<?php echo esc_url( get_the_post_thumbnail_url($latest_tab_id, 'pubnews-grid') ); ?>"/></a>
                                                                    <?php endif; ?>
                                                                </figure>
                                                                <div class="post-element">
                                                                    <h2 class="post-title"><a href="<?php the_permalink($latest_tab_id); ?>"><?php echo wp_kses_post(get_the_title($latest_tab_id) ); ?></a></h2>
                                                                    <div class="post-meta">
                                                                        <?php pubnews_get_post_categories($latest_tab_id,2); ?>
                                                                        <?php pubnews_posted_on($latest_tab_id); ?>
                                                                    </div>
                                                                </div>
                                                            </article>
                                                        <?php
                                                        $delay += 100;
                                                        endforeach;
                                                    endif;
                                                    $tab_ccount++;
                                                break;
                                    case 'popular':    $popular_tab_posts = get_posts( apply_filters( 'pubnews_query_args_filter', [ 'numberposts' => absint( $popular_tab_count ), 'cat' => absint( $popular_tab_category ) ] ) );
                                                        if( $popular_tab_posts ) :
                                                            $delay = 0;
                                                            foreach( $popular_tab_posts as $popular_tab_post ) :
                                                                $popular_tab_id  = $popular_tab_post->ID;
                                                            ?>
                                                                <article class="post-item pubnews-category-no-bk pubnews-card <?php if(!has_post_thumbnail($popular_tab_id)){ echo esc_attr('no-feat-img');} ?>" <?php echo esc_attr(pubnews_get_block_animation_attr($delay)); ?>>
                                                                    <figure class="post-thumb">
                                                                        <?php if( has_post_thumbnail($popular_tab_id) ): ?>
                                                                            <a href="<?php echo esc_url(get_the_permalink($popular_tab_id));?>"><img src="<?php echo esc_url( get_the_post_thumbnail_url($popular_tab_id, 'pubnews-grid') ); ?>"/></a>
                                                                        <?php endif; ?>
                                                                    </figure>
                                                                    <div class="post-element">
                                                                        <h2 class="post-title"><a href="<?php the_permalink($popular_tab_id); ?>"><?php echo wp_kses_post(get_the_title($popular_tab_id) ); ?></a></h2>
                                                                        <div class="post-meta">
                                                                            <?php pubnews_get_post_categories( $popular_tab_id, 2 ); ?>
                                                                            <?php pubnews_posted_on($popular_tab_id); ?>
                                                                        </div>
                                                                    </div>
                                                                </article>
                                                            <?php
                                                            $delay += 100;
                                                            endforeach;
                                                        endif;
                                                        $tab_ccount++;
                                                        break;
                                    case 'comments': $tab_comments = get_comments(array( 'number'   => absint( $comments_tab_count ) ));
                                                        if( $tab_comments ) :
                                                            $delay = 0;
                                                            foreach( $tab_comments as $tab_comment ) :
                                                        ?>
                                                                <div class="comment-item pubnews-card" <?php echo esc_attr(pubnews_get_block_animation_attr($delay)); ?>>
                                                                    <figure class="pubnews_avatar">
                                                                            <a href="<?php echo esc_url( get_comment_link( $tab_comment->comment_ID ) ); ?>">
                                                                                <?php echo get_avatar( $tab_comment->comment_author_email, 50 ); ?>     
                                                                            </a>                               
                                                                    </figure> 
                                                                    <div class="pubnews-comm-content">
                                                                        <a href="<?php echo esc_url( get_comment_link( $tab_comment->comment_ID ) ); ?>">
                                                                            <span class="pubnews-comment-author"><?php echo esc_html( get_comment_author( $tab_comment->comment_ID ) ); ?> </span> - <span class="pubnews_comment_post"><?php echo esc_html( get_the_title($tab_comment->comment_post_ID) ); ?></span>
                                                                        </a>
                                                                        <p class="pubnews-comment">
                                                                            <?php echo wp_kses_post( $tab_comment->comment_content ); ?>
                                                                        </p>
                                                                    </div>
                                        
                                                                </div>
                                                        <?php
                                                            $delay += 100;
                                                            endforeach;
                                                        endif;
                                                        $tab_ccount++;
                                                        break;
                                }
                            ?>
                            </div>
                        <?php
                    endforeach; ?>
                </div>
            </div>
    <?php
        echo wp_kses_post($after_widget);
    }

    /**
     * Widgets fields
     * 
     */
    function widget_fields() {
        $categories = get_categories();
        $categories_options[''] = esc_html__( 'Select category', 'pubnews' );
        foreach( $categories as $category ) :
            $categories_options[$category->term_id] = $category->name. ' (' .$category->count. ') ';
        endforeach;
        return array(
                array(
                    'name'      => 'latest_tab_title',
                    'type'      => 'icon-text',
                    'title'     => esc_html__( 'Latest Tab', 'pubnews' ),
                    'default'   => json_encode( array( 'icon' => 'far fa-clock', 'title'    => esc_html__( 'Latest', 'pubnews' ) ) )
                ),
                array(
                    'name'      => 'popular_tab_title',
                    'type'      => 'icon-text',
                    'title'     => esc_html__( 'Popular Tab', 'pubnews' ),
                    'default'   => json_encode( array( 'icon' => 'fas fa-fire', 'title'    => esc_html__( 'Popular', 'pubnews' ) ) )
                ),
                array(
                    'name'      => 'popular_tab_category',
                    'type'      => 'select',
                    'title'     => esc_html__( 'Categories', 'pubnews' ),
                    'description'=> esc_html__( 'Choose the category to display for popular tab', 'pubnews' ),
                    'options'   => $categories_options
                ),
                array(
                    'name'      => 'comments_tab_title',
                    'type'      => 'icon-text',
                    'title'     => esc_html__( 'Comments Tab', 'pubnews' ),
                    'default'   => json_encode( array( 'icon' => 'far fa-comments', 'title'    => esc_html__( 'Comments', 'pubnews' ) ) )
                ),
                array(
                    'name'      => 'tab_order',
                    'type'      => 'select',
                    'title'     => esc_html__( 'Tab Order', 'pubnews' ),
                    'default'   => 'latest-popular-comments',
                    'options'   => array(
                        'latest-popular-comments'   => esc_html( 'Latest - Popular - Comments', 'pubnews' ),
                        'latest-comments-popular'   => esc_html( 'Latest - Comments - Popular', 'pubnews' ),
                        'popular-latest-comments'   => esc_html( 'Popular - Latest - Comments', 'pubnews' ),
                        'popular-comments-latest'   => esc_html( 'Popular - Comments - Latest', 'pubnews' ),
                        'comments-latest-popular'   => esc_html( 'Comments - Latest - Popular', 'pubnews' ),
                        'comments-popular-latest'   => esc_html( 'Comments - Popular - Latest', 'pubnews' ),
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
 
} // class Pubnews_Tabbed_Posts_Widget