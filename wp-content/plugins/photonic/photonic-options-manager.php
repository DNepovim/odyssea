<?php
class Photonic_Options_Manager {
	var $options, $tab, $tab_options, $reverse_options, $shown_options, $option_defaults, $allowed_values, $hidden_options, $nested_options, $displayed_sections;
	var $option_structure, $previous_displayed_section, $file, $tab_name, $core;

	function Photonic_Options_Manager($file, $core) {
		global $photonic_setup_options, $photonic_generic_options, $photonic_flickr_options, $photonic_picasa_options, $photonic_500px_options, $photonic_smugmug_options, $photonic_instagram_options, $photonic_zenfolio_options;
		$options_page_array = array(
			'generic-options.php' => $photonic_generic_options,
			'flickr-options.php' => $photonic_flickr_options,
			'picasa-options.php' => $photonic_picasa_options,
			'500px-options.php' => $photonic_500px_options,
			'smugmug-options.php' => $photonic_smugmug_options,
			'instagram-options.php' => $photonic_instagram_options,
			'zenfolio-options.php' => $photonic_zenfolio_options,
		);

		$tab_name_array = array(
			'generic-options.php' => 'Generic Options',
			'flickr-options.php' => 'Flickr Options',
			'picasa-options.php' => 'Picasa Options',
			'500px-options.php' => '500px Options',
			'smugmug-options.php' => 'SmugMug Options',
			'instagram-options.php' => 'Instagram Options',
			'zenfolio-options.php' => 'Zenfolio Options',
		);

		$this->core = $core;
		$this->file = $file;
		$this->tab = 'generic-options.php';
		if (isset($_REQUEST['tab']) && array_key_exists($_REQUEST['tab'], $options_page_array)) {
			$this->tab = $_REQUEST['tab'];
		}

		$this->tab_options = $options_page_array[$this->tab];
		$this->tab_name = $tab_name_array[$this->tab];
		$this->options = $photonic_setup_options;
		$this->reverse_options = array();
		$this->nested_options = array();
		$this->displayed_sections = 0;
		$this->option_structure = $this->get_option_structure();

		$all_options = get_option('photonic_options');
		if (!isset($all_options)) {
			$this->hidden_options = array();
		}
		else {
			$this->hidden_options = $all_options;
		}

		foreach ($this->tab_options as $option) {
			if (isset($option['id'])) {
				$this->shown_options[] = $option['id'];
				if (isset($this->hidden_options[$option['id']])) unset($this->hidden_options[$option['id']]);
			}
		}

		foreach ($photonic_setup_options as $option) {
			if (isset($option['category']) && !isset($this->nested_options[$option['category']])) {
				$this->nested_options[$option['category']] = array();
			}

			if (isset($option['id'])) {
				$this->reverse_options[$option['id']] = $option['type'];
				if (isset($option['std'])) {
					$this->option_defaults[$option['id']] = $option['std'];
				}
				if (isset($option['options'])) {
					$this->allowed_values[$option['id']] = $option['options'];
				}
				if (isset($option['grouping'])) {
					if (!isset($this->nested_options[$option['grouping']])) {
						$this->nested_options[$option['grouping']] = array();
					}
					$this->nested_options[$option['grouping']][] = $option['id'];
				}
			}
		}
	}

	function render_settings_page() {
		$saved_options = get_option('photonic_options');
		if (isset($saved_options) && !empty($saved_options) && !empty($saved_options['css_in_file'])) {
			$generated_css = $this->core->generate_css(false);
			$this->save_css_to_file($generated_css);
		}

		?>
		<div class="photonic-wrap">
			<div class="photonic-tabbed-options">
				<div class="photonic-header-nav">
					<div class="photonic-header-nav-top fix">
						<h1 class='photonic-header-1'>Photonic</h1>
						<div class='donate fix'>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-submit" >
								<input type="hidden" name="cmd" value="_s-xclick"/>
								<input type="hidden" name="hosted_button_id" value="9018267"/>
								<ul>
									<li class='announcements'><a href='https://aquoid.com'>Announcements</a></li>
									<li class='support'><a href='https://wordpress.org/support/plugin/photonic/'>Support</a></li>
									<li class='coffee'><input type='submit' name='submit' value='Like Photonic? Buy me a coffee &hellip;' /></li>
									<li class='rate'><a href='https://wordpress.org/support/plugin/photonic/reviews/'>&hellip; Or Rate it Well!</a></li>
								</ul>
								<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
							</form>
						</div><!-- donate -->
					</div>
					<div class="photonic-options-header-bar fix">
						<h2 class='nav-tab-wrapper'>
							<a class='nav-tab <?php if ($this->tab == 'generic-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-generic' href='?page=photonic-options-manager&amp;tab=generic-options.php'><span class="icon">&nbsp;</span> Generic Options</a>
							<a class='nav-tab <?php if ($this->tab == 'flickr-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-flickr' href='?page=photonic-options-manager&amp;tab=flickr-options.php'><span class="icon">&nbsp;</span> Flickr</a>
							<a class='nav-tab <?php if ($this->tab == 'picasa-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-picasa' href='?page=photonic-options-manager&amp;tab=picasa-options.php'><span class="icon">&nbsp;</span> Picasa</a>
							<a class='nav-tab <?php if ($this->tab == '500px-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-500px' href='?page=photonic-options-manager&amp;tab=500px-options.php'><span class="icon">&nbsp;</span> 500px</a>
							<a class='nav-tab <?php if ($this->tab == 'smugmug-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-smugmug' href='?page=photonic-options-manager&amp;tab=smugmug-options.php'><span class="icon">&nbsp;</span> SmugMug</a>
							<a class='nav-tab <?php if ($this->tab == 'instagram-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-instagram' href='?page=photonic-options-manager&amp;tab=instagram-options.php'><span class="icon">&nbsp;</span> Instagram</a>
							<a class='nav-tab <?php if ($this->tab == 'zenfolio-options.php') echo 'nav-tab-active'; ?>' id='photonic-options-zenfolio' href='?page=photonic-options-manager&amp;tab=zenfolio-options.php'><span class="icon">&nbsp;</span> Zenfolio</a>
						</h2>
					</div>
				</div>
				<?php
				$option_structure = $this->get_option_structure();
				$group = substr($this->tab, 0, stripos($this->tab, '.'));

				echo "<div class='photonic-options photonic-options-$group' id='photonic-options'>";
				echo "<ul class='photonic-section-tabs'>";
				foreach ($option_structure as $l1_slug => $l1) {
					echo "<li><a href='#$l1_slug'>" . $l1['name'] . "</a></li>\n";
				}
				echo "</ul>";

				do_settings_sections($this->file);

				$last_option = end($option_structure);
				$last_slug = key($option_structure);
				$this->show_buttons($last_slug, $last_option);

				echo "</form>\n";
				echo "</div><!-- /photonic-options-panel -->\n";

				echo "</div><!-- /#photonic-options -->\n";
				?>
			</div><!-- /#photonic-tabbed-options -->
		</div>
		<?php
	}

	function show_buttons($slug, $option) {
		if (!isset($option['buttons']) || ($option['buttons'] != 'no-buttons' && $option['buttons'] != 'special-buttons')) {
			echo "<div class=\"photonic-button-bar photonic-button-bar-{$slug}\">\n";
			echo "<input name=\"photonic_options[submit-{$slug}]\" type='submit' value=\"Save page &ldquo;".esc_attr($option['name'])."&rdquo;\" class=\"button button-primary\" />\n";
			echo "<input name=\"photonic_options[submit-{$slug}]\" type='submit' value=\"Reset page &ldquo;".esc_attr($option['name'])."&rdquo;\" class=\"button\" />\n";
			echo "<input name=\"photonic_options[submit-{$slug}]\" type='submit' value=\"Delete all options\" class=\"button\" />\n";
			echo "</div><!-- photonic-button-bar -->\n";
		}
	}

