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

if (!empty($_SERVER['REMOTE_ADDR'])) {
	$ip = $_SERVER['REMOTE_ADDR'];
}

if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) or (!isset($_SESSION['antivol'])) or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip)) or (!isset($_SESSION['timestamp'])) or ($_SESSION['timestamp'] < time()-1800)) {
	header('Location: logout.php');
	exit;
}
$_SESSION['timestamp'] = time();

// RECUP MAJ
$post='';
$article_id='';
if (isset($_SERVER['QUERY_STRING'])) {
		if (isset($_GET['post_id'])) {
			$article_id=htmlspecialchars($_GET['post_id']);
			$loc_data= $GLOBALS['dossier_data_articles'].'/'.get_path($article_id);
			if ( (file_exists($loc_data)) AND (preg_match('/\d{4}/',$article_id)) ) {
				$post= init_billet('admin', $article_id);
				$commentaires = liste_commentaires($GLOBALS['dossier_data_commentaires'], $article_id);
			} else {
				echo $GLOBALS['lang']['note_no_article'];
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
afficher_msg_error();
echo '<div id="top">'."\n";
moteur_recherche();
echo '<ul id="nav">'."\n";


if ( (isset($article_id)) AND ($article_id != '') ) {
	afficher_menu('index.php');
} else {
	afficher_menu('ecrire.php');
}

echo '</ul>'."\n";
echo '</div>'."\n";

// SUBNAV
 	if ($article_id != '') {
 		echo '<div id="subnav">';
 			back_list();
 	echo '<ul id="mode">';
 			echo '<li id="lien-edit">'.$GLOBALS['lang']['ecrire'].'</li>';
 			echo '<li id="lien-comments"><a href="commentaires.php?post_id='.$article_id.'">'.ucfirst(nombre_commentaires($post['nb_comments'])).'</a></li>';
 	echo '</ul>';
 		echo '</div>';
 	}
 	
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// EDIT
	if ( ($GLOBALS['activer_apercu'] == '1') AND ($article_id != '') ) {
		apercu($post);
	}
   afficher_form_billet($post, $erreurs_form);

footer();
?>
