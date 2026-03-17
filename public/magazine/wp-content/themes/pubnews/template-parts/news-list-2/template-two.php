<?php
/**
 * News List 2 template one
 * 
 * @package Pubnews
 * @since 1.0.0
 */
extract( $args );
?>
<div id="<?php echo esc_attr( $uniqueID ); ?>" class="news-list-2<?php if( isset($options->thumbOption) && ! $options->thumbOption ) echo ' section-no-thumbnail'; ?> <?php echo esc_attr( 'layout--' . $options->layout );?>">
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

        $elementClass = 'news-list-post-wrap';
        $elementClass .= isset( $options->column ) ? ( ' column--' . $options->column ) : ( ' column--one' );
        $elementClass .= ( $options->viewallLabelOption ) ? ' viewall_enabled' : ' viewall_disabled';
    ?>
    <div class="<?php echo esc_attr( $elementClass ); ?>">
        <?php
        $post_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $post_args ) );
        if( $post_query -> have_posts() ) :
            $delay = 0;
            $numbering = 1;
            while( $post_query -> have_posts() ) : $post_query -> the_post();
            ?>
                <article class="list-item pubnews-category-no-bk <?php if(!has_post_thumbnail()){ echo esc_attr('no-feat-img');} ?>" <?php echo esc_attr(pubnews_get_block_animation_attr($delay)); ?>>
                   <div class="blaze_box_wrap pubnews-card">
                        <figure class="post-thumb-wrap">
                            <?php
                                if( $numbering > 1 ) {
                                    echo '<span class="post-numbering">' . pubnews_numbering_with_pad_format($numbering). '</span>';
                                } else {
                                    ?>
                                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                            <?php
                                                if( has_post_thumbnail() ) : 
                                                    the_post_thumbnail($options->imageSize, array(
                                                        'title' => the_title_attribute(array(
                                                            'echo'  => false
                                                        ))
                                                    ));
                                                endif;
                                            ?>
                                        </a>
                                    <?php
                                }
                            ?>
                        </figure>
                        <div class="post-element">
                            <?php if( $options->categoryOption ) pubnews_get_post_categories( get_the_ID(), 2 ); ?>
                            <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                            <?php if( $numbering == 1 ) echo '<span class="post-numbering">' . pubnews_numbering_with_pad_format($numbering). '</span>'; ?>
                        </div>
                    </div>
                </article>
            <?php
            $numbering++;
            $delay += 50;
            endwhile;
        endif;
        ?>
    </div>
</div>