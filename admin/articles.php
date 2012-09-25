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

$begin = microtime(TRUE);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();

$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);

$tableau = array();
if (!empty($_GET['q'])) {
	$tableau = liste_base_articles('recherche', urldecode($_GET['q']), 'admin', '', 0, '');
}
elseif ( !empty($_GET['filtre']) and !isset($_GET['msg']) ) {
	if ( preg_match('#\d{6}(\d{2})?#',($_GET['filtre'])) ) {
		$annee = substr($_GET['filtre'], 0, 4);
		$mois = substr($_GET['filtre'], 4, 2);
		$jour = substr($_GET['filtre'], 6, 2);
		$tableau = liste_base_articles('date', $annee.$mois.$jour, 'admin', '', 0, '');
	}
	elseif ($_GET['filtre'] == 'draft') {
		$tableau = liste_base_articles('statut', '0', 'admin', '0', 0, '');
	}
	elseif ($_GET['filtre'] == 'pub') {
		$tableau = liste_base_articles('statut', '1', 'admin', '1', 0, '');
	}
}
else {
	$tableau = liste_base_articles('', '', 'admin', '', 0, $GLOBALS['max_bill_admin']);
}

afficher_top($GLOBALS['lang']['mesarticles']);
echo '<div id="top">'."\n";
afficher_msg($GLOBALS['lang']['mesarticles']);
echo moteur_recherche($GLOBALS['lang']['search_in_articles']);
afficher_menu(pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME));
echo '</div>'."\n";

echo '<div id="axe">'."\n";

// SUBNAV
echo '<div id="subnav">'."\n";

echo '<p id="mode">'."\n";
	echo '<span id="lien-comments">'.ucfirst(nombre_articles(count($tableau))).' '.$GLOBALS['lang']['sur'].' '.liste_base_articles('nb', '', 'admin', '', '0', '').'</span>';
echo '</p>'."\n";


if (isset($_GET['filtre'])) {
	afficher_form_filtre('articles', htmlspecialchars($_GET['filtre']), 'admin');
} else {
	afficher_form_filtre('articles', '', 'admin');
}
echo '</div>'."\n";

echo '<div id="page">'."\n";

afficher_liste_articles($tableau);

footer('', $begin);
?>
