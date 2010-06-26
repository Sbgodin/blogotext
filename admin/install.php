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
if ( (file_exists('../config/user.php')) AND (file_exists('../config/prefs.php')) ) {
	header('Location: auth.php');
	exit;
}
require_once '../inc/conf.php';
require_once '../inc/lang.php';
require_once '../inc/html.php';
require_once '../inc/form.php';
require_once '../inc/conv.php';
require_once '../inc/fich.php';
require_once '../inc/veri.php';
require_once '../inc/util.php';

if (isset($_GET['l'])) {
	$lang = $_GET['l'];
	if ($lang == $lang_fr['id']) {
       $GLOBALS['lang'] = $lang_fr;
   } elseif ($lang == $lang_en['id']) {
	     $GLOBALS['lang'] = $lang_en;
   } elseif ($lang == $lang_nl['id']) {
	     $GLOBALS['lang'] = $lang_nl;
   }
}
        		
if (isset($_GET['s'])) {
	$step = $_GET['s'];
} else { 
	$step = '1';
}

if ($step == '1') {
	// LANGUE
	if (isset($_POST['verif_envoi_1'])) {
		if ($err_1 = valid_install_1()) {
				afficher_form_1($err_1);
		} else {
			redirection('install.php?s=2&l='.$_POST['langue']);
		}
	} else {
	afficher_form_1();
	}
} elseif ($step == '2') {
	// ID + MOT DE PASSE
		if (isset($_POST['verif_envoi_2'])) {
		if ($err_2 = valid_install_2()) {
				afficher_form_2($err_2);
		} else {
			traiter_install_2();
			redirection('install.php?s=3&l='.$_GET['l']);
		}
	} else {
	afficher_form_2();
	}
} elseif ($step == '3') {
		// ID + MOT DE PASSE
		if (isset($_POST['verif_envoi_3'])) {
		if ($err_3 = valid_install_3()) {
				afficher_form_3($err_3);
		} else {
			traiter_install_3();
			redirection('auth.php');
		}
	} else {
	afficher_form_3();
	}
}

function afficher_form_1($erreurs = '') {
afficher_top('Install');
print '<div id="axe">'."\n";
print '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');
afficher_titre ('Bienvenue Welcome', 'step', '1');
erreurs($erreurs);
print '<form method="post" action="'.$_SERVER['PHP_SELF'].'" >' ;
form_langue_install('Choisissez votre langue / Choose your language');
hidden_input('verif_envoi_1', '1');
print '<input class="inpauth" accesskey="s" type="submit" name="enregistrer" value="Ok" />';
print '</form>' ;
}

function afficher_form_2($erreurs = '') {
afficher_top('Install');
print '<div id="axe">'."\n";
print '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');
afficher_titre ($GLOBALS['lang']['install'], 'step', '1');
erreurs($erreurs);
print '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" >' ;
form_text('identifiant', '', $GLOBALS['lang']['install_id']);
form_password('nouveau-mdp', '', $GLOBALS['lang']['install_mdp']);
form_password('mdp_rep', '', $GLOBALS['lang']['install_remdp']);
hidden_input('langue', $_GET['l']);
hidden_input('verif_envoi_2', '1');
print '<input class="inpauth" accesskey="s" type="submit" name="enregistrer" value="Ok" />';
print '</form>' ;
}

function afficher_form_3($erreurs = '') {
afficher_top('Install');
print '<div id="axe">'."\n";
print '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');
afficher_titre ($GLOBALS['lang']['install'], 'step', '1');
erreurs($erreurs);
print '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" >' ;
form_text('racine', 'http://', $GLOBALS['lang']['pref_racine']);
hidden_input('auteur', '');
hidden_input('email', '');
hidden_input('nomsite', '');
hidden_input('description', '');
hidden_input('nb_maxi', '10');
hidden_input('nb_list', '25');
hidden_input('nb_list_com', '50');
hidden_input('format_date', '0');
hidden_input('format_heure', '0');
hidden_input('onglet_commentaires', 'on');
hidden_input('onglet_images', 'on');
hidden_input('verif_envoi_3', '1');
hidden_input('apercu', '1');
hidden_input('theme', 'defaut');
print '<input class="inpauth" accesskey="s" type="submit" name="enregistrer" value="Ok" />';
print '</form>' ;
}

function traiter_install_2() {
	fichier_user();
}

function traiter_install_3() {
	fichier_prefs();
	creer_dossier($GLOBALS['dossier_data_articles']);
	creer_dossier($GLOBALS['dossier_data_commentaires']);
		$first_post= array (
			$GLOBALS['data_syntax']['bt_version'][$GLOBALS['syntax_version']] => $GLOBALS['version'],
			$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']] => date('Y').date('m').date('d').date('H').date('i').date('s'),
   		$GLOBALS['data_syntax']['article_title'][$GLOBALS['syntax_version']] => $GLOBALS['lang']['first_titre'],
   		$GLOBALS['data_syntax']['article_abstract'][$GLOBALS['syntax_version']] => $GLOBALS['lang']['first_edit'],
   		$GLOBALS['data_syntax']['article_content'][$GLOBALS['syntax_version']] => $GLOBALS['lang']['first_edit'],
   		$GLOBALS['data_syntax']['article_wiki_content'][$GLOBALS['syntax_version']] => $GLOBALS['lang']['first_edit'],
   		$GLOBALS['data_syntax']['article_keywords'][$GLOBALS['syntax_version']] => '',
	  	$GLOBALS['data_syntax']['article_status'][$GLOBALS['syntax_version']] => '1'
	  );
	fichier_data($GLOBALS['dossier_data_articles'], $first_post);
}

function valid_install_1() {
				$erreurs = array();
	    if (!strlen(trim($_POST['langue']))) {
	    	$erreurs[] = 'Vous devez choisir une langue';
	    }
	    return $erreurs;
}

function valid_install_2() {
				$erreurs = array();
	    if (!strlen(trim($_POST['identifiant']))) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_identifiant'];
	    }	
		  if ( (strlen($_POST['nouveau-mdp']) < '6') OR (strlen($_POST['mdp_rep']) < '6') ) {
	    	$erreurs[] = $GLOBALS['lang']['err_prefs_mdp'] ;
	    }
	    if ( ($_POST['nouveau-mdp']) !== ($_POST['mdp_rep']) ) {
	    	$erreurs[] = $GLOBALS['lang']['err_prefs_mdp'] ;
	    }
	    return $erreurs;
}

function valid_install_3() {
				$erreurs = array();
	    if (!strlen(trim($_POST['racine']))) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_racine'];
	    } elseif (!preg_match('/^http:\/\//', $_POST['racine'])) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_racine_http'];
	    } elseif (!preg_match('/\/$/', $_POST['racine'])) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_racine_slash'];
	    }
	    return $erreurs;
}


footer();
?>
