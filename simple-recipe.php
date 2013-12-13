<?php 
/*
Plugin Name: Simple Recipe
Plugin URI: http://www.shawnbeelman.com
Description: Creates the "Recipe" post type and shortcode to insert into posts.
Author: Shawn Beelman
Version: 0.1
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
		'name' => 				__( 'Recipes', $textdomain ),
		'singular_name' =>		__( 'Recipe', $textdomain ),
		'add_new' =>			__( 'Add New', $textdomain ),
		'add_new_item' =>		__( 'Add New Recipe', $textdomain ),
		'edit_item' =>			__( 'Edit Recipe', $textdomain ),
		'new_item' =>			__( 'New Recipe', $textdomain ),
		'all_items' =>			__( 'All Recipes', $textdomain ),
		'view_item' =>			__( 'View Recipe', $textdomain ),
		'search_items' =>		__( 'Search Recipes', $textdomain ),
		'not_found' =>			__( 'No recipes found', $textdomain ),
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



/*
//Add Meta boxes 'manually'
add_action( 'add_meta_boxes', 'sr_meta_boxes' );
function sr_meta_boxes() {
    add_meta_box(
	    'simple_recipe_meta_box',
        __( 'Recipe Details', 'simple-recipe-textdomain' ),
        'display_simple_recipe_meta_box',
        'recipes', 'normal', 'high'
    );
}

function display_simple_recipe_meta_box( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'simple_recipe_nonce' );
	$simple_recipe_stored_meta = get_post_meta( $post->ID );
	?>
	
	<p>
		<label for="simple-recipe-ptime" class="simple-recipe-row-title">
			<?php _e( 'Prep Time', 'simple-recipe-textdomain' )?>
		</label><br>
		<input type="text" name="simple-recipe-ptime" id="simple-recipe-ptime" value="<?php if ( isset ( $simple_recipe_stored_meta['simple-recipe-ptime'] ) ) echo $simple_recipe_stored_meta['simple-recipe-ptime'][0]; ?>" size="3" maxlength="4" />
	</p>
	<p>
		<label for="simple-recipe-ctime" class="simple-recipe-row-title">
			<?php _e( 'Cook Time', 'simple-recipe-textdomain' )?>
		</label><br>
		<input type="text" name="simple-recipe-ctime" id="simple-recipe-ctime" value="<?php if ( isset ( $simple_recipe_stored_meta['simple-recipe-ctime'] ) ) echo $simple_recipe_stored_meta['simple-recipe-ctime'][0]; ?>" size="3" maxlength="4" />
	</p>
	<div class="customEditor">
		<label for="simple-recipe-ingredients" class="simple-recipe-row-title">
			<?php _e( 'Ingredients', 'simple-recipe-textdomain' )?>
		</label><br>
		<textarea name="simple-recipe-ingredients"><?php if ( isset ( $simple_recipe_stored_meta['simple-recipe-ingredients'] ) ) echo $simple_recipe_stored_meta['simple-recipe-ingredients'][0]; ?></textarea>
	</div>
	<?php	
}

function simple_recipe_meta_save( $post_id ) {
	
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'simple_recipe_nonce' ] ) && wp_verify_nonce( $_POST[ 'simple_recipe_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}
	
	// Checks for input and sanitizes/saves if needed
	if( isset( $_POST[ 'simple-recipe-ptime' ] ) ) {
		update_post_meta( $post_id, 'simple-recipe-ptime', sanitize_text_field( $_POST[ 'simple-recipe-ptime' ] ) );
	}
	if( isset( $_POST[ 'simple-recipe-ctime' ] ) ) {
		update_post_meta( $post_id, 'simple-recipe-ctime', sanitize_text_field( $_POST[ 'simple-recipe-ctime' ] ) );
	}
	if( isset( $_POST[ 'simple-recipe-ingredients' ] ) ) {
		update_post_meta( $post_id, 'simple-recipe-ingredients', sanitize_text_field( $_POST[ 'simple-recipe-ingredients' ] ) );
	}
	
}
add_action( 'save_post', 'simple_recipe_meta_save' );
*/



