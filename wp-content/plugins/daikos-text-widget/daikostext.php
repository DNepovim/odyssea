<?php
/*
Plugin Name: Daiko's Text Widget
Plugin URI: http://www.daikos.net/widgets/daikos-text-widget/
Description: Works basically like the Text widget, but it will also take PHP code and includes conditional tags to specify where to show. An extension of the Execphp widget by Otto at http://ottodestruct.com.
Author: Rune Fjellheim
Version: 0.9.4
Author URI: http://www.daikos.net
*/
                                                                                                                                                        
function widget_daikos_text_init()
{
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	function widget_daikos_text_control($number) {
		$options = $newoptions = get_option('widget_daikos_text');
		if ( $_POST["daikos-text-submit-$number"] ) {
			$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["daikos-text-title-$number"]));
			$newoptions[$number]['text'] = stripslashes($_POST["daikos-text-text-$number"]);
			$newoptions[$number]['show'] = $_POST["daikos-text-show-$number"];
			$newoptions[$number]['slug'] = strip_tags(stripslashes($_POST["daikos-text-slug-$number"]));
			if ( !current_user_can('unfiltered_html') )
				$newoptions[$number]['text'] = stripslashes(wp_filter_post_kses($newoptions[$number]['text']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_daikos_text', $options);
		}
		$title = htmlspecialchars($options[$number]['title'], ENT_QUOTES);
		$text = htmlspecialchars($options[$number]['text'], ENT_QUOTES);
		$allSelected = $homeSelected = $postSelected = $postInCategorySelected = $pageSelected = $categorySelected = false;
		switch ($options[$number]['show']) {
			case "all":
			$allSelected = true;
			break;
			case "":
			$allSelected = true;
			break;
			case "home":
			$homeSelected = true;
			break;
			case "post":
			$postSelected = true;
			break;
			case "post_in_category":
			$postInCategorySelected = true;
			break;
			case "page":
			$pageSelected = true;
			break;
			case "category":
			$categorySelected = true;
			break;
		}
	?>
				<label for="daikos-text-title-<?php echo "$number"; ?>" title="Title above the widget" style="line-height:35px;display:block;">Title:<input style="width: 470px;" id="daikos-text-title-<?php echo "$number"; ?>" name="daikos-text-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" /></label> 
				<p>PHP Code (MUST be enclosed in &lt;?php and ?&gt; tags!):</p>
				<label for="daikos-text-text-<?php echo "$number"; ?>" title="PHP Code (MUST be enclosed in &lt;?php and ?&gt; tags!):" style="width: 490px; height: 280px;display:block;"><textarea style="width: 490px; height: 230px;" id="daikos-text-text-<?php echo "$number"; ?>" name="daikos-text-text-<?php echo "$number"; ?>"><?php echo $text; ?></textarea></label>
				<label for="daikos-text-show-<?php echo "$number"; ?>"  title="Show only on specified page(s)/post(s)/category. Default is All" style="line-height:35px;">Display only on: <select name="daikos-text-show-<?php echo"$number"; ?>" id="daikos-text-show-<?php echo"$number"; ?>"><option label="All" value="all" <?php if ($allSelected){echo "selected";} ?>>All</option><option label="Home" value="home" <?php if ($homeSelected){echo "selected";} ?>>Home</option><option label="Post" value="post" <?php if ($postSelected){echo "selected";} ?>>Post(s)</option><option label="Post in Category ID(s)" value="post_in_category" <?php if ($postInCategorySelected){echo "selected";} ?>>Post In Category ID(s)</option><option label="Page" value="page" <?php if ($pageSelected){echo "selected";} ?>>Page(s)</option><option label="Category" value="category" <?php if ($categorySelected){echo "selected";} ?>>Category</option></select></label> 
				<label for="daikos-text-slug-<?php echo "$number"; ?>"  title="Optional limitation to specific page, post or category. Use ID, slug or title." style="line-height:35px;">Slug/Title/ID: <input type="text" style="width: 130px;" id="daikos-text-slug-<?php echo "$number"; ?>" name="daikos-text-slug-<?php echo "$number"; ?>" value="<?php echo htmlspecialchars($options[$number]['slug']); ?>" /></label>
				<?php if ($postInCategorySelected) echo "<p>In <strong>Post In Category</strong> add one or more cat. IDs (not Slug or Title) comma separated!</p>" ?>
				<input type="hidden" id="daikos-text-submit-<?php echo "$number"; ?>" name="daikos-text-submit-<?php echo "$number"; ?>" value="1" />
	<?php
	}
	
	function widget_daikos_text($args, $number = 1) {
		extract($args);
		$options = get_option('widget_daikos_text');
		$title = $options[$number]['title'];
		$text = $options[$number]['text'];
		$show = $options[$number]['show'];
		$slug = $options[$number]['slug'];
		?>
		<?php 
 
 /* Do the conditional tag checks. */
		switch ($show) {
			case "all": 
				echo $before_widget;
				echo "<div class='DaikosText'>"; 
				$title ? print($before_title . $title . $after_title) : null;
			    eval('?>'.$text);
				echo "</div>"; 
				echo $after_widget."
				";			
				break;
			case "home":
				if (is_home()) {
					echo $before_widget;
					echo "<div class='DaikosText'>"; 
					$title ? print($before_title . $title . $after_title) : null;
			        eval('?>'.$text);
					echo "</div>"; 
					echo $after_widget."
					";				}
			    else {
			        echo "<!-- Daiko's Text Widget ".$number." is disabled for this page/post! -->";
			    }
				break;
			case "post":
			    if (is_single($slug)) {
					echo $before_widget;
					echo "<div class='DaikosText'>"; 
					$title ? print($before_title . $title . $after_title) : null;
			        eval('?>'.$text);
					echo "</div>"; 
					echo $after_widget."
					";				}
				else {
			        echo "<!-- Daiko's Text Widget ".$number." is disabled for this page/post! -->";
			    }
				break;
			case "post_in_category":
				$PiC = explode(",",$slug);
				$InCategory = false;
				foreach($PiC as $CategoryID) {
					if(is_single() && in_category($CategoryID)){
						$InCategory = true;
					}
					elseif (is_category($CategoryID)) {
						$InCategory = true;
					}
				}
			    if ($InCategory) {
					echo $before_widget;
					echo "<div class='DaikosText'>"; 
					$title ? print($before_title . $title . $after_title) : null;
			        eval('?>'.$text);
					echo "</div>"; 
					echo $after_widget."
					";				}
				else {
			        echo "<!-- Daiko's Text Widget ".$number." is disabled for this page/post! -->";
			    }
				break;
			case "page":
				if (is_page($slug)) {
					echo $before_widget;
					echo "<div class='DaikosText'>"; 
					$title ? print($before_title . $title . $after_title) : null;
			        eval('?>'.$text);
					echo "</div>"; 
					echo $after_widget."
					";				}
			    else {
			        echo "<!-- Daiko's Text Widget ".$number." is disabled for this page/post! -->";
			    }
				break;
			case "category":
			    if (is_category($slug)) {
					echo $before_widget;
					echo "<div class='DaikosText'>"; 
					$title ? print($before_title . $title . $after_title) : null;
			        eval('?>'.$text);
					echo "</div>"; 
					echo $after_widget."
					";				}
			    else {
			        echo "<!-- Daiko's Text Widget ".$number." is disabled for this page/post! -->";
			    }
				break;
		}
		?>
	<?php
	}
	
	function widget_daikos_text_setup() {
		$options = $newoptions = get_option('widget_daikos_text');
		if ( isset($_POST['daikos-text-number-submit']) ) {
			$number = (int) $_POST['daikos-text-number'];
			if ( $number > 9 ) $number = 9;
			if ( $number < 1 ) $number = 1;
			$newoptions['number'] = $number;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_daikos_text', $options);
			widget_daikos_text_register($options['number']);
		}
	}
	
	function widget_daikos_text_page() {
		$options = $newoptions = get_option('widget_daikos_text');
	?>
		<div class="wrap">
			<form method="POST">
				<h2>Daiko's Text  Widgets</h2>
				<p style="line-height: 30px;"><?php _e('How many Daiko\'s Text Widgets would you like?'); ?>
				<select id="daikos-text-number" name="daikos-text-number" value="<?php echo $options['number']; ?>">
	<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
				</select>
				<span class="submit"><input type="submit" name="daikos-text-number-submit" id="daikos-text-number-submit" value="<?php _e('Save'); ?>" /></span></p>
			</form>
		</div>
	<?php
	}
	
	function widget_daikos_text_register() {
		$options = get_option('widget_daikos_text');
		$number = $options['number'];
		if ( $number < 1 ) $number = 1;
		if ( $number > 9 ) $number = 9;
		for ($i = 1; $i <= 9; $i++) {
			$name = array('Daiko\'s Text %s', null, $i);
			register_sidebar_widget($name, $i <= $number ? 'widget_daikos_text' : /* unregister */ '', $i);
			register_widget_control($name, $i <= $number ? 'widget_daikos_text_control' : /* unregister */ '', 530, 450, $i);
		}
		add_action('sidebar_admin_setup', 'widget_daikos_text_setup');
		add_action('sidebar_admin_page', 'widget_daikos_text_page');
	}
	// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
	widget_daikos_text_register();
}

	
// Tell Dynamic Sidebar about our new widget and its control
add_action('plugins_loaded', 'widget_daikos_text_init');

?>