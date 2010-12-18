<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

if (isset($GLOBALS['fuseau_horaire'])) {
	date_default_timezone_set($GLOBALS['fuseau_horaire']);
} else {
	date_default_timezone_set('UTC');
}
// VERSION
$GLOBALS['version_timo'] = '22';

// DOSSIER ADMIN
$GLOBALS['dossier_admin']= 'admin';

// GENERAL
$GLOBALS['nom_application']= 'BlogoText';
$GLOBALS['charset']= 'UTF-8';
$GLOBALS['version']= '0.9.3';
//$GLOBALS['appsite']= 'http://www.blogotext.com/';
$GLOBALS['appsite']= 'http://lehollandaisvolant.net/blogotext/';
$GLOBALS['ext_data']= 'php';
// FOLDERS
$GLOBALS['dossier_data_articles']= '../articles';
$GLOBALS['dossier_articles']= 'articles';
$GLOBALS['dossier_data_commentaires']= '../commentaires';
$GLOBALS['dossier_commentaires']= 'commentaires';
$GLOBALS['dossier_backup']= 'bt_backup';
$GLOBALS['dossier_data_backup']= '../bt_backup';
$GLOBALS['dossier_images']= '../img/';
$GLOBALS['dossier_vignettes']= 'thb';
$GLOBALS['date_premier_message_blog'] = '199001';/* éviter que l'on puisse aller trop loin dans le passé avec le calendrier (format : YYYYMM) */
$GLOBALS['salt']= '123456';
$GLOBALS['activer_apercu']= '1';

// CAPTCHA
function mk_captcha() {

	$captcha['x'] = rand(rand(1,5),rand(6,9));
	$captcha['y'] = rand(rand(1,rand(3,7)),9);

	return $captcha;
}
if (!isset($_SESSION['captx']) or !(isset($_POST['captcha'])) or !(htmlspecialchars($_POST['captcha']) == $_SESSION['captx']+$_SESSION['capty']) ) {
	$GLOBALS['captcha'] = mk_captcha();
	$_SESSION['captx'] = $GLOBALS['captcha']['x'];
	$_SESSION['capty'] = $GLOBALS['captcha']['y'];
}


// THEMES
$GLOBALS['dossier_themes']= 'themes';
if ( isset($GLOBALS['theme_choisi']) ) {
$GLOBALS['theme_style']= $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/style.css';												
$GLOBALS['theme_article']= $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/post.html';										
$GLOBALS['theme_liste']= $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/list.html';
$GLOBALS['rss']= $GLOBALS['racine'].'rss.php';
}

// PATCH 0.9.2
if (isset($GLOBALS['nb_list'])) {
	$GLOBALS['nb_list'] = $GLOBALS['nb_list'];
} else {
	$GLOBALS['nb_list'] = '25';
}
if (isset($GLOBALS['nb_list_com'])) {
	$GLOBALS['nb_list_com'] = $GLOBALS['nb_list_com'];
} else {
	$GLOBALS['nb_list_com'] = '25';
}

// TEMPLATE VARS
$GLOBALS['boucles'] = array(
	'articles' => array('BOUCLE_article', 'BOUCLE_articles', 'LOOP_post'),
	'commentaires' => array('BOUCLE_commentaire', 'BOUCLE_commentaires', 'LOOP_comments')
);

$GLOBALS['balises']= array(
	'charset' => array('{charset}'),
	'version' => array('{version}'),
	'style' => array('{style}'),
	'racine_du_site' => array('{racine_du_site}'),
	'rss' => array('{rss}'),
	'rss_comments' => '{rss_comments}',
// Blog
	'blog_nom' => array('{nom_du_blog}','{blog_nom}','{blog_name}'),
	'blog_description' => array('{blog_description}','{description}'),
	'blog_auteur' => array('{blog_auteur}','{blog_author}'),
	'blog_email'=> array('{blog_email}'),
// Formulaires
	'form_recherche' => array('{recherche}', '{search}'),
	'form_calendrier' => array('{calendrier}', '{calendar}'),
	'form_commentaire' => array('{formulaire_commentaire}', '{form_comment}'),
// Encarts
	'commentaires_encart' => array('{commentaires_encart}'),
	'categories_encart' => array('{categories_encart}'),
// Article
	'article_titre' => array('{article_titre}','{article_title}'),
	'article_titre_url' => array('{article_titre_url}','{article_title_url}'),
	'article_chapo' => array('{article_chapo}','{article_abstract}'),
	'article_contenu' => array('{article_contenu}','{article_content}'),
	'article_heure'=> array('{article_heure}','{article_time}'),
	'article_date'=> array('{article_date}','{article_date}'),
	'article_motscles'=> array('{article_motscles}','{article_keywords}'),
	'article_lien'=> array('{article_lien}','{article_link}'),
	'article_tags'=> array('{article_tags}'),
	'nb_commentaires'=> array('{nombre_commentaires}','{comments_number}'),
// Commentaire
	'commentaire_auteur' => array('{commentaire_auteur}','{comment_author}'),
	'commentaire_contenu' => array('{commentaire_contenu}','{comment_content}'),
	'commentaire_heure'=> array('{commentaire_heure}','{comment_time}'),
	'commentaire_date'=> array('{commentaire_date}','{comment_date}'),
	'commentaire_email'=> array('{commentaire_email}','{comment_email}'),
	'commentaire_webpage'=> array('{commentaire_webpage}','{comment_webpage}'),
	'commentaire_anchor'=> array('{commentaire_ancre}','{comment_anchor}')
);

