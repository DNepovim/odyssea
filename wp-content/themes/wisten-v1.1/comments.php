<?php
/**
 * The template for displaying Comments
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to wisten_comment() which is
 * located in the functions.php file.
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() )
	return;
?>

<div id="comments" class="comments-area">

	<?php // You can start editing here -- including this comment! ?>

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
				printf( 'COMMENTS (%s)', get_comments_number());
					//number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
			?>
		</h2>

		<ol class="commentlist">
			<?php wp_list_comments( array( 'callback' => 'fastwp_comment', 'style' => 'ol' ) ); ?>
		</ol><!-- .commentlist -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
		<nav id="comment-nav-below" class="navigation" role="navigation">
			<h1 class="assistive-text section-heading"><?php _e( 'Comment navigation', 'wisten' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments' ,'fastwp' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;' ,'fastwp' ) ); ?></div>
		</nav>
		<?php endif; // check for comment navigation ?>

		<?php
		/* If there are no comments and comments are closed, let's leave a note.
		 * But we only want the note on posts and pages that had comments in the first place.
		 */
		if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="nocomments"><?php _e( 'Comments are closed.' ,'fastwp' ); ?></p>
		<?php endif; ?>

	<?php endif; // have_comments() ?>

<?php 
$aria_req = ' aria-required="true"';
$commenter = wp_get_current_commenter();
$req = get_option('require_name_email');
$args_of_comment = array(
  'id_form'           => 'commentform',
  'id_submit'         => 'submit',
  'title_reply'       => __( 'Leave a Comment' ,'fastwp'),
  'title_reply_to'    => __( 'Leave a Comment to %s' ,'fastwp'),
  'cancel_reply_link' => __( 'Cancel Reply' ,'fastwp'),
  'label_submit'      => __( 'Post Comment' ,'fastwp'),

  'comment_field' =>  '<p class="comment-form-comment"><label for="comment">' . __( 'Comment', 'fastwp' ) .
    '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="' . __( 'Comment', 'fastwp' ) .'">' .
    '</textarea></p>',

  'must_log_in' => '<p class="must-log-in">' .
    sprintf(
      __( 'You must be <a href="%s">logged in</a> to post a comment.' ),
      wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
    ) . '</p>',

  'logged_in_as' => '<p class="logged-in-as">' .
    sprintf(
    __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ),
      admin_url( 'profile.php' ),
      $user_identity,
      wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) )
    ) . '</p>',

  'comment_notes_before' => '<p class="comment-notes">' .
    __( 'Your email address will not be published.' ,'fastwp') . 
    '</p>',

  'comment_notes_after' => '',

  'fields' => apply_filters( 'comment_form_default_fields', array(

    'author' =>
      '<p class="comment-form-author col-xs-4">' .
      '<label for="author">' . __( 'Name', 'fastwp' ) . '</label> ' .
      ( $req ? '<span class="required">*</span>' : '' ) .
      '<input id="author" name="author" type="text" placeholder="' . __( 'Name', 'fastwp' ) . '" value="' . esc_attr( $commenter['comment_author'] ) .
      '" size="30"' . $aria_req . ' /></p>',

    'email' =>
      '<p class="comment-form-email col-xs-4"><label for="email">' . __( 'Email', 'fastwp' ) . '</label> ' .
      ( $req ? '<span class="required">*</span>' : '' ) .
      '<input id="email" name="email" type="text" placeholder="' . __( 'Email', 'fastwp' ) . '" value="' . esc_attr(  $commenter['comment_author_email'] ) .
      '" size="30"' . $aria_req . ' /></p>',

    'url' =>
      '<p class="comment-form-url col-xs-4"><label for="url">' .
      __( 'Website', 'fastwp' ) . '</label>' .
      '<input id="url" name="url" type="text" placeholder="' . __( 'Website', 'fastwp' ) . '" value="' . esc_attr( $commenter['comment_author_url'] ) .
      '" size="30" /></p>'
    )
  ),
);

	
	
	comment_form( $args_of_comment ); ?>

</div><!-- #comments .comments-area -->