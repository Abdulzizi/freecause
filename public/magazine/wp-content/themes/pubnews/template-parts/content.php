<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Pubnews
 */
use Pubnews\CustomizerDefault as PND;
$archive_post_element_order = $args['archive_post_element_order'];
$archive_page_category_option = $args['archive_page_category_option'];
$archive_image_size = ( array_key_exists( 'archive_image_size', $args ) ? $args['archive_image_size'] : 'pubnews-list' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?> <?php echo esc_attr(pubnews_get_block_animation_attr()); ?>>
    <div class="blaze_box_wrap pubnews-card">
    	<figure class="post-thumb-wrap <?php if(!has_post_thumbnail()){ echo esc_attr('no-feat-img');} ?>">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php
                    if( has_post_thumbnail() ) { 
                        the_post_thumbnail( $archive_image_size, array(
                            'title' => the_title_attribute(array(
                                'echo'  => false
                            ))
                        ));
                    }
                ?>
            </a>
            <?php if( $archive_page_category_option ) pubnews_get_post_categories(get_the_ID(), 2); ?>
        </figure>
        <div class="post-element">
            <?php
                foreach( $archive_post_element_order as $element_order ) :
                    if( $element_order['option'] ) {
                        switch( $element_order['value'] ) {
                            case 'title': ?> <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                            <?php
                                break;
                            case 'meta': ?> 
                                        <div class="post-meta">
                                            <?php
                                                pubnews_posted_by();
                                                pubnews_posted_on();
                                                pubnews_comments_number();
                                                $website_read_time_before_icon = [
                                                    'type'  => 'icon',
                                                    'value' => 'fas fa-clock'
                                                ];
                                                echo '<span class="read-time ' .esc_attr( $website_read_time_before_icon['value'] ). '">' .pubnews_post_read_time( get_the_content() ). ' ' .esc_html__( 'mins', 'pubnews' ). '</span>';
                                            ?>
                                        </div>
                            <?php
                                        break;
                                case 'excerpt': ?> <div class="post-excerpt"><?php the_excerpt(); ?></div>
                                        <?php
                                                break;
                                case 'button':
                                                do_action( 'pubnews_section_block_view_all_hook', array(
                                                    'option'    => $element_order['option']
                                                ));
                                                break;
                            default: '';
                        }
                    }
                endforeach;
            ?>
        </div>
    </div>
    <?php
        edit_post_link(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Edit <span class="screen-reader-text">%s</span>', 'pubnews' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            ),
            '<span class="edit-link">',
            '</span>'
        );
    ?>
</article><!-- #post-<?php the_ID(); ?> -->