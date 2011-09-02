<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way BEFORE the download.
# *** LICENSE ***

if (isset($GLOBALS['fuseau_horaire'])) {
	date_default_timezone_set($GLOBALS['fuseau_horaire']);
} else {
	date_default_timezone_set('UTC');
}

// PHP VERSION OF THE SERVER (if you encounter problems, try set it to '4')
$GLOBALS['version_PHP'] = '5';

// BLOGOTEXT VERSION (do not change it)
$GLOBALS['version']= '30';

// GENERAL
$GLOBALS['nom_application']= 'BlogoText';
$GLOBALS['charset']= 'UTF-8';
$GLOBALS['appsite']= 'http://lehollandaisvolant.net/blogotext/';
$GLOBALS['ext_data']= 'php';
$GLOBALS['date_premier_message_blog'] = '199701';
$GLOBALS['salt']= '123456';

// FOLDERS (change this only if you know what you are doing...)
$GLOBALS['dossier_admin']= 'admin';
$GLOBALS['dossier_articles'] = 'articles';
$GLOBALS['dossier_commentaires'] = 'commentaires';
$GLOBALS['dossier_backup']= 'bt_backup';
$GLOBALS['dossier_images']= 'img';
$GLOBALS['dossier_themes'] = 'themes';

// MISC BONUS PREFS
$GLOBALS['connexion_delai'] = 0;       // login anti-bruteforce delay : 0 => 0.1sec ; 1 => 10sec ;          (recommended : '0')
$GLOBALS['onglet_commentaires'] = 1;   // show panel link "comments" ('on'= show ; '' = hide) ;             (recommended : '1')
$GLOBALS['onglet_images'] = 1;         // show panel link "images" ('on'= show ; '' = hide) ;               (recommended : '1')
$GLOBALS['session_admin_time'] = 7200; // time in seconds until admin session expires (1800s = 30min)       (recommended : '1800')

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
if ( isset($GLOBALS['theme_choisi']) ) {
	if (isset($_COOKIE['mobile_theme']) and $_COOKIE['mobile_theme'] == '1') {
		$GLOBALS['theme_style'] = $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/m';
	} else {
		$GLOBALS['theme_style'] = $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'];
	}
//	$GLOBALS['theme_article'] = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/post.html';
//	$GLOBALS['theme_liste'] = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/list.html';
	$GLOBALS['theme_article'] = $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/post.html';
	$GLOBALS['theme_liste'] = $GLOBALS['dossier_themes'].'/'.$GLOBALS['theme_choisi'].'/list.html';
	$GLOBALS['rss'] = $GLOBALS['racine'].'rss.php';
}

// TEMPLATE VARS
$GLOBALS['boucles'] = array(
	'articles' => array('BOUCLE_article', 'BOUCLE_articles', 'LOOP_post'),
	'commentaires' => array('BOUCLE_commentaire', 'BOUCLE_commentaires', 'LOOP_comments')
);

$GLOBALS['balises']= array(
	'charset' => '{charset}',
	'version' => '{version}',
	'style' => '{style}',
	'racine_du_site' => '{racine_du_site}',
	'rss' => '{rss}',
	'rss_comments' => '{rss_comments}',
	// Blog
	'blog_nom' => array('{nom_du_blog}', '{blog_nom}', '{blog_name}'),
	'blog_description' => array('{blog_description}', '{description}'),
	'blog_auteur' => array('{blog_auteur}', '{blog_author}'),
	'blog_email' => '{blog_email}',
	// Formulaires
	'form_recherche' => array('{recherche}', '{search}'),
	'form_calendrier' => '{calendrier}',
	'form_commentaire' => '{formulaire_commentaire}',
	// Encarts
	'commentaires_encart' => '{commentaires_encart}',
	'categories_encart' => '{categories_encart}',
	// Article
	'article_titre' => array('{article_titre}', '{article_title}'),
	'article_titre_url' => array('{article_titre_url}', '{article_title_url}'),
	'article_chapo' => array('{article_chapo}', '{article_abstract}'),
	'article_contenu' => array('{article_contenu}', '{article_content}'),
	'article_heure' => array('{article_heure}', '{article_time}'),
	'article_date' => array('{article_date}', '{article_date}'),
	'article_motscles' => array('{article_motscles}', '{article_keywords}'),
	'article_lien' => array('{article_lien}', '{article_link}'),
	'article_tags' => '{article_tags}',
	'nb_commentaires' => array('{nombre_commentaires}', '{comments_number}'),
	// Commentaire
	'commentaire_auteur' => array('{commentaire_auteur}', '{comment_author}'),
	'commentaire_contenu' => array('{commentaire_contenu}', '{comment_content}'),
	'commentaire_heure' => array('{commentaire_heure}', '{comment_time}'),
	'commentaire_date' => array('{commentaire_date}', '{comment_date}'),
	'commentaire_email' => array('{commentaire_email}', '{comment_email}'),
	'commentaire_webpage' => array('{commentaire_webpage}', '{comment_webpage}'),
	'commentaire_anchor' => array('{commentaire_ancre}', '{comment_anchor}')
);

