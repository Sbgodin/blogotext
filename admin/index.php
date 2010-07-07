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
if ( (!isset($_SESSION['nom_utilisateur'])) || ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) ) {
	header('Location: auth.php');
	exit;
}

if (isset($_GET['q'])) {
				$tableau=table_recherche($GLOBALS['dossier_data_articles'], $_GET['q']);
				if (count($tableau) == '1') {
					redirection('ecrire.php?post_id='.remove_ext($tableau['0']));
				}
} else if ( (isset($_GET['filtre'])) AND ($_GET['filtre'] !== '') AND (!isset($_GET['msg'])) ) {
				if ( preg_match('/\d{4}/',($_GET['filtre'])) ) {
					$annee = substr($_GET['filtre'], '0', '4');
					$mois = substr($_GET['filtre'], '4', '2');
					$dossier= $GLOBALS['dossier_data_articles'].'/'.$annee.'/'.$mois;
	    		$tableau=table_date($GLOBALS['dossier_data_articles'], $annee, $mois);
				} elseif ($_GET['filtre'] == 'draft') {
					$tableau=table_derniers($GLOBALS['dossier_data_articles'], '', '0');
				} elseif ($_GET['filtre'] == 'pub') {
					$tableau=table_derniers($GLOBALS['dossier_data_articles'], '', '1');
				}
} else {
	    	$tableau=table_derniers($GLOBALS['dossier_data_articles'], $GLOBALS['nb_list']);
}


afficher_top($GLOBALS['lang']['mesarticles']);
afficher_msg();
echo '<div id="top">';
moteur_recherche();
echo '<ul id="nav">';

afficher_menu('index.php');

echo '</ul>';
echo '</div>';
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";
if (isset($_GET['filtre'])) {
afficher_form_filtre($_GET['filtre']);
} else {
afficher_form_filtre();
}
afficher_liste_articles($tableau);

footer();
?>
