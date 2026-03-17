<?php
/**
 * News Carousel template one
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;
extract( $args );
$block_id_attribute = ( $options->blockId ) ? ( ' ' . $options->blockId ) : '';
?>
<div id="<?php echo esc_attr( $uniqueID . $block_id_attribute ); ?>" class="news-carousel <?php echo esc_attr( 'layout--' . $options->layout ); ?>">
    <?php
        $view_allclass = 'viewall_disabled';
        if( $options->viewallLabelOption ){
            $view_allclass = 'viewall_enabled';
        }

        do_action( 'pubnews_section_block_view_all_hook', array(
            'option'=> isset( $options->viewallLabelOption ) ? $options->viewallLabelOption : false,
            'classes' => 'view-all-button',
            'link'  => isset( $options->viewallUrl ) ? $options->viewallUrl : '',
            'text_option'  => isset( $options->viewallLabelOption ) ? $options->viewallLabelOption : false,
            'text'  => isset( $options->viewallLabel ) ? $options->viewallLabel : esc_html__( 'View all', 'pubnews' )
        ));

        if( $options->title ) :
    ?>
            <h2 class="pubnews-block-title">
                <span><?php echo esc_html( $options->title ); ?></span>
            </h2>
    <?php
        endif;
    ?>
    <div class="news-carousel-post-wrap <?php echo esc_attr($view_allclass); ?>" data-dots="<?php echo esc_attr( pubnews_bool_to_string( $options->dots ) ); ?>" data-loop="<?php echo esc_attr( pubnews_bool_to_string( $options->loop ) ); ?>" data-arrows="<?php echo esc_attr( pubnews_bool_to_string( $options->arrows ) ); ?>" data-auto="<?php echo esc_attr( pubnews_bool_to_string( $options->auto ) ); ?>" data-columns="<?php if( isset($options->columns) ) { echo absint( $options->columns ); } else { echo absint(1); }; ?>">
        <?php
            $post_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $post_args ) );
            if( $post_query -> have_posts() ) :
                while( $post_query -> have_posts() ) : $post_query -> the_post();
                ?>
                    <article class="carousel-item <?php if(!has_post_thumbnail()){ echo esc_attr('no-feat-img');} ?>">
                        <div class="blaze_box_wrap pubnews-card">
                            <figure class="post-thumb-wrap">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php
                                        if( has_post_thumbnail() ) :
                                            the_post_thumbnail((property_exists( $options, 'imageSize' ) ? $options->imageSize : 'pubnews-list'), array(
                                                'title' => the_title_attribute(array(
                                                    'echo'  => false
                                                ))
                                            ));
                                        endif;
                                    ?>
                                </a>
                                <?php if( $options->categoryOption ) pubnews_get_post_categories( get_the_ID(), 2 ); ?>
                            </figure>
                            <div class="post-element">
                                <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                                <div class="post-meta">
                                    <?php if( $options->authorOption ) pubnews_posted_by(); ?>
                                    <?php if( $options->dateOption ) pubnews_posted_on(); ?>
                                    <?php if( $options->commentOption ) pubnews_comments_number(); ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php
                endwhile;
            endif;
        ?>
    </div>
</div>