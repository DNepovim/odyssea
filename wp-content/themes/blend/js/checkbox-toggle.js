/* 
By James R Whitehead of InterconnectIT.com

Simple function to seach a form for anything with a class you specify and change its class to disabled and change form fields to status disabled.
Usage:  ICITCheckboxTogggle(  {the ID of the form we are to search} ,  {the ID of the check box to toggle on} ,  {the class we are to search for and toggle} ) ;
eg.

<form id="TheForm">
	<input type="checkbox" id="TheCheckBox" onclick="ICITCheckboxTogggle("TheForm","TheCheckBox","ToggleSwitch");" />
	<label for="TheToggledItem" class="ToggleSwitch">Some text</label><input type="text" id="TheToggledItem" class="ToggleSwitch"/>
	<script language="JavaScript" type="text/javascript">
		ICITCheckboxTogggle("TheForm","TheCheckBox","ToggleSwitch");
	</script>
</form>
*/ 

ICITCheckboxTogggle = function (formName,checkBox,searchClassName,invert) {
	if (document.getElementById) {
		TheForm = document.getElementById(formName);
		AllTags = TheForm.getElementsByTagName("*");
		checkBoxStatus = document.getElementById(checkBox);
		if (invert != true) {
			if (checkBoxStatus.checked && !checkBoxStatus.disabled){
				for (i=0; i < AllTags.length; i++) {
					node = AllTags[i];
					if (node.className.indexOf(searchClassName) >= 0) {
						if (node.disabled) node.disabled=false;
						node.className=node.className.replace(" disabled", "");
					}
				}
			} else {
				for (i=0; i < AllTags.length; i++) {
					node = AllTags[i];
					if (node.className.indexOf(searchClassName) >= 0) {
						if (!node.disabled) node.disabled=true;
						node.className+=" disabled";
					}
				}
			}
		} else {
			if (checkBoxStatus.checked && !checkBoxStatus.disabled){
				for (i=0; i < AllTags.length; i++) {
					node = AllTags[i];
					if (node.className.indexOf(searchClassName) >= 0) {
						if (!node.disabled) node.disabled=true;
						node.className+=" disabled";
					}
				}
			} else {
				for (i=0; i < AllTags.length; i++) {
					node = AllTags[i];
					if (node.className.indexOf(searchClassName) >= 0) {
						if (node.disabled) node.disabled=false;
						node.className=node.className.replace(" disabled", "");
					}
				}
			}
		}
	}
}