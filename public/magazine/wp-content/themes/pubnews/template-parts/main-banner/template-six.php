<?php
/**
 * Main Banner template six
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;

$slider_args = $args['slider_args'];
?>
<div class="main-banner-wrap">
    <div class="main-banner-slider pubnews-card" data-auto="<?php echo esc_attr( json_encode( false ) ); ?>" data-arrows="<?php echo esc_attr( json_encode( true ) ); ?>" data-dots="<?php echo esc_attr( json_encode( true ) ); ?>" data-speed="<?php echo esc_attr( 300 ); ?>">
        <?php
            $slider_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $slider_args ) );
            if( $slider_query -> have_posts() ) :
                while( $slider_query -> have_posts() ) : $slider_query -> the_post();
                ?>
                    <article class="slide-item<?php if(!has_post_thumbnail()){ echo esc_attr(' no-feat-img');} ?>">
                        <figure class="post-thumb">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <?php 
                                    if( has_post_thumbnail()) { 
                                        the_post_thumbnail('pubnews-featured', array(
                                            'title' => the_title_attribute(array(
                                                'echo'  => false
                                            ))
                                        ));
                                    }
                                ?>
                            </a>
                        </figure>
                        <div class="post-element">
                            <div class="post-meta">
                                <?php
                                    pubnews_get_post_categories( get_the_ID(), 2 );
                                    pubnews_posted_on();
                                ?>
                            </div>
                            <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                            <?php
                                /**
                                 * hook - pubnews_main_banner_post_append_hook
                                 * 
                                 */
                                do_action('pubnews_main_banner_post_append_hook', get_the_ID());
                            ?>
                        </div>
                    </article>
                <?php
                endwhile;
                wp_reset_postdata();
            endif;
        ?>
    </div>
</div>

<div class="main-banner-trailing-posts">
    <div class="trailing-posts-wrap">
        <?php
            $main_banner_six_trailing_posts_order_by = PND\pubnews_get_customizer_option( 'main_banner_six_trailing_posts_order_by' );
            $listPostsOrderArray = explode( '-', $main_banner_six_trailing_posts_order_by );
            $trailing_posts_args = array(
                'numberposts' => 2,
                'order' => esc_html( $listPostsOrderArray[1] ),
                'orderby' => esc_html( $listPostsOrderArray[0] ),
            );
            $main_banner_six_trailing_posts_categories = PND\pubnews_get_customizer_option( 'main_banner_six_trailing_posts_categories' );
            if( $main_banner_six_trailing_posts_categories ) $trailing_posts_args['cat'] = pubnews_get_categories_for_args($main_banner_six_trailing_posts_categories);
            $main_banner_six_trailing_posts = PND\pubnews_get_customizer_option( 'main_banner_six_trailing_posts' );
            if( $main_banner_six_trailing_posts ) $trailing_posts_args['post__in'] = pubnews_get_post_id_for_args($main_banner_six_trailing_posts);
            $trailing_posts = get_posts( apply_filters( 'pubnews_query_args_filter', $trailing_posts_args ) );
            if( $trailing_posts ) :
                foreach( $trailing_posts as $trailing_post_key => $trailing_post ) :
                    $trailing_post_id  = $trailing_post->ID;
                ?>
                        <article class="post-item pubnews-card <?php if(!has_post_thumbnail($trailing_post_id)){ echo esc_attr(' no-feat-img');} ?>">
                            <figure class="post-thumb">
                                <?php if( has_post_thumbnail($trailing_post_id) ): ?> 
                                    
                                    <a href="<?php echo esc_url(get_the_permalink($trailing_post_id)); ?>" title="<?php the_title_attribute(['post' => $trailing_post_id]); ?>">
                                        <img src="<?php echo esc_url( get_the_post_thumbnail_url($trailing_post_id, 'pubnews-list') ); ?>" alt="<?php echo esc_attr( get_post_meta( get_post_thumbnail_id($trailing_post_id), '_wp_attachment_image_alt', true ) ); ?>"/>
                                    </a>
                                <?php endif; ?>
                                <div class="post-element-wrap">
                                    <div class="post-element">
                                        <h2 class="post-title"><a href="<?php the_permalink($trailing_post_id); ?>"><?php echo wp_kses_post( get_the_title($trailing_post_id) ); ?></a></h2>
                                    </div>
                                    <div class="post-meta">
                                        <?php pubnews_posted_by( $trailing_post_id ); ?>
                                        <?php pubnews_posted_on( $trailing_post_id ); ?>
                                    </div>
                                </div>
                            </figure>
                        </article>
                <?php
                endforeach;
            endif;
        ?>
    </div>
</div>