<?php 
/*
Plugin Name: Simple Recipe
Plugin URI: https://github.com/Protohominid/simple-recipe
Description: Creates the "Recipe" post type and shortcode to insert into posts.
Author: Shawn Beelman
Version: 0.3
Author URI: http://www.shawnbeelman.com
License: GPLv2
Text Domain: simple-recipe
*/
// text domain for I18n (should be same as plugin slug):
$textdomain = 'simple-recipe';


//Register Recipe Custom Post Type 
add_action( 'init', 'create_simple_recipe_cpt' );
function create_simple_recipe_cpt() {
	global $textdomain;
	$labels = array(
		'name' => 		__( 'Recipes', $textdomain ),
		'singular_name' =>	__( 'Recipe', $textdomain ),
		'add_new' =>		__( 'Add New', $textdomain ),
		'add_new_item' =>	__( 'Add New Recipe', $textdomain ),
		'edit_item' =>		__( 'Edit Recipe', $textdomain ),
		'new_item' =>		__( 'New Recipe', $textdomain ),
		'all_items' =>		__( 'All Recipes', $textdomain ),
		'view_item' =>		__( 'View Recipe', $textdomain ),
		'search_items' =>	__( 'Search Recipes', $textdomain ),
		'not_found' =>		__( 'No recipes found', $textdomain ),
		'not_found_in_trash' => __( 'No recipes found in the Trash', $textdomain ), 
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'has_archive' => false,
		'supports' => array( 'title', 'thumbnail' )
	);
	register_post_type( 'recipes', $args );
}


