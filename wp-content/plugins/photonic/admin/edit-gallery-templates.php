<?php

require_once(trailingslashit(plugin_dir_path(__FILE__)).'/../photonic-form.php');
$providers = array('default', 'flickr', 'picasa', '500px', 'smugmug', 'zenfolio', 'instagram');

foreach ($providers as $provider) {
	?>
	<script type="text/html" id="tmpl-photonic-editor-<?php echo $provider; ?>">
		<?php
		$field_list = $fields[$provider]['fields'];
		echo "<div class='photonic-form'>\n";
		echo "<h2>Photonic ".($provider == 'default' ? 'WP' : $provider)." Gallery Settings</h2>\n";
		foreach ($field_list as $field) {
			echo "\t<label class='setting'>\n";
			echo "\t\t<span class='label'>{$field['name']} " . (isset($field['req']) && $field['req'] ? '(*)' : '') . " </span>\n";
			switch ($field['type']) {
				case 'text':
					echo "\t\t<input type='text' name='{$field['id']}' value='" . (isset($field['std']) ? $field['std'] : '') . "'/>\n";
					continue;
				case 'select':
					echo "\t\t<select name='{$field['id']}'>\n";
					$default = isset($field['std']) ? $field['std'] : '';
					foreach ($field['options'] as $option_name => $option_value) {
						if ($option_name == $default) {
							$selected = 'selected';
						}
						else {
							$selected = '';
						}
						echo "\t\t\t<option value='$option_name' $selected>" . esc_attr($option_value) . "</option>\n";
					}
					echo "\t\t</select>\n";
					continue;
				case 'raw':
					echo "\t\t" . $field['std'] . "\n";
					continue;
			}
			echo "\t\t<span class='hint'>" . (isset($field['hint']) ? $field['hint'] : '') . "</span>\n";
			echo "\t</label>\n";
		}
		echo "</div>\n";
		?>
	</script>
	<?php
}