	function render_helpers() { ?>
	<div class="photonic-wrap">
		<div class="photonic-tabbed-options" style='position: relative; display: inline-block; '>
			<div class="photonic-waiting"><img src="<?php echo plugins_url('/include/images/downloading-dots.gif', __FILE__); ?>" alt='waiting'/></div>
			<form method="post" id="photonic-helper-form">
				<div class="photonic-header-nav">
					<div class="photonic-header-nav-top fix">
						<h2 class='photonic-header-1'>Photonic</h2>
					</div>
				</div>
				<h3 class="photonic-helper-header">Flickr</h3>
				<div class="photonic-helper-box left">
					<?php $this->display_flickr_id_helper(); ?>
				</div>
				<div class="photonic-helper-box right">
					<?php $this->display_flickr_group_helper(); ?>
				</div>
				<h3 class="photonic-helper-header">Picasa / Google Photos</h3>
				<div class="photonic-helper-box left">
					<?php $this->display_picasa_token_getter(); ?>
				</div>
				<div class="photonic-helper-box right">
					<?php $this->display_picasa_album_helper(); ?>
				</div>
				<h3 class="photonic-helper-header">SmugMug</h3>
				<div class="photonic-helper-box left">
					<?php $this->display_smugmug_album_id_helper(); ?>
				</div>
				<h3 class="photonic-helper-header">Instagram</h3>
				<div class="photonic-helper-box left">
					<?php $this->display_instagram_access_token_helper(); ?>
				</div>
				<div class="photonic-helper-box right">
					<?php $this->display_instagram_id_helper(); ?>
				</div>
				<div class="photonic-helper-box left">
					<?php $this->display_instagram_location_helper(); ?>
				</div>
				<h3 class="photonic-helper-header">Zenfolio</h3>
				<div class="photonic-helper-box left">
					<?php $this->display_zenfolio_category_helper(); ?>
				</div>
			</form>
		</div>
	</div>
	<?php
	}

	function display_flickr_id_helper() {
		global $photonic_flickr_api_key;
		if (empty($photonic_flickr_api_key)) {
			_e('Please set up your Flickr API Key under <em>Photonic &rarr; Settings &rarr; Flickr &rarr; Flickr Settings</em>', 'photonic');
		}
		else {
			_e('<h4>Flickr User ID Finder</h4>', 'photonic');
			_e('<label>Enter your Flickr photostream URL and click "Find"', 'photonic');
			echo '<input type="text" value="http://www.flickr.com/photos/username/" id="photonic-flickr-user" name="photonic-flickr-user"/>';
			echo '</label>';
			echo '<input type="button" value="'.__('Find', 'photonic').'" id="photonic-flickr-user-find" class="button button-primary"/>';
			echo '<div class="result">&nbsp;</div>';
		}
	}

	function display_flickr_group_helper() {
		global $photonic_flickr_api_key;
		if (empty($photonic_flickr_api_key)) {
			_e('Please set up your Flickr API Key under <em>Photonic &rarr; Settings &rarr; Flickr &rarr; Flickr Settings</em>', 'photonic');
		}
		else {
			_e('<h4>Flickr Group ID Finder</h4>', 'photonic');
			_e('<label>Enter your Flickr group URL and click "Find"', 'photonic');
			echo '<input type="text" value="http://www.flickr.com/groups/groupname/" id="photonic-flickr-group" name="photonic-flickr-group"/>';
			echo '</label>';
			echo '<input type="button" value="'.__('Find', 'photonic').'" id="photonic-flickr-group-find" class="button button-primary"/>';
			echo '<div class="result">&nbsp;</div>';
		}
	}

	function display_smugmug_album_id_helper() {
		global $photonic_smug_api_key;
		if (empty($photonic_smug_api_key)) {
			_e('Please set up your SmugMug API Key under <em>Photonic &rarr; Settings &rarr; SmugMug &rarr; SmugMug Settings</em>', 'photonic');
		}
		else {
			_e('<h4>SmugMug User Albums and Folders</h4>', 'photonic');
			_e('<label>Enter your SmugMug username and click "Find"', 'photonic');
			echo '<input type="text" value="username" id="photonic-smugmug-user" name="photonic-smugmug-user"/>';
			echo '</label>';
			echo '<input type="button" value="'.__('Find', 'photonic').'" id="photonic-smugmug-user-tree" class="button button-primary"/>';
			echo '<div class="result">&nbsp;</div>';
		}
	}

	function display_picasa_token_getter() {
		_e('<h4>Picasa / Google Refresh Token Getter</h4>', 'photonic');
		global $photonic_picasa_client_id, $photonic_picasa_client_secret, $photonic_picasa_gallery, $photonic_picasa_refresh_token;
		if (empty($photonic_picasa_client_id) || empty($photonic_picasa_client_secret)) {
			_e('Please set up your Google Client ID and Client Secret under <em>Photonic &rarr; Settings &rarr; Picasa &rarr; Picasa Settings</em>', 'photonic');
		}
		else {
			if (!isset($photonic_picasa_gallery)) {
				$photonic_picasa_gallery = new Photonic_Picasa_Processor();
			}
			$parameters = Photonic_Processor::parse_parameters($_SERVER['QUERY_STRING']);

			if (!empty($photonic_picasa_refresh_token)) {
				_e("You have already set up your Refresh Token. <strong>Unless you wish to regenerate the token these steps are not required.</strong><br/>", 'photonic');
			}

			if (!isset($parameters['code'])) {
				_e("You first have to authorize Photonic to connect to your Google account.<br/>", 'photonic');
				echo "<a href='".$photonic_picasa_gallery->get_authorization_url(array('redirect_uri' => admin_url('admin.php?page=photonic-helpers'), 'prompt' => 'consent'))."' class='button button-primary'>".
					__('Step 1: Authenticate', 'photonic')."</a>";
				echo '<br/>'.__("Next, you have to obtain the token.", 'photonic').'<br/>';
				echo "<span class='button photonic-helper-button-disabled'>".
					__('Step 2: Obtain Token', 'photonic')."</span>";
			}
			else {
				_e("You first have to authorize Photonic to connect to your Google account.<br/>", 'photonic');
				echo "<span class='button photonic-helper-button-disabled'>".
					__('Step 1: Authenticate', 'photonic')."</span>";
				echo '<br>'.__("Next, you have to obtain the token.", 'photonic').'<br/>';
				echo "<a href='#' class='button button-primary photonic-picasa-refresh'>".
					__('Step 2: Obtain Token', 'photonic')."</a>";
				echo '<input type="hidden" value="'.$parameters['code'].'" id="photonic-picasa-oauth-code"/>';
				echo '<input type="hidden" value="'.$parameters['state'].'" id="photonic-picasa-oauth-state"/>';
			}
		}
		echo '<div class="result">&nbsp;</div>';

	}

	function display_picasa_album_helper() {
		_e('<h4>Picasa / Google Photos Album ID Finder</h4>', 'photonic');
		global $photonic_picasa_client_id, $photonic_picasa_client_secret, $photonic_picasa_gallery, $photonic_picasa_refresh_token;
		if (empty($photonic_picasa_client_id) || empty($photonic_picasa_client_secret)) {
			_e('Please set up your Google Client ID and Client Secret under <em>Photonic &rarr; Settings &rarr; Picasa &rarr; Picasa Settings</em>', 'photonic');
		}
		else if (empty($photonic_picasa_refresh_token)) {
			_e('Please obtain your Refresh Token and save it under <em>Photonic &rarr; Settings &rarr; Picasa &rarr; Picasa Settings</em>', 'photonic');
		}
		else {
			_e('<label>Enter your Picasa / Google Photos user name click "Find"', 'photonic');
			echo '<br/><input type="text" value="odysseus.ithacky" id="photonic-picasa-user" name="photonic-picasa-user"/>';
			echo '</label>';
			echo '<input type="button" value="'.__('Find', 'photonic').'" id="photonic-picasa-album-find" class="button button-primary"/>';
			echo '<div class="result">&nbsp;</div>';
		}
	}

