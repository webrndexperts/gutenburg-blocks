<?php
/**
 * Recipe Carousel - A WordPress Plugin
 * 
 * @author      Ansuman Satapathy
 * @copyright   2023 RND Experts
 * @license     GPL-2.0+
 * 
 * @wordpress-plugin
 * Plugin Name: Recipe Carousel
 * Plugin URI:  https://rndexperts.com/plugins/recipe-carousel
 * Description: A comprehensive recipe management system with custom post types, taxonomies, and Gutenberg blocks.
 * Version:     1.0.0
 * Author:      Ansuman Satapathy
 * Author URI:  https://rndexperts.com
 * Text Domain: recipe-slider
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize the Recipe Slider plugin.
 * 
 * This function sets up the core functionality of the plugin including
 * custom post types, taxonomies, and meta boxes.
 * 
 * @since 1.0.0
 * @return void
 */
function recipe_slider_init()
{
    add_action('enqueue_block_assets', 'recipe_slider_enqueue_swiper');
    require_once __DIR__ . '/includes/class-slider-recipe-post-type.php';
    require_once __DIR__ . '/includes/class-slider-recipe-taxonomies.php';
    require_once __DIR__ . '/includes/class-slider-recipe-meta.php';
    require_once __DIR__ . '/includes/class-slider-recipe-meta-box.php';
    require_once __DIR__ . '/includes/class-recipe-list-shortcode.php';

    $slider_recipe_tax = new Slider_Recipe_Taxonomies();
    $slider_recipe_tax->register_taxonomies();

    $slider_recipe_cpt = new Slider_Recipe_Post_Type();
    $slider_recipe_cpt->register_post_type();

    $slider_meta = new Slider_Recipe_Meta_Registry();
    $slider_meta->register_meta();

    if (is_admin()) {
        $slider_meta_box = new Slider_Recipe_Meta_Box();
        $slider_meta_box->hooks();
    }

    register_block_type(__DIR__);

    $sc = new Recipe_List_Shortcode();
    $sc->register();
}

/**
 * Enqueue Swiper and plugin styles/scripts.
 * 
 * Loads the Swiper library and custom plugin assets.
 * 
 * @since 1.0.0
 * @return void
 */
function recipe_slider_enqueue_swiper()
{
    wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css', [], '8.4.5');
    wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', [], '8.4.5', true);

    wp_enqueue_style('recipe-slider-styles', plugins_url('style.css', __FILE__), [], '1.0.1');

    wp_register_script('recipe-slider-ajax', plugins_url('public/recipe-slider-ajax.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('recipe-slider-ajax', 'RecipeSliderAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('recipe_slider_nonce'),
        'isUser' => is_user_logged_in() ? true : false,
    ));
    wp_enqueue_script('recipe-slider-ajax');

    wp_register_script('recipe-list-ajax', plugins_url('public/recipe-list-ajax.js', __FILE__), array(), '1.0.0', true);
    wp_localize_script('recipe-list-ajax', 'RecipeListAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('recipe_slider_nonce'),
    ));
    wp_enqueue_script('recipe-list-ajax');
}

add_action('init', 'recipe_slider_init');

/**
 * Plugin activation handler.
 * 
 * Sets up the database table for recipe feedback and flushes rewrite rules.
 * 
 * @since 1.0.0
 * @global wpdb $wpdb WordPress database abstraction object.
 * @return void
 */
function recipe_slider_activate()
{
    recipe_slider_init();
    global $wpdb;
    $table = $wpdb->prefix . 'recipe_slider_feedback';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED DEFAULT NULL,
        liked TINYINT UNSIGNED DEFAULT 0,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY post_user (post_id, user_id),
        KEY post_idx (post_id)
    ) $charset;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    flush_rewrite_rules();
}

/**
 * Plugin deactivation handler.
 * 
 * Cleans up rewrite rules on plugin deactivation.
 * 
 * @since 1.0.0
 * @return void
 */
function recipe_slider_deactivate()
{
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'recipe_slider_activate');
register_deactivation_hook(__FILE__, 'recipe_slider_deactivate');

require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

