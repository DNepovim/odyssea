<?php
/*
Template Name: Contact form
*/
get_header();?>
<div id="content" class="<?php echo sidebar_position();?>">
	<div class="min-height-prop"></div>
	<div id="posts"><?php 
	if (have_posts()) {
		while (have_posts()) {
			the_post();?>
			<div class="post">
			<?php include( TEMPLATEPATH . '/thecontent.php');?>
			<?php $contact = new icit_contact_form();?>
			</div><?php
		}
	} else {?>
		<div class="post search">
		<h2>Sorry no pages matching your request were found.</h2>
		<p>Either hit the back button on your browser or use this search form to find what you where looking for.</p>
		<?php include (TEMPLATEPATH . "/searchform.php");?>
		</div><?php
	}
		global $wp_query; /* To avoid orphaned tags showing up when there is no need for post_nav_link() the following checks for the need of it. */
		if ( $wp_query->max_num_pages > 1 && !is_singular()) {?>
			<div id="page-navigation">
				<div class="previous_posts"><?php previous_posts_link("&laquo; Předchozí stránka");?></div>
				<div class="next_posts"><?php next_posts_link("Další stránka &raquo;");?></div>
			</div><?php
		}?>
	</div><?php
	if (sidebar_position() != "sidebar-off") get_sidebar(); // No point even calling the sidebar if its not wanted. ?>
	<span class="clear"></span>
</div>
<?php get_footer();

// From here on down you will find the code for the contact form.

class icit_contact_form {
	function icit_contact_form () {
		global $post;
		/* Adding a new field to the contact form is relatively simple.
		Just add a new line to the array below of the following format:
		unique_name => array(
			"description" => "{The text that will show as the label to the field}",
			"type" => "{checkbox|text|textarea|submit|hidden|password}",
			"vital" => {true|false} // Is this field compulsory.
		);
		*/
		$this->contact_form_fields = array(
			'icit_persoanl_para' =>	array('description' => 'Tvé kontaktní informace', 'type' => 'paragraph'),
			'icit_name' => 			array('description' => 'Jméno', 'type' => 'text', 'vital' => true),
			'icit_email' => 		array('description' => 'e-mail', 'type' => 'text', 'vital' => true, 'validate' => 'email'),
			'icit_text' =>			array('description' => 'Tvůj vzkaz', 'type' => 'textarea'),
			'icit_submit_3' =>		array('description' => 'Odešli', 'type' => 'submit')
		);
		$to = get_post_meta($post->ID,"to",true);
		$to = explode ("\n",$to); // If there is more than one line we'll just take the first. In future I plan to make this to you can have multiple address lines, just not now.
		$to = $to[0];
		$to = 'Zampachova.Hana@seznam.cz';
		if ($to != "") { //If the post meta data TO is set then we'll try and not use the admin email. Otherwise we have to.
			if ($this->valid_email($to)) { // If to is set to an email address lets split the name off the start and set email to address.
				$name = spliti("@",$to);
				$this->recipient = array('email' => $to, 'name' => $name[0]);
			} elseif(is_numeric($to)) { // If it's a number we'll presume it's a user ID and get the info from the user profile. If the user ID returns nothing use the admin email.
				$user = get_userdata($to);
				if ($user) {
					$this->recipient = array('email' => $user->user_email, 'name' => $user->display_name);
				} else {
					$this->recipient = array("email" => get_bloginfo("admin_email"), "name" => "Vlastník stránky");
				}
			}
		} else {
			$this->recipient = array("email" => get_bloginfo("admin_email"), "name" => "Vlastník stránky");
		}

                if (!$_POST['submitted']) {
			$this->html();
		} else {
			// Check data for errors and setup local copy of the post variable
			unset($_POST['submit']);
			unset($this->postresults);
			unset($this->errors);
			foreach ($_POST as $key => $post_var) {
				if (!empty($post_var))
					$this->postresults[$key] = stripslashes($post_var);

				if ($this->contact_form_fields[$key]['vital'] && empty($this->postresults[$key])) $this->errors[$key] = true;
				if ($this->contact_form_fields[$key]['validate'] == 'email' && !$this->valid_email($this->postresults[$key])) $this->errors[$key] = true;
			}
			// Send the message an regenerate the HTML using the return value to generate a message or two.
			$this->html($this->send());
		}
	}
	
