<?php
/**
 * Creates a form in the "Add Media" screen under the new "Photonic" tab. This form lets you insert the gallery shortcode with
 * the right arguments for native WP galleries, Flickr, Picasa, SmugMug, Zenfolio, 500px and Instagram.
 *
 * @package Photonic
 * @subpackage UI
 */

$selected_tab = isset($_GET['photonic-tab']) ? esc_attr($_GET['photonic-tab']) : 'default';
if (!in_array($selected_tab, array('default', 'flickr', 'picasa', 'smugmug', '500px', 'zenfolio', 'instagram'))) {
	$selected_tab = 'default';
}

if (isset($_POST['photonic-submit'])) {
	$shortcode =  stripslashes($_POST['photonic-shortcode']);
	return media_send_to_editor($shortcode);
}
else if (isset($_POST['photonic-cancel'])) {
	return media_send_to_editor('');
}

?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			window.photonicAdminHtmlEncode = function photonicAdminHtmlEncode(value){
				return $('<div/>').text(value).html();
			};

			$('#photonic-shortcode-form input[type="text"], #photonic-shortcode-form select').change(function(event) {
				var comboValues = $('#photonic-shortcode-form').serializeArray();
				var newValues = [];
				var len = comboValues.length;

				$(comboValues).each(function(i, obj) {
					var individual = this;
					if (individual['name'].trim() != 'photonic-shortcode' && individual['name'].trim() != 'photonic-submit' &&
						individual['name'].trim() != 'photonic-cancel' && individual['value'].trim() != '') {
						newValues.push(individual['name'] + "='" + photonicAdminHtmlEncode(decodeURIComponent(individual['value'].trim())) + "'");
					}
				});

				var shortcode = "[gallery type='<?php echo $selected_tab; ?>' ";
				len = newValues.length;
				$(newValues).each(function() {
					shortcode += this + ' ';
				});
				shortcode += ']';

				$('#photonic-preview').text(shortcode);
				$('#photonic-shortcode').val(shortcode);
			});
			$('#photonic-shortcode-form select').change();
		});
	</script>
<?php
require_once(plugin_dir_path(__FILE__).'/../photonic-form.php');

$tab_list = '';
$tab_fields = '';
$field_list = array();
$prelude = '';
foreach ($fields as $tab => $field_group) {
	$tab_list .= "<li><a href='".esc_url(add_query_arg(array('photonic-tab' => $tab)))."' class='".($tab == $selected_tab ? 'current' : '')."'>".esc_attr($field_group['name'])."</a> | </li>";
	if ($tab == $selected_tab) {
		$field_list = $field_group['fields'];
		$prelude = isset($field_group['prelude']) ? $field_group['prelude'] : '';
	}
}

echo "<form id='photonic-shortcode-form' method='post' action=''>";
echo "<ul class='subsubsub'>";
if (strlen($tab_list) > 8) {
	$tab_list = substr($tab_list, 0, -8);
}
echo $tab_list;
echo "</ul>";

if (!empty($prelude)) {
	echo "<p class='prelude'>"; print_r($prelude); echo "</p>";
}

echo "<table class='photonic-form'>";
foreach ($field_list as $field) {
	echo "<tr>";
	echo "<th scope='row'>{$field['name']} ".(isset($field['req']) && $field['req'] ? '(*)' : '')." </th>";
	switch ($field['type']) {
		case 'text':
			echo "<td><input type='text' name='{$field['id']}' value='".(isset($field['std']) ? $field['std'] : '')."'/></td>";
			continue;
		case 'select':
			echo "<td><select name='{$field['id']}'>";
			$default = isset($field['std']) ? $field['std'] : '';
			foreach ($field['options'] as $option_name => $option_value) {
				if ($option_name == $default) {
					$selected = 'selected';
				}
				else {
					$selected = '';
				}
				echo "<option value='$option_name' $selected>".esc_attr($option_value)."</option>";
			}
			echo "</select></td>";
			continue;
		case 'raw':
			echo "<td>".$field['std']."</td>";
			continue;
	}
	echo "<td class='hint'>".(isset($field['hint']) ? $field['hint'] : '')."</td>";
	echo "</tr>";
}
echo "</table>";

echo "<div class='preview'>";
echo "<script type='text/javascript'></script>";
echo "<h4>".__('Shortcode preview', 'photonic')."</h4>";
echo "<pre class='html' id='photonic-preview' name='photonic-preview'></pre>";
echo "<input type='hidden' id='photonic-shortcode' name='photonic-shortcode' />";
echo "</div>";

echo "<div class='button-panel'>";
echo get_submit_button(__('Insert into post', 'photonic'), 'primary', 'photonic-submit', false);
echo get_submit_button(__('Cancel', 'photonic'), 'delete', 'photonic-cancel', false);
echo "</div>";
echo "</form>";
