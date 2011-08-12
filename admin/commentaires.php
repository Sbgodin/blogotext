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
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

//error_reporting(-1);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';

operate_session();

// RECUP MAJ
$article_id='';
$article_title='';

// if article ID is given in query string
if ( isset($_GET['post_id']) and preg_match('#\d{14}#', $_GET['post_id']) )  {
	$param_makeup['menu_theme'] = 'for_article';
	$article_id = $_GET['post_id'];
	$loc_data = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($article_id);
	if ( (file_exists($loc_data)) and (preg_match('/\d{14}/',$article_id)) ) {
		$post = init_billet('admin', $article_id);
		$article_title = $post['titre'];
		$commentaires = liste_commentaires($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $article_id, '');
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
			$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'].'/'.$annee.'/'.$mois;
			$commentaires = table_date($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $annee, $mois);
		} elseif ($_GET['filtre'] == 'draft') {
			$commentaires = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '-1', '0', 'admin');
		} elseif ($_GET['filtre'] == 'pub') {
			$commentaires = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '-1', '1', 'admin');
		} else {
			$commentaires = table_auteur($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], htmlspecialchars($_GET['filtre']), '', 'admin' );

		}
	} elseif (isset($_GET['q'])) {
		$commentaires = table_recherche($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $_GET['q'], '', 'admin');
	} else { // no filter, so list'em all
		$commentaires = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $GLOBALS['max_comm_admin'], '', 'admin');
	}
	$nb_total_comms = count(table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '-1', '', 'admin'));
	$post['nb_comments'] = sizeof($commentaires);
	$param_makeup['show_links'] = '1';
}



function afficher_commentaire($comment, $with_link) {
	$date = decode_id($comment['id']);
	echo '<div class="commentbloc" id="'.article_anchor($comment['id']).'">'."\n";

		echo '<span onclick="reply(\'@['.$comment['auteur'].'|#'.article_anchor($comment['id']).'] : \'); ">@</span> ';
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

		echo "\n".'<form method="post" id="update-comm-'.$comment['id'].'" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" >'."\n";
				$time = time();
				$_SESSION['some_time'] = $time;
				echo "\t\t".hidden_input('comm_id', $comment['id']);
				echo "\t\t".hidden_input('security_coin', md5($comment['id'].$time));
				echo "\t\t".hidden_input('activer_comm_choix', $comment['status']);
				$text_bouton = ($comment['status'] == 1) ? $GLOBALS['lang']['desactiver'] : $GLOBALS['lang']['activer'];
				echo "\t\t".'<input class="submit-suppr" type="submit" name="supprimer_comm" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_comment'].'\')" />'."\n";
				echo "\t\t".'<input class="submit" type="submit" name="activer_comm" value="'.$text_bouton.'" />'."\n";

				if (isset($_GET['post_id'])) {
					echo "\t\t".'<input class="submit" name="showhide-form" onclick="unfold(this);" value="'.$GLOBALS['lang']['editer'].'" type="button"/> '."\n";
				}
		echo '</form>'."\n";

		echo '<br style="clear: right;"/>'."\n";
		// fomulaire édition commentaires, affichage uniquement si sur page "commentaires par article", et non "last_comm"
		if (isset($_GET['post_id']) and preg_match('#\d{14}#', ($_GET['post_id']))) {
			afficher_form_commentaire($comment['article_id'], 'admin', '', $comment['id']);
			echo $GLOBALS['form_commentaire'];
		}
	echo '</div>'."\n\n";
}


// COMMENT POST INIT
$comment = init_post_comment($article_id, 'admin');
// TRAITEMENT
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$erreurs_form = valider_form_commentaire($comment,0 ,0 , 'admin');
}
if (empty($erreurs_form)) {
	traiter_form_commentaire($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $comment);
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
	echo "\t".'<li id="lien-comments">'.ucfirst(nombre_commentaires($post['nb_comments'])).'</li>'."\n";
} elseif ($param_makeup['menu_theme'] == 'for_comms') {
	echo "\t".'<li id="lien-edit">'.ucfirst(nombre_commentaires($post['nb_comments'])).' sur '.$nb_total_comms.'</li>'."\n";
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
if ($post['nb_comments'] > 0) {
	foreach ($commentaires as $id => $content) {
		$comment = init_comment('admin', get_id($content));
		afficher_commentaire($comment, $param_makeup['show_links']);
	}
} else {
	info($GLOBALS['lang']['note_no_comment']);
}

if ($param_makeup['menu_theme'] == 'for_article') {
	afficher_form_commentaire($article_id, 'admin', $erreurs_form);
	echo '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>'."\n";
	echo $GLOBALS['form_commentaire'];

	echo '<script type="text/javascript">
function unfold(button) {
	var elem2hide = button.parentNode.parentNode.getElementsByTagName(\'form\')[1];
	if (elem2hide.style.display !== \'\') {
		elem2hide.style.display = \'\';
		button.style.display = \'none\';
	} else {
		elem2hide.style.display = \'none\';
	}
}

function reply(code) {
	var field = document.getElementById(\'form-commentaire\').getElementsByTagName(\'textarea\')[0];
	field.focus();
	if (field.value !== \'\') {
		field.value += \'\n\';
	}
	field.value += code;
	field.scrollTop = 10000;
	field.focus();
}

function insertTag(startTag, endTag, tag, tagType) {
	var field = tag.parentNode.parentNode.getElementsByTagName(\'fieldset\')[0].getElementsByTagName(\'textarea\')[0];
	var scroll = field.scrollTop;
	field.focus();

	if (window.ActiveXObject) {
		var textRange = document.selection.createRange();            
		var currentSelection = textRange.text;
		textRange.text = startTag + currentSelection + endTag;
		textRange.moveStart("character", -endTag.length - currentSelection.length);
		textRange.moveEnd("character", -endTag.length);
		textRange.select();     
	} else {
		var startSelection   = field.value.substring(0, field.selectionStart);
		var currentSelection = field.value.substring(field.selectionStart, field.selectionEnd);
		var endSelection     = field.value.substring(field.selectionEnd);
		if (currentSelection == "") { currentSelection = "TEXT"; }
		field.value = startSelection + startTag + currentSelection + endTag + endSelection;
		field.focus();
		field.setSelectionRange(startSelection.length + startTag.length, startSelection.length + startTag.length + currentSelection.length);
	}
	field.scrollTop = scroll;
}

</script>';
}
footer();
?>