// SYNTAX FOR DATA STORAGE
$GLOBALS['data_syntax'] = array(
	// GENERAL
	'bt_version' => 'bt_version',
	// POST
	'article_id' => 'bt_id',
	'article_title' => 'bt_title',
	'article_abstract' => 'bt_abstract',
	'article_content' => 'bt_content',
	'article_wiki_content' => 'bt_wiki_content',
	'article_keywords' => 'bt_keywords',
	'article_status' => 'bt_status',
	'article_allow_comments' => 'bt_allow_comments',
	'article_categories' => 'bt_categories',
	// COMMENTS
	'comment_id' => 'bt_id',
	'comment_article_id' => 'bt_article_id',
	'comment_status' => 'bt_status',
	'comment_content' => 'bt_content',
	'comment_author' => 'bt_author',
	'comment_email' => 'bt_email',
	'comment_webpage' => 'bt_webpage',
);

// POST SYNTAX
function init_billet($mode, $id) {
	$dec = decode_id($id);
if ($mode == 'public') {
		$art_directory = $GLOBALS['dossier_articles'];
		$com_directory = $GLOBALS['dossier_commentaires'];
} elseif ($mode == 'admin') {
		$art_directory = $GLOBALS['dossier_data_articles'];
		$com_directory = $GLOBALS['dossier_data_commentaires'];
}
	$file = $art_directory.'/'.get_path($id);
		$billet['version'] = parse_xml($file, 'bt_version');
		$billet['id'] = $id;
		
		$billet['statut'] = parse_xml($file, $GLOBALS['data_syntax']['article_status']);
		$billet['titre'] = parse_xml($file, $GLOBALS['data_syntax']['article_title']);
		$billet['chapo'] = parse_xml($file, $GLOBALS['data_syntax']['article_abstract']);
		$billet['contenu'] = parse_xml($file, $GLOBALS['data_syntax']['article_content']);
		$billet['contenu_wiki'] = parse_xml($file, $GLOBALS['data_syntax']['article_wiki_content']);
		$billet['mots_cles'] = parse_xml($file, $GLOBALS['data_syntax']['article_keywords']);
		$billet['categories'] = parse_xml($file, $GLOBALS['data_syntax']['article_categories']);
		$billet['annee'] = $dec['annee'];
		$billet['mois'] = $dec['mois'];
		$billet['mois_en_lettres'] = mois_en_lettres($dec['mois']);
		$billet['jour'] = $dec['jour'];
		$billet['heure'] = $dec['heure'];
		$billet['minutes'] = $dec['minutes'];
		$billet['secondes'] = $dec['secondes'];
		$GLOBALS['rss_comments'] = $GLOBALS['racine'].'rss.php?id='.$billet['id'];
		$billet['lien'] = $_SERVER['PHP_SELF'].'?'.$dec['annee'].'/'.$dec['mois'].'/'.$dec['jour'].'/'.$dec['heure'].'/'.$dec['minutes'].'/'.$dec['secondes'].'-'.titre_url($billet['titre']);
		$billet['nb_comments'] = count(liste_commentaires($com_directory, $id));
		if ($billet['version'] == '') {
			$billet['allow_comments'] = '1';
		} else {
			$billet['allow_comments'] = parse_xml($file, $GLOBALS['data_syntax']['article_allow_comments']);
		}
	return $billet;
}

