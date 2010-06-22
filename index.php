<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

if (isset($_GET['n'])) {
		$arguments = $_SERVER['QUERY_STRING'];
		$ntab = explode('&',$arguments);
		$page = $ntab['0'];

		header('Location: '.'index.php?'.$page);
	}

if (isset($_POST['auteur'])) {
	setcookie('auteur_c', $_POST['auteur'], time() + 365*24*3600, null, null, false, true);
}
if (isset($_POST['email'])) {
	setcookie('email_c', $_POST['email'], time() + 365*24*3600, null, null, false, true);
}
if (isset($_POST['webpage'])) {
	setcookie('webpage_c', $_POST['webpage'], time() + 365*24*3600, null, null, false, true);
}



if ( !file_exists('config/user.php') || !file_exists('config/prefs.php') ) {
	header('Location: admin/install.php');
}
require_once 'inc/lang.php';
require_once 'config/user.php';
require_once 'config/prefs.php';
require_once 'inc/conf.php';
require_once 'inc/them.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/list.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';

$depart=$GLOBALS['dossier_articles'];

if ( isset($_SERVER['QUERY_STRING']) AND (url_article($_SERVER['QUERY_STRING']) === 'TRUE') ) {
		$article_id= $_SERVER['QUERY_STRING'] ;
		$tab = explode('/',$article_id);
						$id = substr($tab['0'].$tab['1'].$tab['2'].$tab['3'].$tab['4'].$tab['5'], '0', '14');
						$fichier_data= $depart.'/'.$tab['0'].'/'.$tab['1'].'/'.$id.'.'.$GLOBALS['ext_data'] ;
				if (file_exists($fichier_data)) {
					afficher_form_recherche();
					afficher_calendrier($depart, $tab['1'], $tab['0'], $tab['2']);
					afficher_article($id);
				}
} elseif (isset($_GET['q'])) {
				afficher_form_recherche($_GET['q']);
		    afficher_calendrier($depart, date('m'), date('Y'));
				$tableau=table_recherche($depart, $_GET['q'], '1');
				afficher_index($tableau);
} elseif ( (isset($_SERVER['QUERY_STRING'])  AND (url_date($_SERVER['QUERY_STRING']) === 'TRUE') ) ) {
				$tab = explode('/', ($_SERVER['QUERY_STRING']));
				if ( preg_match('/\d{4}/',($tab['0'])) ) {
				$annee = $tab['0'];
				} else {
					$annee = date('Y');
				}
				if ( isset($tab['1']) AND (preg_match('/\d{2}/',($tab['1']))) ) {
				$mois = $tab['1'];
				} else {
				$mois = date('m');
				}
				if ( isset($tab['2']) AND( preg_match('/\d{2}/',($tab['2'])) ) ) {
				$jour = $tab['2'];
				} else {
				$jour = '';
				}
				afficher_form_recherche();
	    	afficher_calendrier($depart, $mois, $annee, $jour);
	    	$tableau=table_date($depart, $annee, $mois, $jour, '1');
	    	afficher_index($tableau);
} elseif (!isset($_SERVER['QUERY_STRING']) OR ($_SERVER['QUERY_STRING'] == '') ) {
				afficher_form_recherche();
	    	afficher_calendrier($depart, date('m'), date('Y'));
	    	$tableau=table_derniers($depart, $GLOBALS['nb_maxi'], '1');
	    	afficher_index($tableau);
	    }

?>