	function send() {
		if (count($this->errors) == 0) {
			// Format the conent of the mail.
			$email_subject = "[".get_bloginfo('name')."] Vzkaz odeslaný z webu.";
			$email_headers = "";
			if (!empty($this->contact_form_fields['icit_email'])) $email_headers .= "Odpovědět:".$this->postresults['icit_email']."\n";
			$email_headers .= "Od: ".get_bloginfo('name')." <".get_settings('admin_email').">\n";
			$email_headers .= "MIME-Version: 1.0\n";
			$email_headers .= "Content-Type: text/plain; charset=".get_option('blog_charset')."; format=flowed \n";
			$email_content = "Následující informace byly odeslány '".get_bloginfo('name')."' web ".date('l dS \of F Y h:i:s A')."\n";
			$email_content .= "Tento e-mail pochází ze stránky ".$_SERVER["HTTP_REFERER"]." a odesílatel má následující IP adresu: ".$_SERVER["REMOTE_ADDR"]."\n\n";
			foreach (array_keys($this->contact_form_fields) as $key) {
				if ($this->postresults[$key] != '' && !in_array(strtolower($this->contact_form_fields[$key]['type']), array('checkbox', 'radio'))) {
					$email_content .= $this->contact_form_fields[$key]['description'].":\n\t".$this->postresults[$key]."\n\n";
				} elseif (in_array(strtolower($this->contact_form_fields[$key]['type']), array('checkbox', 'radio')) && $this->postresults[$key] == 'on') {
					$email_content .= $this->contact_form_fields[$key]['description']." = Yes\n";
				}
			}
			// Send the mail and return.
			if (@wp_mail($this->recipient['email'],$email_subject,$email_content,$email_headers )) {
				return(true);
			} else {
				$this->errors['mail_server_problem'] = "V rodné Ithace mají problémy s poštou.\n Zkus poštovního holuba, my se zatím pokusíme problém opravit.";
				return(false);
			}
		} else {
			return(false);
		}
	}
	
	function valid_email($email) {
		return (eregi ("^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$", $email));
	}
	
	function html($success = null) {
		if ($success) { ?>
			<h3>Message sent successfully</h3>
			<p>Thank you, <br/>Someone will get back to you as soon as they can.</p><?php
			return (true);
		} elseif ($success === false && $this->errors['mail_server_problem'] == '') { ?>
			<h3>Oops.</h3>
			<p>Please check any field that is marked in red and try again.</p><?php
		} elseif ($success === false && !$this->errors['mail_server_problem'] == '') { ?>
			<?php echo $this->errors['mail_server_problem'];
		}?>

		<form method="post" action="<?php echo $post_action_url;?>" id="contactform"><?php 
		foreach (array_keys($this->contact_form_fields) as $key) { // Assemble the html based on the content of the array.?> 
			<div class="contactformrow" id="div_<?php echo $key?>"><?php 
				if ($this->contact_form_fields[$key]['description'] && in_array(strtolower($this->contact_form_fields[$key]['type']),array('checkbox', 'hidden', 'password', 'radio', 'text','textarea'))) { 
					?><label for="<?php echo $key; ?>" id="label_<?php echo $key; ?>" <?php if ($this->errors[$key] == true) echo 'style="color: red;" '?>><?php echo $this->contact_form_fields[$key]['description'].($this->contact_form_fields[$key]['vital'] ? " <sup style=\"color: red;\">required</sup>" : "");?></label><?php
				} elseif ($this->contact_form_fields[$key]['type'] == 'paragraph') {
					echo '<p id="'.$key.'" class="paragraph">'.$this->contact_form_fields[$key]['description'].'</p>';
				} elseif ($this->contact_form_fields[$key]['type'] == 'submit') {?>
				<input type="submit" class="<?php echo $this->contact_form_fields[$key]['type']; ?>" id="<?php echo $key; ?>" value="<?php echo $this->contact_form_fields[$key]['description']; ?>"/><?php
				}
				if (in_array(strtolower($this->contact_form_fields[$key]['type']), array('checkbox', 'hidden', 'password', 'radio', 'text')) ) { 
				?><input class="<?php echo $this->contact_form_fields[$key]['type'];?>" name="<?php echo $key; ?>" id="<?php echo $key; ?>" type="<?php echo $this->contact_form_fields[$key]['type'];?>" <?php
					if ($this->postresults[$key] && in_array(strtolower($this->contact_form_fields[$key]['type']), array('hidden', 'password', 'text'))) {
						echo 'value="'.$this->postresults[$key].'" ';
					} elseif (!empty($this->postresults[$key]) && in_array(strtolower($this->contact_form_fields[$key]['type']), array('checkbox', 'radio'))) {
						echo 'checked="checked" ';
					}
					if ($this->errors[$key] == true) echo 'style="border-color: red;" ';
					?> /><?php
				} elseif (strtolower($this->contact_form_fields[$key]['type']) == 'textarea') { 
				?><textarea class="textarea" name="<?php echo $key; ?>" id="<?php echo $key; ?>" rows="10" cols="36" <?php if ($this->errors[$key] == true) echo 'style="border-color: red;" ';?>><?php echo $this->postresults[$key]; ?></textarea><?php
				} 
				?><a name="link_<?php echo $key; ?>" style="clear:both;display:block"></a></div>
			<?php
		}
		?><input type="hidden" name="submitted" value="true" />
	</form><?php
	}
}
?>