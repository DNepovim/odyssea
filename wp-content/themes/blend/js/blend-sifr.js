//<![CDATA[
var header_text 			= sifrSettings.header_text,
	header_back 			= sifrSettings.header_back,
	content_text 			= sifrSettings.content_text,
	content_link1 			= sifrSettings.content_link1,
	content_back 			= sifrSettings.content_back,
	content_highlight		= sifrSettings.content_highlight,
	content_link1_hover 	= sifrSettings.content_link1_hover,
	footer1_text 			= sifrSettings.footer1_text,
	footer1_back 			= sifrSettings.footer1_back,
	sIFRtitle 				= sifrSettings.sIFRtitle,
	sIFRtag 				= sifrSettings.sIFRtag,
	sIFRwidgettitle 		= sifrSettings.sIFRwidgettitle,
	sIFRposttitle 			= sifrSettings.sIFRposttitle,
	sIFRFooter 				= sifrSettings.sIFRFooter;

	if(typeof sIFR == "function"){
		if (sIFRtitle)
			sIFR.replaceElement("h1#main-page-title", named({sFlashSrc:sIFRtitle, sColor:header_text, sLinkColor:header_text, sBgColor:header_back, sHoverColor:header_text, sFlashVars:"textalign=left", sWmode:"opaque"}));

		if (sIFRtag)
			sIFR.replaceElement("h2#tag-line", named({sFlashSrc:sIFRtag, sColor:header_text, sLinkColor:"#333333", sBgColor:header_back, sHoverColor:"#CCCCCC", sFlashVars:"textalign=left", sWmode:"opaque"}));

		if (sIFRwidgettitle)
			sIFR.replaceElement("#content .widgettitle h3", named({sFlashSrc:sIFRwidgettitle, sColor:content_text, sLinkColor:content_link1, sBgColor:content_back, sHoverColor:content_link1_hover, sFlashVars:"textalign=left"}));

		if (sIFRposttitle)
			sIFR.replaceElement(".post-title h2", named({sFlashSrc:sIFRposttitle, sColor:content_text, sLinkColor:content_link1, sBgColor:content_highlight, sHoverColor:content_link1_hover, sFlashVars:"textalign=left"}));

		if (sIFRFooter)
			sIFR.replaceElement("#content-footer .widgettitle h3", named({sFlashSrc:sIFRFooter, sColor:footer1_text, sLinkColor:content_link1, sBgColor:footer1_back, sHoverColor:content_link1_hover, sFlashVars:"textalign=left"}));

	}

//]]>
