<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

// GENERAL
$GLOBALS['nom_application']= 'BlogoText';
$GLOBALS['charset']= 'UTF-8';
$GLOBALS['version']= '0.9.3';
//$GLOBALS['version']= '';
$GLOBALS['syntax_version']= '1';
//$GLOBALS['appsite']= 'http://www.blogotext.com/';
$GLOBALS['appsite']= 'http://lehollandaisvolant.net/blogotext/';
$GLOBALS['ext_data']= 'txt';
$GLOBALS['dossier_admin']= 'admin';
// FOLDERS
$GLOBALS['dossier_data_articles']= '../articles';
$GLOBALS['dossier_articles']= 'articles';
$GLOBALS['dossier_data_commentaires']= '../commentaires';
$GLOBALS['dossier_commentaires']= 'commentaires';
$GLOBALS['dossier_images']= '../img/';
$GLOBALS['dossier_vignettes']= 'thb';
$GLOBALS['salt']= '123456';

// CAPTCHA
if (isset($_SERVER['QUERY_STRING']) and ($_SERVER['QUERY_STRING'] != '')) {
	$query =  $_SERVER['QUERY_STRING'];
	$ntab = explode('&',$_SERVER['QUERY_STRING']);

	if (isset($ntab['0'])) {
		$page = $ntab['0'];
		$mots = explode('-',$page);

		if (isset($mots['0'])) {
			$mot_1 = strlen($mots['0']) +1;
		}		
		else $mot_1 = 4;

		if (isset($mots['1'])) {
			$mot_2 = strlen($mots['1']) +1;
		}
		else $mot_2 = 2;
	}
	else {
		$mot_1 = 5;
		$mot_2 = 3;
		$page = $mot_1 + $mot_2;
	}
}
else {
	$mot_1 = 6;
	$mot_2 = 1;
	$page = $mot_1 + $mot_2;
}
$captch_x = ((strlen($page) % $mot_2) + (strlen($page) % $mot_1)) % 10;
$captch_y = ($mot_2 % $mot_1) % 10;

if ($captch_x == 0 and $captch_y == 0) {
	$captch_x = 4;
	$captch_y = 3;
}

$GLOBALS['captcha'] = array (
	'x' => ("$captch_x"),
	'y' => ("$captch_y"),
);


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
// Blog
	'blog_nom' => array('{nom_du_blog}','{blog_nom}','{blog_name}'),
	'blog_description' => array('{blog_description}','{description}'),
	'blog_auteur' => array('{blog_auteur}','{blog_author}'),
	'blog_email'=> array('{blog_email}'),
// Formulaires
	'form_recherche' => array('{recherche}', '{search}'),
	'form_calendrier' => array('{calendrier}', '{calendar}'),
	'form_commentaire' => array('{formulaire_commentaire}', '{form_comment}'),
// Article
	'article_titre' => array('{article_titre}','{article_title}'),
	'article_chapo' => array('{article_chapo}','{article_abstract}'),
	'article_contenu' => array('{article_contenu}','{article_content}'),
	'article_heure'=> array('{article_heure}','{article_time}'),
	'article_date'=> array('{article_date}','{article_date}'),
	'article_motscles'=> array('{article_motscles}','{article_keywords}'),
	'article_lien'=> array('{article_lien}','{article_link}'),
	'nb_commentaires'=> array('{nombre_commentaires}','{comments_number}'),
// Commentaire
	'commentaire_auteur' => array('{commentaire_auteur}','{comment_author}'),
	'commentaire_contenu' => array('{commentaire_contenu}','{comment_content}'),
	'commentaire_heure'=> array('{commentaire_heure}','{comment_time}'),
	'commentaire_date'=> array('{commentaire_date}','{comment_date}'),
	'commentaire_email'=> array('{commentaire_email}','{comment_email}'),
	'commentaire_webpage'=> array('{commentaire_webpage}','{comment_webpage}')
);

