<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
error_reporting(E_ALL);
if ( !file_exists('../config/user.php') or !file_exists('../config/prefs.php') or !file_exists('../config/tags.php') ) {
	header('Location: install.php');
}

// LOG
if (isset($_POST['nom_utilisateur'])) {

	$fichier = "xauthlog.php";
	$dest = fopen("$fichier", "a+"); 
	$ip = $_SERVER["REMOTE_ADDR"];
	$browser = $_SERVER['HTTP_USER_AGENT'];
	$curent_time = date('d m Y')." / ".date('H\:i\:s');


	$nom = $_POST['nom_utilisateur'];

fputs($dest, "$curent_time : $ip ;\tlogin :\t$nom avec ".$browser."\n");
fclose($dest);
}

// end log
require_once '../inc/inc.php';
session_start() ;

if (isset($_POST['_verif_envoi'])) {
	
	if ((!isset($GLOBALS['connexion_delai']) or $GLOBALS['connexion_delai'] != '0')) {
		sleep(10);
	}
	session_regenerate_id();
	header('Location: index.php');
}

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$ip = $_SERVER['REMOTE_ADDR'];
}
$_SESSION['antivol'] = md5($_SERVER['HTTP_USER_AGENT'].$ip);
$_SESSION['timestamp'] = time();

require_once '../inc/inc.php';

afficher_top('Identification');
echo '<div id="axe">'."\n";
decompte_sleep();
js_reload_captcha();

echo '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');

if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form()) {
		afficher_form($erreurs_form);
	} else {
		$_SESSION['nom_utilisateur'] = $_POST['nom_utilisateur'].ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']);
	}
} else {
	afficher_form();
}

function afficher_form($erreur = '') {
	if ($erreur) {
		erreur($erreur);
	}
	echo	'<form method="post" action="'.$_SERVER['PHP_SELF'].'" onsubmit="return decompte()">'."\n";
	echo	'<div id="auth">'."\n";
	echo	'<p><label for="nom_utilisateur">'.$GLOBALS['lang']['label_identifiant'].'</label>'."\n";
	echo	'<input type="text" id="nom_utilisateur" name="nom_utilisateur" value="" /></p>'."\n";
	echo	'<p><label for="mot_de_passe">'.$GLOBALS['lang']['label_motdepasse'].'</label>';
	echo	'<input type="password" id="mot_de_passe" name="mot_de_passe" value="" /></p>'."\n";

	if (isset($GLOBALS['connexion_captcha']) and ($GLOBALS['connexion_captcha'] == "1")) {
		echo	'<p><label for="word">'.$GLOBALS['lang']['label_word_captcha'].'</label>';
		echo	'<input type="text" id="word" name="word" value="" /></p>'."\n";
		echo	'<p><a href="#" onClick="this.blur();new_freecap();return false;" title="'.$GLOBALS['lang']['label_changer_captcha'].'"><img src="../inc/freecap/freecap.php" id="freecap"></a></p>'."\n";
	}
		echo	'<input class="inpauth" type="submit" name="submit" value="'.$GLOBALS['lang']['connexion'].'" />';
		echo	'<input type="hidden" name="_verif_envoi" value="1" />';
		echo	'</div>'."\n";
		echo	'</form>';
}


function valider_form() {
	$mot_de_passe_ok = $GLOBALS['mdp'].$GLOBALS['identifiant'];
	$mot_de_passe_essai = ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']).$GLOBALS['identifiant'];
	if ( ($mot_de_passe_essai !=  $mot_de_passe_ok) or ($_POST['nom_utilisateur'] != $GLOBALS['identifiant'])) {
		$erreur = $GLOBALS['lang']['err_connexion'];
		return $erreur;
	}
	if (isset($GLOBALS['connexion_captcha']) and ($GLOBALS['connexion_captcha'] == "1")) {
		if ((empty($_SESSION['freecap_word_hash'])) or (empty($_POST['word'])) or ($_SESSION['hash_func'](strtolower($_POST['word'])) != $_SESSION['freecap_word_hash']) ) {
			$erreur = $GLOBALS['lang']['err_connexion'];
			return $erreur;
			$word_ok = "no";
		}
		if($_SESSION['hash_func'](strtolower($_POST['word']))==$_SESSION['freecap_word_hash']) {
			// reset freeCap session vars
			$_SESSION['freecap_attempts'] = 0;
			$_SESSION['freecap_word_hash'] = false;
		}
	}
}

footer();
?>
