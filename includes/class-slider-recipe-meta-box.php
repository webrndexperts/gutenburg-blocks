<?php
/**
 * Exit if accessed directly.
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles the creation and management of the Recipe meta box.
 * 
 * This class is responsible for rendering and saving the custom meta box
 * that appears on the Recipe post type edit screen, allowing users to input
 * additional recipe details like preparation time, ingredients, and nutrition information.
 * 
 * @package    RecipeSlider
 * @subpackage Includes
 * @since      1.0.0
 */
class Slider_Recipe_Meta_Box
{

	/**
	 * Initializes hooks for the meta box.
	 * 
	 * Sets up the necessary WordPress hooks to add and save the meta box.
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function hooks()
	{
		add_action('add_meta_boxes', array($this, 'add_box'));
		add_action('save_post_recipe', array($this, 'save'));
	}

	/**
	 * Adds the meta box to the Recipe post type edit screen.
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function add_box()
	{
		add_meta_box('slider_recipe_meta', __('Recipe Details', 'recipe-slider'), array($this, 'render'), 'recipe', 'normal', 'high');
	}

	/**
	 * Renders the meta box content.
	 * 
	 * Outputs the HTML for the meta box fields and populates them with saved values.
	 * 
	 * @since 1.0.0
	 * @param WP_Post $post The post object being edited.
	 * @return void
	 */
	public function render($post)
	{
		wp_nonce_field('slider_recipe_meta_save', 'slider_recipe_meta_nonce');
		$prep = get_post_meta($post->ID, '_recipe_prep_time', true);
		$cook = get_post_meta($post->ID, '_recipe_cook_time', true);
		$serve = get_post_meta($post->ID, '_recipe_servings', true);
		$diff = get_post_meta($post->ID, '_recipe_difficulty', true);
		$ing = get_post_meta($post->ID, '_recipe_ingredients', true);
		$instr = get_post_meta($post->ID, '_recipe_instructions', true);
		$gallery = (array) get_post_meta($post->ID, '_recipe_gallery', true);
		$cal = get_post_meta($post->ID, '_recipe_nutrition_calories', true);
		$protein = get_post_meta($post->ID, '_recipe_nutrition_protein', true);
		$carbs = get_post_meta($post->ID, '_recipe_nutrition_carbs', true);
		$fat = get_post_meta($post->ID, '_recipe_nutrition_fat', true);
		?>
		<style>
			.slider-recipe-meta-grid {
				display: grid;
				grid-template-columns: repeat(2, minmax(0, 1fr));
				gap: 12px
			}

			.slider-recipe-meta-grid textarea {
				min-height: 120px;
				width: 100%
			}
		</style>
		<div class="slider-recipe-meta-grid">
			<label><?php _e('Preparation time (minutes)', 'recipe-slider'); ?><input type="number" name="_recipe_prep_time"
					value="<?php echo esc_attr($prep); ?>" /></label>
			<label><?php _e('Cooking time (minutes)', 'recipe-slider'); ?><input type="number" name="_recipe_cook_time"
					value="<?php echo esc_attr($cook); ?>" /></label>
			<label><?php _e('Servings', 'recipe-slider'); ?><input type="number" name="_recipe_servings"
					value="<?php echo esc_attr($serve); ?>" /></label>
			<label><?php _e('Difficulty', 'recipe-slider'); ?>
				<select name="_recipe_difficulty">
					<option value="">—</option>
					<?php foreach (array('Easy', 'Medium', 'Hard') as $level): ?>
						<option value="<?php echo esc_attr($level); ?>" <?php selected($diff, $level); ?>>
							<?php echo esc_html($level); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label style="grid-column:1/-1"><?php _e('Ingredients (one per line)', 'recipe-slider'); ?><textarea
					name="_recipe_ingredients"><?php echo esc_textarea($ing); ?></textarea></label>
			<label style="grid-column:1/-1"><?php _e('Instructions', 'recipe-slider'); ?><textarea
					name="_recipe_instructions"><?php echo esc_textarea($instr); ?></textarea></label>
			<label><?php _e('Calories', 'recipe-slider'); ?><input type="number" name="_recipe_nutrition_calories"
					value="<?php echo esc_attr($cal); ?>" /></label>
			<label><?php _e('Protein (g)', 'recipe-slider'); ?><input type="number" name="_recipe_nutrition_protein"
					value="<?php echo esc_attr($protein); ?>" /></label>
			<label><?php _e('Carbs (g)', 'recipe-slider'); ?><input type="number" name="_recipe_nutrition_carbs"
					value="<?php echo esc_attr($carbs); ?>" /></label>
			<label><?php _e('Fat (g)', 'recipe-slider'); ?><input type="number" name="_recipe_nutrition_fat"
					value="<?php echo esc_attr($fat); ?>" /></label>
		</div>

		<?php
	}

	/**
	 * Saves the meta box data when the post is saved.
	 * 
	 * Validates and sanitizes the input before saving to the database.
	 * 
	 * @since 1.0.0
	 * @param int $post_id The ID of the post being saved.
	 * @return void
	 */
	public function save($post_id)
	{
		if (!isset($_POST['slider_recipe_meta_nonce']) || !wp_verify_nonce($_POST['slider_recipe_meta_nonce'], 'slider_recipe_meta_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$fields = array(
			'_recipe_prep_time' => 'floatval',
			'_recipe_cook_time' => 'floatval',
			'_recipe_servings' => 'floatval',
			'_recipe_difficulty' => 'sanitize_text_field',
			'_recipe_ingredients' => null,
			'_recipe_instructions' => null,
			'_recipe_nutrition_calories' => 'floatval',
			'_recipe_nutrition_protein' => 'floatval',
			'_recipe_nutrition_carbs' => 'floatval',
			'_recipe_nutrition_fat' => 'floatval',
		);

		foreach ($fields as $key => $san) {
			if (isset($_POST[$key])) {
				$value = $_POST[$key];
				if ($san === 'sanitize_text_field') {
					$value = sanitize_text_field($value);
				} elseif ($san === 'floatval') {
					$value = floatval($value);
				}
				update_post_meta($post_id, $key, $value);
			}
		}

		if (isset($_POST['_recipe_gallery'])) {
			$ids = array_filter(array_map('intval', array_map('trim', explode(',', (string) $_POST['_recipe_gallery']))));
			update_post_meta($post_id, '_recipe_gallery', $ids);
		}
	}
}