/**
 * Handle AJAX requests for recipe reactions (like/dislike).
 * 
 * Processes like/dislike actions from the frontend and updates the database.
 * 
 * @since 1.0.0
 * @return void
 */
function handle_recipe_slider_react()
{
    check_ajax_referer('recipe_slider_nonce');
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in to react.']);
        return;
    }
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    if ($post_id && in_array($type, array('like', 'dislike'), true)) {
        global $wpdb;
        $table = $wpdb->prefix . 'recipe_slider_feedback';
        $user_id = get_current_user_id();
        $current = $wpdb->get_var($wpdb->prepare("SELECT liked FROM $table WHERE post_id=%d AND user_id=%d", $post_id, $user_id));
        $liked = (int) $current === 1 ? 0 : 1;
        $wpdb->replace($table, array('post_id' => $post_id, 'user_id' => $user_id, 'liked' => $liked), array('%d', '%d', '%d'));
        $likes = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE post_id=%d AND liked=1", $post_id));
        update_post_meta($post_id, '_recipe_likes', $likes);
        wp_send_json_success(array(
            'likes' => $likes,
            'liked' => $liked,
        ));
    }
    wp_send_json_error(['message' => 'Invalid request.']);
}
add_action('wp_ajax_recipe_slider_react', 'handle_recipe_slider_react');
add_action('wp_ajax_nopriv_recipe_slider_react', 'handle_recipe_slider_react');

/**
 * Handle AJAX requests for recipe ratings.
 * 
 * Processes rating submissions and updates the average rating in the database.
 * 
 * @since 1.0.0
 * @global wpdb $wpdb WordPress database abstraction object.
 * @return void
 */
function handle_recipe_slider_rate()
{
    check_ajax_referer('recipe_slider_nonce');
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in to rate.']);
        return;
    }
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 0)));
    if ($post_id && $rating) {
        $user_id = get_current_user_id();
        global $wpdb;
        $table = $wpdb->prefix . 'recipe_slider_feedback';
        $wpdb->replace($table, array('post_id' => $post_id, 'user_id' => $user_id, 'rating' => $rating), array('%d', '%d', '%d'));

        $avg = (float) $wpdb->get_var($wpdb->prepare("SELECT AVG(rating) FROM $table WHERE post_id=%d AND rating IS NOT NULL", $post_id));
        $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE post_id=%d AND rating IS NOT NULL", $post_id));
        update_post_meta($post_id, '_recipe_rating_count', $count);
        update_post_meta($post_id, '_recipe_rating_avg', $avg);
        wp_send_json_success(array('avg' => round($avg), 'count' => $count));
    }
    wp_send_json_error(['message' => 'Invalid request.']);
}
add_action('wp_ajax_recipe_slider_rate', 'handle_recipe_slider_rate');
add_action('wp_ajax_nopriv_recipe_slider_rate', 'handle_recipe_slider_rate');

add_action('wp_ajax_recipe_list_query', 'recipe_slider_ajax_recipe_list');
add_action('wp_ajax_nopriv_recipe_list_query', 'recipe_slider_ajax_recipe_list');
/**
 * AJAX handler for recipe list queries.
 * 
 * Processes AJAX requests for filtering and displaying recipes.
 * 
 * @since 1.0.0
 * @return void
 */
