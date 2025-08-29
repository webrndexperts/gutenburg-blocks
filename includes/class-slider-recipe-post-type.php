<?php
/**
 * Exit if accessed directly.
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles the registration and management of the Recipe custom post type.
 * 
 * This class is responsible for defining the Recipe post type, its labels,
 * and its behavior within the WordPress admin and frontend.
 * 
 * @package    RecipeSlider
 * @subpackage Includes
 * @since      1.0.0
 */
class Slider_Recipe_Post_Type
{

	/**
	 * Registers the Recipe custom post type.
	 * 
	 * This method sets up the labels, arguments, and registers the 'recipe' post type
	 * with WordPress. It includes proper localization support and registers the post type
	 * with the Gutenberg editor.
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function register_post_type()
	{
		if (post_type_exists('recipe')) {
			return;
		}

		$labels = array(
			'name' => _x('Recipes', 'Post Type General Name', 'recipe-slider'),
			'singular_name' => _x('Recipe', 'Post Type Singular Name', 'recipe-slider'),
			'menu_name' => __('Recipes', 'recipe-slider'),
			'name_admin_bar' => __('Recipe', 'recipe-slider'),
			'archives' => __('Recipe Archives', 'recipe-slider'),
			'attributes' => __('Recipe Attributes', 'recipe-slider'),
			'parent_item_colon' => __('Parent Recipe:', 'recipe-slider'),
			'all_items' => __('All Recipes', 'recipe-slider'),
			'add_new_item' => __('Add New Recipe', 'recipe-slider'),
			'add_new' => __('Add New', 'recipe-slider'),
			'new_item' => __('New Recipe', 'recipe-slider'),
			'edit_item' => __('Edit Recipe', 'recipe-slider'),
			'update_item' => __('Update Recipe', 'recipe-slider'),
			'view_item' => __('View Recipe', 'recipe-slider'),
			'view_items' => __('View Recipes', 'recipe-slider'),
			'search_items' => __('Search Recipe', 'recipe-slider'),
			'not_found' => __('Not found', 'recipe-slider'),
			'not_found_in_trash' => __('Not found in Trash', 'recipe-slider'),
			'featured_image' => __('Recipe Image', 'recipe-slider'),
			'set_featured_image' => __('Set recipe image', 'recipe-slider'),
			'remove_featured_image' => __('Remove recipe image', 'recipe-slider'),
			'use_featured_image' => __('Use as recipe image', 'recipe-slider'),
			'insert_into_item' => __('Insert into recipe', 'recipe-slider'),
			'uploaded_to_this_item' => __('Uploaded to this recipe', 'recipe-slider'),
			'items_list' => __('Recipes list', 'recipe-slider'),
			'items_list_navigation' => __('Recipes list navigation', 'recipe-slider'),
			'filter_items_list' => __('Filter recipes list', 'recipe-slider'),
		);

		$args = array(
			'label' => __('Recipe', 'recipe-slider'),
			'description' => __('Recipe custom post type', 'recipe-slider'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'),
			'taxonomies' => array('recipe_category', 'cuisine_type', 'dietary_restriction'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-food',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => 'recipes',
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'capability_type' => 'post',
			'show_in_rest' => true,
		);

		register_post_type('recipe', $args);
	}
}


