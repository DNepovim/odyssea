<form method="get" action="<?php bloginfo('url'); ?>/" class="search-form">
	<input type="text" class="input-text search_input" value="<?php the_search_query(); ?>" name="s" />
	<input type="submit" class="submit" value="<?php _e("Vyhledat")?>" />
</form>
