<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
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

// RECUP MAJ
$post='';
$article_id='';
if (isset($_SERVER['QUERY_STRING'])) {
		if (isset($_GET['post_id'])) {
			$article_id=$_GET['post_id'];
			$loc_data= $GLOBALS['dossier_data_articles'].'/'.get_path($article_id);
			if ( (file_exists($loc_data)) AND (preg_match('/\d{4}/',$article_id)) ) {
				$post= init_billet('admin', $article_id);
				$commentaires = liste_commentaires($GLOBALS['dossier_data_commentaires'], $article_id);
			} else {
				print $GLOBALS['lang']['note_no_article'];
				exit;
			}
		}
}

// INIT POST BILLET
$billet= init_post_article();

// TRAITEMENT
$erreurs_form= array();
if (isset($_POST['_verif_envoi'])) {
		$erreurs_form= valider_form_billet($billet);
}
if ( empty($erreurs_form) )  {
		traiter_form_billet($billet);
}

// TITRE PAGE
if ( isset($fichier_data) ) {
	$titre_ecrire = $GLOBALS['lang']['titre_maj'];
} else {
	$titre_ecrire = $GLOBALS['lang']['titre_ecrire'];
}

// DEBUT PAGE
afficher_top($titre_ecrire);
afficher_msg();
print '<div id="top">'."\n";
moteur_recherche();
print '<ul id="nav">'."\n";

if ( (isset($article_id)) AND ($article_id != '') ) {
lien_nav('index.php', 'lien-liste', $GLOBALS['lang']['mesarticles'], 'true');
lien_nav('ecrire.php', 'lien-nouveau', $GLOBALS['lang']['nouveau']);
} else {
lien_nav('index.php', 'lien-liste', $GLOBALS['lang']['mesarticles']);
lien_nav('ecrire.php', 'lien-nouveau', $GLOBALS['lang']['nouveau'], 'true');
}
lien_nav('preferences.php', 'lien-preferences', $GLOBALS['lang']['preferences']);
lien_nav($GLOBALS['racine'], 'lien-site', $GLOBALS['lang']['lien_blog']);
lien_nav('logout.php', 'lien-deconnexion', $GLOBALS['lang']['deconnexion']);
print '</ul>'."\n";
print '</div>'."\n";

// SUBNAV
 	if ($article_id != '') {
 		print '<div id="subnav">';
 			back_list();
 	print '<ul id="mode">';
 			print '<li id="lien-edit">'.$GLOBALS['lang']['ecrire'].'</li>';
 			print '<li id="lien-comments"><a href="commentaires.php?post_id='.$article_id.'">'.ucfirst(nombre_commentaires($post['nb_comments'])).'</a></li>';
 	print '</ul>';
 		print '</div>';
 	}
 	
print '<div id="axe">'."\n";
print '<div id="page">'."\n";

// EDIT
	if ( ($GLOBALS['activer_apercu'] == '1') AND ($article_id != '') ) {
		apercu($post);
	}
   afficher_form_billet($post, $erreurs_form);

footer();
?>