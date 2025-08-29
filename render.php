<?php
// Parse block attributes with defaults
$posts_per_page = isset($attributes['postsPerPage']) ? (int) $attributes['postsPerPage'] : 6;
$order = !empty($attributes['order']) ? $attributes['order'] : 'DESC';
$orderby = !empty($attributes['orderby']) ? $attributes['orderby'] : 'date';
$recipe_category = !empty($attributes['recipeCategory']) ? (array) $attributes['recipeCategory'] : array();
$cuisine_type = !empty($attributes['cuisineType']) ? (array) $attributes['cuisineType'] : array();
$dietary_restriction = !empty($attributes['dietaryRestriction']) ? (array) $attributes['dietaryRestriction'] : array();

$slides_per_view = isset($attributes['slidesPerView']) ? (int) $attributes['slidesPerView'] : 1;
$show_arrows = isset($attributes['showArrows']) ? (bool) $attributes['showArrows'] : true;
$show_dots = isset($attributes['showDots']) ? (bool) $attributes['showDots'] : true;
$breakpoints = !empty($attributes['breakpoints']) ? (array) $attributes['breakpoints'] : array();
$loop = isset($attributes['loop']) ? (bool) $attributes['loop'] : true;
$autoplay = isset($attributes['autoplay']) ? (bool) $attributes['autoplay'] : true;
$delay = isset($attributes['delay']) ? (int) $attributes['delay'] : 1000;

$show_title = isset($attributes['showTitle']) ? (bool) $attributes['showTitle'] : true;
$show_excerpt = isset($attributes['showExcerpt']) ? (bool) $attributes['showExcerpt'] : true;
$use_excerpt = isset($attributes['useExcerpt']) ? (bool) $attributes['useExcerpt'] : true;
$show_prep = isset($attributes['showPrepTime']) ? (bool) $attributes['showPrepTime'] : true;
$show_diff = isset($attributes['showDifficulty']) ? (bool) $attributes['showDifficulty'] : true;
$show_ratings = isset($attributes['showRatings']) ? (bool) $attributes['showRatings'] : false;
$color_text = !empty($attributes['colorText']) ? $attributes['colorText'] : '';
$color_bg = !empty($attributes['colorBackground']) ? $attributes['colorBackground'] : '';
$spacing = isset($attributes['spacing']) ? (int) $attributes['spacing'] : 16;
$radius = isset($attributes['borderRadius']) ? (int) $attributes['borderRadius'] : 8;

// Set up taxonomy query based on selected terms
$tax_query = array();
if (!empty($recipe_category)) {
    $tax_query[] = array(
        'taxonomy' => 'recipe_category',
        'field' => is_int(reset($recipe_category)) ? 'term_id' : 'slug',
        'terms' => $recipe_category,
    );
}
if (!empty($cuisine_type)) {
    $tax_query[] = array(
        'taxonomy' => 'cuisine_type',
        'field' => is_int(reset($cuisine_type)) ? 'term_id' : 'slug',
        'terms' => $cuisine_type,
    );
}
if (!empty($dietary_restriction)) {
    $tax_query[] = array(
        'taxonomy' => 'dietary_restriction',
        'field' => is_int(reset($dietary_restriction)) ? 'term_id' : 'slug',
        'terms' => $dietary_restriction,
    );
}
if (count($tax_query) > 1) {
    $tax_query['relation'] = 'AND';
}

/**
 * Set up the main query arguments.
 *
 * @var array $query_args The main query arguments.
 */
$query_args = array(
    'post_type' => 'recipe',
    'posts_per_page' => $posts_per_page,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => $order,
    'ignore_sticky_posts' => true,
);
if (!empty($tax_query)) {
    $query_args['tax_query'] = $tax_query;
}

if ( ! empty( $attributes['ratingOrder'] ) && $attributes['ratingOrder'] !== 'none' ) {
    $query_args['meta_key'] = '_recipe_rating_avg';
    $query_args['orderby'] = 'meta_value_num';
    $query_args['order'] = strtoupper( $attributes['ratingOrder'] ) === 'ASC' ? 'ASC' : 'DESC';
}

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'wp-block-my-plugin-testimonial-slider',
    'data-slidesperview' => $slides_per_view,
    'data-loop' => $loop ? 'true' : 'false',
    'data-autoplay' => $autoplay ? 'true' : 'false',
    'data-delay' => (string) $delay,
    'data-spacing' => (string) $spacing,
    'data-breakpoints' => esc_attr(json_encode($breakpoints)),
]);

