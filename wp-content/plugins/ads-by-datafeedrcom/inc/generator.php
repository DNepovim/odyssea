<p>Use the form below to create an embed code for your site.</p>

<div class="dfads_form_css">

	<fieldset class="radio">
		<label for="code_type">What type of code do you need?</label>
		<ul>
			<li><label><input type="radio" name="code_type" class="code_type" value="sc" /> Shortcode <span>(Good for embedding in posts, pages and Text Widgets.)</span></label></li>
			<li><label><input type="radio" name="code_type" class="code_type" value="php" /> PHP <span>(Good for adding to your theme's template files.)</span></label></li>
		</ul>
	</fieldset>
	
	<div id="code_area_wrapper">
		<div id="code_area">
			<span id="code_begin"></span><span id="code_middle"></span><span id="code_end"></span>
		</div>
	</div>

	<form id="dfads_build_qs">
		
		<fieldset>
			<label for="dfads_groups">Groups</label>
			<?php wp_dropdown_categories( 'show_option_none=All&taxonomy=dfads_group&hide_empty=0&name=groups&id=dfads_groups&multiple=multiple&class=notpostform' ); ?> 
			<p class="form-help">Select the group (or groups) from which to select ads from. (Default: Ads from all groups)</p>
		</fieldset>
		
		<fieldset>
			<label for="dfads_limit">Number of Ads</label>
			<input name="limit" type="text" id="dfads_limit" class="form-text" value="" style="width:5em;" />
			<p class="form-help">The number of ads to show. (Default: All)</p>
		</fieldset>

		<fieldset>
			<label for="dfads_orderby">Order By</label>
			<select name="orderby" id="dfads_orderby">
				<option value=""><option>
				<?php
				$orderbys = DFADS::orderby_array();
				foreach ($orderbys as $k => $v) {
					echo '<option value="'.$k.'">'.$v['name'].'</option>';
				}
				?>
			</select>
			<p class="form-help">Choose how ads are ordered. (Default: Random)</p>
		</fieldset>

		<fieldset id="dfads_order_field">
			<label for="dfads_order">Order</label>
			<select name="order" id="dfads_order">
				<option value=""><option>
				<option value="ASC">Ascending</option>
				<option value="DESC">Descending</option>
			</select>
			<p class="form-help">Choose the sort order. (Default: ASC)</p>
		</fieldset>
	
		<fieldset>
			<label for="dfrads_container_html">Container HTML Tag</label>
			<input name="container_html" type="text" id="dfrads_container_html" class="form-text" value="" />
			<p class="form-help">The wrapping container's HTML tag. (Default: div)<br />Example: div, p, span, ul, ol, etc...<br />Use <em>none</em> to prevent this tag from being rendered.<br />Do not include these characters: &lt; &gt; /</p>
		</fieldset>
	
		<fieldset>
			<label for="dfrads_container_id">Container CSS ID</label>
			<input name="container_id" type="text" id="dfrads_container_id" class="form-text" value="" />
			<p class="form-help">The CSS ID of the wrapping HTML container.<br />Example: ads_150x150, top-ads, etc...<br />Do not include the hash symbol (#)</p>
		</fieldset>
	
		<fieldset>
			<label for="dfrads_container_class">Container CSS Class</label>
			<input name="container_class" type="text" id="dfrads_container_class" class="form-text" value="" />
			<p class="form-help">The CSS class of the wrapping HTML container.<br />Example: ads, banners, etc...<br />Do not include a period (.)</p>
		</fieldset>
	
		<fieldset>
			<label for="dfrads_ad_html">Ad HTML Tag</label>
			<input name="ad_html" type="text" id="dfrads_ad_html" class="form-text" value="" />
			<p class="form-help">The HTML tag wrapping each ad. (Default: div)<br />Example: div, span, ul, ol, etc...<br />Use <em>none</em> to prevent this tag from being rendered.<br />Do not include these characters: &lt; &gt; /</p>
		</fieldset>
	
		<fieldset>
			<label for="dfrads_ad_class">Ad CSS Class</label>
			<input name="ad_class" type="text" id="dfrads_ad_class" class="form-text" value="" />
			<p class="form-help">The CSS class of the tag wrapping each ad.<br />Example: ad, banner, etc...<br />Do not include a period (.)</p>
		</fieldset>
	
		<fieldset>
			<label for="dfads_callback_function">Callback Function (<em>advanced</em>)</label>
			<input name="callback_function" type="text" id="dfads_callback_function" class="form-text" value="" />
			<p class="form-help">A PHP function you've defined to handle the output of these ads. (<a href="<?php echo DFADS_DOCS_URL; ?>#output" target="_blank">read more</a>)</p>
		</fieldset>
	
		<fieldset class="radio">
			<label for="return_javascript">Return JavaScript (<em>advanced</em>)</label>
			<ul>
				<li><label><input type="radio" name="return_javascript" value="1" /> Yes</label></li>
				<li><label><input type="radio" name="return_javascript" value="0" /> No</label></li>
			</ul>
			<p class="form-help">Return JavaScript. This is useful if you're using a caching plugin. This will return ad content ensuring that impressions are counted and ads appear randomly if order by is set to random. Default is "No". Impressions will not be counted nor will 'random' if your site's visitor has disabled JavaScript in their browser.</p>
		</fieldset>
		
	</form>
</div>
