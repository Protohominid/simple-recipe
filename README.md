simple-recipe
=============

##Simple Recipe plugin for Wordpress

Existing recipe plugins were overkill for what I needed, so I created this. It's meant more for developers than end users.

Goals:
-display the recipe with schema.org microdata
-ability to include multiple recipes in post/page
-control the location of the recipe within the content (which is why I'm using the shortcode method)
-keep things simple, with no added CSS or JS to the front end

The plugin adds a "Recipe" custom post type and the "simple_recipe" shortcode.

The shortcode only accepts one parameter right now, a recipe slug, like so:

    [simple_recipe title="coconut-cinnamon-crumble"]

The plugin outputs clean, unstlyed, semantic HTML so it is up to the dev to create the look. I've added classes where appropriate to hook into for CSS.

To do:
I'd like to create an easy way to insert the shortcode with the slug automatically. For now you have to know the recipe's slug and the shortcode format and type it in manually.
More recipe details would also be good, like nutrition info.
