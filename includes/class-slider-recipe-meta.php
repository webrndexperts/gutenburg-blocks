<?php
if (!defined('ABSPATH')) {
	exit;
}

class Slider_Recipe_Meta_Registry
{

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


