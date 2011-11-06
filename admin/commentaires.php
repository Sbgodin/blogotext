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

//error_reporting(-1);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';

operate_session();

// RECUP MAJ
$article_id='';
$article_title='';



// TRAITEMENT
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$comment = init_post_comment($_POST['comment_article_id'], 'admin');
//	die(print_r($comment));
	$erreurs_form = valider_form_commentaire($comment, 0, 0, 'admin');
	if (empty($erreurs_form)) {
		traiter_form_commentaire($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $comment);
	}
}

// if article ID is given in query string
if ( isset($_GET['post_id']) and preg_match('#\d{14}#', $_GET['post_id']) )  {
	$param_makeup['menu_theme'] = 'for_article';
	$article_id = $_GET['post_id'];
	$loc_data = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($article_id);
	if ( (file_exists($loc_data)) and (preg_match('/\d{14}/',$article_id)) ) {
		$post = init_billet('admin', $article_id);
		$article_title = $post['titre'];
		$commentaires = $post['nb_comments'];
		$param_makeup['show_links'] = '0';
	} else {
		echo $GLOBALS['lang']['note_no_article'];
		exit;
	}
}
// else, no ID 
else {
	$param_makeup['menu_theme'] = 'for_comms';
	// if filter for date
	if ( isset($_GET['filtre']) and $_GET['filtre'] !== '' ) {
		if ( preg_match('/\d{6}/',($_GET['filtre'])) ) {
			$annee = substr($_GET['filtre'], 0, 4);
			$mois = substr($_GET['filtre'], 4, 2);
			$jour = substr($_GET['filtre'], 6, 2);
			$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'].'/'.$annee.'/'.$mois;
			$commentaires = table_date($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $annee, $mois, $jour, -1);
		} elseif ($_GET['filtre'] == 'draft') {
			$commentaires = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '-1', '0', 'admin');
		} elseif ($_GET['filtre'] == 'pub') {
			$commentaires = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '-1', '1', 'admin');
		} else {
			$commentaires = table_auteur($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], htmlspecialchars($_GET['filtre']), '', 'admin' );
		}
	} elseif (isset($_GET['q'])) {
		$commentaires = table_recherche($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], htmlspecialchars($_GET['q']), '', 'admin');
	} else { // no filter, so list'em all
		$commentaires = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $GLOBALS['max_comm_admin'], '', 'admin');
	}
	$nb_total_comms = count(table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '-1', '', 'admin'));
	$post['nb_comments'] = $commentaires;
	$param_makeup['show_links'] = '1';
}

function afficher_commentaire($content, $with_link) {
	$comment = init_comment('admin', get_id($content));
	afficher_form_commentaire($comment['article_id'], 'admin', '', get_id($content));
	$date = decode_id($comment['id']);
	echo '<div class="commentbloc" id="'.article_anchor($comment['id']).'">'."\n";
		echo '<span onclick="reply(\'[b]@['.$comment['auteur'].'|#'.article_anchor($comment['id']).'] :[/b] \'); ">@</span> ';
		echo '<h3 class="titre-commentaire">'.$comment['auteur_lien'].'</h3>'."\n";
		echo '<p class="email"><a href="mailto:'.$comment['email'].'">'.$comment['email'].'</a></p>'."\n";
		echo '<p class="lien_article_de_com">';
		if ($with_link == 1) {
			echo '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$comment['article_id'].'">'.parse_xml($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles']."/".get_path($comment['article_id']), 'bt_title').'</a>';
		}
		if ($comment['status'] == '1') {
			echo '<img src="style/accept.gif" title="'.$GLOBALS['lang']['comment_is_visible'].'"/>';
		} elseif ($comment['status'] == '0') {
			echo '<img src="style/deny.gif" title="'.$GLOBALS['lang']['comment_is_invisible'].'"/>';
		}
		echo '</p>'."\n";

		echo '<p class="date">'.date_formate($comment['id']).', '.heure_formate($comment['id']).'</p>'."\n";
		echo $comment['contenu'];
		echo "\t\t".'<input class="submit" name="showhide-form" onclick="unfold(this);" value="'.$GLOBALS['lang']['editer'].'" type="button"/> '."\n";
		echo '<br style="clear: right;"/>'."\n";
		echo $GLOBALS['form_commentaire'];
	echo '</div>'."\n\n";
}

// DEBUT PAGE
afficher_top($GLOBALS['lang']['titre_commentaires'].' | '.$article_title);
afficher_msg();
afficher_msg_error();
echo '<div id="top">'."\n";
echo moteur_recherche();

echo '<ul id="nav">'."\n";
afficher_menu('commentaires.php');
echo '</ul>'."\n";

echo '</div>'."\n";

// SUBNAV
echo '<div id="subnav">'."\n";

echo '<ul id="mode">'."\n";
if ($param_makeup['menu_theme'] == 'for_article') {
	echo "\t".'<li id="lien-edit"><a href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].' : '.$article_title.'</a></li>'."\n";
	echo "\t".'<li id="lien-comments">'.ucfirst(nombre_commentaires(count($commentaires))).'</li>'."\n";
} elseif ($param_makeup['menu_theme'] == 'for_comms') {
	echo "\t".'<li id="lien-edit">'.ucfirst(nombre_commentaires(count($commentaires))).' sur '.$nb_total_comms.'</li>'."\n";
}
echo '</ul>'."\n";

echo '</div>'."\n";
 	
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// Affichage formulaire filtrage commentaires
if (isset($_GET['filtre'])) {
	afficher_form_filtre('commentaires', $_GET['filtre'], 'admin');
} else {
	afficher_form_filtre('commentaires', '', 'admin');
}

// COMMENTAIRES
if (count($commentaires) > 0) {
	foreach ($commentaires as $file => $content) {
		afficher_commentaire($content, $param_makeup['show_links']);
	}
} else {
	info($GLOBALS['lang']['note_no_comment']);
}

if ($param_makeup['menu_theme'] == 'for_article') {
	afficher_form_commentaire($article_id, 'admin', $erreurs_form);
	echo '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>'."\n";
	echo $GLOBALS['form_commentaire'];
}
	echo '<script type="text/javascript">
function unfold(button) {
	var elem2hide = button.parentNode.getElementsByTagName(\'form\')[0];
	if (elem2hide.style.display !== \'\') {
		elem2hide.style.display = \'\';
//		button.style.display = \'none\';
	} else {
		elem2hide.style.display = \'none\';
	}
}
';
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

footer();
?>