function recipe_slider_ajax_recipe_list() {
    check_ajax_referer('recipe_slider_nonce');
    
    $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 9;
    $page = isset($_POST['rl_page']) ? (int) $_POST['rl_page'] : 1;
    $search = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';
    $sort = isset($_POST['rl_sort']) ? sanitize_text_field($_POST['rl_sort']) : 'date_desc';
    
    $cat = isset($_POST['rl_cat']) ? 
        (is_array($_POST['rl_cat']) ? array_map('sanitize_text_field', $_POST['rl_cat']) : [sanitize_text_field($_POST['rl_cat'])]) : [];
        
    $cui = isset($_POST['rl_cui']) ? 
        (is_array($_POST['rl_cui']) ? array_map('sanitize_text_field', $_POST['rl_cui']) : [sanitize_text_field($_POST['rl_cui'])]) : [];
        
    $die = isset($_POST['rl_die']) ? 
        (is_array($_POST['rl_die']) ? array_map('sanitize_text_field', $_POST['rl_die']) : [sanitize_text_field($_POST['rl_die'])]) : [];

    $tax_query = [];
    
    if (!empty($cat)) {
        $tax_query[] = [
            'taxonomy' => 'recipe_category',
            'field'    => 'slug',
            'terms'    => $cat,
            'operator' => 'IN'
        ];
    }
    
    if (!empty($cui)) {
        $tax_query[] = [
            'taxonomy' => 'cuisine_type',
            'field'    => 'slug',
            'terms'    => $cui,
            'operator' => 'IN'
        ];
    }
    
    if (!empty($die)) {
        $tax_query[] = [
            'taxonomy' => 'dietary_restriction',
            'field'    => 'slug',
            'terms'    => $die,
            'operator' => 'IN'
        ];
    }
    
    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }

    $args = array(
        'post_type'      => 'recipe',
        'posts_per_page' => max(1, min(24, $per_page)),
        'paged'          => max(1, $page),
        'post_status'    => 'publish',
        'no_found_rows'  => false, 
        's'              => $search,
    );
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    switch ($sort) {
        case 'title_asc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'title_desc':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'rating_desc':
            $args['meta_key'] = '_recipe_rating_avg';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'rating_asc':
            $args['meta_key'] = '_recipe_rating_avg';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'likes_desc':
            $args['meta_key'] = '_recipe_likes';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'likes_asc':
            $args['meta_key'] = '_recipe_likes';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'date_asc':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
    }

    $recipes_query = new WP_Query($args);
    
    ob_start();
    
    if ($recipes_query->have_posts()) :
        echo '<div class="recipe-grid">';
        
        while ($recipes_query->have_posts()) : $recipes_query->the_post();
            $recipe_id = get_the_ID();
            $rating = (int) round((float) get_post_meta($recipe_id, '_recipe_rating_avg', true));
            $likes = (int) get_post_meta($recipe_id, '_recipe_likes', true);
            $thumbnail = get_the_post_thumbnail_url($recipe_id, 'medium_large');
            $categories = get_the_terms($recipe_id, 'recipe_category');
            ?>
            <article class="recipe-card" data-id="<?php echo esc_attr($recipe_id); ?>">
                <?php if ($thumbnail) : ?>
                    <div class="recipe-card__image">
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>">
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="recipe-card__content">
                    <?php if ($categories && !is_wp_error($categories)) : ?>
                        <div class="recipe-card__categories">
                            <?php foreach (array_slice($categories, 0, 2) as $category) : ?>
                                <span class="recipe-category"><?php echo esc_html($category->name); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="recipe-card__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    
                    <div class="recipe-card__meta">
                        <?php if ($rating > 0) : ?>
                            <div class="recipe-rating">
                                <span class="stars"><?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?></span>
                                <span class="rating-count">(<?php echo $rating; ?>/5)</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="recipe-likes">
                            <span class="like-count"><?php echo $likes; ?></span>
                            <span class="like-icon">❤️</span>
                        </div>
                    </div>
                    
                    <?php if (has_excerpt()) : ?>
                        <div class="recipe-card__excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?php the_permalink(); ?>" class="recipe-card__button">
                        <?php esc_html_e('View Recipe', 'recipe-slider'); ?>
                    </a>
                </div>
            </article>
            <?php
        endwhile;
        
        echo '</div>'; 
    else :
        ?>
        <div class="no-recipes-found">
            <p><?php esc_html_e('No recipes found matching your criteria. Please try different filters.', 'recipe-slider'); ?></p>
        </div>
        <?php
    endif;
    
    wp_reset_postdata();
    $html = ob_get_clean();
    $pagination = '';
    if ($recipes_query->max_num_pages > 1) {
        $big = 999999999;
        $pagination = paginate_links(array(
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?rl_page=%#%',
            'current'   => max(1, $page),
            'total'     => $recipes_query->max_num_pages,
            'prev_text' => '&larr; ' . __('Previous', 'recipe-slider'),
            'next_text' => __('Next', 'recipe-slider') . ' &rarr;',
            'type'      => 'list',
        ));
    }

    wp_send_json_success(array('html' => $html, 'pagination' => $pagination));
}
