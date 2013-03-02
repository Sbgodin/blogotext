<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2012 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

$begin = microtime(TRUE);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();

$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);


// RECUP MAJ
$article_id='';
$article_title='';



// TRAITEMENT
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$comment = init_post_comment($_POST['comment_article_id'], 'admin');
	$erreurs_form = valider_form_commentaire($comment, 0, 0, 'admin');
	if (empty($erreurs_form)) {
		traiter_form_commentaire($comment, 'admin');
	}
}

$tableau = array();
// if article ID is given in query string
if ( isset($_GET['post_id']) and preg_match('#\d{14}#', $_GET['post_id']) )  {
	$param_makeup['menu_theme'] = 'for_article';
	$article_id = $_GET['post_id'];

	$query = "SELECT * FROM articles WHERE bt_id=?";
	$post = liste_elements($query, array($article_id), 'articles');

	$article_title = $post[0]['bt_title'];

	$query = "SELECT * FROM commentaires WHERE bt_article_id=? ORDER BY bt_id";
	$commentaires = liste_elements($query, array($article_id), 'commentaires');

	$param_makeup['show_links'] = '0';

}
// else, no ID 
else {
	$param_makeup['menu_theme'] = 'for_comms';
	if ( !empty($_GET['filtre']) ) {
		// for "authors" the requests is "auteur.$search" : here we split the type of search and what we search.
		$type = substr($_GET['filtre'], 0, -strlen(strstr($_GET['filtre'], '.')));
		$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
		// filter for date
		if (preg_match('#^\d{6}(\d{1,8})?$#', ($_GET['filtre'])) ) {
			$query = "SELECT * FROM commentaires WHERE bt_id LIKE ? ORDER BY bt_id DESC";
			$commentaires = liste_elements($query, array($_GET['filtre'].'%'), 'commentaires');
		}
		// filter for statut
		elseif ($_GET['filtre'] == 'draft') {
			$query = "SELECT * FROM commentaires WHERE bt_statut=0 ORDER BY bt_id DESC";
			$commentaires = liste_elements($query, array(), 'commentaires');
		}
		elseif ($_GET['filtre'] == 'pub') {
			$query = "SELECT * FROM commentaires WHERE bt_statut =1 ORDER BY bt_id DESC";
			$commentaires = liste_elements($query, array(), 'commentaires');
		}
		// filter for author
		elseif ($type == 'auteur' and $search != '') {
			$query = "SELECT * FROM commentaires WHERE bt_author=? ORDER BY bt_id DESC";
			$commentaires = liste_elements($query, array($search), 'commentaires');
		}
		// no filter
		else {
			$query = "SELECT * FROM commentaires ORDER BY bt_id DESC LIMIT 0, ".$GLOBALS['max_comm_admin'];
			$commentaires = liste_elements($query, array(), 'commentaires');
		}
	}
	elseif (!empty($_GET['q'])) {
			$query = "SELECT * FROM commentaires WHERE bt_content LIKE ? ORDER BY bt_id DESC";
			$commentaires = liste_elements($query, array('%'.htmlspecialchars($_GET['q']).'%'), 'commentaires');
	}
	else { // no filter, so list'em all
			$query = "SELECT * FROM commentaires ORDER BY bt_id DESC LIMIT 0, ".$GLOBALS['max_comm_admin'];
			$commentaires = liste_elements($query, array(), 'commentaires');
	}
	$nb_total_comms = liste_elements_count("SELECT count(*) AS nbr FROM commentaires", array());
	$param_makeup['show_links'] = '1';
}

