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

// SUPPRIMER
if (isset($_GET['del'])) {
		supprimer_commentaire($article_id, $_GET['del']);
}

// COMMENT POST INIT
$comment= init_post_comment($article_id);

// TRAITEMENT
$erreurs_form= array();
if (isset($_POST['_verif_envoi'])) {
		$erreurs_form= valider_form_commentaire($comment, $_POST['captcha'], $GLOBALS['captcha']['x']+$GLOBALS['captcha']['y']);
}
if ( empty($erreurs_form) )  {
		traiter_form_commentaire($GLOBALS['dossier_data_commentaires'], $comment);
}
      
// DEBUT PAGE
afficher_top($GLOBALS['lang']['titre_commentaires']);
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
print '<div id="subnav">';
back_list();
print '<ul id="mode">';
	print '<li id="lien-edit"><a href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].'</a></li>';
	print '<li id="lien-comments">'.ucfirst(nombre_commentaires($post['nb_comments'])).'</li>';
print '</ul>';
print '</div>';
 	
print '<div id="axe">'."\n";
print '<div id="page">'."\n";

// COMMENTAIRES
	if ($post['nb_comments'] >= '1') {
		foreach ($commentaires as $id => $content) {
			$comment = init_comment('admin', remove_ext($content));
			afficher_commentaire($comment);
		}
	} else {
		info($GLOBALS['lang']['note_no_comment']);
	}

	afficher_form_commentaire($article_id, 'admin', $post['allow_comments'], $erreurs_form);
	print '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>';
	print $GLOBALS['form_commentaire'];
footer();
?>