//Custom messages
function sr_updated_messages( $messages ) {
	global $post, $post_ID, $textdomain;
	$messages['recipes'] = array(
		0 => '', 
		1 => sprintf( __( 'Recipe updated. <a href="%s">View recipe</a>', $textdomain ), esc_url( get_permalink($post_ID) ) ),
		2 => __( 'Custom field updated.', $textdomain ),
		3 => __( 'Custom field deleted.', $textdomain ),
		4 => __( 'Recipe updated.', $textdomain ),
		5 => isset($_GET['revision']) ? sprintf( __( 'Recipe restored to revision from %s', $textdomain ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Recipe published. <a href="%s">View recipe</a>', $textdomain ), esc_url( get_permalink($post_ID) ) ),
		7 => __( 'Recipe saved.', $textdomain ),
		8 => sprintf( __( 'Recipe submitted. <a target="_blank" href="%s">Preview recipe</a>', $textdomain ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __( 'Recipe scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview recipe</a>', $textdomain ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __( 'Recipe draft updated. <a target="_blank" href="%s">Preview recipe</a>', $textdomain ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}
add_filter( 'post_updated_messages', 'sr_updated_messages' );


//Add Recipe Categories
add_action( 'init', 'simple_recipe_taxonomies', 0 );
function simple_recipe_taxonomies(){
	global $textdomain;
	$labels = array(
		'name'              => _x( 'Recipe Categories', 'taxonomy general name', $textdomain ),
		'singular_name'     => _x( 'Recipe Category', 'taxonomy singular name', $textdomain ),
		'search_items'      => __( 'Search Recipe Categories', $textdomain ),
		'all_items'         => __( 'All Recipe Categories', $textdomain ),
		'parent_item'       => __( 'Parent Recipe Category', $textdomain ),
		'parent_item_colon' => __( 'Parent Recipe Category:', $textdomain ),
		'edit_item'         => __( 'Edit Recipe Category', $textdomain ), 
		'update_item'       => __( 'Update Recipe Category', $textdomain ),
		'add_new_item'      => __( 'Add New Recipe Category', $textdomain ),
		'new_item_name'     => __( 'New Recipe Category', $textdomain ),
		'menu_name'         => __( 'Recipe Categories', $textdomain )
	);
	$args = array(
		'hierarchical' => true,
		'labels' => $labels
	);
	register_taxonomy( 'recipe-categories', 'recipes', $args );
}



// Meta Boxes
add_filter( 'cmb_meta_boxes', 'simple_recipe_metaboxes' );
function simple_recipe_metaboxes( $meta_boxes ) {
	global $textdomain;
	$prefix = 'simple-recipe-'; // Prefix for all fields
	$meta_boxes[] = array(
		'id' => 'simple_recipe_metabox',
		'title' => __( 'Recipe Details', $textdomain ),
		'pages' => array( 'recipes' ), // post type
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => __( 'Prep time', $textdomain ),
				'desc' => __( 'Prep time in minutes (optional)', $textdomain ),
				'id' => $prefix . 'ptime',
				'type' => 'text'
			),
			array(
				'name' => __( 'Cook time', $textdomain ),
				'desc' => __( 'Cook time in minutes (optional)', $textdomain ),
				'id' => $prefix . 'ctime',
				'type' => 'text'
			),
			array(
				'name' => __( 'Yield', $textdomain ),
				'desc' => __( '(optional)', $textdomain ),
				'id' => $prefix . 'yield',
				'type' => 'text'
			),
			array(
				'name' => __( 'Ingredients', $textdomain ),
				'desc' => __( 'Unordered list (ul)', $textdomain ),
				'id' => $prefix . 'ingredients',
				'type' => 'wysiwyg',
				'options' => array(
					'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags' => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
					'textarea_rows' => 15
				)
			),
			array(
				'name' => __( 'Instructions', $textdomain ),
				'desc' => __( 'Ordered list (ol)', $textdomain ),
				'id' => $prefix . 'instructions',
				'type' => 'wysiwyg',
				'options' => array(
					'wpautop' => true,
					'media_buttons' => false,
					'tinymce' => true,
					'quicktags' => true,
					'textarea_rows' => 15
				)
			),
		),
	);
	return $meta_boxes;
}

// Initialize the metabox class
add_action( 'init', 'initialize_cmb_meta_boxes', 9999 );
function initialize_cmb_meta_boxes() {
	if ( !class_exists( 'cmb_Meta_Box' ) ) {
		require_once( 'cmb/init.php' );
	}
}



// The Recipe Shortcode
add_shortcode( 'simple_recipe', 'simple_recipe_shortcode' );
function simple_recipe_shortcode( $atts ) {
	global $textdomain;
	extract( shortcode_atts( array( 'title' => '', 'rid' => null, 'show_thumb' => false ), $atts ) );
	
	global $post;
	
		
	$args = array(
		'post_type'		=>	'recipes',
		'post_status'	=>	'publish',
		'numberposts'	=>	1
	);
	if ( !empty($rid) ) :
		$args['p'] = $rid;
	else :
		$args['name'] = $title;
	endif;
	
	$recipes = new WP_Query( $args );
	
	$html = '';
	
	if ( $recipes->have_posts() ) : 
	
		while ( $recipes->have_posts() ) :

			$recipes->the_post();
		
			// Set up variables with content
			$pid = get_the_ID();
			$recipe_title = get_the_title();
			$recipe_thumb = wp_get_attachment_image_src( get_post_thumbnail_id($pid), 'medium' );
			$ingredients = get_post_meta( $pid, 'simple-recipe-ingredients', true );
			$ingredients = str_replace( array( '<li>', '</li>' ), array( '<li><span itemprop="ingredients">', '</span></li>' ), $ingredients);
			$instructions = get_post_meta( $pid, 'simple-recipe-instructions', true );
			$yield = get_post_meta( $pid, 'simple-recipe-yield', true );
			$ptime = get_post_meta( $pid, 'simple-recipe-ptime', true );
			$ctime = get_post_meta( $pid, 'simple-recipe-ctime', true );
			$notes = get_post_meta( $pid, 'simple-recipe-notes', true );
	 
			// Build markup
			$html  = '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
			$html .= '<div itemscope itemtype="http://schema.org/Recipe" class="simple-recipe row">';
			if ( !empty( $recipe_thumb ) ) {
				$html .= '<img itemprop="image" class="recipe-thumb" src="' . $recipe_thumb[0] . '" />';
			}
			$html .= '<meta itemprop="url" content="' . get_permalink($post->post_parent) . '" />';
			$html .= '<header><h2 itemprop="name" class="sr-title">' . $recipe_title . '</h2>';
			$html .= '<p itemprop="author" class="sr-author">By ' . get_bloginfo( 'name' ) . '</p>';
			$html .= '<span class="recipe-meta">';
			
			if ( !empty( $ptime ) ) {
				$html .= '<p class="recipe-meta-item sr-preptime">' . __( 'Prep Time:', $textdomain ) . ' <meta itemprop="prepTime" content="PT' . $ptime . 'M">' . $ptime . ' minutes</p>';
			}
			if ( !empty ( $ctime ) ) {
				$html .= '<p class="recipe-meta-item sr-cooktime">' . __( 'Cook Time:', $textdomain ) . ' <meta itemprop="cookTime" content="PT' . $ctime . 'M">' . $ctime . ' minutes</p>';
			}
			if ( !empty ( $yield ) ) {
				$html .= '<p class="recipe-meta-item sr-yield">' . __( 'Yield:', $textdomain ) . ' <span itemprop="recipeYield">' . $yield . '</span></p>';
			}
			
			$html .= '</span></header>';
			$html .= '<div class="sr-ingredients-wrap">';
			$html .= '<h3>' . __( 'Ingredients', $textdomain ) . '</h3>';
			$html .= '<div class="sr-ingredients">' . $ingredients . '</div>';
			$html .= '</div>';
			
			$html .= '<div class="sr-instructions-wrap">';
			$html .= '<h3>' . __( 'Instructions', $textdomain ) . '</h3>';
			$html .= '<div class="sr-instructions"><span itemprop="recipeInstructions">' . $instructions . '</span></div>';
			$html .= '</div>';
			
			if ( !empty ( $notes ) ) $html .= '<h3>' . __( 'Notes', $textdomain ) . '</h3><div class="sr-notes">' . $notes . '</div>';
			
			$html .= '</div><!-- end .simple-recipe -->';

		endwhile;
		
	else :
	
		return;
		
	endif;
	
	wp_reset_query();
	
	return $html;
}

/* schema todo
<div itemprop="nutrition"
	itemscope itemtype="http://schema.org/NutritionInformation">
	Nutrition facts:
	<span itemprop="calories">240 calories</span>,
	<span itemprop="fatContent">9 grams fat</span>
</div>

<span itemprop="description"></span>
*/


// Show the recipe on the single recipe page (e.g. site.com/recipes/recipename/)
// this is needed for the microdata URL meta tag in the recipe shortcode
add_filter( 'the_content', 'show_simple_recipe' );
function show_simple_recipe( $content ) { 

    if ( is_singular('recipes') ) {
        $content = do_shortcode( '[simple_recipe rid="' . get_the_id() . '"]' );
	}

    return $content;
}

// eof