$recipes = new WP_Query($query_args);
?>

<!-- Recipe Slider Container -->
<div <?php echo $wrapper_attributes; ?>>
    <div class="swiper">
        <div class="swiper-wrapper">
            <?php if ($recipes->have_posts()): ?>
                <?php while ($recipes->have_posts()):
                    $recipes->the_post(); ?>
                    <div class="swiper-slide" style="color: <?php echo esc_attr( $color_text ); ?>;">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="recipe-slide-image" style="position:relative;border-radius:<?php echo (int) $radius; ?>px;overflow:hidden;background:<?php echo esc_attr( $color_bg ); ?>;">
                                <a class="slide-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>"></a>
                                <?php the_post_thumbnail('large'); ?>
                                <div class="slide-overlay">
                                    <?php if ($show_title): ?>
                                        <h3 class="slide-title"><a style="color:inherit;text-decoration:none" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <?php endif; ?>
                                    <?php if ($show_excerpt): ?>
                                        <div class="slide-excerpt"><?php echo esc_html($use_excerpt ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 24)); ?></div>
                                        <a class="slide-readmore" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read more about %s', 'recipe-slider' ), get_the_title() ) ); ?>"><?php echo esc_html__( 'Read more', 'recipe-slider' ); ?></a>
                                    <?php endif; ?>
                                    <div class="slide-meta">
                                        <?php
                                        $prep = get_post_meta( get_the_ID(), '_recipe_prep_time', true );
                                        $diff = get_post_meta( get_the_ID(), '_recipe_difficulty', true );
                                        $avg  = (float) get_post_meta( get_the_ID(), '_recipe_rating_avg', true );
                                        $likes = (int) get_post_meta( get_the_ID(), '_recipe_likes', true );
                                        if ( $show_prep && $prep !== '' ) {
                                            echo '<span class="recipe-prep-time">' . esc_html( sprintf( __( 'Prep: %s min', 'recipe-slider' ), $prep ) ) . '</span>';
                                        }
                                        if ( $show_diff && $diff ) {
                                            echo ' <span class="recipe-difficulty">' . esc_html( $diff ) . '</span>';
                                        }
                                        if ( $show_ratings ) {
                                            $rounded_display = (int) round( $avg );
                                            $rounded_display = max(0, min(5, $rounded_display));
                                            $stars = str_repeat('★', $rounded_display) . str_repeat('☆', 5 - $rounded_display);
                                            echo ' <span class="slide-rating" title="' . esc_attr( $avg ) . '">' . $stars . '</span>';
                                        }
                                        ?>
                                    </div>
                                    <?php if ( is_user_logged_in() ) : ?>
                                        <div class="slide-actions">
                                            <span>
                                                <?php $rounded_control = (int) round( $avg ); $rounded_control = max(0, min(5, $rounded_control)); for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                    <a href="#" class="rate-star" data-post="<?php echo (int) get_the_ID(); ?>" data-value="<?php echo $i; ?>" aria-label="Rate <?php echo $i; ?> stars"><?php echo $i <= $rounded_control ? '★' : '☆'; ?></a>
                                                <?php endfor; ?>
                                            </span>
                                            <a href="#" class="recipe-like" data-post="<?php echo (int) get_the_ID(); ?>"></a>
                                            <span class="counts"><?php echo esc_html( sprintf( '%d', $likes ) ); ?></span>
                                        </div>
                                    <?php else : ?>
                                        <div class="slide-actions">
                                            <span>
                                                <?php $rounded_control = (int) round( $avg ); $rounded_control = max(0, min(5, $rounded_control)); for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                    <span class="rate-star" aria-disabled="true"><?php echo $i <= $rounded_control ? '★' : '☆'; ?></span>
                                                <?php endfor; ?>
                                            </span>
                        					<span class="recipe-like" aria-disabled="true"></span>
                                            <span class="counts"><?php echo (int) $likes; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endif; ?>
                </div>
                <?php endwhile;
                wp_reset_postdata(); ?>
            <?php endif; ?>
        </div>
        <?php if ($show_dots): ?>
            <div class="swiper-pagination"></div>
        <?php endif; ?>
        <?php if ($show_arrows): ?>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        <?php endif; ?>
    </div>
</div>