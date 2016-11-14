function chPostAvatar() {
	var pAvaImg  = document.getElementById('postuserpic').value;
	if (pAvaImg == 'no_avatar.png')
		document.getElementById('postavatar').src = gkl_postavatar_text.avatar_img + '/no_avatar.png';
	else
		document.getElementById('postavatar').src = gkl_postavatar_text.avatar_url + pAvaImg;
	
	return true;
}

function nextPostAvatar() {	
	var list_count = document.getElementById('postuserpic').length - 1;
	
	if (document.getElementById('postuserpic').selectedIndex < list_count) {
		document.getElementById('postuserpic').selectedIndex++;
	} else {
		document.getElementById('postuserpic').selectedIndex = 1;
	}
	
	if ( document.getElementById('postuserpic').options[document.getElementById('postuserpic').selectedIndex].text == gkl_postavatar_text.noavatar_msg )
		document.getElementById('postavatar').src = gkl_postavatar_text.avatar_img + '/no_avatar.png';
	else
		document.getElementById('postavatar').src = gkl_postavatar_text.avatar_url + document.getElementById('postuserpic').options[document.getElementById('postuserpic').selectedIndex].text;

}

function prevPostAvatar() {
	var list_count = document.getElementById('postuserpic').length - 1;
	if (document.getElementById('postuserpic').selectedIndex > 1) {
		document.getElementById('postuserpic').selectedIndex--;
	} else {
		document.getElementById('postuserpic').selectedIndex = list_count;
	}
	if ( document.getElementById('postuserpic').options[document.getElementById('postuserpic').selectedIndex].text == 'No Avatar selected' )
		document.getElementById('postavatar').src = gkl_postavatar_text.avatar_img + '/no_avatar.png';
	else
		document.getElementById('postavatar').src = gkl_postavatar_text.avatar_url + document.getElementById('postuserpic').options[document.getElementById('postuserpic').selectedIndex].text;	
}