// SYNTAX FOR DATA STORAGE
$GLOBALS['data_syntax'] = array(
	// GENERAL
	'bt_version' => 'bt_version',
	// POST
	'article_id' => 'bt_id',
	'article_notes' => 'bt_notes',
	'article_title' => 'bt_title',
	'article_status' => 'bt_status',
	'article_content' => 'bt_content',
	'article_keywords' => 'bt_keywords',
	'article_abstract' => 'bt_abstract',
	'article_categories' => 'bt_categories',
	'article_wiki_content' => 'bt_wiki_content',
	'article_allow_comments' => 'bt_allow_comments',
	// COMMENTS
	'comment_id' => 'bt_id',
	'comment_email' => 'bt_email',
	'comment_author' => 'bt_author',
	'comment_status' => 'bt_status',
	'comment_content' => 'bt_content',
	'comment_webpage' => 'bt_webpage',
	'comment_article_id' => 'bt_article_id',
	'comment_wiki_content' => 'bt_wiki_content',
);

// POST SYNTAX
function init_billet($mode, $id) {
	if ($mode == 'admin') {
		$statut_com = '';
	} else {
		$statut_com = '1';
	}
	$dec = decode_id($id);
	$art_directory = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'];
	$com_directory = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];

	$file = $art_directory.'/'.get_path($id);
	$billet['version'] = parse_xml($file, $GLOBALS['data_syntax']['bt_version']);
	$billet['id'] = $id;
	$billet['statut'] = parse_xml($file, $GLOBALS['data_syntax']['article_status']);
	$billet['titre'] = parse_xml($file, $GLOBALS['data_syntax']['article_title']);
	$billet['chapo'] = parse_xml($file, $GLOBALS['data_syntax']['article_abstract']);
	$billet['notes'] = parse_xml($file, $GLOBALS['data_syntax']['article_notes']);
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
	$billet['nb_comments'] = count(liste_commentaires($com_directory, $id, $statut_com));
	$billet['allow_comments'] = parse_xml($file, $GLOBALS['data_syntax']['article_allow_comments']);
	return $billet;
}

