<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2012 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

// gzip compression
function initOutputFilter() {
  ob_start('ob_gzhandler');
  register_shutdown_function('ob_end_flush');
}
initOutputFilter();

//$begin = microtime(TRUE);
error_reporting(-1);

session_start();
if (isset($_POST['allowcookie'])) { // si cookies autorisés, conserve les champs remplis
	if (isset($_POST['auteur'])) {  setcookie('auteur_c', $_POST['auteur'], time() + 365*24*3600, null, null, false, true); }
	if (isset($_POST['email'])) {   setcookie('email_c', $_POST['email'], time() + 365*24*3600, null, null, false, true); }
	if (isset($_POST['webpage'])) { setcookie('webpage_c', $_POST['webpage'], time() + 365*24*3600, null, null, false, true); }
	setcookie('subscribe_c', (isset($_POST['subscribe']) and $_POST['subscribe'] == 'on')?1:0, time() + 365*24*3600, null, null, false, true);
	setcookie('cookie_c', 1, time() + 365*24*3600, null, null, false, true);
} elseif (isset($_POST['auteur'])) { // cookies interdits : on en fait des vides (afin de vider les éventuels précédents cookies)
	setcookie('auteur_c', '', time()-42, null, null, false, true);
	setcookie('email_c', '', time()-42, null, null, false, true);
	setcookie('webpage_c', '', time()-42, null, null, false, true);
	setcookie('cookie_c', '', time()-42, null, null, false, true);
	setcookie('subscribe_c', '', time()-42, null, null, false, true);
}

if ( !file_exists('config/user.php') or !file_exists('config/prefs.php') ) {
	require_once 'inc/conf.php';
	header('Location: '.$GLOBALS['dossier_admin'].'/install.php');
}

$GLOBALS['BT_ROOT_PATH'] = '';

$GLOBALS['tags'] = file_get_contents('config/tags.php');
require_once 'inc/lang.php';
require_once 'config/user.php';
require_once 'config/prefs.php';
require_once 'inc/conf.php';
require_once 'inc/them.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';
require_once 'inc/jasc.php';

$depart = $GLOBALS['dossier_articles'];

// anti XSS : /index.php/%22onmouseover=prompt(971741)%3E or /index.php/ redirects all on index.php
if ($_SERVER['PHP_SELF'] !== $_SERVER['SCRIPT_NAME']) {
	header('Location: '.$_SERVER['SCRIPT_NAME']);
}
// mobile theme ?
if ( isset($_GET['m'])) {
	if (isset($_COOKIE['mobile_theme']) and $_COOKIE['mobile_theme'] == 1) {
		setcookie('mobile_theme', '0', time() + 32000000, null, null, false, true);
	} else {
		setcookie('mobile_theme', '1', time() + 32000000, null, null, false, true);
	}
	header('Location: '.$_SERVER['PHP_SELF']);
}

// unsubscribe from comments-newsletter and redirect on main page
if ((isset($_GET['unsub']) and $_GET['unsub'] == 1) and (isset($_GET['article']) and preg_match('#\d{14}#',($_GET['article']))) and isset($_GET['mail']) ) { echo 'hi';
	if (unsubscribe(htmlspecialchars($_GET['article']), $_GET['mail']) == TRUE) {
		header('Location: '.$_SERVER['PHP_SELF'].'?unsubsribe=yes');
	} else {
		header('Location: '.$_SERVER['PHP_SELF'].'?unsubsribe=no');
	}
// Single 
} elseif ( isset($_SERVER['QUERY_STRING']) and preg_match('/^\d{4}\/\d{2}\/\d{2}\/\d{2}\/\d{2}\/\d{2}/',($_SERVER['QUERY_STRING'])) ) {
	$article_id = $_SERVER['QUERY_STRING'] ;
	$tab = explode('/',$article_id);
	$id = substr($tab['0'].$tab['1'].$tab['2'].$tab['3'].$tab['4'].$tab['5'], '0', '14');
	$fichier_data = $depart.'/'.$tab['0'].'/'.$tab['1'].'/'.$id.'.'.$GLOBALS['ext_data'];
	afficher_calendrier($depart, $tab['1'], $tab['0'], $tab['2']);
	afficher_article($id);
// search query
} elseif (isset($_GET['q'])) {
	afficher_calendrier($depart, date('m'), date('Y'));
	$tableau = table_recherche($depart, htmlspecialchars($_GET['q']), '1', 'public');
	afficher_index($tableau);
// display by tag
} elseif (!empty($_GET['tag'])) {
	afficher_calendrier($depart, date('m'), date('Y'));
	$tableau = table_tags($depart, $_GET['tag'], '1', 'public');
	afficher_index($tableau);
// display by day, month
} elseif (isset($_SERVER['QUERY_STRING']) and ( (preg_match('/^\d{4}\/\d{2}(\/\d{2})?/',($_SERVER['QUERY_STRING']))) ) ) {
	$tab = explode('/', ($_SERVER['QUERY_STRING']));
	if ( preg_match('/\d{4}/',($tab['0'])) ) {
		$annee = $tab['0'];
	} else {
		$annee = date('Y');
	}
	if ( isset($tab['1']) and (preg_match('/\d{2}/',($tab['1']))) ) {
		$mois = $tab['1'];
	} else {
		$mois = date('m');
	}
	if ( isset($tab['2']) and (preg_match('/\d{2}/',($tab['2']))) ) {
		$jour = $tab['2'];
	} else {
		$jour = '';
	}
	afficher_calendrier($depart, $mois, $annee, $jour);
	$tableau = table_date($depart, $annee, $mois, $jour, '1');
	afficher_index($tableau);
// display regular blog page
} else {

	// la mise en cache est active
	if ($GLOBALS['cached_index'] == 1) {

		$fichierCache = 'cache_index.dat';
		// si la page n'existe pas dans le cache ou si elle a expiré (15 minutes)
		if (@filemtime($fichierCache)<time()-(900)) {
			// on démarre la bufferisation de la page: rien de ce qui suit n'est envoyé au navigateur 
			ob_start(); 
			afficher_calendrier($depart, date('m'), date('Y'));
			$tableau = table_derniers($depart, $GLOBALS['max_bill_acceuil'], '1', 'public');
			afficher_index($tableau);
			$contenuCache = ob_get_contents(); // on recuperre le contenu du buffer
			ob_end_flush();// on termine la bufferisation
			$fd = fopen("$fichierCache", "w"); // on ouvre le fichier cache
			if ($fd) {
				fwrite($fd,$contenuCache); // on ecrit le contenu du buffer dans le fichier cache
				fclose($fd);
			}
		} else { // le fichier cache existe déjà, donc on l'envoie
			readfile('cache_index.dat');
			echo "\n".'<!-- Servi par le cache -->';
		} 
	}
	// pas d'utilisation de la mise en cache (par exemple pour du DEV)
	else {
		afficher_calendrier($depart, date('m'), date('Y'));
		$tableau = table_derniers($depart, $GLOBALS['max_bill_acceuil'], '1', 'public');
		afficher_index($tableau);
	}
}

//$end = microtime(TRUE);
//echo round(($end - $begin),6).' seconds';

?>
