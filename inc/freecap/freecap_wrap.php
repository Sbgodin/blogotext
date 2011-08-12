<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

session_start();

if(!empty($_SESSION['freecap_word_hash']) && !empty($_POST['word']))
{
	// all freeCap words are lowercase.
	// font #4 looks uppercase, but trust me, it's not...
	if($_SESSION['hash_func'](strtolower($_POST['word']))==$_SESSION['freecap_word_hash'])
	{
		// reset freeCap session vars
		// cannot stress enough how important it is to do this
		// defeats re-use of known image with spoofed session id
		$_SESSION['freecap_attempts'] = 0;
		$_SESSION['freecap_word_hash'] = false;


		// now process form


		// now go somewhere else
		// header("Location: somewhere.php");
		$word_ok = "yes";
	} else {
		$word_ok = "no";
	}
} else {
	$word_ok = false;
}
?>
<html>
<head>
<script language="javascript">
<!--
function new_freecap()
{
	// loads new freeCap image
	if(document.getElementById)
	{
		// extract image name from image source (i.e. cut off ?randomness)
		thesrc = document.getElementById("freecap").src;
		thesrc = thesrc.substring(0,thesrc.lastIndexOf(".")+4);
		// add ?(random) to prevent browser/isp caching
		document.getElementById("freecap").src = thesrc+"?"+Math.round(Math.random()*100000);
	} else {
		alert("Sorry, cannot autoreload freeCap image\nSubmit the form and a new freeCap will be loaded");
	}
}
//-->
</script>
<style type="text/css">
	body{
		font-family: verdana;
		font-size: 14px;
		background: #CCC;
	}
	td{
		font-family: verdana;
		font-size: 10px;
	}
</style>
</head>
<body>
<b>freeCap v1.4 - <a href="http://www.puremango.co.uk" target="_blank">www.puremango.co.uk</a></b><br /><br />
<?
if($word_ok!==false)
{
	if($word_ok=="yes")
	{
		echo "you got the word correct, rock on.<br />";
	} else {
		echo "sorry, that's not the right word, try again.<br />";
	}
}
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post"><? echo $_SESSION['freecap_word_hash']; ?>
<table cellpadding="0" cellspacing="0">
<tr><td colspan="2"><img src="freecap.php" id="freecap"></td></tr>
<tr><td colspan="2">If you can't read the word, <a href="#" onClick="this.blur();new_freecap();return false;">click here</a></td></tr>
<tr><td>word above:</td><td><input type="text" name="word"></td></tr>
<tr><td colspan="2"><input type="submit" value="submit"></td></tr>
</table><br /><br />
</form>
</body>
</html>
