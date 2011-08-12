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
// gzip compression

function initOutputFilter() {
  ob_start('ob_gzhandler');
  register_shutdown_function('ob_end_flush');
}
initOutputFilter();

//$begin = microtime(TRUE);
error_reporting(-1);

session_start() ;
if (isset($_POST['auteur'])) {
	setcookie('auteur_c', $_POST['auteur'], time() + 365*24*3600, null, null, false, true);
}
if (isset($_POST['email'])) {
	setcookie('email_c', $_POST['email'], time() + 365*24*3600, null, null, false, true);
}
if (isset($_POST['webpage'])) {
	setcookie('webpage_c', $_POST['webpage'], time() + 365*24*3600, null, null, false, true);
}

if ( !file_exists('config/user.php') or !file_exists('config/prefs.php') ) {
	require_once 'inc/conf.php';
	header('Location: '.$GLOBALS['dossier_admin'].'/install.php');
}

require_once 'inc/lang.php';
require_once 'config/user.php';
require_once 'config/prefs.php';
require_once 'config/tags.php';
require_once 'inc/conf.php';
require_once 'inc/them.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';

$GLOBALS['BT_ROOT_PATH'] = '';
$depart = $GLOBALS['dossier_articles'];

if ( isset($_GET['m'])) {
	if (isset($_COOKIE['mobile_theme']) and $_COOKIE['mobile_theme'] == 1) {
		setcookie('mobile_theme', '0', time() + 32000000, null, null, false, true);
	} else {
		setcookie('mobile_theme', '1', time() + 32000000, null, null, false, true);
	}
	header('Location: '.$_SERVER['PHP_SELF']);
}

if ( isset($_SERVER['QUERY_STRING']) and (url_article($_SERVER['QUERY_STRING']) === TRUE) ) {
	$article_id = $_SERVER['QUERY_STRING'] ;
	$tab = explode('/',$article_id);
	$id = substr($tab['0'].$tab['1'].$tab['2'].$tab['3'].$tab['4'].$tab['5'], '0', '14');
	$fichier_data = $depart.'/'.$tab['0'].'/'.$tab['1'].'/'.$id.'.'.$GLOBALS['ext_data'] ;
	if (file_exists($fichier_data)) {
		afficher_calendrier($depart, $tab['1'], $tab['0'], $tab['2']);
		afficher_article($id);
	}
} elseif (isset($_GET['q'])) {
	afficher_calendrier($depart, date('m'), date('Y'));
	$tableau = table_recherche($depart, $_GET['q'], '1', 'public');
	afficher_index($tableau);
} elseif (isset($_GET['tag'])) {
	afficher_calendrier($depart, date('m'), date('Y'));
	$tableau = table_tags($depart, $_GET['tag'], '1', 'public');
	afficher_index($tableau);
} elseif (isset($_SERVER['QUERY_STRING']) and (url_date($_SERVER['QUERY_STRING']) === TRUE) ) {
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
} else {
	afficher_calendrier($depart, date('m'), date('Y'));
	$tableau = table_derniers($depart, $GLOBALS['max_bill_acceuil'], '1', 'public');
	afficher_index($tableau);
}

// $end = microtime(TRUE);
// echo round(($end - $begin),6).' seconds';

?>

