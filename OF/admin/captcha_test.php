<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(E_ALL);
require_once '../inc/inc.php';
session_start() ;

if (!empty($_SERVER['REMOTE_ADDR'])) {
	$ip = $_SERVER['REMOTE_ADDR'];
}

if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) or (!isset($_SESSION['antivol'])) or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip)) or (!isset($_SESSION['timestamp'])) or ($_SESSION['timestamp'] < time()-1800)) {
	header('Location: logout.php');
	exit;
}
$_SESSION['timestamp'] = time();

if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form_preferences()) {
		afficher_form($erreurs_form);
	} else {        		
		if ( (fichier_user() == 'TRUE') AND (fichier_prefs() == 'TRUE') ) {
		redirection($_SERVER['PHP_SELF'].'?msg=confirm_prefs_maj');
		}
	}
	} else {	
	afficher_form();
}

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
legend('Captcha', 'legend-config');
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
input_valider();
echo '</fieldset>';
echo '</form>'."\n";

footer();
}

?>