	function display_instagram_access_token_helper() {
		_e('<h4>Instagram Access Token Getter</h4>', 'photonic');
		echo "<a href='https://instagram.com/oauth/authorize/?client_id=f95ba49c90034990b8f5c7270c264fd3&scope=basic+public_content&redirect_uri=https://aquoid.com/photonic-router/?internal_uri=".
			admin_url('admin.php?page=photonic-helpers')."&response_type=token' class='button button-primary'>".
			__('Login and get Access Token', 'photonic')."</a>";
		$response = Photonic_Processor::parse_parameters($_SERVER['QUERY_STRING']);
		echo '<div class="result">'.(!empty($response['access_token']) ? 'Access token: <code>'.$response['access_token'].'</code>' : '&nbsp;').'</div>';
	}

	function display_instagram_id_helper() {
		global $photonic_instagram_access_token;
		if (!isset($photonic_instagram_access_token)) {
			_e('Please set up your Instagram Access Token under <em>Photonic &rarr; Settings &rarr; Instagram &rarr; Instagram Settings</em>', 'photonic');
		}
		else {
			_e('<h4>Instagram ID Finder</h4>', 'photonic');
			_e('<label>Enter your Instagram login id and click "Find"', 'photonic');
			echo '<input type="text" value="login-id" id="photonic-instagram-user" name="photonic-instagram-user"/>';
			echo '</label>';
			echo '<input type="button" value="'.__('Find', 'photonic').'" id="photonic-instagram-user-find" class="button button-primary"/>';
			echo '<div class="result">&nbsp;</div>';
		}
	}

	function display_instagram_location_helper() {
		global $photonic_instagram_access_token;
		if (!isset($photonic_instagram_access_token)) {
			_e('Please set up your Instagram Access Token under <em>Photonic &rarr; Settings &rarr; Instagram &rarr; Instagram Settings</em>', 'photonic');
		}
		else {
			_e('<h4>Instagram Location Finder</h4>', 'photonic');
			echo '<div class="location-fields">';
			echo '<div class="location"><label>'.__('Latitude', 'photonic').'<br/><input type="text" id="photonic-instagram-lat" name="photonic-instagram-lat"></label></div>';
			echo '<div class="location"><label>'.__('Longitude', 'photonic').'<br/><input type="text" id="photonic-instagram-lng" name="photonic-instagram-lng"></label></div>';
			echo '<div class="fs-separator"><span>'.__('OR', 'photonic').'</span></div>';
			echo '<div class="location"><label>'.__('FourSquare ID', 'photonic').'<br/><input type="text" id="photonic-instagram-fsid" name="photonic-instagram-fsid"></label></div>';
			echo '</div>';
			echo '<input type="button" value="'.__('Find', 'photonic').'" id="photonic-instagram-location-find" class="button button-primary"/>';
			echo '<div class="result">&nbsp;</div>';
		}
	}

	function display_zenfolio_category_helper() {
		_e('<h4>Zenfolio Categories</h4>', 'photonic');
		echo '<input type="button" value="'.__('List', 'photonic').'" id="photonic-zenfolio-categories-find" class="button button-primary"/>';
		echo '<div class="result">&nbsp;</div>';
	}

	function init() {
		foreach ($this->option_structure as $slug => $option) {
			register_setting('photonic_options-'.$slug, 'photonic_options', array(&$this, 'validate_options'));
			add_settings_section($slug, "", array(&$this, "create_settings_section"), $this->file);
			$this->add_settings_fields($this->file);
		}
	}

	function validate_options($options) {
		foreach ($options as $option => $option_value) {
			if (isset($this->reverse_options[$option])) {
				//Sanitize options
				switch ($this->reverse_options[$option]) {
					// For all text type of options make sure that the eventual text is properly escaped.
					case "text":
					case "textarea":
					case "color-picker":
					case "background":
					case "border":
						$options[$option] = esc_attr($option_value);
						break;

					case "select":
					case "radio":
						if (isset($this->allowed_values[$option])) {
							if (!array_key_exists($option_value, $this->allowed_values[$option])) {
								$options[$option] = $this->option_defaults[$option];
							}
						}
				        break;

					case "multi-select":
						$selections = explode(',', $option_value);
						$final_selections = array();
						foreach ($selections as $selection) {
							if (array_key_exists($selection, $this->allowed_values[$option])) {
								$final_selections[] = $selection;
							}
						}
						$options[$option] = implode(',', $final_selections);
						break;

					case "sortable-list":
						$selections = explode(',', $option_value);
						$final_selections = array();
						$master_list = $this->option_defaults[$option]; // Sortable lists don't have their values in ['options']
						foreach ($selections as $selection) {
							if (array_key_exists($selection, $master_list)) {
								$final_selections[] = $selection;
							}
						}
						$options[$option] = implode(',', $final_selections);
						break;

					case "checkbox":
						if (!in_array($option_value, array('on', 'off', 'true', 'false')) && isset($this->option_defaults[$option])) {
							$options[$option] = $this->option_defaults[$option];
						}
						break;
				}
			}
		}

		/* The Settings API does an update_option($option, $value), overwriting the $photonic_options array with the values on THIS page
		 * This is problematic because all options are stored in a single array, but are displayed on different options pages.
		 * Hence the overwrite kills the options from the other pages.
		 * So this is a workaround to include the options from other pages as hidden fields on this page, so that the array gets properly updated.
		 * The alternative would be to separate options for each page, but that would cause a migration headache for current users.
		 */
		if (isset($this->hidden_options) && is_array($this->hidden_options)) {
			foreach ($this->hidden_options as $hidden_option => $hidden_value) {
				if (strlen($hidden_option) >= 7 && (substr($hidden_option, 0, 7) == 'submit-' || substr($hidden_option, 0, 6) == 'reset-')) {
					continue;
				}
				$options[$hidden_option] = esc_attr($hidden_value);
			}
		}

		foreach ($this->nested_options as $section => $children) {
			if (isset($options['submit-'.$section])) {
				$options['last-set-section'] = $section;
				if (substr($options['submit-'.$section], 0, 9) == 'Save page' || substr($options['submit-'.$section], 0, 10) == 'Reset page') {
					global $photonic_options;
					foreach ($this->nested_options as $inner_section => $inner_children) {
						if ($inner_section != $section) {
							foreach ($inner_children as $inner_child) {
								if (isset($photonic_options[$inner_child])) {
									$options[$inner_child] = $photonic_options[$inner_child];
								}
							}
						}
					}

					if (substr($options['submit-'.$section], 0, 10) == 'Reset page') {
						unset($options['submit-'.$section]);
						// This is a reset for an individual section. So we will unset the child fields.
						foreach ($children as $child) {
							unset($options[$child]);
						}
					}
					unset($options['submit-'.$section]);
				}
				else if (substr($options['submit-'.$section], 0, 12) == 'Save changes') {
					unset($options['submit-'.$section]);
				}
				else if (substr($options['submit-'.$section], 0, 13) == 'Reset changes') {
					unset($options['submit-'.$section]);
					// This is a reset for all options in the sub-menu. So we will unset all child fields.
					foreach ($this->nested_options as $inner_section => $inner_children) {
						foreach ($inner_children as $child) {
							unset($options[$child]);
						}
					}
				}
				else if (substr($options['submit-'.$section], 0, 6) == 'Delete') {
					return;
				}
				break;
			}
		}
		return $options;
	}

