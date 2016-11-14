<?php
if (__FILE__ == basename($_SERVER['SCRIPT_FILENAME']))
	die ("Prosím, to mi nedělej.");

/*
 In order to support older versions of WP the following functions will duplicate
 some of the newer WP function. Commenting works as expected in older versions
 but if you want/need support for the newer capabilities that WP offers then
 you'll need to upgrade to the latest version.
*/

/*
 @abstract Quick check to see if the post is password protected. For <= WP26.
 @return bool
*/
if (!function_exists('post_password_required')) {
	function post_password_required(){
		return !empty($post->post_password) && $_COOKIE['wp-postpass_'.COOKIEHASH] != $post->post_password;
	}
}

/*
 @abstract Assembles the log out URL for WP26 and older.
 @param $redirect A URL to redirect to after log out has completed.
 @return string Link to logout URL with an appropriate redirect parameter.
*/
if (!function_exists('wp_logout_url')) { // For <= WP26
	function wp_logout_url($redirect = ''){
		$redirect =  strlen($redirect) ? "&redirect_to=$redirect" : 'redirect_to='.urlencode(get_permalink());
		return get_option('siteurl')."/wp-login.php?action=logout$redirect";
	}
}

/*
 @abstract Simple check to see if there are comments or not. Needed for <= WP21
 @return bool
*/
if (!function_exists('have_comments')) {
	function have_comments(){
		return (get_comments_number() > 0 ? true : false);
	}
}

/*
 @abstract Quick interpretation of the WP27 function comment_class for <= WP26
 @param $class array of strings to be added to the returned class
 @param $ignored As the name implies this param is ignored
 @param $echo bool Choose to echo or return
 @return string standard html class attribute
*/
if (!function_exists('comment_class')) { //
	function comment_class($class = array(), $ignored = null, $ignored = null, $echo = true ){
		global $comment,$comment_count,$post;
		$comment_count ++;

		// Set up the class for this comment.
		$class[] = get_comment_type();
		$class[] = 'depth-1';
		$class[] = $comment->comment_approved == 0 ? 'unapproved' : 'approved';
		$class[] = $comment_count % 2 ? 'odd' : 'even';

		if ($comment_count == 1)
			$class[] = 'first';

		if ($comment->user_id == $post->post_author)
			$class[] = 'bypostauthor';

		if (is_array($class) && count($class) > 0)
			$commentClass = ' class="'.implode(' ',$class).'"';
		else
			unset ($commentClass);

		if ($echo)
			echo $commentClass;
		else
			return $commentClass;
	}
}

/*
 @abstract: Comment layout function used by WP27 walker .
 @return null
*/

if (!function_exists('comment_layout')) {
	function comment_layout($comment,$args = array(),$depth = null){
		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);

		if ( 'div' == $style ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}

		echo "<$tag id=\"comment-".get_comment_ID().'" ' . comment_class(empty($has_children) ? '' : 'parent',get_comment_ID(),null,false).'>';?>
		<div class="comment-body">
			<div id="div-comment-<?php comment_ID() ?>">
				<div class="comment-author vcard">
					<?php echo function_exists('get_avatar') && $avatar_size != 0 ? get_avatar( $comment, $avatar_size ) : ''; ?>
					<?php printf(__('<cite class="fn">%s</cite>:'), get_comment_author_link()) ?>
				</div>

				<?php comment_text();?>

				<div class="comment-meta commentmetadata">
					<?php function_exists('comment_reply_link') ? comment_reply_link(array_merge($args,array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $max_depth))) : '';?>
					<?php //comment_type(__('comment'),__('trackback'),__('trackback')) ?>
					<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a>
					<?php $comment->comment_approved == 0 ? printf('<em>|&nbsp;%s</em>',__('Moderovat')) : ''; ?>
					<?php edit_comment_link(__('Edituj'),'|&nbsp;','') ?>
				</div>
			</div>
		</div>
		<?php
	}
}

/*
 If we have no comments and comments are closed we drop out of here without
 doing anything at all, no point telling the user that something isn't available.
*/