// The Recipe Shortcode
function simple_recipe_shortcode( $atts ) {
	global $textdomain;
	extract( shortcode_atts( array( 'title' => '' ), $atts ) );
	
	$args = array( 'post_type'=>'recipes', 'name'=>$title, 'post_status'=>'publish', 'numberposts'=>1 );
	$posts = new WP_Query( $args );
	
	if ( $posts->have_posts() ) : $posts->the_post();
		
		// Set up variables with content
		$pid = get_the_ID();
		$title = get_the_title();
		//$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($pid), 'thumbnail' );
		$ingredients = get_post_meta( $pid, 'simple-recipe-ingredients', true );
		$ingredients = str_replace( array( '<li>', '</li>' ), array( '<li><span itemprop="ingredients">', '</span></li>' ), $ingredients);
		$instructions = get_post_meta( $pid, 'simple-recipe-instructions', true );
		$yield = get_post_meta( $pid, 'simple-recipe-yield', true );
		$ptime = get_post_meta( $pid, 'simple-recipe-ptime', true );
		$ctime = get_post_meta( $pid, 'simple-recipe-ctime', true );
		$notes = get_post_meta( $pid, 'simple-recipe-notes', true );
 
		// Build markup
		$html  = '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
		$html .= '<div itemscope itemtype="http://schema.org/Recipe" class="simple-recipe recipe">';
		/*
		if ( '' != $thumb ) :
					$html .= '<img itemprop="image" src="' . $thumb[0] . '" />';
				endif;
		*/
		$html .= '<meta itemprop="url" content="' . get_permalink() . '" />';
		$html .= '<header><h2 itemprop="name" class="sr-title">' . $title . '</h2>';
		$html .= '<p itemprop="author" class="sr-author">By ' . get_bloginfo( 'name' ) . '</p>';
		$html .= '<span class="recipe-meta">';
		
		if ( '' != $ptime ) {
			$html .= '<p class="recipe-meta-item sr-preptime">' . __( 'Prep Time:', $textdomain ) . ' <meta itemprop="prepTime" content="PT' . $ptime . 'M">' . $ptime . ' minutes</p>';
		}
		if ( '' != $ctime ) {
			$html .= '<p class="recipe-meta-item sr-cooktime">' . __( 'Cook Time:', $textdomain ) . ' <meta itemprop="cookTime" content="PT' . $ctime . 'M">' . $ctime . ' minutes</p>';
		}
		if ( '' != $yield ) {
			$html .= '<p class="recipe-meta-item sr-yield">' . __( 'Yield:', $textdomain ) . ' <span itemprop="recipeYield">' . $yield . '</span></p>';
		}
		
		$html .= '</span></header>';
		$html .= '<h4>' . __( 'Ingredients', $textdomain ) . '</h4>';
		$html .= '<div class="sr-ingredients">' . $ingredients . '</div>';
		$html .= '<h4>' . __( 'Instructions', $textdomain ) . '</h4>';
		$html .= '<div class="sr-instructions"><span itemprop="recipeInstructions">' . $instructions . '</span></div>';
		
		if ( '' != $notes ) $html .= '<h4>' . __( 'Notes', $textdomain ) . '</h4><div class="sr-notes">' . $notes . '</div>';
		
		$html .= '</div><!-- end .recipe -->';
		
		return $html;

	endif;
	wp_reset_postdata();
}
add_shortcode( 'simple_recipe', 'simple_recipe_shortcode' );

/* schema todo
<div itemprop="nutrition"
	itemscope itemtype="http://schema.org/NutritionInformation">
	Nutrition facts:
	<span itemprop="calories">240 calories</span>,
	<span itemprop="fatContent">9 grams fat</span>
</div>

<span itemprop="description"></span>
*/

// eof