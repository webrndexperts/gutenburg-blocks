<?php
/**
 * Exit if accessed directly.
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles the registration and management of custom taxonomies for the Recipe post type.
 * 
 * This class is responsible for defining and registering the following taxonomies:
 * - Recipe Categories (hierarchical)
 * - Cuisine Types (non-hierarchical)
 * - Dietary Restrictions (non-hierarchical)
 * 
 * @package    RecipeSlider
 * @subpackage Includes
 * @since      1.0.0
 */
class Slider_Recipe_Taxonomies
{

	/**
	 * Registers all taxonomies for the Recipe post type.
	 * 
	 * This method serves as an entry point to register all custom taxonomies
	 * used by the Recipe post type.
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function register_taxonomies()
	{
		$this->register_recipe_category_taxonomy();
		$this->register_cuisine_type_taxonomy();
		$this->register_dietary_restriction_taxonomy();
	}

	/**
	 * Registers the Recipe Category taxonomy.
	 * 
	 * This is a hierarchical taxonomy (like categories) used to organize recipes
	 * into different categories such as Appetizers, Main Courses, etc.
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	private function register_recipe_category_taxonomy()
	{
		if (taxonomy_exists('recipe_category')) {
			return;
		}
		$labels = array(
			'name' => _x('Recipe Categories', 'taxonomy general name', 'recipe-slider'),
			'singular_name' => _x('Recipe Category', 'taxonomy singular name', 'recipe-slider'),
			'search_items' => __('Search Recipe Categories', 'recipe-slider'),
			'all_items' => __('All Recipe Categories', 'recipe-slider'),
			'parent_item' => __('Parent Recipe Category', 'recipe-slider'),
			'parent_item_colon' => __('Parent Recipe Category:', 'recipe-slider'),
			'edit_item' => __('Edit Recipe Category', 'recipe-slider'),
			'update_item' => __('Update Recipe Category', 'recipe-slider'),
			'add_new_item' => __('Add New Recipe Category', 'recipe-slider'),
			'new_item_name' => __('New Recipe Category Name', 'recipe-slider'),
			'menu_name' => __('Categories', 'recipe-slider'),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'recipe-category'),
			'show_in_rest' => true,
		);

		register_taxonomy('recipe_category', array('recipe'), $args);

		$default_categories = array(
			'Appetizers' => 'Small dishes served before the main course',
			'Main Courses' => 'Hearty dishes that make up the main part of a meal',
			'Desserts' => 'Sweet dishes served at the end of a meal',
			'Breakfast' => 'Morning meal recipes',
			'Lunch' => 'Midday meal recipes',
			'Dinner' => 'Evening meal recipes',
			'Snacks' => 'Light meals or small portions',
			'Beverages' => 'Drinks and cocktails',
		);
		foreach ($default_categories as $name => $desc) {
			if (!term_exists($name, 'recipe_category')) {
				wp_insert_term($name, 'recipe_category', array('description' => $desc));
			}
		}
	}

	/**
	 * Registers the Cuisine Type taxonomy.
	 * 
	 * This is a non-hierarchical taxonomy (like tags) used to classify recipes
	 * by their regional cuisine type (e.g., Italian, Mexican, Chinese).
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	private function register_cuisine_type_taxonomy()
	{
		if (taxonomy_exists('cuisine_type')) {
			return;
		}
		$labels = array(
			'name' => _x('Cuisine Types', 'taxonomy general name', 'recipe-slider'),
			'singular_name' => _x('Cuisine Type', 'taxonomy singular name', 'recipe-slider'),
			'search_items' => __('Search Cuisine Types', 'recipe-slider'),
			'popular_items' => __('Popular Cuisine Types', 'recipe-slider'),
			'all_items' => __('All Cuisine Types', 'recipe-slider'),
			'edit_item' => __('Edit Cuisine Type', 'recipe-slider'),
			'update_item' => __('Update Cuisine Type', 'recipe-slider'),
			'add_new_item' => __('Add New Cuisine Type', 'recipe-slider'),
			'new_item_name' => __('New Cuisine Type Name', 'recipe-slider'),
			'separate_items_with_commas' => __('Separate cuisine types with commas', 'recipe-slider'),
			'add_or_remove_items' => __('Add or remove cuisine types', 'recipe-slider'),
			'choose_from_most_used' => __('Choose from the most used cuisine types', 'recipe-slider'),
			'not_found' => __('No cuisine types found.', 'recipe-slider'),
			'menu_name' => __('Cuisine Types', 'recipe-slider'),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'cuisine'),
			'show_in_rest' => true,
		);

		register_taxonomy('cuisine_type', array('recipe'), $args);

		$default_cuisines = array('Italian', 'Mexican', 'Chinese', 'Indian', 'Japanese', 'Thai', 'Mediterranean', 'American', 'French');
		foreach ($default_cuisines as $cuisine) {
			if (!term_exists($cuisine, 'cuisine_type')) {
				wp_insert_term($cuisine, 'cuisine_type');
			}
		}
	}

	/**
	 * Registers the Dietary Restriction taxonomy.
	 * 
	 * This is a non-hierarchical taxonomy used to tag recipes with dietary
	 * considerations (e.g., Vegan, Gluten-Free, Keto).
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	private function register_dietary_restriction_taxonomy()
	{
		if (taxonomy_exists('dietary_restriction')) {
			return;
		}
		$labels = array(
			'name' => _x('Dietary Restrictions', 'taxonomy general name', 'recipe-slider'),
			'singular_name' => _x('Dietary Restriction', 'taxonomy singular name', 'recipe-slider'),
			'search_items' => __('Search Dietary Restrictions', 'recipe-slider'),
			'popular_items' => __('Common Dietary Restrictions', 'recipe-slider'),
			'all_items' => __('All Dietary Restrictions', 'recipe-slider'),
			'edit_item' => __('Edit Dietary Restriction', 'recipe-slider'),
			'update_item' => __('Update Dietary Restriction', 'recipe-slider'),
			'add_new_item' => __('Add New Dietary Restriction', 'recipe-slider'),
			'new_item_name' => __('New Dietary Restriction Name', 'recipe-slider'),
			'separate_items_with_commas' => __('Separate dietary restrictions with commas', 'recipe-slider'),
			'add_or_remove_items' => __('Add or remove dietary restrictions', 'recipe-slider'),
			'choose_from_most_used' => __('Choose from the most common dietary restrictions', 'recipe-slider'),
			'not_found' => __('No dietary restrictions found.', 'recipe-slider'),
			'menu_name' => __('Dietary', 'recipe-slider'),
		);

		$args = array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'dietary'),
			'show_in_rest' => true,
		);

		register_taxonomy('dietary_restriction', array('recipe'), $args);

		$default_restrictions = array('Vegetarian', 'Vegan', 'Gluten-Free', 'Dairy-Free', 'Nut-Free', 'Keto', 'Paleo', 'Low-Carb', 'Low-Fat');
		foreach ($default_restrictions as $restriction) {
			if (!term_exists($restriction, 'dietary_restriction')) {
				wp_insert_term($restriction, 'dietary_restriction');
			}
		}
	}
}


