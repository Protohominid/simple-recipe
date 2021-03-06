<?php
/*
Plugin Name: Simple Recipe
Plugin URI: https://github.com/Protohominid/simple-recipe
Description: Creates the "Recipe" post type and shortcode to insert into posts.
Author: Shawn Beelman
Version: 0.8.1
Author URI: http://www.sbgraphicdesign.com
License: GPLv2
Text Domain: simple-recipe
*/
// text domain for I18n (should be same as plugin slug):

include_once( 'updater.php' );

if ( is_admin() ) {
	$config = array(
		'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
		'proper_folder_name' => 'simple-recipe', // this is the name of the folder your plugin lives in
		'api_url' => 'https://api.github.com/repos/Protohominid/simple-recipe', // the GitHub API url of your GitHub repo
		'raw_url' => 'https://raw.github.com/Protohominid/simple-recipe/master', // the GitHub raw url of your GitHub repo
		'github_url' => 'https://github.com/Protohominid/simple-recipe', // the GitHub url of your GitHub repo
		'zip_url' => 'https://github.com/Protohominid/simple-recipe/zipball/master', // the zip url of the GitHub repo
		'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
		'requires' => '3.0', // which version of WordPress does your plugin require?
		'tested' => '4.4', // which version of WordPress is your plugin tested up to?
		'readme' => 'README.md', // which file to use as the readme for the version number
		'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
	);
	new WP_GitHub_Updater( $config );
}

add_action( 'plugins_loaded', 'simple_recipe_setup' );
function simple_recipe_setup() {
	if ( post_type_exists( 'recipes' ) ) :
		add_action( 'admin_notices', 'same_post_type_warning' );
	else :
		add_action( 'init', 'create_simple_recipe_cpt' );
		add_action( 'init', 'simple_recipe_taxonomies', 0 );
		add_filter( 'post_updated_messages', 'sr_updated_messages' );
		add_filter( 'cmb_meta_boxes', 'simple_recipe_metaboxes' );
		add_action( 'init', 'initialize_cmb_meta_boxes', 9999 );
		add_shortcode( 'simple_recipe', 'simple_recipe_shortcode' );
		add_action( 'wp_enqueue_scripts', 'sr_enqueue_scripts' );
		add_filter( 'the_content', 'show_simple_recipe' );
		if( is_admin() ) {
			add_action( 'media_buttons', 'simple_recipe_media_button', 11 );
			add_action( 'admin_footer', 'simple_recipe_admin_footer_for_thickbox' );
		}
	endif;
}

//Register Recipe Custom Post Type
function same_post_type_warning() {
	echo '<div class="error">
	<p><strong>' . __( 'Warning', 'translation-domain' ) . ':</strong> ' . __( 'A post type with the name "recipes" has already been registered by another plugin or theme. This will most probably cause conflicts.', 'simple-recipe' ) . '</p>
	</div>';
}

