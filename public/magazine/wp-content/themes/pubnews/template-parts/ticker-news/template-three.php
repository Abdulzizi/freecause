<?php
/**
 * Ticker news template three
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;
$ticker_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $args ) );
if( $ticker_query->have_posts() ) :
    while( $ticker_query->have_posts() ) : $ticker_query->the_post();
    $figureClass = 'feature_image';
    if( ! has_post_thumbnail() ) $figureClass .= ' no-feat-img';
    ?>
        <li class="ticker-item pubnews-card">
            <figure class="<?php echo esc_attr( $figureClass ); ?>">
                <?php
                    if( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <?php
                                the_post_thumbnail('pubnews-thumb', array(
                                            'title' => the_title_attribute(array(
                                                'echo'  => false
                                            ))
                                        ));
                                    ?>
                        </a>
                <?php 
                    endif;
                ?>
            </figure>
            <div class="title-wrap">
                <?php pubnews_posted_on(); ?>
                <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            </div>
        </li>
    <?php
    endwhile;
    wp_reset_postdata();
endif;