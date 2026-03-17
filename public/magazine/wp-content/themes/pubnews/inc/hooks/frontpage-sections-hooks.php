<?php
/**
 * Includes all the frontpage sections html functions
 * 
 * @package Pubnews
 * @since 1.0.0
 */
use Pubnews\CustomizerDefault as PND;

if( ! function_exists( 'pubnews_main_banner_part' ) ) :
    /**
     * Main Banner element
     * 
     * @since 1.0.0
     */
     function pubnews_main_banner_part() {
        $main_banner_option = PND\pubnews_get_customizer_option( 'main_banner_option' );
        if( ! $main_banner_option || is_paged() || pubnews_is_paged_filtered() ) return;
        $main_banner_layout = 'six';
        $main_banner_slider_order_by = PND\pubnews_get_customizer_option( 'main_banner_slider_order_by' );
        $orderArray = explode( '-', $main_banner_slider_order_by );
        $main_banner_slider_categories = PND\pubnews_get_customizer_option( 'main_banner_slider_categories' );
        $main_banner_args = array(
            'slider_args'  => array(
                'order' => esc_html( $orderArray[1] ),
                'orderby' => esc_html( $orderArray[0] ),
                'ignore_sticky_posts'   => true
            )
        );
        $main_banner_args['slider_args']['posts_per_page'] = 4;
        if( PND\pubnews_get_customizer_option( 'main_banner_date_filter' ) != 'all' ) $main_banner_args['slider_args']['date_query'] = pubnews_get_date_format_array_args(PND\pubnews_get_customizer_option( 'main_banner_date_filter' ));
        if( $main_banner_slider_categories ) $main_banner_args['slider_args']['cat'] = pubnews_get_categories_for_args($main_banner_slider_categories);
        $main_banner_posts = PND\pubnews_get_customizer_option( 'main_banner_posts' );
        if( $main_banner_posts ) $main_banner_args['slider_args']['post__in'] = pubnews_get_post_id_for_args($main_banner_posts);
        $banner_section_order = PND\pubnews_get_customizer_option( 'banner_section_order' );
        $section_column_sort_class = implode( '--', array( $banner_section_order[0]['value'], $banner_section_order[1]['value'] ) );
        
        $main_banner_width_layout = pubnews_get_section_width_layout_val('main_banner_width_layout');
        $sectionClass = 'pubnews-section';
        $sectionClass .= ' banner-layout--'. $main_banner_layout;
        $sectionClass .= ' ' . $section_column_sort_class;
        $sectionClass .= ' width-' . $main_banner_width_layout;
        if( $main_banner_layout == 'six' ) $sectionClass .= ' layout--' . PND\pubnews_get_customizer_option( 'main_banner_six_trailing_posts_layout' );
        ?>
            <section id="main-banner-section" class="<?php echo esc_attr( $sectionClass ); ?>">
                <div class="pubnews-container">
                    <div class="row">
                        <?php get_template_part( 'template-parts/main-banner/template', esc_html( $main_banner_layout ), $main_banner_args ); ?>
                    </div>
                </div>
            </section>
        <?php
     }
endif;
add_action( 'pubnews_main_banner_hook', 'pubnews_main_banner_part', 10 );

