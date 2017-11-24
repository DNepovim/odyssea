<?php
/**
 * The sidebar containing the main widget area
 *
 * If no active widgets are in the sidebar, hide it completely.
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
 global $theme_sidebar;
?>
<?php if ( is_active_sidebar( $theme_sidebar ) ) : ?>
	<div id="secondary" class="widget-area" role="complementary">
		<?php dynamic_sidebar( $theme_sidebar ); ?>
	</div>
<?php endif; ?>