// COMMENT SYNTAX
function init_comment($mode, $id) {
	$dec = decode_id($id);
	$com_directory = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];

	$file = $com_directory.'/'.get_path($id);
	$comment['id'] = $id;
	$comment['article_id'] = parse_xml($file, $GLOBALS['data_syntax']['comment_article_id']);
	$comment['version'] = parse_xml($file, $GLOBALS['data_syntax']['bt_version']);
	$comment['auteur'] = parse_xml($file, $GLOBALS['data_syntax']['comment_author']);
	$comment['webpage'] = parse_xml($file, $GLOBALS['data_syntax']['comment_webpage']);
	if ($comment['webpage'] != '') {
		$comment['auteur_lien'] = '<a href="'.$comment['webpage'].'" class="webpage">'.$comment['auteur'].'</a>';
	} else {
		$comment['auteur_lien'] = $comment['auteur'];
	}
	$comment['email'] = parse_xml($file, $GLOBALS['data_syntax']['comment_email']);
	$comment['status'] = parse_xml($file, $GLOBALS['data_syntax']['comment_status']);
	$comment['anchor'] = '<a href="#form-commentaire" onclick="reply(\'@['.$comment['auteur'].'|#'.article_anchor($comment['id']).'] : \'); ">@</a> <a href="#'.article_anchor($comment['id']).'" id="'.article_anchor($comment['id']).'">#</a>';
	$comment['contenu'] = parse_xml($file, $GLOBALS['data_syntax']['comment_content']);
	$comment['contenu_wiki'] = parse_xml($file, $GLOBALS['data_syntax']['comment_wiki_content']);
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
function init_post_article($id) {
	if ($GLOBALS['automatic_keywords'] == '0') {
		$keywords = htmlspecialchars(stripslashes(protect_markup($_POST['mots_cles'])));
	} else {
		$keywords = extraire_mots($_POST['titre'].' '.$_POST['chapo'].' '.$_POST['contenu']);
	}
	$comment = array (
		$GLOBALS['data_syntax']['bt_version'] => $GLOBALS['version'],
		$GLOBALS['data_syntax']['article_id'] => $_POST['annee'].$_POST['mois'].$_POST['jour'].$_POST['heure'].$_POST['minutes'].$_POST['secondes'],
		$GLOBALS['data_syntax']['article_title'] => htmlspecialchars(stripslashes(protect_markup($_POST['titre']))),
		$GLOBALS['data_syntax']['article_abstract'] => htmlspecialchars(stripslashes(protect_markup($_POST['chapo']))),
		$GLOBALS['data_syntax']['article_notes'] => htmlspecialchars(stripslashes(protect_markup($_POST['notes']))),
		$GLOBALS['data_syntax']['article_content'] => formatage_wiki(protect_markup($_POST['contenu'])),
		$GLOBALS['data_syntax']['article_wiki_content'] => stripslashes(protect_markup($_POST['contenu'])),
		$GLOBALS['data_syntax']['article_keywords'] => $keywords,
		$GLOBALS['data_syntax']['article_categories'] => traiter_tags($_POST['categories']),
		$GLOBALS['data_syntax']['article_status'] => $_POST['statut'],
		$GLOBALS['data_syntax']['article_allow_comments'] => $_POST['allowcomment']
	);
	return $comment;
}


// POST COMMENT
function init_post_comment($id, $mode='') {
	$comment = array();
	$edit_msg = '';
	if ( (isset($id)) and (isset($_POST['_verif_envoi'])) ) {
		if ( (isset($mode) and $mode == 'admin') and (isset($_POST['is_it_edit']) and $_POST['is_it_edit'] == 'yes') ) {
			$status = $_POST['status'];
			$comment_id = $_POST['comment_id'];
		} elseif (isset($mode) and $mode == 'admin' and !isset($_POST['is_it_edit'])) {
			$status = '1';
			$comment_id = date('Y').date('m').date('d').date('H').date('i').date('s');
		} else {
			$status = $GLOBALS['comm_defaut_status'];
			$comment_id = date('Y').date('m').date('d').date('H').date('i').date('s');
		}
		$comment = array (
			$GLOBALS['data_syntax']['bt_version'] => $GLOBALS['version'],
			$GLOBALS['data_syntax']['comment_id'] => $comment_id,
			$GLOBALS['data_syntax']['comment_article_id'] => $id,
			$GLOBALS['data_syntax']['comment_content'] => formatage_commentaires(stripslashes(htmlspecialchars(clean_txt($_POST['commentaire'].$edit_msg)))),
			$GLOBALS['data_syntax']['comment_wiki_content'] => stripslashes(protect_markup($_POST['commentaire'])),
			$GLOBALS['data_syntax']['comment_author'] => htmlspecialchars(stripslashes(clean_txt($_POST['auteur']))),
			$GLOBALS['data_syntax']['comment_email'] => htmlspecialchars(stripslashes(clean_txt($_POST['email']))),
			$GLOBALS['data_syntax']['comment_webpage'] => htmlspecialchars(stripslashes(clean_txt($_POST['webpage']))),
			$GLOBALS['data_syntax']['comment_status'] => $status,
		);
	}
	return $comment;
}

?>