if( ! function_exists( 'pubnews_full_width_blocks_part' ) ) :
    /**
     * Full Width Blocks element
     * 
     * @since 1.0.0
     */
     function pubnews_full_width_blocks_part() {
        $full_width_blocks = PND\pubnews_get_customizer_option( 'full_width_blocks' );
        if( empty( $full_width_blocks ) || is_paged() || pubnews_is_paged_filtered() ) return;
        $full_width_blocks = json_decode( $full_width_blocks );
        if( ! in_array( true, array_column( $full_width_blocks, 'option' ) ) ) {
            return;
        }
        $full_width_blocks_width_layout = pubnews_get_section_width_layout_val('full_width_blocks_width_layout');
        ?>
            <section id="full-width-section" class="pubnews-section full-width-section <?php echo esc_attr( 'width-' . $full_width_blocks_width_layout ); ?>">
                <div class="pubnews-container">
                    <div class="row">
                        <?php
                            foreach( $full_width_blocks as $block ) :
                                if( $block->option ) :
                                    $type = $block->type;
                                    switch($type) {
                                        case 'ad-block' : pubnews_advertisement_block_html( $block, true );
                                                        break;
                                        default: $layout = $block->layout;
                                                $block_query = json_decode( $block->query );
                                                $order = $block_query->order;
                                                $postCategories = $block_query->categories;
                                                $customexclude_ids = $block_query->ids;
                                                $orderArray = explode( '-', $order );
                                                $block_args = array(
                                                    'post_args' => array(
                                                        'post_type' => 'post',
                                                        'order' => esc_html( $orderArray[1] ),
                                                        'orderby' => esc_html( $orderArray[0] ),
                                                        'ignore_sticky_posts'   => true
                                                    ),
                                                    'options'    => $block
                                                );
                                                $offset = isset( $block_query->offset ) ? $block_query->offset: 0;
                                                if( $offset > 0 ) $block_args['post_args']['offset'] = absint($offset);
                                                $block_args['post_args']['posts_per_page'] = absint( $block_query->count );
                                                if( $customexclude_ids ) $block_args['post_args']['post__not_in'] = pubnews_get_post_id_for_args( $customexclude_ids );
                                                if( $postCategories ) $block_args['post_args']['cat'] = pubnews_get_categories_for_args($postCategories);
                                                if( $block_query->dateFilter != 'all' ) $block_args['post_args']['date_query'] = pubnews_get_date_format_array_args($block_query->dateFilter);
                                                if( $block_query->posts ) $block_args['post_args']['post__in'] = pubnews_get_post_id_for_args($block_query->posts);
                                                // get template file w.r.t par
                                                $block_args['uniqueID'] = wp_unique_id('pubnews-block--');
                                                $style_variables = [
                                                    'unique_id' =>  $block_args['uniqueID'],
                                                    'layout'    =>  $block_args['options']->layout,
                                                    'image_ratio' => $block->imageRatio
                                                ];
                                                ( in_array( $block_args['options']->type, [ 'news-grid', 'news-carousel', 'news-list' ] ) ) ? pubnews_get_style_tag( $style_variables ) : pubnews_get_style_tag_fb( $style_variables );
                                                get_template_part( 'template-parts/' .esc_html( $type ). '/template', esc_html( $layout ), $block_args );
                                    }
                                endif;
                            endforeach;
                        ?>
                    </div>
                </div>
            </section>
        <?php
     }
     add_action( 'pubnews_full_width_blocks_hook', 'pubnews_full_width_blocks_part' );
endif;

