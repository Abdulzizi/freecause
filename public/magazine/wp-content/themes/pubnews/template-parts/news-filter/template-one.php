<?php
/**
 * News Filter template one
 * 
 * @package Pubnews
 * @since 1.0.0
 */
extract( $args );
$filter_query = json_decode( $options->query );
$postCategories = ( isset( $filter_query->categories ) && ! empty( $filter_query->categories ) ) ? pubnews_get_categories_for_args( $filter_query->categories ) : '';
$postCategories = explode( ",", $postCategories );
array_unshift( $postCategories, 'All' );

$view_allclass = 'viewall_disabled';
if( $options->viewallLabelOption ){
    $view_allclass = 'viewall_enabled';
}
$block_id_attribute = ( $options->blockId ) ? ( ' ' . $options->blockId ) : '';
?>
<div id="<?php echo esc_attr( $uniqueID . $block_id_attribute ); ?>" class="news-filter pubnews-block pubnews-mobile-burger <?php echo esc_attr( 'layout--' . $options->layout );?>" data-args="<?php echo esc_attr( json_encode( $options ) ); ?>">
    <?php
        do_action( 'pubnews_section_block_view_all_hook', array(
            'option'=> isset( $options->viewallLabelOption ) ? $options->viewallLabelOption : false,
            'classes' => 'view-all-button',
            'link'  => isset( $options->viewallUrl ) ? $options->viewallUrl : '',
            'text_option'  => isset( $options->viewallLabelOption ) ? $options->viewallLabelOption : false,
            'text'  => isset( $options->viewallLabel ) ? $options->viewallLabel : esc_html__( 'View all', 'pubnews' )
        ));
    ?>
    <div class="news-filter-post-wrap <?php echo esc_attr($view_allclass); ?>">
        <div class="post_title_filter_wrap">
            <?php 
                if( $options->title ) : ?>
                    <h2 class="pubnews-block-title">
                        <span><?php echo esc_html( $options->title ); ?></span>
                    </h2>
            <?php endif; ?>
            <?php 
            if( $postCategories ) : ?>
                <div class="filter-tab-wrapper">
                    <div class="tab-burger-wrap">
                        <div class="title-tab-wrap">
                            <?php
                                foreach( $postCategories as $postCat => $postCatVal ) :
                                    $category_name = get_cat_name( absint( $postCatVal ) ) ? get_cat_name( absint( $postCatVal ) ) : $postCatVal;
                                        if( $category_name ) :
                                            ?>
                                            <div class="tab-title<?php if( $postCat < 1 ) echo esc_attr( ' isActive' ); ?>" data-tab="<?php echo ( $postCat > 0 ) ? esc_attr( $postCatVal ) : 'pubnews-filter-all'; ?>"><?php echo esc_html( $category_name ); ?></div>
                                        <?php
                                    endif;
                                endforeach;
                            ?>
                        </div>
                        <span class="pubnews-burger">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        if( $postCategories ) :
        ?>
            <div class="filter-tab-content-wrapper">
                <div class="tab-content content-pubnews-filter-all">
                    <?php
                        unset( $post_args['category_name'] );
                        $post_query = new WP_Query( apply_filters( 'pubnews_query_args_filter', $post_args ) );
                        $total_posts = $post_query->post_count;
                        if( $post_query->have_posts() ) :
                            $delay =0;
                            $row_count = 0;
                            while( $post_query->have_posts() ) : $post_query->the_post();
                            $current_post = $post_query->current_post;
                            $options->featuredPosts = false;
                                if( ($current_post % 5) === 0 && $row_count < 2 ) {
                                    echo '<div class="row-wrap">';
                                    $row_count++;
                                }
                                    if( $current_post === 0 ) {
                                        echo '<div class="featured-post">';
                                        $options->featuredPosts = true;
                                    }
                                        if( $current_post === 1 || $current_post === 5 ) {
                                            ?>
                                            <div class="trailing-post <?php if($current_post === 5) echo esc_attr('bottom-trailing-post'); ?>">
                                            <?php
                                        }
                                            $options->delay = $delay;
                                            // get template file w.r.t par
                                            get_template_part( 'template-parts/news-filter/content', 'one', $options );
                                        if( $current_post === 4 || ( $total_posts === $current_post + 1 ) ) echo '</div><!-- .trailing-post -->';
                                    if( $current_post === 0 ) echo '</div><!-- .featured-post-->';
                                    if( ( $current_post != 4 && $current_post != 0 ) && ( $total_posts === $current_post + 1 ) ) echo '</div><!-- .total-posts-close -->';
                                if( $row_count <= 2 && $current_post  === 4 ) echo '</div><!-- .row-wrap -->';
                                $delay += 50;
                            endwhile;
                        endif;
                    ?>
                </div>
            </div>
            <?php
        endif;
        ?>
    </div>
</div>