<?php
# *** LICENSE ***
# This file is part of BlogoText.
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

//error_reporting(E_ALL);
require_once '../inc/inc.php';

check_session();

afficher_form();

function afficher_form($erreurs = '') {
$titre_page= $GLOBALS['lang']['preferences'];
afficher_top($titre_page);
afficher_msg();
echo '<div id="top">';
echo '<ul id="nav">';

afficher_menu('preferences.php');

echo '</ul>';
echo '</div>';

echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";
if(!empty($_SESSION['freecap_word_hash']) && !empty($_POST['word']))
{
	if($_SESSION['hash_func'](strtolower($_POST['word']))==$_SESSION['freecap_word_hash'])
	{
		$_SESSION['freecap_attempts'] = 0;
		$_SESSION['freecap_word_hash'] = false;
		$word_ok = "yes";
	} else {
		$word_ok = "no";
	}
} else {
	$word_ok = false;
}
echo '<script type="text/javascript">
<!--
function new_freecap()
{
	if(document.getElementById)
	{
		thesrc = document.getElementById("freecap").src;
		thesrc = thesrc.substring(0,thesrc.lastIndexOf(".")+4);
		document.getElementById("freecap").src = thesrc+"?"+Math.round(Math.random()*100000);
	} else {
		alert("Sorry, cannot autoreload freeCap image\nSubmit the form and a new freeCap will be loaded");
	}
}
//-->
</script>';

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
echo '<fieldset class="pref">';
echo legend('Captcha', 'legend-config');
echo '<p>';
if($word_ok!==false)
{
	if($word_ok=="yes")
	{
		echo '<b style="color: green;">you got the word correct, rock on.</b>';
	} else {
		echo '<b style="color: red;">sorry, that\'s not the right word, try again.</b>';
	}
}
echo '</p>';
echo '<p><img src="../inc/freecap/freecap.php" id="freecap" alt="freecap"/></p>'."\n";
echo '<p>If you can\'t read the word, <a href="#" onclick="this.blur();new_freecap();return false;">click here to change image</a></p>'."\n";
echo '<p>word above:<input type="text" name="word" /></p>'."\n";
echo input_valider();
echo '</fieldset>';
echo '</form>'."\n";

footer();
}

?>
