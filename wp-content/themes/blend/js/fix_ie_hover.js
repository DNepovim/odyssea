sfHover = function() {
	if (document.all&&document.getElementById) {
		allLIs  = document.getElementById("pages_list").getElementsByTagName("li");
		for (i=0; i < allLIs.length; i++) {
			node = allLIs[i];
			if (node.nodeName=="LI") {
				node.onmouseover=function() { this.className+=" over";	}
				node.onmouseout=function() { this.className=this.className.replace(" over", "");}
			}
		}
	}
}

if (window.attachEvent) window.attachEvent("onload", sfHover);