if( ! function_exists( 'pubnews_leftc_rights_blocks_part' ) ) :
    /**
     * Left Content Right Sidebar Blocks element
     * 
     * @since 1.0.0
     */
     function pubnews_leftc_rights_blocks_part() {
        $leftc_rights_blocks = PND\pubnews_get_customizer_option( 'leftc_rights_blocks' );
        if( empty( $leftc_rights_blocks ) || is_paged() || pubnews_is_paged_filtered() ) return;
        $leftc_rights_blocks = json_decode( $leftc_rights_blocks );
        if( ! in_array( true, array_column( $leftc_rights_blocks, 'option' ) ) ) {
            return;
        }
        $leftc_rights_blocks_width_layout = pubnews_get_section_width_layout_val('leftc_rights_blocks_width_layout');
        ?>
            <section id="leftc-rights-section" class="pubnews-section leftc-rights-section <?php echo esc_attr( 'width-' . $leftc_rights_blocks_width_layout ); ?>">
                <div class="pubnews-container">
                    <div class="row">
                        <div class="primary-content">
                            <?php
                                foreach( $leftc_rights_blocks as $block ) :
                                    if( $block->option ) :
                                        $type = $block->type;
                                        switch($type) {
                                            case 'ad-block' : pubnews_advertisement_block_html( $block, true );
                                                            break;
                                            default: $layout = $block->layout;
                                                    $block_query = json_decode( $block->query );
                                                    $order = $block_query->order;
                                                    $postCategories = $block_query->categories;
                                                    $customexclude_ids = $block_query->ids;
                                                    $orderArray = explode( '-', $order );
                                                    $block_args = array(
                                                        'post_args' => array(
                                                            'post_type' => 'post',
                                                            'order' => esc_html( $orderArray[1] ),
                                                            'orderby' => esc_html( $orderArray[0] ),
                                                            'ignore_sticky_posts'   => true
                                                        ),
                                                        'options'    => $block
                                                    );
                                                    $offset = isset( $block_query->offset ) ? $block_query->offset: 0;
                                                    if( $offset > 0 ) $block_args['post_args']['offset'] = absint($offset);
                                                    $block_args['post_args']['posts_per_page'] = absint( $block_query->count );
                                                    if( $customexclude_ids ) $block_args['post_args']['post__not_in'] = pubnews_get_post_id_for_args( $customexclude_ids );
                                                    if( $postCategories ) $block_args['post_args']['cat'] = pubnews_get_categories_for_args($postCategories);
                                                    if( $block_query->dateFilter != 'all' ) $block_args['post_args']['date_query'] = pubnews_get_date_format_array_args($block_query->dateFilter);
                                                    if( $block_query->posts ) $block_args['post_args']['post__in'] = pubnews_get_post_id_for_args($block_query->posts);
                                                    // get template file w.r.t par
                                                    $block_args['uniqueID'] = wp_unique_id('pubnews-block--');
                                                    $style_variables = [
                                                        'unique_id' =>  $block_args['uniqueID'],
                                                        'layout'    =>  $block_args['options']->layout,
                                                        'image_ratio' => $block->imageRatio
                                                    ];
                                                    ( in_array( $block_args['options']->type, [ 'news-grid', 'news-carousel', 'news-list' ] ) ) ? pubnews_get_style_tag( $style_variables ) : pubnews_get_style_tag_fb( $style_variables );
                                                    get_template_part( 'template-parts/' .esc_html( $type ). '/template', esc_html( $layout ), $block_args );
                                        }
                                    endif;
                                endforeach;
                            ?>
                        </div>
                        <div class="secondary-sidebar">
                            <?php dynamic_sidebar( 'front-right-sidebar' ); ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php
     }
     add_action( 'pubnews_leftc_rights_blocks_hook', 'pubnews_leftc_rights_blocks_part', 10 );
endif;

if( ! function_exists( 'pubnews_lefts_rightc_blocks_part' ) ) :
    /**
     * Left Sidebar Right Content Blocks element
     * 
     * @since 1.0.0
     */
     function pubnews_lefts_rightc_blocks_part() {
        $lefts_rightc_blocks = PND\pubnews_get_customizer_option( 'lefts_rightc_blocks' );
        if( empty( $lefts_rightc_blocks )|| is_paged() || pubnews_is_paged_filtered() ) return;
        $lefts_rightc_blocks = json_decode( $lefts_rightc_blocks );
        if( ! in_array( true, array_column( $lefts_rightc_blocks, 'option' ) ) ) {
            return;
        }
        $lefts_rightc_blocks_width_layout = pubnews_get_section_width_layout_val('lefts_rightc_blocks_width_layout');
        ?>
            <section id="lefts-rightc-section" class="pubnews-section lefts-rightc-section <?php echo esc_attr( 'width-' . $lefts_rightc_blocks_width_layout ); ?>">
                <div class="pubnews-container">
                    <div class="row">
                        <div class="secondary-sidebar">
                            <?php dynamic_sidebar( 'front-left-sidebar' ); ?>
                        </div>
                        <div class="primary-content">
                            <?php
                                foreach( $lefts_rightc_blocks as $block ) :
                                    if( $block->option ) :
                                        $type = $block->type;
                                        switch($type) {
                                            case 'ad-block' : pubnews_advertisement_block_html( $block, true );
                                                            break;
                                            default: $layout = $block->layout;
                                                    $block_query = json_decode( $block->query );
                                                    $order = $block_query->order;
                                                    $postCategories = $block_query->categories;
                                                    $customexclude_ids = $block_query->ids;
                                                    $orderArray = explode( '-', $order );
                                                    $block_args = array(
                                                        'post_args' => array(
                                                            'post_type' => 'post',
                                                            'order' => esc_html( $orderArray[1] ),
                                                            'orderby' => esc_html( $orderArray[0] ),
                                                            'ignore_sticky_posts'   => true
                                                        ),
                                                        'options'    => $block
                                                    );
                                                    $offset = isset( $block_query->offset ) ? $block_query->offset: 0;
                                                    if( $offset > 0 ) $block_args['post_args']['offset'] = absint($offset);
                                                    $block_args['post_args']['posts_per_page'] = absint( $block_query->count );
                                                    if( $customexclude_ids ) $block_args['post_args']['post__not_in'] = pubnews_get_post_id_for_args( $customexclude_ids );
                                                    if( $postCategories ) $block_args['post_args']['cat'] = pubnews_get_categories_for_args($postCategories);
                                                    if( $block_query->dateFilter != 'all' ) $block_args['post_args']['date_query'] = pubnews_get_date_format_array_args($block_query->dateFilter);
                                                    if( $block_query->posts ) $block_args['post_args']['post__in'] = pubnews_get_post_id_for_args($block_query->posts);
                                                    // get template file w.r.t par
                                                    $block_args['uniqueID'] = wp_unique_id('pubnews-block--');
                                                    $style_variables = [
                                                        'unique_id' =>  $block_args['uniqueID'],
                                                        'layout'    =>  $block_args['options']->layout,
                                                        'image_ratio' => $block->imageRatio
                                                    ];
                                                    ( in_array( $block_args['options']->type, [ 'news-grid', 'news-carousel', 'news-list' ] ) ) ? pubnews_get_style_tag( $style_variables ) : pubnews_get_style_tag_fb( $style_variables );
                                                    get_template_part( 'template-parts/' .esc_html( $type ). '/template', esc_html( $layout ), $block_args );
                                        }
                                    endif;
                                endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php
     }
     add_action( 'pubnews_lefts_rightc_blocks_hook', 'pubnews_lefts_rightc_blocks_part', 10 );