function create_simple_recipe_cpt() {
	$labels = array(
		'name' => 				__( 'Recipes', 'simple-recipe' ),
		'singular_name' =>		__( 'Recipe', 'simple-recipe' ),
		'add_new' =>			__( 'Add New', 'simple-recipe' ),
		'add_new_item' =>		__( 'Add New Recipe', 'simple-recipe' ),
		'edit_item' =>			__( 'Edit Recipe', 'simple-recipe' ),
		'new_item' =>			__( 'New Recipe', 'simple-recipe' ),
		'all_items' =>			__( 'All Recipes', 'simple-recipe' ),
		'view_item' =>			__( 'View Recipe', 'simple-recipe' ),
		'search_items' =>		__( 'Search Recipes', 'simple-recipe' ),
		'not_found' =>			__( 'No recipes found', 'simple-recipe' ),
		'not_found_in_trash' => __( 'No recipes found in the Trash', 'simple-recipe' ),
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
	global $post, $post_ID;
	$messages['recipes'] = array(
		0 => '',
		1 => sprintf( __( 'Recipe updated. <a href="%s">View recipe</a>', 'simple-recipe' ), esc_url( get_permalink($post_ID) ) ),
		2 => __( 'Custom field updated.', 'simple-recipe' ),
		3 => __( 'Custom field deleted.', 'simple-recipe' ),
		4 => __( 'Recipe updated.', 'simple-recipe' ),
		5 => isset($_GET['revision']) ? sprintf( __( 'Recipe restored to revision from %s', 'simple-recipe' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Recipe published. <a href="%s">View recipe</a>', 'simple-recipe' ), esc_url( get_permalink($post_ID) ) ),
		7 => __( 'Recipe saved.', 'simple-recipe' ),
		8 => sprintf( __( 'Recipe submitted. <a target="_blank" href="%s">Preview recipe</a>', 'simple-recipe' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __( 'Recipe scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview recipe</a>', 'simple-recipe' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __( 'Recipe draft updated. <a target="_blank" href="%s">Preview recipe</a>', 'simple-recipe' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}

//Add Recipe Categories
function simple_recipe_taxonomies(){
	$labels = array(
		'name'              => _x( 'Recipe Categories', 'taxonomy general name', 'simple-recipe' ),
		'singular_name'     => _x( 'Recipe Category', 'taxonomy singular name', 'simple-recipe' ),
		'search_items'      => __( 'Search Recipe Categories', 'simple-recipe' ),
		'all_items'         => __( 'All Recipe Categories', 'simple-recipe' ),
		'parent_item'       => __( 'Parent Recipe Category', 'simple-recipe' ),
		'parent_item_colon' => __( 'Parent Recipe Category:', 'simple-recipe' ),
		'edit_item'         => __( 'Edit Recipe Category', 'simple-recipe' ),
		'update_item'       => __( 'Update Recipe Category', 'simple-recipe' ),
		'add_new_item'      => __( 'Add New Recipe Category', 'simple-recipe' ),
		'new_item_name'     => __( 'New Recipe Category', 'simple-recipe' ),
		'menu_name'         => __( 'Recipe Categories', 'simple-recipe' )
	);
	$args = array(
		'hierarchical' => true,
		'labels' => $labels
	);
	register_taxonomy( 'recipe-categories', 'recipes', $args );
}

// Meta Boxes
function simple_recipe_metaboxes( $meta_boxes ) {
	$prefix = 'simple-recipe-'; // Prefix for all fields
	$meta_boxes[] = array(
		'id' => 'simple_recipe_metabox',
		'title' => __( 'Recipe Details', 'simple-recipe' ),
		'pages' => array( 'recipes' ), // post type
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => __( 'Prep time', 'simple-recipe' ),
				'desc' => __( 'Prep time in minutes (optional)', 'simple-recipe' ),
				'id' => $prefix . 'ptime',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Cook time', 'simple-recipe' ),
				'desc' => __( 'Cook time in minutes (optional)', 'simple-recipe' ),
				'id' => $prefix . 'ctime',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Yield', 'simple-recipe' ),
				'desc' => __( '(optional)', 'simple-recipe' ),
				'id' => $prefix . 'yield',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Ingredients', 'simple-recipe' ),
				'desc' => __( 'Unordered list (ul)', 'simple-recipe' ),
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
				'name' => __( 'Instructions', 'simple-recipe' ),
				'desc' => __( 'Ordered list (ol)', 'simple-recipe' ),
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
			array(
				'name' => __( 'Notes', 'simple-recipe' ),
				'desc' => __( '(optional)', 'simple-recipe' ),
				'id' => $prefix . 'notes',
				'type' => 'textarea'
			),
			array(
				'name' => __( 'Serving Size', 'simple-recipe' ),
				'desc' => __( '(optional)', 'simple-recipe' ),
				'id' => $prefix . 'servsize',
				'type' => 'text'
			),
			array(
				'name' => __( 'Calories', 'simple-recipe' ),
				'desc' => __( '(optional)', 'simple-recipe' ),
				'id' => $prefix . 'calories',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Total Fat', 'simple-recipe' ),
				'desc' => __( 'in grams (optional)', 'simple-recipe' ),
				'id' => $prefix . 'fat',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Saturated Fat', 'simple-recipe' ),
				'desc' => __( 'in grams (optional)', 'simple-recipe' ),
				'id' => $prefix . 'satfat',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Carbohydrates', 'simple-recipe' ),
				'desc' => __( 'in grams (optional)', 'simple-recipe' ),
				'id' => $prefix . 'carbs',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Fiber', 'simple-recipe' ),
				'desc' => __( 'in grams (optional)', 'simple-recipe' ),
				'id' => $prefix . 'fiber',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Sodium', 'simple-recipe' ),
				'desc' => __( 'in milligrams (optional)', 'simple-recipe' ),
				'id' => $prefix . 'sodium',
				'type' => 'text_small'
			),
			array(
				'name' => __( 'Protein', 'simple-recipe' ),
				'desc' => __( 'in grams (optional)', 'simple-recipe' ),
				'id' => $prefix . 'protein',
				'type' => 'text_small'
			),

		),
	);
	return $meta_boxes;
}

// Initialize the metabox class
function initialize_cmb_meta_boxes() {
	if ( !class_exists( 'cmb_Meta_Box' ) ) {
		require_once( 'cmb/init.php' );
	}
}

// The Recipe Shortcode
function simple_recipe_shortcode( $atts ) {
	extract( shortcode_atts( array( 'title' => '', 'id' => null, 'show_thumb' => false ), $atts ) );

	global $post;

	$args = array(
		'post_type'		=>	'recipes',
		'post_status'	=>	'publish',
		'numberposts'	=>	1
	);
	if ( !empty($id) ) :
		$args['p'] = $id;
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

			$meta_list = array(	'ingredients', 'instructions', 'yield', 'ptime', 'ctime', 'notes', 'servsize', 'calories', 'fat', 'satfat', 'carbs', 'fiber', 'sodium', 'protein' );
			// loop through and create variables for each value
			foreach( $meta_list as $value ) {
				$$value = get_post_meta( $pid, 'simple-recipe-' . $value, true );
			}
			$ingredients = str_replace( array( '<li>', '</li>' ), array( '<li><span itemprop="ingredients">', '</span></li>' ), $ingredients);


			// Build markup
			$html  = '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
			$html .= '<div itemscope itemtype="http://schema.org/Recipe" class="simple-recipe">';
			if ( !empty( $recipe_thumb ) ) {
				$html .= '<img itemprop="image" class="recipe-thumb" src="' . $recipe_thumb[0] . '" alt="' . $recipe_title . '" />';
			}
			$html .= '<meta itemprop="url" content="' . get_permalink($post->post_parent) . '" />';
			$html .= '<header class="row"><h2 itemprop="name" class="sr-title">' . $recipe_title . '</h2>';
			$html .= '<p itemprop="author" class="sr-author">By ' . get_bloginfo( 'name' ) . '</p>';
			$html .= '<div class="recipe-meta">';

			if ( !empty( $ptime ) ) {
				$html .= '<p class="recipe-meta-item sr-preptime">' . __( 'Prep Time:', 'simple-recipe' ) . ' <meta itemprop="prepTime" content="PT' . $ptime . 'M">' . $ptime . ' minutes</p>';
			}
			if ( !empty ( $ctime ) ) {
				$html .= '<p class="recipe-meta-item sr-cooktime">' . __( 'Cook Time:', 'simple-recipe' ) . ' <meta itemprop="cookTime" content="PT' . $ctime . 'M">' . $ctime . ' minutes</p>';
			}
			if ( !empty ( $yield ) ) {
				$html .= '<p class="recipe-meta-item sr-yield">' . __( 'Yield:', 'simple-recipe' ) . ' <span itemprop="recipeYield">' . $yield . '</span></p>';
			}

			$html .= '<button class="sr-print-recipe"><span>Print</span></button>';
			$html .= '</div></header>';
			$html .= '<div class="sr-content row">';
			$html .= '<div class="sr-ingredients-wrap">';
			$html .= '<h3>' . __( 'Ingredients', 'simple-recipe' ) . '</h3>';
			$html .= '<div class="sr-ingredients">' . $ingredients . '</div>';
			$html .= '</div>';

			$html .= '<div class="sr-instructions-wrap">';
			$html .= '<h3>' . __( 'Instructions', 'simple-recipe' ) . '</h3>';
			$html .= '<div class="sr-instructions"><span itemprop="recipeInstructions">' . $instructions . '</span></div>';
			$html .= '</div></div>';

			if ( !empty ( $notes ) ) $html .= '<h3>' . __( 'Notes', 'simple-recipe' ) . '</h3><div class="sr-notes">' . $notes . '</div>';
			if ( $servsize != '' || $calories != '' || $fat != '' || $satfat !='' || $fiber != '' || $sodium !='' ) {
				$html .= '<h3>' . __( 'Nutrition Information', 'simple-recipe' ) . '</h3><div class="sr-nutrition-info" itemprop="nutrition" itemscope itemtype="http://schema.org/NutritionInformation">';
				if ( !empty( $servsize ) ) $html .= 'Serving Size: <span itemprop="servingSize">' . $servsize . '</span> ';
				if ( !empty( $calories ) ) $html .= 'Calories: <span itemprop="calories">' . $calories . '</span> ';
				if ( !empty( $fat ) ) $html .= 'Fat: <span itemprop="fatContent">' . $fat . ' g</span> ';
				if ( !empty( $satfat ) ) $html .= 'Saturated Fat: <span itemprop="saturatedFatContent">' . $satfat . ' g</span> ';
				if ( !empty( $carbs ) ) $html .= 'Carbohydrates: <span itemprop="carbohydrateContent">' . $carbs . ' g</span> ';
				if ( !empty( $fiber ) ) $html .= 'Fiber: <span itemprop="fiberContent">' . $fiber . ' g</span> ';
				if ( !empty( $sodium ) ) $html .= 'Sodium: <span itemprop="sodiumContent">' . $sodium . ' mg</span> ';
				if ( !empty( $protein ) ) $html .= 'Protein: <span itemprop="proteinContent">' . $protein . ' g</span> ';
				$html .= '</div>';
			}

			$html .= '</div><!-- end .simple-recipe -->';

		endwhile;

	else :

		return;

	endif;

	wp_reset_query();

	return $html;
}

/* schema todo
<span itemprop="description"></span>
*/

function sr_enqueue_scripts() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'simple_recipe') ) {
		wp_register_script( 'simple-recipe', WP_PLUGIN_URL . '/simple-recipe/simple-recipe-min.js', array ('jquery'), '', true );
		wp_enqueue_script( 'simple-recipe' );
	}
}

// Show the recipe on the single recipe page (e.g. site.com/recipes/recipename/)
// this is needed for the microdata URL meta tag in the recipe shortcode
function show_simple_recipe( $content ) {

    if ( is_singular('recipes') ) {
        $content = do_shortcode( '[simple_recipe rid="' . get_the_id() . '"]' );
	}

    return $content;
}

// MEDIA BUTTON
function simple_recipe_media_button()
{
	global $pagenow, $typenow, $wp_version;
	$output = '';
	if ( version_compare( $wp_version, '3.5', '>=' ) AND in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'recipes' )
	{
		#$img = '<style>#simple-recipe-media-button::before { font: 400 18px/1 dashicons; content: \'\f180\'; }</style><span class="wp-media-buttons-icon" id="simple-recipe-media-button"></span>';
		$output = '<a href="#TB_inline?width=640&inlineId=add-simple-recipe" class="thickbox button simple-recipe-thickbox" title="' .  __( 'Add Recipe', 'simple-recipe'  ) . '" style="padding-left: .4em;"> ' . __( 'Add Recipe', 'simple-recipe'  ) . '</a>';
	}
	echo $output;
}

// MEDIA BUTTON FUNCTIONALITY
function simple_recipe_admin_footer_for_thickbox() {
	global $pagenow, $typenow, $wp_version;

	// Only run in post/page creation and edit screens
	if ( version_compare( $wp_version, '3.5', '>=' ) AND in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'recipes' ) { ?>

		<script type="text/javascript">
            function insertReusableTextBlock()
            {
            	var id = jQuery('#simple-recipe-select-box').val();
                if ('' === id)
                {
                    alert('<?php _e( "You must choose a recipe", "simple-recipe" ); ?>');
                    return;
                }


                var slug = jQuery('#simple-recipe-select-box option:selected').data('slug');

                window.send_to_editor('[simple_recipe id="' + id + '" title="' + slug + '"]');
            }
		</script>

		<div id="add-simple-recipe" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<?php

				$blocks = get_posts( array( 'post_type' => 'recipes', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );

				if( $blocks ) { ?>
					<select id="simple-recipe-select-box" style="clear: both; display: block; margin-bottom: 1em;">
						<option value=""><?php _e('Choose a Recipe', 'simple-recipe'); ?></option>
						<?php
							foreach ( $blocks as $block )
							{
								echo '<option value="' . $block->ID . '" data-slug="' . $block->post_name . '">' . $block->post_title . '</option>';
							}
						?>
					</select>
				<?php } else { echo __('No recipes have been created yet. Please create one first and then you will be able to select it here.', 'simple-recipe'); } ?>

				<p class="submit">
					<input type="button" id="simple-recipe-insert-download" class="button-primary" value="<?php echo __( 'Insert Recipe', 'simple-recipe' ); ?>" onclick="insertReusableTextBlock();" />
					<a id="simple-recipe-cancel-add" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Cancel', 'simple-recipe' ); ?>"><?php _e( 'Cancel', 'simple-recipe' ); ?></a>
				</p>
			</div>
		</div>
	<?php
	}
}

// eof