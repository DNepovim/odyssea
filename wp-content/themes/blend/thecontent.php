


<?php
/* I use this a my basis for the content body in pages, post, archives, search results, everywhere basically. Saves me from having to make the same change several times when I need to update something, basically I'm lazy. :-P */
if ("thecontent.php" == basename($_SERVER["SCRIPT_FILENAME"])) die ("Tohle mi nedělej...");
$title = get_the_title();
/* If there is no title I miss out most of the header elements. Allows for the posting of images without any of the extra fluff getting in the way.
Title will have page number appended to the end when the page is greater than page 1. The title will be a link except in situations where that 
page is the one loaded, no point going in a loop and confusing people.
*/
if ($title && !(get_post_meta($post->ID,"hide-title",true) == 1 && is_page())) {?>
	<div class="post-header"><?php 
	if (is_single() || is_page()) { 
		echo ($page >= 2 ? "<div class=\"post-title\"><h2>$title Stránka $page</h2></div>" : "<div class=\"post-title\"><h2>$title</h2></div>");
	} else {
		$link = get_permalink();
		echo "<div class=\"post-title\"><h2><a href=\"$link\" rel=\"bookmark\">$title</a></h2></div>";
	}
	if ($post->post_type == "post") { ?>
		<div class="post-author">
		<span class="post-cetegory">
			<?php _e("Příspěvek patří do kategorií ");the_category(", ");?>
		</span><?php
		if(get_post_meta($post->ID,"hide-author",true) != 1) {
			_e(" od ");?><a href="<?php echo get_author_posts_url(get_the_author_ID()); ?>"><?php the_author();?></a><?php
		}?>
		</div>
		<?php
		if(get_post_meta($post->ID,"hide-date",true) != 1) {?>
		<div class="post-date">
			<span class="post-month"><?php the_time("M");?></span>
			<span class="post-day"><?php the_time("d");?></span>
			<span class="post-year"><?php the_time("Y");?></span>
		</div><?php
		}
	}?>
	<?php if (pings_open() && !is_attachment() && !is_page()) {?><div class="trackback-url"><a href="<?php trackback_url() ?>" rel="trackback"><?php _e("TrackBack adresy.");?></a></div><?php } /*Show the trackback link for Pages and posts. */ ?>
	</div><?php
} ?>





<div class="post-body"><?php
	/* The following reads the attachments mime type and either shows an image centred or launches a flash mp3 player if the file type is mpeg audio. */
	if (is_attachment() && wp_attachment_is_image($post->ID)) {
		/* Images should be aligned to the center of the page.*/
		$attachmentData = wp_get_attachment_metadata($post->ID);
		$attachmentURL = wp_get_attachment_url($post->ID);
		if ((int) $attachmentData["width"] > 600) {
			$newHeight = (int) $attachmentData["height"] / ((int) $attachmentData["width"] / 600);
			$scalled = " width=\"600\" height=\"$newHeight\"";
		}
		?>
		<div style="text-align:center;margin-bottom:3em;padding: 10px; border: solid 1px #eee;">
			<img style="margin:0;" src="<?php echo $attachmentURL;?>" alt="<?php echo attribute_escape(get_the_title());?>"<?php echo $scalled;?>/>
			<?php
			the_excerpt();
			if ($scalled != "")
				echo "<small>Obrázek je upravený tak, aby se vešel kam má. Klikni<a href=\"$attachmentURL\">sem</a> a uvidíš ho v celé své kráse.</small>";?>
		</div><?php

		if (function_exists("previous_image_link")) {?>
			<div class="previous-image"><?php
			previous_image_link();?>
			</div><?php
		}

		if (function_exists("next_image_link")) {?>
			<div class="next-image"><?php
			next_image_link();?>
			</div><?php
		}?>
		<br style="clear:both;"/>
		<br/>
		<?php previous_post_link(__("Soubor připojený k")." %link","%title",false);
	} else {
		the_content(); /* Finally the content */
		edit_post_link(__("Upravit tohle."),"<div class=\"edit-post\">","</div>"); /* I put this here as it is only admins who will see it and I can put it in the header as you have the option of hiding the header. */
	}?>
	<div class="clear"></div>
</div>
<div class="post-footer"><?php
	global $multipage;
	/* The following allows me to avoid me cluttering up the place with unneeded tag and allows me to style things up as I see fit.  Can't pass quotes inside wp_link_pages so have to do it this way. */
	if ($multipage) { ?>
		<div class="page-links"><strong><?php _e("Vyber stránku");?>:&nbsp;</strong><?php
			wp_link_pages("before=&after=&pagelink=<span>%</span>");?>
		</div><?php
	}
	/* Show the link to the comments if commenting is allowed otherwise show nothing, no point telling people what is not allowed. */
	if (comments_open() && !(is_single() || is_page())) {
		echo "<div class=\"comment-pop-link\">"; 
		comments_popup_link(__("Zatím žádné komentáře.")." &#187;", __("1 komentář")." &#187;", __("% komentářů &#187;"));
		echo "</div>";
	}
	/* Make sure tagging is available before we go on. */
	if (function_exists("get_the_tags")) { 
	$posttags = get_the_tags();
		if ($posttags) { /* Now that we know tagging is availavle lets see if there are any tags, only if there are will we add the needed mark up. */?>
			<div class="tag-cloud-links">
				<strong><?php _e("Označeno jako: ");?></strong><?php 
				$x = 1; // Put a comma at the end of each tag if it is not the last.
				foreach($posttags as $tag) {
					echo "<a href=\"".get_tag_link($tag->term_id)."\">{$tag->name}</a>".(count($posttags) != $x ? ", " : "");
					$x++;
				}?>
			</div><?php
		}
	}?>
</div>