	function get_option_structure() {
		if (isset($this->option_structure)) {
			return $this->option_structure;
		}
		$options = $this->tab_options;
		$option_structure = array();
		foreach ($options as $value) {
			switch ($value['type']) {
				case "title":
					$option_structure[$value['category']] = array();
					$option_structure[$value['category']]['slug'] = $value['category'];
					$option_structure[$value['category']]['name'] = $value['name'];
					$option_structure[$value['category']]['children'] = array();
					break;
				case "section":
			//		$option_structure[$value['parent']]['children'][$value['category']] = $value['name'];

					$option_structure[$value['category']] = array();
					$option_structure[$value['category']]['slug'] = $value['category'];
					$option_structure[$value['category']]['name'] = $value['name'];
					$option_structure[$value['category']]['children'] = array();
					if (isset($value['help'])) $option_structure[$value['category']]['help'] = $value['help'];
					if (isset($value['buttons'])) $option_structure[$value['category']]['buttons'] = $value['buttons'];
					break;
				default:
//					$option_structure[$value['grouping']]['children'][$value['name']] = $value['name'];
					if (isset($value['id'])) {
						$option_structure[$value['grouping']]['children'][$value['id']] = $value['name'];
					}
			}
		}
		return $option_structure;
	}

	function add_settings_fields($page) {
		$ctr = 0;
		foreach ($this->tab_options as $value) {
			$ctr++;
			switch ($value['type']) {
				case "blurb";
					add_settings_field($value['grouping'].'-'.$ctr, $value['name'], array(&$this, "create_section_for_blurb"), $page, $value['grouping'], $value);
					break;

				case "text";
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_text"), $page, $value['grouping'], $value);
					break;

				case "textarea";
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_textarea"), $page, $value['grouping'], $value);
					break;

				case "select":
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_select"), $page, $value['grouping'], $value);
					break;

				case "radio":
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_radio"), $page, $value['grouping'], $value);
					break;

				case "checkbox":
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_checkbox"), $page, $value['grouping'], $value);
					break;

				case "border":
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_border"), $page, $value['grouping'], $value);
					break;

				case "background":
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_background"), $page, $value['grouping'], $value);
					break;

				case "padding":
					add_settings_field($value['id'], $value['name'], array(&$this, "create_section_for_padding"), $page, $value['grouping'], $value);
					break;
			}
		}
	}

	function create_title($value) {
		//echo '<h2 class="photonic-header-1">'.$value['name']."</h2>\n";
	}

	function create_section_for_radio($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		foreach ($value['options'] as $option_value => $option_text) {
			$option_value = stripslashes($option_value);
			if (isset($photonic_options[$value['id']])) {
				$checked = checked(stripslashes($photonic_options[$value['id']]), $option_value, false);
			}
			else {
				$checked = checked($value['std'], $option_value, false);
			}
			echo '<div class="photonic-radio"><label><input type="radio" name="photonic_options['.$value['id'].']" value="'.$option_value.'" '.$checked."/>".$option_text."</label></div>\n";
		}
		$this->create_closing_tag($value);
	}

	function create_section_for_text($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		if (!isset($photonic_options[$value['id']])) {
			$text = $value['std'];
		}
		else {
			$text = $photonic_options[$value['id']];
			$text = stripslashes($text);
			$text = esc_attr($text);
		}

		echo '<input type="text" name="photonic_options['.$value['id'].']" value="'.$text.'" />'."\n";
		if (isset($value['hint'])) {
			echo "<em> &laquo; ".$value['hint']."<br /></em>\n";
		}
		$this->create_closing_tag($value);
	}

	function create_section_for_textarea($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		echo '<textarea name="photonic_options['.$value['id'].']" cols="" rows="">'."\n";
		if (isset($photonic_options[$value['id']]) && $photonic_options[$value['id']] != "") {
			$text = stripslashes($photonic_options[$value['id']]);
			$text = esc_attr($text);
			echo $text;
		}
		else {
			echo $value['std'];
		}
		echo '</textarea>';
		if (isset($value['hint'])) {
			echo " &laquo; ".$value['hint']."<br />\n";
		}
		$this->create_closing_tag($value);
	}

	function create_section_for_select($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		echo '<select name="photonic_options['.$value['id'].']">'."\n";
		foreach ($value['options'] as $option_value => $option_text) {
			echo "<option ";
			if (isset($photonic_options[$value['id']])) {
				selected($photonic_options[$value['id']], $option_value);
			}
			else {
				selected($value['std'], $option_value);
			}
			echo " value='$option_value' >".$option_text."</option>\n";
		}
		echo "</select>\n";
		$this->create_closing_tag($value);
	}

	function create_section_for_color_picker($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		if (!isset($photonic_options[$value['id']])) {
			$color_value = $value['std'];
		}
		else {
			$color_value = $photonic_options[$value['id']];
		}
		if (substr($color_value, 0, 1) != '#') {
			$color_value = "#$color_value";
		}

		echo '<div class="color-picker">'."\n";
		echo '<input type="text" id="'.$value['id'].'" name="photonic_options['.$value['id'].']" value="'.$color_value.'" class="color color-'.$value['id'].'" /> <br/>'."\n";
		echo "<strong>Default: ".$value['std']."</strong> (You can copy and paste this into the box above)\n";
		echo "</div>\n";
		$this->create_closing_tag($value);
	}

	function create_settings_section($section) {
		$option_structure = $this->option_structure;
		if ($this->displayed_sections != 0) {
			$this->show_buttons($this->previous_displayed_section, $option_structure[$this->previous_displayed_section]);
			echo "</form>\n";
			echo "</div><!-- /photonic-options-panel -->\n";
		}

		echo "<div id='{$section['id']}' class='photonic-options-panel'> \n";
		echo "<form method=\"post\" action=\"options.php\" id=\"photonic-options-form-{$section['id']}\" class='photonic-options-form'>\n";
		echo '<h3>' . $option_structure[$section['id']]['name'] . "</h3>\n";

		/*
		 * We store all options in one array, but display them across multiple pages. Hence we need the following hack.
		 * We are registering the same setting across multiple pages, hence we need to pass the "page" parameter to options.php.
		 * Otherwise options.php returns an error saying "Options page not found"
		 */
		echo "<input type='hidden' name='page' value='" . esc_attr($_REQUEST['page']) . "' />\n";
		if (!isset($_REQUEST['tab'])) {
			$tab = 'theme-options-intro.php';
		}
		else {
			$tab = esc_attr($_REQUEST['tab']);
		}
		echo "<input type='hidden' name='tab' value='" . $tab . "' />\n";

		settings_fields("photonic_options-{$section['id']}");
		$this->displayed_sections++;
		$this->previous_displayed_section = $section['id'];
	}

