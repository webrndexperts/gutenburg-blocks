# Recipe Slider Plugin

A powerful WordPress plugin for creating beautiful recipe carousels with advanced filtering, search, and REST API support.

## Features

- **Custom Post Type**: Dedicated "Recipe" post type for managing your recipes
- **Taxonomies**: Built-in support for categories, cuisines, and dietary restrictions
- **Interactive Slider**: Responsive carousel with touch support
- **Shortcode**: Easy embedding of recipe lists with search and filters
- **REST API**: Headless WordPress support with custom endpoints
- **Ratings & Likes**: User engagement features with AJAX support
- **Gutenberg Block**: Drag-and-drop block for easy content creation

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation

1. Upload the `recipe-slider` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Recipes' in the WordPress admin to start adding recipes

## Usage

### Adding Recipes
1. Navigate to "Recipes > Add New" in the WordPress admin
2. Fill in the recipe details (title, content, featured image)
3. Set recipe metadata (prep time, difficulty, etc.)
4. Categorize your recipe and add relevant tags
5. Publish your recipe

### Using the Gutenberg Block
1. Edit a post or page with the Gutenberg editor
2. Click the "+" button to add a new block
3. Search for and select "Recipe Slider"
4. Configure the slider settings in the block sidebar
5. Save and publish your post/page

### Using the Shortcode
Add the following shortcode to any post, page, or widget:

```
[recipe_list]
```

#### Shortcode Attributes
- `posts_per_page` (int): Number of recipes to show (default: 6)
- `order` (string): Sort order (ASC/DESC, default: DESC)
- `orderby` (string): Sort by (date, title, rating, etc., default: date)
- `category` (string): Comma-separated category slugs to filter by
- `cuisine` (string): Comma-separated cuisine types to filter by
- `diet` (string): Comma-separated dietary restrictions to filter by

Example:
```
[recipe_list posts_per_page="9" orderby="title" order="ASC" category="desserts,breakfast"]
```

## REST API Endpoints

The plugin provides the following REST API endpoints:

### Get Featured Recipes
```
GET /wp-json/recipes/v1/featured
```

### Search Recipes
```
GET /wp-json/recipes/v1/search
```
Parameters:
- `s` (string): Search term
- `category` (string): Filter by category slug
- `page` (int): Pagination (default: 1)
- `per_page` (int): Items per page (default: 12)

### Get Recipe Categories
```
GET /wp-json/recipes/v1/categories
```

### Rate a Recipe
```
POST /wp-json/recipes/v1/recipe/{id}/rate
```
Required headers:
- `X-WP-Nonce`: WordPress nonce
- `Content-Type`: application/json

Body:
```json
{
    "rating": 5
}
```

## Customization

### Styling
You can override the default styles by adding custom CSS to your theme's stylesheet or using the WordPress Customizer.

## Development

### Prerequisites
- Node.js 14+
- npm or yarn
- Composer (for PHP dependencies)

### Setup
1. Clone the repository
2. Run `npm install` to install JavaScript dependencies
3. Run `composer install` to install PHP dependencies
4. Run `npm run build` to compile assets

### Available Scripts
- `npm start`: Start development server
- `npm run build`: Build production assets

## License

GPL v2 or later