endif;

if( ! function_exists( 'pubnews_bottom_full_width_blocks_part' ) ) :
    /**
     * Bottom Full Width Blocks element
     * 
     * @since 1.0.0
     */
     function pubnews_bottom_full_width_blocks_part() {
        $bottom_full_width_blocks = PND\pubnews_get_customizer_option( 'bottom_full_width_blocks' );
        if( empty( $bottom_full_width_blocks )|| is_paged() || pubnews_is_paged_filtered() ) return;
        $bottom_full_width_blocks = json_decode( $bottom_full_width_blocks );
        if( ! in_array( true, array_column( $bottom_full_width_blocks, 'option' ) ) ) {
            return;
        }
        $bottom_full_width_blocks_width_layout = pubnews_get_section_width_layout_val('bottom_full_width_blocks_width_layout');
        ?>
            <section id="bottom-full-width-section" class="pubnews-section bottom-full-width-section <?php echo esc_attr( 'width-' . $bottom_full_width_blocks_width_layout ); ?>">
                <div class="pubnews-container">
                    <div class="row">
                        <?php
                            foreach( $bottom_full_width_blocks as $block ) :
                                if( $block->option ) :
                                    $type = $block->type;
                                    switch($type) {
                                        case 'ad-block' : pubnews_advertisement_block_html( $block, true );
                                                        break;
                                        default: $layout = $block->layout;
                                                $block_query = json_decode( $block->query );
                                                $order = $block_query->order;
                                                $postCategories = $block_query->categories;
                                                $customexclude_ids = $block_query->ids;
                                                $orderArray = explode( '-', $order );
                                                $block_args = array(
                                                    'post_args' => array(
                                                        'post_type' => 'post',
                                                        'order' => esc_html( $orderArray[1] ),
                                                        'orderby' => esc_html( $orderArray[0] ),
                                                        'ignore_sticky_posts'   => true
                                                    ),
                                                    'options'    => $block
                                                );
                                                $offset = isset( $block_query->offset ) ? $block_query->offset: 0;
                                                if( $offset > 0 ) $block_args['post_args']['offset'] = absint($offset);
                                                $block_args['post_args']['posts_per_page'] = absint( $block_query->count );
                                                if( $customexclude_ids ) $block_args['post_args']['post__not_in'] = pubnews_get_post_id_for_args( $customexclude_ids );
                                                if( $postCategories ) $block_args['post_args']['cat'] = pubnews_get_categories_for_args($postCategories);
                                                if( $block_query->dateFilter != 'all' ) $block_args['post_args']['date_query'] = pubnews_get_date_format_array_args($block_query->dateFilter);
                                                if( $block_query->posts ) $block_args['post_args']['post__in'] = pubnews_get_post_id_for_args($block_query->posts);
                                                // get template file w.r.t par
                                                $block_args['uniqueID'] = wp_unique_id('pubnews-block--');
                                                $style_variables = [
                                                    'unique_id' =>  $block_args['uniqueID'],
                                                    'layout'    =>  $block_args['options']->layout,
                                                    'image_ratio' => $block->imageRatio
                                                ];
                                                ( in_array( $block_args['options']->type, [ 'news-grid', 'news-carousel', 'news-list' ] ) ) ? pubnews_get_style_tag( $style_variables ) : pubnews_get_style_tag_fb( $style_variables );
                                                get_template_part( 'template-parts/' .esc_html( $type ). '/template', esc_html( $layout ), $block_args );
                                    }
                                endif;
                            endforeach;
                        ?>
                    </div>
                </div>
            </section>
        <?php
     }
     add_action( 'pubnews_bottom_full_width_blocks_hook', 'pubnews_bottom_full_width_blocks_part', 10 );
