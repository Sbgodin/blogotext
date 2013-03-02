<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2013 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();
$begin = microtime(TRUE);

$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);


// modèle d'affichage d'un div pour un lien (avec un formaulaire d'édition par lien).
function afficher_liens($link) {
	$list = '';

	if ($link['bt_statut'] == 0) { // lien privé
		$list .= '<div class="linkbloc privatebloc">'."\n";
	} else { // lien public
		$list .= '<div class="linkbloc">'."\n";
	}
	$list .= "\t".'<h3 class="titre-lien"><a href="'.$_SERVER['PHP_SELF'].'?id='.$link['bt_id'].'">'.$link['ID'].'</a> - <a href="'.$link['bt_link'].'">'.$link['bt_title'].'</a></h3>'."\n";
	$list .= "\t".'<p class="lien_editer"><span>';
	$list .= '<a href="'.$GLOBALS['racine'].'?mode=links&amp;id='.$link['bt_id'].'" >'.$GLOBALS['lang']['voir_sur_le_blog'].'</a>';
	if (empty($_GET['id']) or !is_numeric($_GET['id'])) {
		$list .= ' - <a href="'.$_SERVER['PHP_SELF'].'?id='.$link['bt_id'].'" class="submit-like-link">'.$GLOBALS['lang']['editer'].'</a></span>';
	}
	if ($link['bt_statut'] == '1') {
		$list .= '<img src="style/lock2.png" title="'.$GLOBALS['lang']['link_is_public'].'"/>';
	} elseif ($link['bt_statut'] == '0') {
		$list .= '<img src="style/lock.png" title="'.$GLOBALS['lang']['link_is_private'].'"/>';
	}
	$list .= '</p>'."\n";
	$list .= "\t".'<p class="date">'.date_formate($link['bt_id']).', '.heure_formate($link['bt_id']).' '.$GLOBALS['lang']['par'].' <a href="'.$_SERVER['PHP_SELF'].'?filtre='.urlencode($link['bt_author']).'">'.$link['bt_author'].'</a></p>'."\n";
	$list .= "\t".'<p>'.$link['bt_content'].'</p>'."\n";
	$list .= "\t".'<p class="link_no_clic">'.$link['bt_link'].'</p>'."\n";
	$list .= (!empty($link['bt_tags'])) ? "\t".'<p class="link-tags">'.'<span class="tag">'.str_replace(', ', '</span> <span class="tag">', $link['bt_tags']).'</span>'.'</p>'."\n" : '';
	$list .= '<hr style="clear:both; border: none;margin:0;"/></div>'."\n";
	// si ID est dans l'url, alors on affiche le formulaire d'édition
	if (!empty($_GET['id']) and preg_match('#\d{14}#' ,$_GET['id'])) {
		$list .= afficher_form_link($step = 'edit', '', $link);
	}
	echo $list;
}


// TRAITEMENT
$erreurs_form = array();
if (!isset($_GET['url'])) { // rien : on affiche le premier FORM
	$step = 1;
} else { // l’url est donné (peut-être vide aussi)
	$step = 2;
}
if (isset($_POST['_verif_envoi'])) {
	$link = init_post_link2();

	
	$erreurs_form = valider_form_link($link);
	$step = 1;
	if (empty($erreurs_form)) {

		// URL est un fichier !html !js !css !php ![vide] && téléchargement de fichiers activé :
		if (!isset($_POST['is_it_edit']) and $GLOBALS['dl_link_to_files'] >= 1) {

			// dl_link_to_files : 0 = never ; 1 = always ; 2 = ask with checkbox
			if ( ($GLOBALS['dl_link_to_files'] == 1) or ($GLOBALS['dl_link_to_files'] == 2 and isset($_POST['add_to_files'])) ) {
				//echo 'ok';die;
				$fichier = init_post_fichier();
				$erreurs = valider_form_fichier($fichier);
				if (empty($erreurs)) {
					$GLOBALS['liste_fichiers'] = open_file_db_fichiers($GLOBALS['fichier_liste_fichiers']);
					bdd_fichier($fichier, 'ajout-nouveau', 'download', $link['bt_link']);
				}
			}				
		}

		traiter_form_link($link);
	}
}

