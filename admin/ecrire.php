<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

$begin = microtime(TRUE);
//error_reporting(-1);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';

operate_session();

// RECUP MAJ
$post = '';
$article_id = '';
if (isset($_SERVER['QUERY_STRING'])) {
	if (isset($_GET['post_id'])) {
		$article_id = htmlspecialchars($_GET['post_id']);
		$loc_data = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($article_id);
		if ( file_exists($loc_data) and preg_match('/\d{4}/',$article_id) ) {
			$post = init_billet('admin', $article_id);
			$commentaires = liste_commentaires($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $article_id, '');
		} else {
			echo $GLOBALS['lang']['note_no_article'];
			exit;
		}
	}
}

// TRAITEMENT
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$billet = init_post_article($article_id);
	$erreurs_form = valider_form_billet($billet);
	if (empty($erreurs_form)) {
		traiter_form_billet($billet);
	}
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
echo '<ul id="nav">'."\n";

if ( !empty($article_id) ) {
	afficher_menu('index.php');
} else {
	afficher_menu('ecrire.php');
}

echo '</ul>'."\n";
echo '</div>'."\n";

// SUBNAV
if ($article_id != '') {
	echo '<div id="subnav">'."\n";
	echo '<a id="backlist" href="index.php">'.$GLOBALS['lang']['retour_liste'].'</a>';
	echo '<ul id="mode">'."\n";
		echo "\t".'<li id="lien-edit">'.$GLOBALS['lang']['ecrire'].'</li>'."\n";
		echo "\t".'<li id="lien-comments"><a href="commentaires.php?post_id='.$article_id.'">'.ucfirst(nombre_commentaires(count($post['nb_comments']))).'</a></li>'."\n";
	echo '</ul>'."\n";
	echo '</div>'."\n";
}
 	
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// EDIT
if ($article_id != '') {
	apercu($post);
}
afficher_form_billet($post, $erreurs_form);
echo js_resize(1);
echo js_inserttag(1);
footer('', $begin);
?>