// SYNTAX FOR DATA STORAGE
$GLOBALS['data_syntax'] = array(
	// GENERAL
	'bt_version' => array('version', 'bt_version'),
	// POST
	'article_id' => array('id', 'bt_id'),
	'article_title' => array('titre', 'bt_title'),
	'article_abstract' => array('chapo', 'bt_abstract'),
	'article_content' => array('contenu', 'bt_content'),
	'article_wiki_content' => array('contenu_wiki', 'bt_wiki_content'),
	'article_keywords' => array('motscles', 'bt_keywords'),
	'article_status' => array('statut', 'bt_status'),
	'article_allow_comments' => array('commentaires', 'bt_allow_comments'),
	// COMMENTS
	'comment_id' => array('id', 'bt_id'),
	'comment_article_id' => array('article_id', 'bt_article_id'),
	'comment_status' => array('statut', 'bt_status'),
	'comment_content' => array('commentaire', 'bt_content'),
	'comment_author' => array('auteur', 'bt_author'),
	'comment_email' => array('email', 'bt_email'),
	'comment_webpage' => array('webpage', 'bt_webpage'),
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
		
// GET VERSION
$syntax_version = get_version($file);
		$billet['statut'] = parse_xml($file, $GLOBALS['data_syntax']['article_status'][$syntax_version]);
		$billet['titre'] = parse_xml($file, $GLOBALS['data_syntax']['article_title'][$syntax_version]);
		$billet['chapo'] = parse_xml($file, $GLOBALS['data_syntax']['article_abstract'][$syntax_version]);
		$billet['contenu'] = parse_xml($file, $GLOBALS['data_syntax']['article_content'][$syntax_version]);
		$billet['contenu_wiki'] = parse_xml($file, $GLOBALS['data_syntax']['article_wiki_content'][$syntax_version]);
		$billet['mots_cles'] = parse_xml($file, $GLOBALS['data_syntax']['article_keywords'][$syntax_version]);
		$billet['annee'] = $dec['annee'];
		$billet['mois'] = $dec['mois'];
		$billet['mois_en_lettres'] = mois_en_lettres($dec['mois']);
		$billet['jour'] = $dec['jour'];
		$billet['heure'] = $dec['heure'];
		$billet['minutes'] = $dec['minutes'];
		$billet['secondes'] = $dec['secondes'];
		$billet['lien'] = $_SERVER['PHP_SELF'].'?'.$dec['annee'].'/'.$dec['mois'].'/'.$dec['jour'].'/'.$dec['heure'].'/'.$dec['minutes'].'/'.$dec['secondes'].'-'.titre_url($billet['titre']);
		$billet['nb_comments'] = count(liste_commentaires($com_directory, $id));
		if ($billet['version'] == '') {
			$billet['allow_comments'] = '1';
		} else {
			$billet['allow_comments'] = parse_xml($file, $GLOBALS['data_syntax']['article_allow_comments'][$syntax_version]);
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
$syntax_version = get_version($file);
		$comment['id'] = $id;
		$comment['article_id'] = parse_xml($file, $GLOBALS['data_syntax']['comment_article_id'][$syntax_version]);
		$comment['version'] = parse_xml($file, $GLOBALS['data_syntax']['bt_version'][$syntax_version]);
		$comment['auteur'] = parse_xml($file, $GLOBALS['data_syntax']['comment_author'][$syntax_version]);
		$comment['email'] = parse_xml($file, $GLOBALS['data_syntax']['comment_email'][$syntax_version]);
		$comment['webpage'] = parse_xml($file, $GLOBALS['data_syntax']['comment_webpage'][$syntax_version]);

// ceci ajoute une possibilite de distinguer les messages du webmaster des autres (ajout d'un <span> autour du nom)
		if ($comment['auteur'] == $GLOBALS['auteur']) {
			$comment['auteur'] = '<span class="admin">'.$comment['auteur'].'</span>';
		}

// le site web du visiteur (regarde s'il existe ou s'il est vide...)
		if ($comment['webpage'] != '') {
			$comment['auteur'] = '<a href="'.$comment['webpage'].'" class="webpage">'.$comment['auteur'].'</a>';
		}
		$comment['contenu'] = parse_xml($file, $GLOBALS['data_syntax']['comment_content'][$syntax_version]);
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
		$GLOBALS['data_syntax']['bt_version'][$GLOBALS['syntax_version']] => $GLOBALS['version'],
		$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']] => $_POST['annee'].$_POST['mois'].$_POST['jour'].$_POST['heure'].$_POST['minutes'].$_POST['secondes'],
   	$GLOBALS['data_syntax']['article_title'][$GLOBALS['syntax_version']] => htmlspecialchars(stripslashes(protect_markup($_POST['titre']))),
   	$GLOBALS['data_syntax']['article_abstract'][$GLOBALS['syntax_version']] => htmlspecialchars(stripslashes(protect_markup($_POST['chapo']))),
   	$GLOBALS['data_syntax']['article_content'][$GLOBALS['syntax_version']] => formatage_wiki(protect_markup($_POST['contenu'])),
   	$GLOBALS['data_syntax']['article_wiki_content'][$GLOBALS['syntax_version']] => stripslashes(protect_markup($_POST['contenu'])),
  	$GLOBALS['data_syntax']['article_keywords'][$GLOBALS['syntax_version']] => extraire_mots($_POST['titre'].' '.$_POST['chapo'].' '.$_POST['contenu']),
	  $GLOBALS['data_syntax']['article_status'][$GLOBALS['syntax_version']] => $_POST['statut'],
	  $GLOBALS['data_syntax']['article_allow_comments'][$GLOBALS['syntax_version']] => $_POST['allowcomment']
	  );
	}
return $billet;
}


// POST COMMENT
function init_post_comment($id) {
$comment= array();
	if ( (isset($id)) AND (isset($_POST['_verif_envoi'])) ) {
		$comment=array (
			$GLOBALS['data_syntax']['bt_version'][$GLOBALS['syntax_version']] => $GLOBALS['version'],
			$GLOBALS['data_syntax']['comment_id'][$GLOBALS['syntax_version']] => date('Y').date('m').date('d').date('H').date('i').date('s'),
			$GLOBALS['data_syntax']['comment_article_id'][$GLOBALS['syntax_version']] => $id,
			$GLOBALS['data_syntax']['comment_content'][$GLOBALS['syntax_version']] => formatage_commentaires(htmlspecialchars(stripslashes(($_POST['commentaire'])))),
			$GLOBALS['data_syntax']['comment_author'][$GLOBALS['syntax_version']] => htmlspecialchars(stripslashes($_POST['auteur'])),
			$GLOBALS['data_syntax']['comment_email'][$GLOBALS['syntax_version']] => htmlspecialchars(stripslashes(($_POST['email']))),
			$GLOBALS['data_syntax']['comment_webpage'][$GLOBALS['syntax_version']] => htmlspecialchars(stripslashes(($_POST['webpage']))),
			);
	}
return $comment;
}
?>
