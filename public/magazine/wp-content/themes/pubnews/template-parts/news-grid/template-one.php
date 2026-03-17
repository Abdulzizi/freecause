<?php
/**
 * News Grid template one
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;
extract( $args );
$block_id_attribute = ( $options->blockId ) ? ( ' ' . $options->blockId ) : '';
?>
<div id="<?php echo esc_attr( $uniqueID . $block_id_attribute ); ?>" class="news-grid<?php if( isset($options->thumbOption) && ! $options->thumbOption ) echo ' section-no-thumbnail'; ?> <?php echo esc_attr( 'layout--' . $options->layout );?>">
    <?php
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

        $elementClass = 'news-grid-post-wrap';
        $elementClass .= isset( $options->column ) ? ( ' column--' . $options->column ) : ( ' column--one' );
        $elementClass .= ( $options->viewallLabelOption ) ? ' viewall_enabled' : ' viewall_disabled';
    ?>

    <div class="<?php echo esc_attr( $elementClass ); ?>">
        <?php
            $post_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $post_args ) );
            if( $post_query -> have_posts() ) :
                $delay = 0;
                while( $post_query -> have_posts() ) : $post_query -> the_post();
                ?>
                    <article class="grid-item <?php if(!has_post_thumbnail()){ echo esc_attr('no-feat-img');} ?>" <?php echo esc_attr(pubnews_get_block_animation_attr($delay)); ?>>
                        <div class="blaze_box_wrap pubnews-card">
                            <figure class="post-thumb-wrap">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php if( has_post_thumbnail() ) { 
                                            the_post_thumbnail($options->imageSize, array(
                                                'title' => the_title_attribute(array(
                                                    'echo'  => false
                                                ))
                                            ));
                                        }
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
                                <?php if( isset($options->excerptOption) && $options->excerptOption ) : 
                                    $excerptLength = isset( $options->excerptLength ) ? $options->excerptLength: 10;
                                ?>
                                    <div class="post-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), $excerptLength ) ); ?></div>
                                <?php endif;

                                    do_action( 'pubnews_section_block_view_all_hook', array(
                                        'option'    => isset( $options->buttonOption ) ? $options->buttonOption : false
                                    ));
                                ?>
                            </div>
                        </div>
                    </article>
                <?php
                $delay += 50;
                endwhile;
            endif;
        ?>
    </div>
</div>