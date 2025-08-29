<?php
/**
 * Recipe Slider - Custom REST API Endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

class Recipe_Slider_REST_API {
    /**
     * Register custom REST API routes
     */
    public static function register_routes() {
        register_rest_route('recipes/v1', '/featured', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'get_featured_recipes'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('recipes/v1', '/search', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'search_recipes'),
            'permission_callback' => '__return_true',
            'args' => array(
                's' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Search term'
                ),
                'category' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Category slug'
                )
            )
        ));

        register_rest_route('recipes/v1', '/categories', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'get_recipe_categories'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('recipes/v1', '/recipe/(?P<id>\d+)/rate', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array(__CLASS__, 'rate_recipe'),
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args' => array(
                'rating' => array(
                    'required' => true,
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 5,
                    'description' => 'Rating value (1-5)'
                )
            )
        ));
    }

    /**
     * Get featured recipes
     */
    public static function get_featured_recipes($request) {
        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => 6,
            'meta_key' => '_featured_recipe',
            'meta_value' => '1',
            'meta_compare' => '='
        );

        $query = new WP_Query($args);
        $recipes = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $recipes[] = self::prepare_recipe_data($post_id);
            }
            wp_reset_postdata();
        }

        return rest_ensure_response($recipes);
    }

    /**
     * Search recipes
     */
    public static function search_recipes($request) {
        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => 12,
            's' => sanitize_text_field($request->get_param('s')),
            'tax_query' => array()
        );

        if ($category = $request->get_param('category')) {
            $args['tax_query'][] = array(
                'taxonomy' => 'recipe_category',
                'field'    => 'slug',
                'terms'    => $category,
            );
        }

        $query = new WP_Query($args);
        $recipes = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $recipes[] = self::prepare_recipe_data(get_the_ID());
            }
            wp_reset_postdata();
        }

        return rest_ensure_response(array(
            'found_posts' => $query->found_posts,
            'recipes' => $recipes
        ));
    }

    /**
     * Get recipe categories with counts
     */
    public static function get_recipe_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'recipe_category',
            'hide_empty' => true,
        ));

        $result = array();
        foreach ($categories as $category) {
            $result[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count,
                'description' => $category->description
            );
        }

        return rest_ensure_response($result);
    }

    /**
     * Handle recipe rating
     */
    public static function rate_recipe($request) {
        $post_id = $request['id'];
        $rating = $request->get_param('rating');
        $user_id = get_current_user_id();

        if (!get_post($post_id) || get_post_type($post_id) !== 'recipe') {
            return new WP_Error('invalid_recipe', 'Invalid recipe ID', array('status' => 404));
        }

        $ratings = get_post_meta($post_id, '_recipe_ratings', true) ?: array();
        $ratings[$user_id] = $rating;

        update_post_meta($post_id, '_recipe_ratings', $ratings);

        $average = array_sum($ratings) / count($ratings);
        update_post_meta($post_id, '_recipe_rating_avg', $average);

        return rest_ensure_response(array(
            'success' => true,
            'average_rating' => round($average, 1),
            'total_ratings' => count($ratings)
        ));
    }

    /**
     * Prepare recipe data for API response
     */
    private static function prepare_recipe_data($post_id) {
        $post = get_post($post_id);
        
        $data = array(
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'content' => apply_filters('the_content', $post->post_content),
            'excerpt' => get_the_excerpt($post_id),
            'date' => get_the_date('c', $post_id),
            'modified' => get_the_modified_date('c', $post_id),
            'slug' => $post->post_name,
            'permalink' => get_permalink($post_id),
            'thumbnail' => get_the_post_thumbnail_url($post_id, 'full'),
            'meta' => array()
        );

        if (function_exists('get_fields')) {
            $data['acf'] = get_fields($post_id);
        }

        $categories = get_the_terms($post_id, 'recipe_category');
        if ($categories && !is_wp_error($categories)) {
            $data['categories'] = array_map(function($cat) {
                return array(
                    'id' => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'link' => get_term_link($cat)
                );
            }, $categories);
        }

        $tags = get_the_terms($post_id, 'recipe_tag');
        if ($tags && !is_wp_error($tags)) {
            $data['tags'] = array_map(function($tag) {
                return array(
                    'id' => $tag->term_id,
                    'name' => $tag->name,
                    'slug' => $tag->slug
                );
            }, $tags);
        }

        $ratings = get_post_meta($post_id, '_recipe_ratings', true) ?: array();
        $average_rating = get_post_meta($post_id, '_recipe_rating_avg', true);
        
        $data['ratings'] = array(
            'average' => $average_rating ? (float) $average_rating : 0,
            'count' => count($ratings)
        );

        $likes = get_post_meta($post_id, '_recipe_likes', true);
        $data['likes'] = $likes ? (int) $likes : 0;

        return $data;
    }
}

// Initializing the REST API
add_action('rest_api_init', array('Recipe_Slider_REST_API', 'register_routes'));