endif;

if( ! function_exists( 'pubnews_two_column_section_columns_part' ) ) :
    /**
     * Three Column Blocks element
     * 
     * @since 1.0.0
     */
     function pubnews_two_column_section_columns_part() {
        $two_column_first_column_blocks = PND\pubnews_get_customizer_option( 'two_column_first_column_blocks' );
        $two_column_second_column_blocks = PND\pubnews_get_customizer_option( 'two_column_second_column_blocks' );
        if( ( empty( $two_column_first_column_blocks ) && empty( $two_column_second_column_blocks ) ) || is_paged() || pubnews_is_paged_filtered() ) return;
        $two_column_first_column_blocks = json_decode( $two_column_first_column_blocks );
        $two_column_second_column_blocks = json_decode( $two_column_second_column_blocks );
        if( ! in_array( true, array_column( $two_column_first_column_blocks, 'option' ) ) && ! in_array( true, array_column( $two_column_second_column_blocks, 'option' ) ) ) {
            return;
        }
        $two_column_section_layout = pubnews_get_section_width_layout_val('two_column_section_layout');
        ?>
            <section id="two-column-section" class="pubnews-section pubnews-multi-column-section two-column-section <?php echo esc_attr( 'width-' . $two_column_section_layout ); ?>">
                <div class="pubnews-container">
                    <div class="row">
                        <div class="section-column-wrap">
                            <?php
                                if( in_array( true, array_column( $two_column_first_column_blocks, 'option' ) ) ) :
                                    echo '<div class="section-column column-first">';
                                        foreach( $two_column_first_column_blocks as $block ) :
                                            if( $block->option ) :
                                                $type = $block->type;
                                                switch($type) {
                                                    case 'ad-block' : pubnews_advertisement_block_html( $block, true );
                                                                    break;
                                                    default: $layout = $block->layout;
                                                            $block_query = json_decode( $block->query );
                                                            $block->column = 'one';
                                                            $order = $block_query->order;
                                                            $postCategories = $block_query->categories;
                                                            $customexclude_ids = $block_query->ids;
                                                            $orderArray = explode( '-', $order );
                                                            $block_args = array(
                                                                'post_args' => array(
                                                                    'post_type' => 'post',
                                                                    'order' => esc_html( $orderArray[1] ),
                                                                    'orderby' => esc_html( $orderArray[0] ),
                                                                    'ignore_sticky_posts'   => true
                                                                ),
                                                                'options'    => $block
                                                            );
                                                            $block_args['post_args']['posts_per_page'] = absint( $block_query->count );
                                                            $offset = isset( $block_query->offset ) ? $block_query->offset: 0;
                                                            if( $offset > 0 ) $block_args['post_args']['offset'] = absint($offset);
                                                            if( $customexclude_ids ) $block_args['post_args']['post__not_in'] = pubnews_get_post_id_for_args( $customexclude_ids );
                                                            if( $postCategories ) $block_args['post_args']['cat'] = pubnews_get_categories_for_args($postCategories);
                                                            if( $block_query->dateFilter != 'all' ) $block_args['post_args']['date_query'] = pubnews_get_date_format_array_args($block_query->dateFilter);
                                                            if( $block_query->posts ) $block_args['post_args']['post__in'] = pubnews_get_post_id_for_args($block_query->posts);
                                                            // get template file w.r.t par
                                                            $block_args['uniqueID'] = wp_unique_id('pubnews-block--');
                                                            $style_variables = [
                                                                'unique_id' =>  $block_args['uniqueID'],
                                                                'layout'    =>  $block_args['options']->layout,
                                                                'image_ratio' => $block->imageRatio
                                                            ];
                                                            ( in_array( $block_args['options']->type, [ 'news-grid', 'news-carousel', 'news-list' ] ) ) ? pubnews_get_style_tag( $style_variables ) : pubnews_get_style_tag_fb( $style_variables );
                                                            get_template_part( 'template-parts/' .esc_html( $type ). '/template', esc_html( $layout ), $block_args );
                                                }
                                            endif;
                                        endforeach;
                                    echo '</div><!-- .section-column.column-first -->';
                                endif;

                                if( in_array( true, array_column( $two_column_second_column_blocks, 'option' ) ) ) :
                                    echo '<div class="section-column column-second">';
                                        foreach( $two_column_second_column_blocks as $block ) :
                                            if( $block->option ) :
                                                $type = $block->type;
                                                switch($type) {
                                                    case 'ad-block' : pubnews_advertisement_block_html( $block, true );
                                                                    break;
                                                    default: $layout = $block->layout;
                                                            $block_query = json_decode( $block->query );
                                                            $block->column = 'one';
                                                            $order = $block_query->order;
                                                            $postCategories = $block_query->categories;
                                                            $customexclude_ids = $block_query->ids;
                                                            $orderArray = explode( '-', $order );
                                                            $block_args = array(
                                                                'post_args' => array(
                                                                    'post_type' => 'post',
                                                                    'order' => esc_html( $orderArray[1] ),
                                                                    'orderby' => esc_html( $orderArray[0] ),
                                                                    'ignore_sticky_posts'   => true
                                                                ),
                                                                'options'    => $block
                                                            );
                                                            $offset = isset( $block_query->offset ) ? $block_query->offset: 0;
                                                            if( $offset > 0 ) $block_args['post_args']['offset'] = absint($offset);
                                                            $block_args['post_args']['posts_per_page'] = absint( $block_query->count );
                                                            if( $customexclude_ids ) $block_args['post_args']['post__not_in'] = pubnews_get_post_id_for_args( $customexclude_ids );
                                                            if( $postCategories ) $block_args['post_args']['cat'] = pubnews_get_categories_for_args($postCategories);
                                                            if( $block_query->dateFilter != 'all' ) $block_args['post_args']['date_query'] = pubnews_get_date_format_array_args($block_query->dateFilter);
                                                            if( $block_query->posts ) $block_args['post_args']['post__in'] = pubnews_get_post_id_for_args($block_query->posts);
                                                            // get template file w.r.t par
                                                            $block_args['uniqueID'] = wp_unique_id('pubnews-block--');
                                                            $style_variables = [
                                                                'unique_id' =>  $block_args['uniqueID'],
                                                                'layout'    =>  $block_args['options']->layout,
                                                                'image_ratio' => $block->imageRatio
                                                            ];
                                                            ( in_array( $block_args['options']->type, [ 'news-grid', 'news-carousel', 'news-list' ] ) ) ? pubnews_get_style_tag( $style_variables ) : pubnews_get_style_tag_fb( $style_variables );
                                                            get_template_part( 'template-parts/' .esc_html( $type ). '/template', esc_html( $layout ), $block_args );
                                                }
                                            endif;
                                        endforeach;
                                    echo '</div><!-- .section-column.column-second -->';
                                endif;
                            ?>
                        </div><!-- .section-column-wrap -->
                    </div>
                </div>
            </section>
        <?php
     }
     add_action( 'pubnews_two_column_section_hook', 'pubnews_two_column_section_columns_part', 10 );
endif;
