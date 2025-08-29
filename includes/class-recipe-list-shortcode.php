<?php
/**
 * Exit if accessed directly.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the [recipe_list] shortcode functionality.
 * 
 * This class provides a flexible shortcode to display a grid of recipes
 * with filtering, sorting, and pagination capabilities.
 * 
 * @package    RecipeSlider
 * @subpackage Includes
 * @since      1.0.0
 */
class Recipe_List_Shortcode
{

    /**
     * Registers the shortcode with WordPress.
     * 
     * @since 1.0.0
     * @return void
     */
    public function register()
    {
        add_shortcode('recipe_list', array($this, 'render'));
    }

    /**
     * Processes and validates shortcode attributes.
     * 
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return array Processed attributes with defaults.
     */
    private function get_atts($atts)
    {
        $defaults = array(
            'per_page' => 6,
            'page' => 1,
            'search' => '',
            'category' => '',
            'cuisine' => '',
            'dietary' => '',
            'sort' => 'date_desc',
            'show_controls' => 'true',
            'show_search' => 'true',
            'show_filters' => 'true',
            'show_sort' => 'true',
        );
        $atts = shortcode_atts($defaults, $atts, 'recipe_list');
        $atts['page'] = max(1, (int) $atts['page']);
        $atts['per_page'] = max(1, min(24, (int) $atts['per_page']));
        foreach (array('show_controls', 'show_search', 'show_filters', 'show_sort') as $k) {
            $atts[$k] = filter_var($atts[$k], FILTER_VALIDATE_BOOLEAN);
        }
        return $atts;
    }

    /**
     * Renders the recipe list shortcode output.
     * 
     * Handles the display of the recipe grid, including processing of
     * search, filter, and sort parameters.
     * 
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string HTML output of the recipe list.
     */
    public function render($atts)
    {
        $atts = $this->get_atts($atts);
        $paged = isset($_GET['rl_page']) ? max(1, (int) $_GET['rl_page']) : $atts['page'];
        $search = isset($_GET['rl_s']) ? sanitize_text_field($_GET['rl_s']) : $atts['search'];
        $sort = isset($_GET['rl_sort']) ? sanitize_text_field($_GET['rl_sort']) : $atts['sort'];
        $catSel = isset($_GET['rl_cat']) ? array_filter(array_map('sanitize_text_field', (array) $_GET['rl_cat'])) : array_filter(array_map('trim', explode(',', $atts['category'])));
        $cuiSel = isset($_GET['rl_cui']) ? array_filter(array_map('sanitize_text_field', (array) $_GET['rl_cui'])) : array_filter(array_map('trim', explode(',', $atts['cuisine'])));
        $dieSel = isset($_GET['rl_die']) ? array_filter(array_map('sanitize_text_field', (array) $_GET['rl_die'])) : array_filter(array_map('trim', explode(',', $atts['dietary'])));

        $tax_query = array();
        if (!empty($catSel)) {
            $tax_query[] = array('taxonomy' => 'recipe_category', 'field' => 'slug', 'terms' => $catSel);
        }
        if (!empty($cuiSel)) {
            $tax_query[] = array('taxonomy' => 'cuisine_type', 'field' => 'slug', 'terms' => $cuiSel);
        }
        if (!empty($dieSel)) {
            $tax_query[] = array('taxonomy' => 'dietary_restriction', 'field' => 'slug', 'terms' => $dieSel);
        }
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }

        $args = array(
            'post_type' => 'recipe',
            'posts_per_page' => (int) $atts['per_page'],
            'paged' => (int) $paged,
            's' => $search,
            'post_status' => 'publish',
        );
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // Advanced search meta
        $meta_query = array();
        $prep_min = isset($_GET['rl_prep_min']) ? (int) $_GET['rl_prep_min'] : 0;
        $prep_max = isset($_GET['rl_prep_max']) ? (int) $_GET['rl_prep_max'] : 0;
        $difficulty = isset($_GET['rl_diff']) ? sanitize_text_field($_GET['rl_diff']) : '';
        if ($prep_min > 0) {
            $meta_query[] = array('key' => '_recipe_prep_time', 'value' => $prep_min, 'type' => 'NUMERIC', 'compare' => '>=');
        }
        if ($prep_max > 0) {
            $meta_query[] = array('key' => '_recipe_prep_time', 'value' => $prep_max, 'type' => 'NUMERIC', 'compare' => '<=');
        }
        if (!empty($difficulty)) {
            $meta_query[] = array('key' => '_recipe_difficulty', 'value' => $difficulty, 'compare' => '=');
        }
        if (!empty($search)) {
            $meta_query[] = array(
                'relation' => 'OR',
                array('key' => '_recipe_ingredients', 'value' => $search, 'compare' => 'LIKE'),
                array('key' => '_recipe_instructions', 'value' => $search, 'compare' => 'LIKE'),
            );
        }
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
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

