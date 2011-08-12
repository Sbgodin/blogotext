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

//error_reporting(-1);
if ( !file_exists('../config/user.php') || !file_exists('../config/prefs.php') ) {
	header('Location: install.php');
}

$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';

operate_session();

if (isset($_GET['q'])) {
	$tableau = table_recherche($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], $_GET['q'], '', 'admin');
} elseif ( (isset($_GET['filtre'])) and ($_GET['filtre'] !== '') and (!isset($_GET['msg'])) ) {
	if ( preg_match('/\d{6}/',($_GET['filtre'])) ) {
		$annee = substr($_GET['filtre'], 0, 4);
		$mois = substr($_GET['filtre'], 4, 2);
		$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.$annee.'/'.$mois;
		$tableau = table_date($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], $annee, $mois);
	} elseif ($_GET['filtre'] == 'draft') {
		$tableau = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], '-1', '0', 'admin');
	} elseif ($_GET['filtre'] == 'pub') {
		$tableau = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], '-1', '1', 'admin');
	}
} else {
  	$tableau = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], $GLOBALS['max_bill_admin'], '', 'admin');
}

afficher_top($GLOBALS['lang']['mesarticles']);
afficher_msg();
echo '<div id="top">'."\n";
echo moteur_recherche();
echo '<ul id="nav">'."\n";

afficher_menu('index.php');

echo '</ul>'."\n";
echo '</div>'."\n";
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

if (isset($_GET['filtre'])) {
	afficher_form_filtre('articles', $_GET['filtre'], 'admin');
} else {
	afficher_form_filtre('articles', '', 'admin');
}

afficher_liste_articles($tableau);

footer('show_last_ip');
?>
