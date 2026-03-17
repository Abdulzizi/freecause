<?php
/**
 * Template part for displaying block content in filter block
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
?>
<article class="filter-item <?php if(!has_post_thumbnail()) { echo esc_attr('no-feat-img');} ?>" <?php if(isset($args->delay)) echo esc_attr(pubnews_get_block_animation_attr($args->delay)); ?>>
    <div class="blaze_box_wrap pubnews-card">
        <figure class="post-thumb-wrap">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php
                    if( has_post_thumbnail() ) { 
                        the_post_thumbnail($args->imageSize, array(
                            'title' => the_title_attribute(array(
                                'echo'  => false
                            ))
                        ));
                    }
                ?>
            </a>
            <?php if( $args->categoryOption && $args->featuredPosts ) pubnews_get_post_categories( get_the_ID(), 2 ); ?>
        </figure>
        <div class="post-element">
            <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            <div class="post-meta">
                <?php if( $args->authorOption ) pubnews_posted_by(); ?>
                <?php if( $args->dateOption ) pubnews_posted_on(); ?>
                <?php
                    if( $args->commentOption ) {
                        $website_comments_before_icon = [
                            'type'  => 'icon',
                            'value' => 'far fa-comment',
                        ];
                        ?>
                            <span class="post-comment <?php echo esc_attr( $website_comments_before_icon['value'] ); ?>"><?php echo absint( get_comments_number() ); ?></span>
                        <?php
                    }
                ?>
            </div>
            
            <?php
                if( $args->excerptOption ):
                    echo '<div class="post-excerpt">' .esc_html( get_the_excerpt() ). '</div>';
                endif;
                do_action( 'pubnews_section_block_view_all_hook', array(
                    'option'    => isset( $args->buttonOption ) ? $args->buttonOption : false
                ));
            ?>
        </div>
    </div>
</article>