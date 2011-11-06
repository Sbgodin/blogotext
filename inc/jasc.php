<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***


function decompte_sleep() {
	if (!isset($GLOBALS['connexion_delai']) or $GLOBALS['connexion_delai'] != '0') {
		echo '<div id="msgcompt" style="text-align: center; color:white;">';
		echo $GLOBALS['lang']['patientez'];
		echo ' <span id="decompte">&nbsp;</span> ';
		echo $GLOBALS['lang']['secondes'];
		echo ' '.$GLOBALS['lang']['note_delay_desactivable'];
		echo '.</div>';
		echo '
<script type="text/javascript">
	chrono=10;
	function decompte() {
		window.document.getElementById("msgcompt").style.color = "black";
		if (chrono > 0) {
			chrono--;
			window.document.getElementById("decompte").innerHTML = chrono;
			setTimeout(decompte,1000);
		}
	}function resize(id, dht) {
	var elem = document.getElementById(id);
	var ht = elem.offsetHeight;
	size = Number(ht)+Number(dht);
	elem.style.height = size+"px";
	return false;
}

</script>
';
	}
}

function js_reload_captcha($a) {
	$sc = '
function new_freecap() {
	if(document.getElementById) {
		thesrc = document.getElementById("freecap").src;
		thesrc = thesrc.substring(0,thesrc.lastIndexOf(".")+4);
		document.getElementById("freecap").src = thesrc+"?"+Math.round(Math.random()*100000);
	} else {
		alert("Sorry, cannot autoreload freeCap image\nSubmit the form and a new freeCap will be loaded");
	}
}';

	if ($a == 1) {
		$sc = '<script type="text/javascript">'."\n".$sc."\n".'</script>'."\n";
	}
	return $sc;
}

function js_resize($a) {
	$sc = '
function resize(id, dht) {
	var elem = document.getElementById(id);
	var ht = elem.offsetHeight;
	size = Number(ht)+Number(dht);
	elem.style.height = size+"px";
	return false;
}';

	if ($a == 1) {
		$sc = '<script type="text/javascript">'."\n".$sc."\n".'</script>'."\n";
	}
	return $sc;
}

function js_inserttag($a) {
	$sc = '
function insertTag(startTag, endTag, tag) {
	var field = document.getElementById(tag);
	var scroll = field.scrollTop;
	field.focus();
	if (window.ActiveXObject) {
		var textRange = document.selection.createRange();
		var currentSelection = textRange.text;
		textRange.text = startTag + currentSelection + endTag;
		textRange.moveStart("character", -endTag.length - currentSelection.length);
		textRange.moveEnd("character", -endTag.length);
		textRange.select();
	} else {
		var startSelection   = field.value.substring(0, field.selectionStart);
		var currentSelection = field.value.substring(field.selectionStart, field.selectionEnd);
		var endSelection     = field.value.substring(field.selectionEnd);
		if (currentSelection == "") { currentSelection = "TEXT"; }
		field.value = startSelection + startTag + currentSelection + endTag + endSelection;
		field.focus();
		field.setSelectionRange(startSelection.length + startTag.length, startSelection.length + startTag.length + currentSelection.length);
	}
	field.scrollTop = scroll;
}';

	if ($a == 1) {
		$sc = '<script type="text/javascript">'."\n".$sc."\n".'</script>'."\n";
	}
	return $sc;
}

?>
