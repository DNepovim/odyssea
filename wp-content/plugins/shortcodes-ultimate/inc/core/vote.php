<?php

class Su_Vote {

	function __construct() {
		add_action( 'load-plugins.php', array( __CLASS__, 'init' ) );
		add_action( 'wp_ajax_su_vote',  array( __CLASS__, 'vote' ) );
	}

	public static function init() {
		Shortcodes_Ultimate::timestamp();
		$vote = get_option( 'su_vote' );
		$timeout = time() > ( get_option( 'su_installed' ) + 60*60*24*3 );
		if ( in_array( $vote, array( 'yes', 'no', 'tweet' ) ) || !$timeout ) return;
		add_action( 'in_admin_footer', array( __CLASS__, 'message' ) );
		add_action( 'admin_head',      array( __CLASS__, 'register' ) );
		add_action( 'admin_footer',    array( __CLASS__, 'enqueue' ) );
	}

	public static function register() {
		wp_register_style( 'su-vote', plugins_url( 'assets/css/vote.css', SU_PLUGIN_FILE ), false, SU_PLUGIN_VERSION, 'all' );
		wp_register_script( 'su-vote', plugins_url( 'assets/js/vote.js', SU_PLUGIN_FILE ), array( 'jquery' ), SU_PLUGIN_VERSION, true );
	}

	public static function enqueue() {
		wp_enqueue_style( 'su-vote' );
		wp_enqueue_script( 'su-vote' );
	}

	public static function vote() {
		$vote = sanitize_key( $_GET['vote'] );
		if ( !is_user_logged_in() || !in_array( $vote, array( 'yes', 'no', 'later', 'tweet' ) ) ) die( 'error' );
		update_option( 'su_vote', $vote );
		if ( $vote === 'later' ) update_option( 'su_installed', time() );
		die( 'OK: ' . $vote );
	}

	public static function message() {
?>
		<div class="su-vote" style="display:none">
			<div class="su-vote-wrap">
				<div class="su-vote-gravatar"><a href="http://profiles.wordpress.org/gn_themes" target="_blank"><img src="http://www.gravatar.com/avatar/54fda46c150e45d18d105b9185017aea.png" alt="<?php _e( 'Vladimir Anokhin', 'shortcodes-ultimate' ); ?>" width="50" height="50"></a></div>
				<div class="su-vote-message">
					<p><?php _e( 'Hello, my name is Vladimir Anokhin, and I am developer of plugin <b>Shortcodes Ultimate</b>.<br>If you like this plugin, please write a few words about it at the wordpress.org or twitter. It will help other people find this useful plugin more quickly.<br><b>Thank you!</b>', 'shortcodes-ultimate' ); ?></p>
					<p>
						<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=su_vote&amp;vote=yes" class="su-vote-action button button-small button-primary" data-action="http://wordpress.org/support/view/plugin-reviews/shortcodes-ultimate?rate=5#postform"><?php _e( 'Rate plugin', 'shortcodes-ultimate' ); ?></a>
						<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=su_vote&amp;vote=tweet" class="su-vote-action button button-small" data-action="http://twitter.com/share?url=http://bit.ly/1blZb7u&amp;text=<?php echo urlencode( __( 'Shortcodes Ultimate - must have WordPress plugin #shortcodesultimate', 'shortcodes-ultimate' ) ); ?>"><?php _e( 'Tweet', 'shortcodes-ultimate' ); ?></a>
						<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=su_vote&amp;vote=no" class="su-vote-action button button-small"><?php _e( 'No, thanks', 'shortcodes-ultimate' ); ?></a>
						<span><?php _e( 'or', 'shortcodes-ultimate' ); ?></span>
						<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=su_vote&amp;vote=later" class="su-vote-action button button-small"><?php _e( 'Remind me later', 'shortcodes-ultimate' ); ?></a>
					</p>
				</div>
				<div class="su-vote-clear"></div>
			</div>
		</div>
		<?php
	}
}

new Su_Vote;
