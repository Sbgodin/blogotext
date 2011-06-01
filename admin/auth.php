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

//error_reporting(E_ALL);
if ( !file_exists('../config/user.php') or !file_exists('../config/prefs.php') or !file_exists('../config/tags.php') ) {
	header('Location: install.php');
}

// LOG
if (isset($_POST['nom_utilisateur'])) {

	$fichier = "xauthlog.php";
	$dest = fopen("$fichier", "a+"); 
	$ip = $_SERVER["REMOTE_ADDR"];				// IPs
   if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip .= '_'.$_SERVER['HTTP_X_FORWARDED_FOR']; }
   if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip .= '_'.$_SERVER['HTTP_CLIENT_IP']; }

	$browser = $_SERVER['HTTP_USER_AGENT'];	// navigateur
	$origine = $_SERVER['HTTP_REFERER'];		// url d'origine
	$curent_time = date('r');						// heure selon RFC 2822
	$timestamp = date('U');							// timestamp : nombre de secondes ecoulees depuis le 01/01/70.

	$nom = $_POST['nom_utilisateur'];			// nom de login tente.

fputs($dest, "DATE : $curent_time (TIMESTAMP: $timestamp ) \n\t\t IP: $ip \n\t\t LOGIN: $nom \n\t\t ORIGINE: $origine \n\t\t BROWSER: $browser\n ---------------------------------------------------\n");
fclose($dest);
}

// end log
require_once '../inc/inc.php';
session_start() ;

if (isset($_POST['_verif_envoi'])) {
	
	if ((!isset($GLOBALS['connexion_delai']) or $GLOBALS['connexion_delai'] != '0')) {
		usleep(10000000);
	}
	else {
		usleep(100000); // sleep during 100,000µs == 100ms to avoid bruteforce
	}
	session_regenerate_id();
	header('Location: index.php');
}

$ip = $_SERVER["REMOTE_ADDR"];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip .= '_'.$_SERVER['HTTP_X_FORWARDED_FOR']; }
if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip .= '_'.$_SERVER['HTTP_CLIENT_IP']; }

$_SESSION['antivol'] = md5($_SERVER['HTTP_USER_AGENT'].$ip);
$_SESSION['timestamp'] = time();

require_once '../inc/inc.php';

afficher_top('Identification');
echo '<div id="axe">'."\n";
decompte_sleep();
js_reload_captcha();

echo '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');

if (isset($_POST['_verif_envoi']) and valider_form()) {
	$_SESSION['nom_utilisateur'] = $_POST['nom_utilisateur'].ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']);
} else {
	afficher_form_login();
}

function afficher_form_login() {
	echo	'<form method="post" action="'.$_SERVER['PHP_SELF'].'" onsubmit="return decompte()">'."\n";
	echo	'<div id="auth">'."\n";
	echo	'<p><label for="nom_utilisateur">'.$GLOBALS['lang']['label_identifiant'].'</label>'."\n";
	echo	'<input type="text" id="nom_utilisateur" name="nom_utilisateur" value="" /></p>'."\n";
	echo	'<p><label for="mot_de_passe">'.$GLOBALS['lang']['label_motdepasse'].'</label>';
	echo	'<input type="password" id="mot_de_passe" name="mot_de_passe" value="" /></p>'."\n";

	if (isset($GLOBALS['connexion_captcha']) and ($GLOBALS['connexion_captcha'] == "1")) {
		echo	'<p><label for="word">'.$GLOBALS['lang']['label_word_captcha'].'</label>';
		echo	'<input type="text" id="word" name="word" value="" /></p>'."\n";
		echo	'<p><a href="#" onclick="this.blur();new_freecap();return false;" title="'.$GLOBALS['lang']['label_changer_captcha'].'"><img src="../inc/freecap/freecap.php" id="freecap"></a></p>'."\n";
	}
		echo	'<input class="inpauth" type="submit" name="submit" value="'.$GLOBALS['lang']['connexion'].'" />';
		echo	'<input type="hidden" name="_verif_envoi" value="1" />';
		echo	'</div>'."\n";
		echo	'</form>';
}


function valider_form() {
	$mot_de_passe_ok = $GLOBALS['mdp'].$GLOBALS['identifiant'];
	$mot_de_passe_essai = ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']).$_POST['nom_utilisateur'];
	if ($mot_de_passe_essai == $mot_de_passe_ok and $_POST['nom_utilisateur'] == $GLOBALS['identifiant']) { // after "or": avoids "$a.$bc" to be equal to "$ab.$c"
		$passwd_is_ok = 1;
		$captcha_is_ok = 1; // temporaire : changé ci-dessous
	}
	if (isset($GLOBALS['connexion_captcha']) and ($GLOBALS['connexion_captcha'] == "1")) {
		if ((empty($_SESSION['freecap_word_hash'])) or (empty($_POST['word'])) or ($_SESSION['hash_func'](strtolower($_POST['word'])) != $_SESSION['freecap_word_hash']) ) {
			$captcha_is_ok = 0;
		}
		if ($_SESSION['hash_func'](strtolower($_POST['word'])) == $_SESSION['freecap_word_hash']) {
			// reset freeCap session vars
			$_SESSION['freecap_attempts'] = 0;
			$_SESSION['freecap_word_hash'] = false;
			$captcha_is_ok = 1;
		}
	}

	if ($passwd_is_ok == 1 and $captcha_is_ok == 1) {
		return TRUE;
	} else {
		return FALSE;
	}

}

footer();
?>