        $q = new WP_Query($args);
        ob_start();
        ?>
        <div class="recipe-list">
            <?php if ($atts['show_controls']): ?>
                <form class="recipe-list__controls" method="get">
                    <input type="hidden" name="rl_page" value="<?php echo (int) $paged; ?>" />
                    <?php if ($atts['show_search']): ?>
                        <input type="text" name="rl_s" placeholder="Search recipes" value="<?php echo esc_attr($search); ?>" />
                    <?php endif; ?>
                    <?php if ($atts['show_filters']): ?>
                        <?php $cats = get_terms(array('taxonomy' => 'recipe_category', 'hide_empty' => true)); ?>
                        <select name="rl_cat[]">
                            <option value="">All Categories</option>
                            <?php foreach ($cats as $t): ?>
                                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $catSel, true)); ?>>
                                    <?php echo esc_html($t->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php $cuis = get_terms(array('taxonomy' => 'cuisine_type', 'hide_empty' => true)); ?>
                        <select name="rl_cui[]">
                            <option value="">All Cuisines</option>
                            <?php foreach ($cuis as $t): ?>
                                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $cuiSel, true)); ?>>
                                    <?php echo esc_html($t->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php $diets = get_terms(array('taxonomy' => 'dietary_restriction', 'hide_empty' => true)); ?>
                        <select name="rl_die[]">
                            <option value="">All Diets</option>
                            <?php foreach ($diets as $t): ?>
                                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $dieSel, true)); ?>>
                                    <?php echo esc_html($t->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="rl_prep_min" placeholder="Min prep (min)" value="<?php echo isset($_GET['rl_prep_min']) ? (int) $_GET['rl_prep_min'] : ''; ?>" />
                        <input type="number" name="rl_prep_max" placeholder="Max prep (min)" value="<?php echo isset($_GET['rl_prep_max']) ? (int) $_GET['rl_prep_max'] : ''; ?>" />
                        <select name="rl_diff">
                            <option value="">Any Difficulty</option>
                            <option value="Easy" <?php selected(isset($_GET['rl_diff']) ? $_GET['rl_diff'] : '', 'Easy'); ?>>Easy</option>
                            <option value="Medium" <?php selected(isset($_GET['rl_diff']) ? $_GET['rl_diff'] : '', 'Medium'); ?>>Medium</option>
                            <option value="Hard" <?php selected(isset($_GET['rl_diff']) ? $_GET['rl_diff'] : '', 'Hard'); ?>>Hard</option>
                        </select>
                    <?php endif; ?>
                    <?php if ($atts['show_sort']): ?>
                        <select name="rl_sort">
                            <option value="date_desc" <?php selected($sort, 'date_desc'); ?>>Newest</option>
                            <option value="rating_desc" <?php selected($sort, 'rating_desc'); ?>>Top Rated</option>
                            <option value="likes_desc" <?php selected($sort, 'likes_desc'); ?>>Most Liked</option>
                            <option value="title_asc" <?php selected($sort, 'title_asc'); ?>>A–Z</option>
                            <option value="title_desc" <?php selected($sort, 'title_desc'); ?>>Z–A</option>
                        </select>
                    <?php endif; ?>
                    <button type="submit">Apply</button>
                </form>
            <?php endif; ?>

            <div class="recipe-list__grid">
                <?php if ($q->have_posts()):
                    while ($q->have_posts()):
                        $q->the_post(); ?>
                        <article class="recipe-card">
                            <a class="recipe-card__thumb" href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium_large');
                                } ?>
                            </a>
                            <h3 class="recipe-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="recipe-card__meta">
                                <?php $avg = (int) round((float) get_post_meta(get_the_ID(), '_recipe_rating_avg', true));
                                $likes = (int) get_post_meta(get_the_ID(), '_recipe_likes', true);
                                echo '<span class="stars">' . str_repeat('★', $avg) . str_repeat('☆', 5 - $avg) . '</span>';
                                echo ' <span class="likes">👍 ' . $likes . '</span>';
                                ?>
                            </div>
                            <p class="recipe-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                        </article>
                    <?php endwhile; else: ?>
                    <p><?php esc_html_e('No recipes found.', 'recipe-slider'); ?></p>
                <?php endif;
                wp_reset_postdata(); ?>
            </div>

            <?php if ($q->max_num_pages > 1): ?>
                <nav class="recipe-list__pagination">
                    <?php
                    echo paginate_links(array(
                        'format' => '?rl_page=%#%',
                        'current' => max(1, (int) ($_GET['rl_page'] ?? $paged)),
                        'total' => (int) $q->max_num_pages,
                    ));
                    ?>
                </nav>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

