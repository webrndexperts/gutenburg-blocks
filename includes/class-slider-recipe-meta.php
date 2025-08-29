<?php
/**
 * Exit if accessed directly.
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles the registration of custom meta fields for the Recipe post type.
 * 
 * This class is responsible for registering all custom meta fields used by the Recipe
 * post type, including their schema and access control settings for the REST API.
 * 
 * @package    RecipeSlider
 * @subpackage Includes
 * @since      1.0.0
 */
class Slider_Recipe_Meta_Registry
{

	/**
	 * Registers all custom meta fields for the Recipe post type.
	 * 
	 * This method sets up the registration of various meta fields including:
	 * - Basic recipe information (prep time, cook time, servings)
	 * - Recipe content (ingredients, instructions)
	 * - Media (gallery)
	 * - Nutrition information
	 * - Rating and like/dislike functionality
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function register_meta()
	{
		$meta_args_number = array(
			'single' => true,
			'type' => 'number',
			'show_in_rest' => true,
			'auth_callback' => function () {
				return current_user_can('edit_posts'); },
		);

		register_post_meta('recipe', '_recipe_prep_time', $meta_args_number);
		register_post_meta('recipe', '_recipe_cook_time', $meta_args_number);
		register_post_meta('recipe', '_recipe_servings', $meta_args_number);

		register_post_meta('recipe', '_recipe_difficulty', array(
			'single' => true,
			'type' => 'string',
			'show_in_rest' => array(
				'schema' => array(
					'type' => 'string',
					'enum' => array('Easy', 'Medium', 'Hard'),
				),
			),
			'auth_callback' => function () {
				return current_user_can('edit_posts'); },
		));

		register_post_meta('recipe', '_recipe_ingredients', array(
			'single' => true,
			'type' => 'string',
			'show_in_rest' => true,
			'auth_callback' => function () {
				return current_user_can('edit_posts'); },
		));

		register_post_meta('recipe', '_recipe_instructions', array(
			'single' => true,
			'type' => 'string',
			'show_in_rest' => true,
			'auth_callback' => function () {
				return current_user_can('edit_posts'); },
		));

		register_post_meta('recipe', '_recipe_gallery', array(
			'single' => true,
			'type' => 'array',
			'show_in_rest' => array(
				'schema' => array(
					'type' => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback' => function () {
				return current_user_can('edit_posts'); },
		));

		register_post_meta('recipe', '_recipe_nutrition_calories', $meta_args_number);
		register_post_meta('recipe', '_recipe_nutrition_protein', $meta_args_number);
		register_post_meta('recipe', '_recipe_nutrition_carbs', $meta_args_number);
		register_post_meta('recipe', '_recipe_nutrition_fat', $meta_args_number);

		// Rating (average and count)
		register_post_meta('recipe', '_recipe_rating_avg', array(
			'single' => true,
			'type' => 'number',
			'show_in_rest' => true,
			'auth_callback' => '__return_true',
		));
		register_post_meta('recipe', '_recipe_rating_count', array(
			'single' => true,
			'type' => 'integer',
			'show_in_rest' => true,
			'auth_callback' => '__return_true',
		));
		register_post_meta('recipe', '_recipe_likes', array(
			'single' => true,
			'type' => 'integer',
			'show_in_rest' => true,
			'auth_callback' => '__return_true',
		));
		register_post_meta('recipe', '_recipe_dislikes', array(
			'single' => true,
			'type' => 'integer',
			'show_in_rest' => true,
			'auth_callback' => '__return_true',
		));
	}
}


