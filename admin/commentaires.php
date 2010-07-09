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
/*if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) ) {
	header('Location: auth.php');
	exit;
}*/

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$ip = $_SERVER['REMOTE_ADDR'];
}


if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) or (!isset($_SESSION['antivol'])) or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip)) or (!isset($_SESSION['timestamp'])) or ($_SESSION['timestamp'] < time()-1800)) {
	header('Location: logout.php');
	exit;
}
$_SESSION['timestamp'] = time();

// RECUP MAJ
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

// SUPPRIMER
if (isset($_POST['supprimer_comm'])) {
	if (isset($_POST['security_coin']) and htmlspecialchars($_POST['security_coin']) == md5($_POST['comm_id'].$_SESSION['time_supprimer_commentaire']) and $_SESSION['time_supprimer_commentaire'] >= (time() - 300) ) {
		supprimer_commentaire(htmlspecialchars($article_id), htmlspecialchars($_POST['comm_id']));
	}
	else {
		redirection($_SERVER['PHP_SELF'].'?post_id='.$article_id.'&errmsg=error_comment_suppr');
	}
}

// COMMENT POST INIT
$comment= init_post_comment($article_id);

// TRAITEMENT
$erreurs_form= array();
if (isset($_POST['_verif_envoi'])) {
		$erreurs_form= valider_form_commentaire($comment, $_POST['captcha'], mk_captcha('x')+mk_captcha('y'));
}
if ( empty($erreurs_form) )  {
		traiter_form_commentaire($GLOBALS['dossier_data_commentaires'], $comment);
}
      
// DEBUT PAGE
afficher_top($GLOBALS['lang']['titre_commentaires']);
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
echo '<div id="subnav">';
back_list();
echo '<ul id="mode">';
	echo '<li id="lien-edit"><a href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].'</a></li>';
	echo '<li id="lien-comments">'.ucfirst(nombre_commentaires($post['nb_comments'])).'</li>';
echo '</ul>';
echo '</div>';
 	
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// COMMENTAIRES
	if ($post['nb_comments'] >= '1') {
		foreach ($commentaires as $id => $content) {
			$comment = init_comment('admin', remove_ext($content));
			afficher_commentaire($comment, 0);
		}
	} else {
		info($GLOBALS['lang']['note_no_comment']);
	}

	afficher_form_commentaire($article_id, 'admin', $post['allow_comments'], $erreurs_form);
	echo '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>';
	echo $GLOBALS['form_commentaire'];
footer();
?>