// create link list.
$tableau = array();

// si on veut ajouter un lien : on n’affiche pas les anciens liens
if (!isset($_GET['url']) and !isset($_GET['ajout'])) {
	if ( !empty($_GET['filtre']) ) {
		// for "tags" & "author" the requests is "tag.$search" : here we split the type of search and what we search.
		$type = substr($_GET['filtre'], 0, -strlen(strstr($_GET['filtre'], '.')));
		$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
		if ( preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre']) ) { // date
			$query = "SELECT * FROM links WHERE bt_id LIKE ? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array($_GET['filtre'].'%'), 'links');
		} elseif ($_GET['filtre'] == 'draft' or $_GET['filtre'] == 'pub') { // visibles & brouillons
			$query = "SELECT * FROM links WHERE bt_statut=? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array((($_GET['filtre'] == 'draft') ? 0 : 1)), 'links');
		} elseif ($type == 'tag' and $search != '') { // tags
			$query = "SELECT * FROM links WHERE bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array($search, $search.',%', '%, '.$search, '%, '.$search.', %'), 'links');
		} elseif ($type == 'auteur' and $search != '') { // auteur
			$query = "SELECT * FROM links WHERE bt_author=? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array($search), 'links');
		} else {
			$query = "SELECT * FROM links ORDER BY bt_id DESC LIMIT 0, ".$GLOBALS['max_linx_admin'];
			$tableau = liste_elements($query, array(), 'links');
		}
	} elseif (!empty($_GET['q'])) { // mot clé
		$query = "SELECT * FROM links WHERE ( bt_content LIKE ? OR bt_title LIKE ? OR bt_link LIKE ? ) ORDER BY bt_id DESC";
		$tableau = liste_elements($query, array('%'.$_GET['q'].'%', '%'.$_GET['q'].'%', '%'.$_GET['q'].'%'), 'links');
	} elseif (!empty($_GET['id']) and is_numeric($_GET['id'])) { // édition d’un lien spécifique
		$query = "SELECT * FROM links WHERE bt_id=?";
		$tableau = liste_elements($query, array($_GET['id']), 'links');
	} else { // aucun filtre : affiche TOUT
		$query = "SELECT * FROM links ORDER BY bt_id DESC LIMIT 0, ".$GLOBALS['max_linx_admin'];
		$tableau = liste_elements($query, array(), 'links');
	}
}

// count total nb of links
$nb_links_displayed = count($tableau);

afficher_top($GLOBALS['lang']['mesliens']);
echo '<div id="top">'."\n";
afficher_msg($GLOBALS['lang']['mesliens']);
echo moteur_recherche($GLOBALS['lang']['search_in_links']);
afficher_menu(pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME));
echo '</div>'."\n";


echo '<div id="axe">'."\n";

// SUBNAV
echo '<div id="subnav">'."\n";
echo '<p id="mode"><span id="lien-comments">'.ucfirst(nombre_liens($nb_links_displayed)).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM links", array(), 'links').'</span></p>'."\n";

// Affichage formulaire filtrage liens
if (isset($_GET['filtre'])) {
	afficher_form_filtre('links', htmlspecialchars($_GET['filtre']));
} else {
	afficher_form_filtre('links', '');
}
echo '</div>'."\n";


echo '<div id="page">'."\n";

if (isset($_GET['ajout'])) {
	echo afficher_form_link(1, '');
}
elseif (!isset($_GET['id'])) {
	echo afficher_form_link($step, $erreurs_form);
}

// affichage

foreach ($tableau as $link) {
	afficher_liens($link);
}

echo js_addcategories(1);

footer('', $begin);
?>
