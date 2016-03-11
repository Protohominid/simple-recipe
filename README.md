##Simple Recipe plugin for Wordpress

Existing recipe plugins were overkill for what I needed, so I created this. It's meant more for theme developers than end users, because it doesn't include any front-end CSS.

Goals:

- Display the recipe with schema.org microdata
- Ability to include multiple recipes in post/page
- Control the location of the recipe within the content (which is why I'm using the shortcode method)
- Keep things simple, with no added CSS or JS to the front end

The plugin adds a "Recipe" custom post type and the "simple_recipe" shortcode.

The plugin outputs clean, unstyled, semantic HTML so it is up to the theme to create the look. I've added classes where appropriate to hook into for CSS.

###Shortcode usage notes

The shortcode has one required ('title') and one optional ('show_thumb', default is false) parameter right now, like so:

    [simple_recipe title="coconut-cinnamon-crumble" show_thumb=true]

###Recipe CPT notes

Recipe ingredients need to be entered as an unordered list.

Recipe instructions should be entered as an ordered list.

####To do:

- I'd like to create an easy way to insert the shortcode with the slug automatically. For now you have to know the recipe's slug and the shortcode format and type it in manually
- Field validation for inputs like "Prep Time"; these must be numeric input

Thanks to jaredatch for the [Custom Metaboxes code](https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress).
