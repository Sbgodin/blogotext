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

error_reporting(-1);
$GLOBALS['BT_ROOT_PATH'] = '../';

$max_attemps = 10; // max attempts before blocking login page
$wait_time = 30;   // time to wait before unblocking login page, in minutes

if ( !file_exists('../config/user.php') or !file_exists('../config/prefs.php') or !file_exists('../config/tags.php') ) {
	header('Location: install.php');
}

require_once '../inc/inc.php';

if (check_session() === TRUE) { // return to index if session is already open.
	header('Location: index.php');
} elseif(!isset($_POST['_verif_envoi'])) { // else destroy session cookies ("elseif" used instead of "else" to avoid "header already send" with line 65)
	if (ini_get("session.use_cookies")) {
		setcookie(session_name(), '', time() - 42000);
	}
}

// LOG
if (isset($_POST['nom_utilisateur'])) {

	$fichier = "xauthlog.php";
	$dest = fopen("$fichier", "a+"); 
	$ip = htmlspecialchars($_SERVER["REMOTE_ADDR"]);				// IPs
   if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip .= '_'.htmlspecialchars($_SERVER['HTTP_X_FORWARDED_FOR']); }
   if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip .= '_'.htmlspecialchars($_SERVER['HTTP_CLIENT_IP']); }

	$browser = ($_SERVER['HTTP_USER_AGENT']);	// navigateur
	$origine = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'none';		// url d'origine
	$curent_time = date('r');						// heure selon RFC 2822
	$timestamp = date('U');							// timestamp : nombre de secondes ecoulees depuis le 01/01/70.

	$nom = htmlspecialchars($_POST['nom_utilisateur']);			// nom de login tente.

	fputs($dest, "DATE : $curent_time (TIMESTAMP: $timestamp ) \n\t\t IP: $ip \n\t\t LOGIN: $nom \n\t\t ORIGINE: $origine \n\t\t BROWSER: $browser\n ---------------------------------------------------\n");
	fclose($dest);
}
// end log

$ip = htmlspecialchars($_SERVER["REMOTE_ADDR"]);
$_SESSION['antivol'] = md5($_SERVER['HTTP_USER_AGENT'].$ip);
$_SESSION['timestamp'] = time();

$_SESSION['last_attempt'] = (isset($_SESSION['last_attempt'])) ? $_SESSION['last_attempt']: time();




if (time() - $_SESSION['last_attempt'] > $wait_time*60) { // after waiting enough time : reset
	$_SESSION['last_attempt'] = NULL;
	$_SESSION['log_attempts'] = 0;
}

$_SESSION['log_attempts'] = (isset($_SESSION['log_attempts'])) ? $_SESSION['log_attempts']++ : 1;
//echo $_SESSION['log_attempts']."<br/>".$_SESSION['last_attempt']."<br/>".time();

if (isset($_POST['_verif_envoi']) and valider_form() === TRUE) { // On entre...
	$_SESSION['nom_utilisateur'] = htmlspecialchars($_POST['nom_utilisateur']).ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']);
	if (!empty($GLOBALS['connexion_delai'])) {
		usleep(10000000);
	} else {
		usleep(100000); // 100ms to avoid bruteforce without anoying users
	}
	header('Location: index.php');
} else { // On sort…

	if ($_SESSION['log_attempts'] <= $max_attemps) {
		// …et affiche la page d'auth
		afficher_top('Identification');
		echo '<div id="axe">'."\n";
		decompte_sleep();
		echo '<div id="pageauth">'."\n";
		afficher_titre ($GLOBALS['nom_application'], 'logo', '1');
		echo	'<form method="post" action="'.$_SERVER['PHP_SELF'].'" onsubmit="return decompte()">'."\n";
		echo	'<div id="auth">'."\n";
		echo	'<p><label for="nom_utilisateur">'.$GLOBALS['lang']['label_identifiant'].'</label>'."\n";
		echo	'<input type="text" id="nom_utilisateur" name="nom_utilisateur" value="" /></p>'."\n";
		echo	'<p><label for="mot_de_passe">'.$GLOBALS['lang']['label_motdepasse'].'</label>';
		echo	'<input type="password" id="mot_de_passe" name="mot_de_passe" value="" /></p>'."\n";

		if (isset($GLOBALS['connexion_captcha']) and ($GLOBALS['connexion_captcha'] == "1")) {
			echo js_reload_captcha(1);
			echo	'<p><label for="word">'.$GLOBALS['lang']['label_word_captcha'].'</label>';
			echo	'<input type="text" id="word" name="word" value="" /></p>'."\n";
			echo	'<p><a href="#" onclick="new_freecap();return false;" title="'.$GLOBALS['lang']['label_changer_captcha'].'"><img src="../inc/freecap/freecap.php" id="freecap"></a></p>'."\n";
		}
		echo	'<input class="inpauth" type="submit" name="submit" value="'.$GLOBALS['lang']['connexion'].'" />';
		echo	'<input type="hidden" name="_verif_envoi" value="1" />';
		echo	'</div>'."\n";
		echo	'</form>'."\n";

	} else { // …on affiche un message disant d'attendre
		afficher_top('Identification');
		echo '<div id="axe">'."\n";
		decompte_sleep();
		echo '<div id="pageauth">'."\n";
		afficher_titre ($GLOBALS['nom_application'], 'logo', '1');

		echo '<p style="padding:40px 0;">Max attempts reached.<br/>Wait '.($wait_time-floor((time() - $_SESSION['last_attempt'])/60)) .' minutes before you can try to login again.</p>'."\n";
	}
}

function valider_form() {
	$mot_de_passe_ok = $GLOBALS['mdp'].$GLOBALS['identifiant'];
	$mot_de_passe_essai = ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']).$_POST['nom_utilisateur'];
	if ($mot_de_passe_essai == $mot_de_passe_ok and $_POST['nom_utilisateur'] == $GLOBALS['identifiant']) { // avoids "string a + string bc" to be equal to "string ab + string c"
		$passwd_is_ok = 1;
	} else {
		$passwd_is_ok = 0;
	}
	if (isset($GLOBALS['connexion_captcha']) and ($GLOBALS['connexion_captcha'] == "1")) { // si captcha activé
		if (!empty($_SESSION['freecap_word_hash']) and !empty($_POST['word']) and (sha1(strtolower($_POST['word'])) == $_SESSION['freecap_word_hash']) ) {
			$captcha_is_ok = 1;
		} else {
			$captcha_is_ok = 0;
		}
		if (sha1(strtolower($_POST['word'])) == $_SESSION['freecap_word_hash']) {
			$_SESSION['freecap_word_hash'] = FALSE;
		}
	} else { // si captcha pas activé
		$captcha_is_ok = 1;
	}
	if ($passwd_is_ok == 1 and $captcha_is_ok == 1) {
		$_SESSION['log_attempts'] = 0;
		return TRUE;
	} else {
		$_SESSION['last_attempt'] = (empty($_SESSION['last_attempt'])) ? time() : $_SESSION['last_attempt'];
		$_SESSION['log_attempts']++;
		return FALSE;
	}
}

footer();
?>