function afficher_commentaire($comment, $with_link) {
	afficher_form_commentaire($comment['bt_article_id'], 'admin', '', $comment);
	$date = decode_id($comment['bt_id']);

	if ($comment['bt_statut'] == 0) { // item privé
		echo '<div class="commentbloc privatebloc" id="'.article_anchor($comment['bt_id']).'">'."\n";
	} else { // item public
		echo '<div class="commentbloc" id="'.article_anchor($comment['bt_id']).'">'."\n";
	}
	echo '<span onclick="reply(\'[b]@['.str_replace('\'', '\\\'', $comment['bt_author']).'|#'.article_anchor($comment['bt_id']).'] :[/b] \'); ">@</span> ';
	echo '<h3 class="titre-commentaire">'.$comment['auteur_lien'].'</h3>'."\n";
	echo '<p class="email"><a href="mailto:'.$comment['bt_email'].'">'.$comment['bt_email'].'</a></p>'."\n";
	echo '<p class="lien_article_de_com">';
	if ($with_link == 1) {
		echo $GLOBALS['lang']['sur'].' <a href="'.$_SERVER['PHP_SELF'].'?post_id='.$comment['bt_article_id'].'">'.get_entry($GLOBALS['db_handle'], 'articles', 'bt_title', $comment['bt_article_id'], 'return').'</a>';
	}
	if ($comment['bt_statut'] == '1') {
		echo '<img src="style/accept.png" title="'.$GLOBALS['lang']['comment_is_visible'].'"/>';
	} elseif ($comment['bt_statut'] == '0') {
		echo '<img src="style/deny.png" title="'.$GLOBALS['lang']['comment_is_invisible'].'"/>';
	}
	echo '</p>'."\n";

	echo '<p class="date">'.date_formate($comment['bt_id']).', '.heure_formate($comment['bt_id']).'</p>'."\n";
	echo $comment['bt_content'];
	echo "\t\t".'<input class="submit blue-square" name="showhide-form" onclick="unfold(this);" value="'.$GLOBALS['lang']['editer'].'" type="button"/> '."\n";
	echo '<br style="clear: right;"/>'."\n";
	echo $GLOBALS['form_commentaire'];
	echo '</div>'."\n\n";
}

// DEBUT PAGE
if (!empty($article_title)) {
	$msgg = $GLOBALS['lang']['titre_commentaires'].' | '.$article_title;
} else {
	$msgg = $GLOBALS['lang']['titre_commentaires'];
}
afficher_top($msgg);

echo '<div id="top">'."\n";
afficher_msg($GLOBALS['lang']['titre_commentaires']);
echo moteur_recherche($GLOBALS['lang']['search_in_comments']);
afficher_menu(pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME));
echo '</div>'."\n";

echo '<div id="axe">'."\n";

// SUBNAV
echo '<div id="subnav">'."\n";

echo '<p id="mode">'."\n";
if ($param_makeup['menu_theme'] == 'for_article') {
	echo '<a id="lien-edit" href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].' : '.$article_title.'</a> &nbsp; – &nbsp; <span id="lien-comments">'.ucfirst(nombre_commentaires(count($commentaires))).'</span>';
} elseif ($param_makeup['menu_theme'] == 'for_comms') {
	echo '<span id="lien-comments">'.ucfirst(nombre_commentaires(count($commentaires))).' '.$GLOBALS['lang']['sur'].' '.$nb_total_comms.'</span>';
}
echo '</p>'."\n";

// Affichage formulaire filtrage commentaires
if (isset($_GET['filtre'])) {
	afficher_form_filtre('commentaires', htmlspecialchars($_GET['filtre']));
} else {
	afficher_form_filtre('commentaires', '');
}
echo '</div>'."\n";
 	
echo '<div id="page">'."\n";

// COMMENTAIRES
if (count($commentaires) > 0) {
	foreach ($commentaires as $content) {
		afficher_commentaire($content, $param_makeup['show_links']);
	}
} else {
	echo info($GLOBALS['lang']['note_no_comment']);
}

if ($param_makeup['menu_theme'] == 'for_article') {
	afficher_form_commentaire($article_id, 'admin', $erreurs_form);
	echo '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>'."\n";
	echo $GLOBALS['form_commentaire'];
}
	echo '<script type="text/javascript">';
	echo js_unfold(0);
	echo js_resize(0);
	echo js_inserttag(0);

echo 'function reply(code) {
	var field = document.getElementById(\'form-commentaire\').getElementsByTagName(\'textarea\')[0];
	field.focus();
	if (field.value !== \'\') {
		field.value += \'\n\';
	}
	field.value += code;
	field.scrollTop = 10000;
	field.focus();
}
</script>';

footer('', $begin);