// COMMENT SYNTAX
function init_comment($mode, $id) {
	$dec = decode_id($id);
if ($mode == 'public') {
		$com_directory = $GLOBALS['dossier_commentaires'];
} elseif ($mode == 'admin') {
		$com_directory = $GLOBALS['dossier_data_commentaires'];
}
$file = $com_directory.'/'.get_path($id);
$comment['version'] = parse_xml($file, 'bt_version');
		$comment['id'] = $id;
		$comment['article_id'] = parse_xml($file, $GLOBALS['data_syntax']['comment_article_id']);
		$comment['version'] = parse_xml($file, $GLOBALS['data_syntax']['bt_version']);
		$comment['auteur'] = parse_xml($file, $GLOBALS['data_syntax']['comment_author']);
		$comment['auteur_ss_lien'] = $comment['auteur'];
		$comment['email'] = parse_xml($file, $GLOBALS['data_syntax']['comment_email']);
		$comment['webpage'] = parse_xml($file, $GLOBALS['data_syntax']['comment_webpage']);

// ceci ajoute une possibilite de distinguer les messages du webmaster des autres (ajout d'un <span> autour du nom)
		if ($comment['auteur'] == $GLOBALS['auteur']) {
			$comment['auteur'] = '<span class="admin">'.$comment['auteur'].'</span>';
		}

// le site web du visiteur
		if ($comment['webpage'] != '') {
			$comment['auteur'] = '<a href="'.$comment['webpage'].'" class="webpage">'.$comment['auteur'].'</a>';
		}

		$comment['anchor'] = '<a href="#'.article_anchor($comment['id']).'" id="'.article_anchor($comment['id']).'">#</a>';
		$comment['contenu'] = parse_xml($file, $GLOBALS['data_syntax']['comment_content']);
		$comment['annee'] = $dec['annee'];
		$comment['mois'] = $dec['mois'];
		$comment['mois_en_lettres'] = mois_en_lettres($dec['mois']);
		$comment['jour'] = $dec['jour'];
		$comment['heure'] = $dec['heure'];
		$comment['minutes'] = $dec['minutes'];
		$comment['secondes'] = $dec['secondes'];
	return $comment;
}

// POST ARTICLE
function init_post_article($id='') {
$billet = array();
if (isset($_POST['_verif_envoi'])) {
	$billet= array (
		$GLOBALS['data_syntax']['bt_version'] => $GLOBALS['version'],
		$GLOBALS['data_syntax']['article_id'] => $_POST['annee'].$_POST['mois'].$_POST['jour'].$_POST['heure'].$_POST['minutes'].$_POST['secondes'],
   	$GLOBALS['data_syntax']['article_title'] => htmlspecialchars(stripslashes(protect_markup($_POST['titre']))),
   	$GLOBALS['data_syntax']['article_abstract'] => htmlspecialchars(stripslashes(protect_markup($_POST['chapo']))),
   	$GLOBALS['data_syntax']['article_content'] => formatage_wiki(protect_markup($_POST['contenu'])),
   	$GLOBALS['data_syntax']['article_wiki_content'] => stripslashes(protect_markup($_POST['contenu'])),
		$GLOBALS['data_syntax']['article_keywords'] => extraire_mots($_POST['titre'].' '.$_POST['chapo'].' '.$_POST['contenu']),
		$GLOBALS['data_syntax']['article_categories'] => traiter_tags($_POST['categories']),
		$GLOBALS['data_syntax']['article_status'] => $_POST['statut'],
		$GLOBALS['data_syntax']['article_allow_comments'] => $_POST['allowcomment']
	  );
	}
return $billet;
}


// POST COMMENT
function init_post_comment($id) {
$comment= array();
	if ( (isset($id)) AND (isset($_POST['_verif_envoi'])) ) {
		$comment=array (
			$GLOBALS['data_syntax']['bt_version'] => $GLOBALS['version'],
			$GLOBALS['data_syntax']['comment_id'] => date('Y').date('m').date('d').date('H').date('i').date('s'),
			$GLOBALS['data_syntax']['comment_article_id'] => $id,
			$GLOBALS['data_syntax']['comment_content'] => formatage_commentaires(stripslashes(htmlspecialchars(clean_txt($_POST['commentaire'])))),
			$GLOBALS['data_syntax']['comment_author'] => htmlspecialchars(stripslashes(clean_txt($_POST['auteur']))),
			$GLOBALS['data_syntax']['comment_email'] => htmlspecialchars(stripslashes(clean_txt($_POST['email']))),
			$GLOBALS['data_syntax']['comment_webpage'] => htmlspecialchars(stripslashes(clean_txt($_POST['webpage']))),
			);
	}
return $comment;
}

?>