	function create_section_for_blurb($value) {
		$this->create_opening_tag($value);
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "checkbox". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_checkbox($value) {
		global $photonic_options;
		$checked = '';
		if (isset($photonic_options[$value['id']])) {
			$checked = checked(stripslashes($photonic_options[$value['id']]), 'on', false);
		}
		$this->create_opening_tag($value);
		echo '<label><input type="checkbox" name="photonic_options['.$value['id'].']" '.$checked."/>{$value['desc']}</label>\n";
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "border". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_border($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		$original = $value['std'];
		if (!isset($photonic_options[$value['id']])) {
			$default = $value['std'];
			$default_txt = "";
			foreach ($value['std'] as $edge => $edge_val) {
				$default_txt .= $edge.'::';
				foreach ($edge_val as $opt => $opt_val) {
					$default_txt .= $opt . "=" . $opt_val . ";";
				}
				$default_txt .= "||";
			}
		}
		else {
			$default_txt = $photonic_options[$value['id']];
			$default = $default_txt;
			$edge_array = explode('||', $default);
			$default = array();
			if (is_array($edge_array)) {
				foreach ($edge_array as $edge_vals) {
					if (trim($edge_vals) != '') {
						$edge_val_array = explode('::', $edge_vals);
						if (is_array($edge_val_array) && count($edge_val_array) > 1) {
							$vals = explode(';', $edge_val_array[1]);
							$default[$edge_val_array[0]] = array();
							foreach ($vals as $val) {
								$pair = explode("=", $val);
								if (isset($pair[0]) && isset($pair[1])) {
									$default[$edge_val_array[0]][$pair[0]] = $pair[1];
								}
								else if (isset($pair[0]) && !isset($pair[1])) {
									$default[$edge_val_array[0]][$pair[0]] = "";
								}
							}
						}
					}
				}
			}
		}
		$edges = array('top' => 'Top', 'right' => 'Right', 'bottom' => 'Bottom', 'left' => 'Left');
		$styles = array("none" => "No border",
			"hidden" => "Hidden",
			"dotted" => "Dotted",
			"dashed" => "Dashed",
			"solid" => "Solid",
			"double" => "Double",
			"grove" => "Groove",
			"ridge" => "Ridge",
			"inset" => "Inset",
			"outset" => "Outset");

		$border_width_units = array("px" => "Pixels (px)", "em" => "Em");

		foreach ($value['options'] as $option_value => $option_text) {
			if (isset($photonic_options[$value['id']])) {
				$checked = checked($photonic_options[$value['id']], $option_value, false);
			}
			else {
				$checked = checked($value['std'], $option_value, false);
			}
			echo '<div class="photonic-radio"><input type="radio" name="'.$value['id'].'" value="'.$option_value.'" '.$checked."/>".$option_text."</div>\n";
		}
	?>
		<div class='photonic-border-options'>
			<p>For any edge set style to "No Border" if you don't want a border.</p>
			<table class='opt-sub-table-5'>
				<col class='opt-sub-table-col-51'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>

				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col">Border Style</th>
					<th scope="col">Color</th>
					<th scope="col">Border Width</th>
					<th scope="col">Border Width Units</th>
				</tr>

		<?php
			foreach ($edges as $edge => $edge_text) {
		?>
			<tr>
				<th scope="row"><?php echo $edge_text; ?></th>
				<td valign='top'>
					<select name="<?php echo $value['id'].'-'.$edge; ?>-style" id="<?php echo $value['id'].'-'.$edge; ?>-style" >
				<?php
					foreach ($styles as $option_value => $option_text) {
						echo "<option ";
						if (isset($default[$edge]) && isset($default[$edge]['style'])) {
							selected($default[$edge]['style'], $option_value);
						}
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>

				<td valign='top'>
					<div class="color-picker-group">
						<input type="radio" name="<?php echo $value['id'].'-'.$edge; ?>-colortype" value="transparent" <?php checked($default[$edge]['colortype'], 'transparent'); ?> /> Transparent / No color<br/>
						<input type="radio" name="<?php echo $value['id'].'-'.$edge; ?>-colortype" value="custom" <?php checked($default[$edge]['colortype'], 'custom'); ?>/> Custom
						<input type="text" id="<?php echo $value['id'].'-'.$edge; ?>-color" name="<?php echo $value['id']; ?>-color" value="<?php echo $default[$edge]['color']; ?>" data-photonic-default-color="<?php echo $original[$edge]['color']; ?>" class="color" /><br />
						Default: <span> <?php echo $original[$edge]['color']; ?> </span>
					</div>
				</td>

				<td valign='top'>
					<input type="text" id="<?php echo $value['id'].'-'.$edge; ?>-border-width" name="<?php echo $value['id'].'-'.$edge; ?>-border-width" value="<?php echo $default[$edge]['border-width']; ?>" /><br />
				</td>

				<td valign='top'>
					<select name="<?php echo $value['id'].'-'.$edge; ?>-border-width-type" id="<?php echo $value['id'].'-'.$edge; ?>-border-width-type" >
				<?php
					foreach ($border_width_units as $option_value => $option_text) {
						echo "<option ";
						selected($default[$edge]['border-width-type'], $option_value);
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>
		<?php
			}
		?>
			</table>
		<input type='hidden' id="<?php echo $value['id']; ?>" name="photonic_options[<?php echo $value['id']; ?>]" value="<?php echo $default_txt; ?>" />
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "background". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_background($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		$original = $value['std'];
		if (!isset($photonic_options[$value['id']])) {
			$default = $value['std'];
			$default_txt = "";
			foreach ($value['std'] as $opt => $opt_val) {
				$default_txt .= $opt."=".$opt_val.";";
			}
		}
		else {
			$default_txt = $photonic_options[$value['id']];
			$default = $default_txt;
			$vals = explode(";", $default);
			$default = array();
			foreach ($vals as $val) {
				$pair = explode("=", $val);
				if (isset($pair[0]) && isset($pair[1])) {
					$default[$pair[0]] = $pair[1];
				}
				else if (isset($pair[0]) && !isset($pair[1])) {
					$default[$pair[0]] = "";
				}
			}
		}
		$repeats = array("repeat" => "Repeat horizontally and vertically",
			"repeat-x" => "Repeat horizontally only",
			"repeat-y" => "Repeat vertically only",
			"no-repeat" => "Do not repeat");

		$positions = array("top left" => "Top left",
			"top center" => "Top center",
			"top right" => "Top right",
			"center left" => "Center left",
			"center center" => "Middle of the page",
			"center right" => "Center right",
			"bottom left" => "Bottom left",
			"bottom center" => "Bottom center",
			"bottom right" => "Bottom right");

		foreach ($value['options'] as $option_value => $option_text) {
			if (isset($photonic_options[$value['id']])) {
				$checked = checked($photonic_options[$value['id']], $option_value, false);
			}
			else {
				$checked = checked($value['std'], $option_value, false);
			}
			echo '<div class="photonic-radio"><input type="radio" name="'.$value['id'].'" value="'.$option_value.'" '.$checked."/>".$option_text."</div>\n";
		}
	?>
		<div class='photonic-background-options'>
		<table class='opt-sub-table'>
	        <col class='opt-sub-table-cols'/>
	        <col class='opt-sub-table-cols'/>
			<tr>
				<td valign='top'>
					<div class="color-picker-group">
						<strong>Background Color:</strong><br />
						<input type="radio" name="<?php echo $value['id']; ?>-colortype" value="transparent" <?php checked($default['colortype'], 'transparent'); ?> /> Transparent / No color<br/>
						<input type="radio" name="<?php echo $value['id']; ?>-colortype" value="custom" <?php checked($default['colortype'], 'custom'); ?>/> Custom
						<input type="text" id="<?php echo $value['id']; ?>-bgcolor" name="<?php echo $value['id']; ?>-bgcolor" value="<?php echo $default['color']; ?>" data-photonic-default-color="<?php echo $original['color']; ?>" class="color" /><br />
						Default: <span> <?php echo $original['color']; ?> </span>
					</div>
				</td>
				<td valign='top'>
					<strong>Image URL:</strong><br />
					<?php $this->display_upload_field($default['image'], $value['id']."-bgimg", $value['id']."-bgimg"); ?>
				</td>
			</tr>

			<tr>
				<td valign='top'>
					<strong>Image Position:</strong><br />
					<select name="<?php echo $value['id']; ?>-position" id="<?php echo $value['id']; ?>-position" >
				<?php
					foreach ($positions as $option_value => $option_text) {
						echo "<option ";
						selected($default['position'], $option_value);
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>

				<td valign='top'>
					<strong>Image Repeat:</strong><br />
					<select name="<?php echo $value['id']; ?>-repeat" id="<?php echo $value['id']; ?>-repeat" >
				<?php
					foreach ($repeats as $option_value => $option_text) {
						echo "<option ";
						selected($default['repeat'], $option_value);
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign='top' colspan='2'>
					<div class='slider'>
						<p>
							<strong>Layer Transparency (not for IE):</strong>
							<select id="<?php echo $value['id']; ?>-trans" name="<?php echo $value['id']; ?>-trans">
								<?php
								for ($i = 0; $i <= 100; $i++) {
									echo "<option ";
									selected($default['trans'], $i);
									echo " value='$i' >".$i."</option>\n";
								}
								?>
							</select>
						</p>
					</div>
				</td>
			</tr>
		</table>
		<input type='hidden' id="<?php echo $value['id']; ?>" name="photonic_options[<?php echo $value['id']; ?>]" value="<?php echo $default_txt; ?>" />
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Renders an option whose type is "background". Invoked by add_settings_field.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_section_for_padding($value) {
		global $photonic_options;
		$this->create_opening_tag($value);
		if (!isset($photonic_options[$value['id']])) {
			$default = $value['std'];
			$default_txt = "";
			foreach ($value['std'] as $edge => $edge_val) {
				$default_txt .= $edge.'::';
				foreach ($edge_val as $opt => $opt_val) {
					$default_txt .= $opt . "=" . $opt_val . ";";
				}
				$default_txt .= "||";
			}
		}
		else {
			$default_txt = $photonic_options[$value['id']];
			$default = $default_txt;
			$edge_array = explode('||', $default);
			$default = array();
			if (is_array($edge_array)) {
				foreach ($edge_array as $edge_vals) {
					if (trim($edge_vals) != '') {
						$edge_val_array = explode('::', $edge_vals);
						if (is_array($edge_val_array) && count($edge_val_array) > 1) {
							$vals = explode(';', $edge_val_array[1]);
							$default[$edge_val_array[0]] = array();
							foreach ($vals as $val) {
								$pair = explode("=", $val);
								if (isset($pair[0]) && isset($pair[1])) {
									$default[$edge_val_array[0]][$pair[0]] = $pair[1];
								}
								else if (isset($pair[0]) && !isset($pair[1])) {
									$default[$edge_val_array[0]][$pair[0]] = "";
								}
							}
						}
					}
				}
			}
		}
		$edges = array('top' => 'Top', 'right' => 'Right', 'bottom' => 'Bottom', 'left' => 'Left');
		$padding_units = array("px" => "Pixels (px)", "em" => "Em");

		foreach ($value['options'] as $option_value => $option_text) {
			if (isset($photonic_options[$value['id']])) {
				$checked = checked($photonic_options[$value['id']], $option_value, false);
			}
			else {
				$checked = checked($value['std'], $option_value, false);
			}
			echo '<div class="photonic-radio"><input type="radio" name="'.$value['id'].'" value="'.$option_value.'" '.$checked."/>".$option_text."</div>\n";
		}
	?>
		<div class='photonic-padding-options'>
			<table class='opt-sub-table-5'>
				<col class='opt-sub-table-col-51'/>
				<col class='opt-sub-table-col-5'/>
				<col class='opt-sub-table-col-5'/>

				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col">Padding</th>
					<th scope="col">Padding Units</th>
				</tr>

		<?php
			foreach ($edges as $edge => $edge_text) {
		?>
			<tr>
				<th scope="row"><?php echo $edge_text; ?></th>
				<td valign='top'>
					<input type="text" id="<?php echo $value['id'].'-'.$edge; ?>-padding" name="<?php echo $value['id'].'-'.$edge; ?>-padding" value="<?php echo $default[$edge]['padding']; ?>" /><br />
				</td>

				<td valign='top'>
					<select name="<?php echo $value['id'].'-'.$edge; ?>-padding-type" id="<?php echo $value['id'].'-'.$edge; ?>-padding-type" >
				<?php
					foreach ($padding_units as $option_value => $option_text) {
						echo "<option ";
						selected($default[$edge]['padding-type'], $option_value);
						echo " value='$option_value' >".$option_text."</option>\n";
					}
				?>
					</select>
				</td>
			</tr>
		<?php
			}
		?>
			</table>
		<input type='hidden' id="<?php echo $value['id']; ?>" name="photonic_options[<?php echo $value['id']; ?>]" value="<?php echo $default_txt; ?>" />
		</div>
	<?php
		$this->create_closing_tag($value);
	}

	/**
	 * Creates the opening markup for each option.
	 *
	 * @param  $value
	 * @return void
	 */
	function create_opening_tag($value) {
		echo "<div class='photonic-section fix'>\n";
/*		if (isset($value['name'])) {
			echo "<h3>" . $value['name'] . "</h3>\n";
		}*/
		if (isset($value['desc']) && $value['type'] != 'checkbox') {
			echo $value['desc']."<br />";
		}
		if (isset($value['note'])) {
			echo "<span class=\"note\">".$value['note']."</span><br />";
		}
	}

	/**
	 * Creates the closing markup for each option.
	 *
	 * @param $value
	 * @return void
	 */
	function create_closing_tag($value) {
		echo "</div><!-- photonic-section -->\n";
	}

	/**
	 * This method displays an upload field and button. This has been separated from the create_section_for_upload method,
	 * because this is used by the create_section_for_background as well.
	 *
	 * @param $upload
	 * @param $id
	 * @param $name
	 * @param null $hint
	 * @return void
	 */
	function display_upload_field($upload, $id, $name, $hint = null) {
		echo '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$upload.'" />'."\n";
		if ($hint != null) {
			echo "<em> &laquo; ".$hint."<br /></em>\n";
		}
	}

	function invoke_helper() {
		if (isset($_POST['helper']) && !empty($_POST['helper'])) {
			$helper = sanitize_text_field($_POST['helper']);
			$photonic_options = get_option('photonic_options');
			switch ($helper) {
				case 'photonic-flickr-user-find':
					$flickr_api_key = $photonic_options['flickr_api_key'];
					$user = isset($_POST['photonic-flickr-user']) ? sanitize_text_field($_POST['photonic-flickr-user']) : '';
					$url = 'https://api.flickr.com/services/rest/?format=json&nojsoncallback=1&api_key='.$flickr_api_key.'&method=flickr.urls.lookupUser&url='.$user;
					$this->execute_query('flickr', $url, 'flickr.urls.lookupUser');
					break;

				case 'photonic-flickr-group-find':
					$flickr_api_key = $photonic_options['flickr_api_key'];
					$group = isset($_POST['photonic-flickr-group']) ? sanitize_text_field($_POST['photonic-flickr-group']) : '';
					$url = 'https://api.flickr.com/services/rest/?format=json&nojsoncallback=1&api_key='.$flickr_api_key.'&method=flickr.urls.lookupGroup&url='.$group;
					$this->execute_query('flickr', $url, 'flickr.urls.lookupGroup');
					break;

				case 'photonic-smugmug-user-tree':
					$smugmug_api_key = $photonic_options['smug_api_key'];
					$user = isset($_POST['photonic-smugmug-user']) ? sanitize_text_field($_POST['photonic-smugmug-user']) : '';
					if (!empty($user)) {
						global $photonic_smugmug_gallery;
						if (!isset($photonic_smugmug_gallery)) {
							$photonic_smugmug_gallery = new Photonic_SmugMug_Processor();
						}

						$cookie = Photonic::parse_cookie();
						global $photonic_smugmug_gallery, $photonic_smug_allow_oauth, $photonic_smug_oauth_done;
						if ($photonic_smug_allow_oauth && isset($cookie['smug']) && isset($cookie['smug']['oauth_token']) && isset($cookie['smug']['oauth_token_secret'])) {
							if (!isset($photonic_smugmug_gallery)) {
								$photonic_smugmug_gallery = new Photonic_SmugMug_Processor();
							}
							$current_token = array(
								'oauth_token' => $cookie['smug']['oauth_token'],
								'oauth_token_secret' => $cookie['smug']['oauth_token_secret']
							);

							if (!$photonic_smug_oauth_done &&
								((isset($cookie['smug']['oauth_token_type']) && $cookie['smug']['oauth_token_type'] == 'request') || !isset($cookie['smug']['oauth_token_type']))
							) {
								$current_token['oauth_verifier'] = $_REQUEST['oauth_verifier'];
								$new_token = $photonic_smugmug_gallery->get_access_token($current_token);
								if (isset($new_token['oauth_token']) && isset($new_token['oauth_token_secret'])) {
									$access_token_response = $photonic_smugmug_gallery->check_access_token($new_token);
									if (is_wp_error($access_token_response)) {
										$photonic_smugmug_gallery->is_server_down = true;
									}
									$photonic_smug_oauth_done = $photonic_smugmug_gallery->is_access_token_valid($access_token_response);
								}
							}
							else if (isset($cookie['smug']['oauth_token_type']) && $cookie['smug']['oauth_token_type'] == 'access') {
								$access_token_response = $photonic_smugmug_gallery->check_access_token($current_token);
								if (is_wp_error($access_token_response)) {
									$photonic_smugmug_gallery->is_server_down = true;
								}
								$photonic_smug_oauth_done = $photonic_smugmug_gallery->is_access_token_valid($access_token_response);
							}
						}

						$api_call = 'https://api.smugmug.com/api/v2/user/' . $user . '?_expand=Node.ChildNodes.ChildNodes.ChildNodes.ChildNodes.ChildNodes';
						$args = array(
							'APIKey' => $smugmug_api_key,
							'_accept' => 'application/json',
							'_verbosity' => 1,
							'_expandmethod' => 'inline',
						);
						if ($photonic_smug_oauth_done) {
							$args = $photonic_smugmug_gallery->sign_call($api_call, 'GET', $args);
						}
						else {
							$request_token = $photonic_smugmug_gallery->get_request_token();
							$authorize_url = $photonic_smugmug_gallery->get_authorize_URL($request_token).'&Access=Full&Permissions=Read';
							echo sprintf(__("If you have protected albums, you will have to %1sauthenticate%2s to see the protected albums.", 'photonic'), "<a href='$authorize_url' target='_blank'>", "</a>");
						}

						$response = Photonic::http($api_call, 'GET', $args);
						$this->process_smugmug_response($response);
					}
					break;

				case 'photonic-picasa-album-find':
					$user = isset($_POST['photonic-picasa-user']) ? sanitize_text_field($_POST['photonic-picasa-user']) : '';
					if (!empty($user)) {
						$url = 'https://picasaweb.google.com/data/feed/api/user/'.$user.'?kind=album';

						global $photonic_picasa_gallery, $photonic_picasa_refresh_token;
						if (!isset($photonic_picasa_gallery)) {
							$photonic_picasa_gallery = new Photonic_Picasa_Processor();
						}
						$photonic_picasa_gallery->perform_back_end_authentication($photonic_picasa_refresh_token);
						if (!empty($photonic_picasa_gallery->access_token)) {
							$url .= '&access_token='.$photonic_picasa_gallery->access_token;
						}
						$response = $photonic_picasa_gallery->get_secure_curl_response($url);
						if (strlen($response) == 0 || substr($response, 0, 1) != '<') {
							if (stripos($response, 'No album found') !== false) {
								$response = '';
							}
						}

						$this->process_picasa_response($response);
					}
					break;

				case 'photonic-instagram-user-find':
					$user = isset($_POST['photonic-instagram-user']) ? sanitize_text_field($_POST['photonic-instagram-user']) : '';
					if (!empty($user)) {
						global $photonic_instagram_gallery, $photonic_instagram_access_token;
						if (!isset($photonic_instagram_gallery)) {
							$photonic_instagram_gallery = new Photonic_Instagram_Processor();
						}

						if (isset($photonic_instagram_access_token) && !$photonic_instagram_gallery->is_token_expired($photonic_instagram_access_token)) {
							$url = 'https://api.instagram.com/v1/users/search?access_token='.$photonic_instagram_access_token.'&q='.$user;
							$this->execute_query('instagram', $url, 'users/search');
						}
						else {
							_e("You have to authenticate to see the details.");
						}
					}
					break;

				case 'photonic-instagram-location-find':
					global $photonic_instagram_gallery, $photonic_instagram_access_token;
					if (!isset($photonic_instagram_gallery)) {
						$photonic_instagram_gallery = new Photonic_Instagram_Processor();
					}
					$lat = isset($_POST['photonic-instagram-lat']) ? sanitize_text_field($_POST['photonic-instagram-lat']) : '';
					$lng = isset($_POST['photonic-instagram-lng']) ? sanitize_text_field($_POST['photonic-instagram-lng']) : '';
					$fs_id = isset($_POST['photonic-instagram-fsid']) ? sanitize_text_field($_POST['photonic-instagram-fsid']) : '';
					if (isset($photonic_instagram_access_token) && !$photonic_instagram_gallery->is_token_expired($photonic_instagram_access_token)) {
						$url = 'https://api.instagram.com/v1/locations/search?access_token=' . $photonic_instagram_access_token . '&lat=' . $lat . '&lng=' . $lng . '&foursquare_v2_id=' . $fs_id;
						$this->execute_query('instagram', $url, 'locations/search');
					}
					else {
						_e("You have to authenticate to see the details.");
					}
					break;

				case 'photonic-zenfolio-categories-find':
					$url = 'https://api.zenfolio.com/api/1.6/zfapi.asmx/GetCategories';
					$this->execute_query('zenfolio', $url, 'GetCategories');
					break;
			}
		}
		die();
	}

	function execute_query($where, $url, $method) {
		$response = wp_remote_request($url, array('sslverify' => false));
		if (!is_wp_error($response)) {
			if (isset($response['response']) && isset($response['response']['code'])) {
				if ($response['response']['code'] == 200) {
					if (isset($response['body'])) {
						if ($where == 'flickr') {
							$this->execute_flickr_query($response['body'], $method);
						}
						else if ($where == 'instagram') {
							$this->execute_instagram_query($response['body'], $method);
						}
						else if ($where == 'zenfolio') {
							$this->execute_zenfolio_query($response['body'], $method);
						}
					}
					else {
						_e('<span class="found-id-text">No response from server!</span>', 'photonic');
					}
				}
				else {
					echo '<span class="found-id-text">'.$response['response']['message'].'</span>';
				}
			}
			else {
				_e('<span class="found-id-text">No response from server!</span>', 'photonic');
			}
		}
		else {
			_e('<span class="found-id-text">Cannot connect to the server. Please try later.</span>', 'photonic');
		}
	}

	function execute_flickr_query($body, $method) {
		$body = json_decode($body);
		if (isset($body->stat) && $body->stat == 'fail') {
			echo '<span class="found-id-text">'.$body->message.'</span>';
		}
		else {
			if ($method == 'flickr.urls.lookupUser') {
				if (isset($body->user)) {
					echo '<span class="found-id-text">'.__('User ID:', 'photonic').'</span> <span class="found-id"><code>'.$body->user->id.'</code></span>';
				}
			}
			else if ($method == 'flickr.urls.lookupGroup') {
				if (isset($body->group)) {
					echo '<span class="found-id-text">'.__('Group ID:', 'photonic').'</span> <span class="found-id"><code>'.$body->group->id.'</code></span>';
				}
			}
		}
	}

	function process_smugmug_response($response) {
		$body = $response['body'];
		$body = json_decode($body);
		if ($body->Code === 200) {
			$body = $body->Response;
			$albums = $body->Album;

			if (isset($body->User) && isset($body->User->Uris) && isset($body->User->Uris->Node) && isset($body->User->Uris->Node->Node)) {
				$node = $body->User->Uris->Node->Node;
				if ($node->Type == 'Folder') {
					$ret = $this->process_smugmug_node($node);
					if (!empty($ret)) {
						$ret = "<table class='photonic-helper-table'>\n".
									"\t<tr>\n".
										"\t<th>Name</th>\n".
										"\t<th>Type</th>\n".
										"\t<th>Album Key</th>\n".
										"\t<th>Security Level</th>\n".
									"\t</tr>\n".
									$ret.
								"</table>\n";
						echo $ret;
					}
				}
			}
		}
	}

	function process_smugmug_node($node) {
		$ret = '';
		if ($node->Type == 'Folder') {
			$albums = array();
			$folders = array();
			if (isset($node->Uris->ChildNodes->Node)) {
				$child_nodes = $node->Uris->ChildNodes->Node;
				foreach ($child_nodes as $child) {
					if ($child->Type == 'Album') {
						$albums[] = $child;
					}
					else if ($child->Type == 'Folder') {
						$folders[] = $child;
					}
				}

				foreach ($albums as $album) {
					$ret .= "\t<tr>\n";
					$ret .= "\t\t<td>{$album->Name}</td>\n";
					$ret .= "\t\t<td>Album</td>\n";
					$album_key = isset($album->Uris->Album) ? $album->Uris->Album->Uri : '';
					$album_key = explode('/', $album_key);
					$album_key = $album_key[count($album_key) - 1];
					$ret .= "\t\t<td>$album_key</td>\n";
					$ret .= "\t\t<td>{$album->SecurityType}</td>\n";
					$ret .= "\t</tr>\n";
				}

				foreach ($folders as $folder) {
					$ret .= "\t<tr>\n";
					$ret .= "\t\t<td>{$folder->Name}</td>\n";
					$ret .= "\t\t<td>Folder</td>\n";
					$ret .= "\t\t<td>{$folder->NodeID}</td>\n";
					$ret .= "\t\t<td>{$folder->SecurityType}</td>\n";
					$ret .= "\t</tr>\n";

					$ret .= $this->process_smugmug_node($folder);
				}
			}
		}
		return $ret;
	}

	function process_picasa_response($response) {
		$picasa_result = simplexml_load_string($response);
		if (isset($picasa_result->entry) && count($picasa_result->entry) > 0) {
			$albums = $picasa_result->entry;
			echo "<table class='photonic-helper-table'>\n";
			echo "\t<tr>\n";
			echo "\t\t<th>Album Title</th>\n";
			echo "\t\t<th>Album Name</th>\n";
			echo "\t\t<th>Album ID</th>\n";
			echo "\t\t<th>Auth Key</th>\n";
			echo "\t\t<th>Access Level</th>\n";
			echo "\t</tr>\n";
			foreach ($albums as $album) {
				$auth_key = '';
				if (isset($album->link)) {
					foreach ($album->link as $link) {
						$attributes = $link->attributes();
						if (isset($attributes['rel']) && $attributes['rel'] == 'self' && isset($attributes['href'])) {
							if (stripos($attributes['href'], '?authkey=') !== FALSE) {
								$auth_key = substr($attributes['href'], stripos($attributes['href'], '?authkey=') + 9);
								break;
							}
						}
					}
				}
				echo "\t<tr>\n";
				echo "\t\t<td>{$album->title}</td>\n";
				$gphoto_photo = $album->children('gphoto', 1);
				echo "\t\t<td>{$gphoto_photo->name}</td>\n";
				echo "\t\t<td>{$gphoto_photo->id}</td>\n";
				echo "\t\t<td>$auth_key</td>\n";
				echo "\t\t<td>{$gphoto_photo->access}</td>\n";
				echo "\t</tr>\n";
			}
			echo "</table>\n";
		}
	}

	function execute_instagram_query($body, $method) {
		$body = json_decode($body);
		if (isset($body->meta) && isset($body->meta->code) && $body->meta->code == 200 && isset($body->data)) {
			$data = $body->data;
			if (count($data) == 0) {
				if ($method == 'users/search') {
					_e('<span class="found-id-text">User not found</span>', 'photonic');
				}
				else if ($method = 'locations/search') {
					_e('<span class="found-id-text">Location not found</span>', 'photonic');
				}
			}
			else if (count($data) == 1) {
				if ($method == 'users/search') {
					$user = $data[0];
					$text = '<code>'.$user->id.'</code> ('.(!empty($user->full_name) ? $user->full_name : $user->username).')';
					echo '<span class="found-id-text">'.__('User ID:', 'photonic').'</span> <span class="found-id">'.$text.'</span>';
				}
				else if ($method == 'locations/search') {
					$location = $data[0];
					$text = '<code>'.$location->id.'</code> ('.(!empty($location->name) ? $location->name : __('Name not available', 'photonic')).')';
					echo '<span class="found-id-text">'.__('Location ID:', 'photonic').'</span> <span class="found-id">'.$text.'</span>';
				}
			}
			else if (count($data) > 1) {
				if ($method == 'users/search') {
					$text = array();
					foreach ($data as $user) {
						$text[] = '<code>'.$user->id.'</code> ('.(!empty($user->full_name) ? $user->full_name : $user->username).')';
					}
					echo '<span class="found-id-text">'.__('Matching users:', 'photonic').'</span> <span class="found-id">'.implode(', ', $text).'</span>';
				}
				else if ($method == 'locations/search') {
					$text = array();
					foreach ($data as $location) {
						$text[] = '<code>'.$location->id.'</code> ('.(!empty($location->name) ? $location->name : __('Name not available', 'photonic')).')';
					}
					echo '<span class="found-id-text">'.__('Matching locations:', 'photonic').'</span> <span class="found-id">'.implode(', ', $text).'</span>';
				}
			}
		}
	}

	function execute_zenfolio_query($body, $method) {
		if ($method == 'GetCategories') {
			$response = simplexml_load_string($body);
			if (!empty($response->Category)) {
				$categories = $response->Category;
				echo "<ul class='photonic-scroll-panel'>\n";
				foreach ($categories as $category) {
					echo "<li>{$category->DisplayName} &ndash; {$category->Code}</li>\n";
				}
				echo "</ul>\n";
			}
		}
	}

	/**
	 * Save generated options to a file. This uses the WP_Filesystem to validate the credentials of the user attempting to save options.
	 *
	 * @param array $custom_css
	 * @return bool
	 */
	function save_css_to_file($custom_css) {
		if(!isset($_GET['settings-updated'])) {
			return false;
		}

		$url = wp_nonce_url('themes.php?page=photonic-options-manager');
		if (false === ($creds = request_filesystem_credentials($url, '', false, false))) {
			return true;
		}

		if (!WP_Filesystem($creds)) {
			request_filesystem_credentials($url, '', true, false);
			return true;
		}

		global $wp_filesystem;
		if (!is_dir(PHOTONIC_UPLOAD_DIR)) {
			if (!$wp_filesystem->mkdir(PHOTONIC_UPLOAD_DIR)) {
				echo "<div class='error'><p>Failed to create directory ".PHOTONIC_UPLOAD_DIR.". Please check your folder permissions.</p></div>";
				return false;
			}
		}

		$filename = trailingslashit(PHOTONIC_UPLOAD_DIR).'custom-styles.css';

		if (empty($custom_css)) {
			return false;
		}

		if (!$wp_filesystem->put_contents($filename, $custom_css, FS_CHMOD_FILE)) {
			echo "<div class='error'><p>Failed to save file $filename. Please check your folder permissions.</p></div>";
			return false;
		}
		return true;
	}

	function render_getting_started() {
		require_once(plugin_dir_path(__FILE__)."/photonic-getting-started.php");
	}
}