if ((comments_open() || get_comments_number() > 0) && (is_single() || is_page()) && !post_password_required()) {?>
	<div id="comments" class="with-collapse">
		<?php
		if(comments_open()) {?>

			<div id="respond">
				<div id="newCommentTitle">
				<?php
				if (function_exists('comment_form_title')) {
					comment_form_title(__('Vyrýt svůj vzkaz na hlavní stožár:'),__('Zanechat odpověď pro %s'),false);
				} else {
					_e('Vyrýt svůj vzkaz na hlavní stožár:');
				}?>
				</div>
			<?php
			if (get_option('comment_registration') && !$user_ID ) {?>
				<a href="<?php echo get_option('siteurl')?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink())?>"><?php _e('Pro přidání komentáře je potřeba se přihlásit.')?></a><?php
			} else {?>

				<form action="<?php echo get_option('siteurl')?>/wp-comments-post.php" method="post" id="commentForm">
				<fieldset><?php

				if ($user_ID) { ?>
					<?php _e('Na palubě jsi známý jako námořník')?> <a href="<?php echo get_option('siteurl')?>/wp-admin/profile.php"><?php echo $user_identity?></a>.
					<a href="<?php echo wp_logout_url($_SERVER['REQUEST_URI']);?>" title="<?php _e('Skočit přes palubu?') ?>"><?php _e('Máš toho dost? Chceš snad skočit přes palubu?')?></a>
					<?php
				} else { ?>
					<div>
						<input type="text" name="author" id="author" value="<?php echo $comment_author?>" size="30" tabindex="1"<?php echo ($req ? ' class="vital"' : '')?>/>
						<label for="author">
							<small><?php _e('Jméno')?> <?php if ($req) _e('(povinné)')?></small>
						</label>
					</div>
					<div>
						<input type="text" name="email" id="email" value="<?php echo $comment_author_email?>" size="30" tabindex="2"<?php echo ($req ? ' class="vital"' : '')?>/>
						<label for="email">
							<small><?php _e('Mail (nebude zveřejněn)')?> <?php if ($req) _e('(povinný)')?></small>
						</label>
					</div>
					<div>
						<input type="text" name="url" id="url" value="<?php echo $comment_author_url?>" size="30" tabindex="3" />
						<label for="url">
							<small><?php _e('Webovky')?> </small>
						</label>
					</div><?php
				}?>

				<textarea name="comment" id="comment" cols="56" rows="10" tabindex="4" class="vital"></textarea>

				<div class="commentSubmit">
					<?php if(function_exists('cancel_comment_reply_link')) cancel_comment_reply_link();?>
					<input name="submit" type="submit" tabindex="5" value="<?php _e('Odeslat Tvůj příspěvek')?>" class="submit" />
				</div>

				<input type="hidden" name="comment_post_ID" value="<?php echo $id?>" /><?php
				if (function_exists('comment_id_fields')) {
					comment_id_fields();
				}
				do_action('comment_form', $post->ID)?>
				</fieldset>
				</form><?php
			}?>
			</div><?php
		}
		
		if (have_comments()) {	// New >= 27 comments.
			if (function_exists('wp_list_comments')){
				if ($comments_by_type['pingback']||$comments_by_type['trackback']) {?>
					<strong class="commentTitle"><?php _e('Trackbacks')?></strong>
					<ul id="trackbackList">
						<?php wp_list_comments(array('max_depth' => 0,type => 'pings'));?>
					</ul>
					<?php
				}?>

				<strong class="commentTitle"><?php _e('Co napsali ostatní:')?></strong>
				<ul id="commentlist">
					<?php wp_list_comments(array('max_depth'=> 10,'type' => 'comment','callback' => 'comment_layout'));?>
				</ul>
				<div id="commentPagination"><?php paginate_comments_links(array('next_text'=> '&raquo;', 'prev_text' => '&laquo;'));?></div>

				<?php
			} else { // Cover WP all the way back to 2.1 with this.?>
				<strong class="commentTitle"><?php _e('Co napsali ostatní:')?></strong>
				<ul id="commentlist"><?php
				foreach ($comments as $count => $comment) {
					$args = array('avatar_size' => 32,'tag' => 'li');
					comment_layout($comment,$args,$depth);
				}?>
				</ul><?php
			}
		}

		

	?>
	</div><?php
}?>
