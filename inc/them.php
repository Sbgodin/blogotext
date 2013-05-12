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

// TEMPLATE VARS
/*
 * Vars used in them files, aimed to get
 * replaced with some specific data
 *
 */
$GLOBALS['boucles'] = array(
	'posts' => 'BOUCLE_posts',
	'commentaires' => 'BOUCLE_commentaires',
);

$GLOBALS['balises'] = array(
	'version' => '{version}',
	'app_name' => '{app_name}',
	'style' => '{style}',
	'racine_du_site' => '{racine_du_site}',
	'rss' => '{rss}',
	'rss_comments' => '{rss_comments}',
	// Navigation
	'pagination' => '{pagination}',
	// Blog
	'blog_nom' => '{blog_nom}',
	'blog_description' => '{blog_description}',
	'blog_auteur' => '{blog_auteur}',
	'blog_email' => '{blog_email}',
	// Formulaires
	'form_recherche' => '{recherche}',
	'form_calendrier' => '{calendrier}',
	'form_commentaire' => '{formulaire_commentaire}',
	// Encarts
	'comm_encart' => '{commentaires_encart}',
	'cat_encart' => '{categories_encart}',

	// Article
	'article_titre' => '{article_titre}',
	'article_titre_page' => '{article_titre_page}',
	'article_titre_echape' => '{article_titre_echape}',
	'article_chapo' => '{article_chapo}',
	'article_contenu' => '{article_contenu}',
	'article_heure' => '{article_heure}',
	'article_date' => '{article_date}',
	'article_motscles' => '{article_motscles}',
	'article_lien' => '{article_lien}',
	'article_tags' => '{article_tags}',
	'article_tags_plain' => '{article_tags_plain}',
	'nb_commentaires' => '{nombre_commentaires}',

	// Commentaire
	'commentaire_auteur' => '{commentaire_auteur}',
	'commentaire_auteur_lien' => '{commentaire_auteur_lien}',
	'commentaire_contenu' => '{commentaire_contenu}',
	'commentaire_heure' => '{commentaire_heure}',
	'commentaire_date' => '{commentaire_date}',
	'commentaire_email' => '{commentaire_email}',
	'commentaire_webpage' => '{commentaire_webpage}',
	'commentaire_anchor' => '{commentaire_ancre}', // the id="" content
	'commentaire_lien' => '{commentaire_lien}',

	// Liens
	'lien_auteur' => '{lien_auteur}',
	'lien_titre' => '{lien_titre}',
	'lien_url' => '{lien_url}',
	'lien_date' => '{lien_date}',
	'lien_heure' => '{lien_heure}',
	'lien_description' => '{lien_description}',
	'lien_permalink' => '{lien_permalink}',
	'lien_id' => '{lien_id}',
);

function conversions_theme($texte, $solo_art) {
	$texte = str_replace($GLOBALS['balises']['version'], $GLOBALS['version'], $texte);
	$texte = str_replace($GLOBALS['balises']['app_name'], $GLOBALS['nom_application'], $texte);
	$texte = str_replace($GLOBALS['balises']['style'], $GLOBALS['theme_style'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_description'], $GLOBALS['description'], $texte);
	$texte = str_replace($GLOBALS['balises']['racine_du_site'], $GLOBALS['racine'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_email'], $GLOBALS['email'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_nom'], $GLOBALS['nom_du_site'], $texte);


	if (isset($solo_art['bt_title'])) {
		$texte = str_replace($GLOBALS['balises']['article_titre_page'], $solo_art['bt_title'].' - ', $texte);
	} else {
		$texte = str_replace($GLOBALS['balises']['article_titre_page'], '', $texte);
	}


	$texte = str_replace($GLOBALS['balises']['pagination'], lien_pagination(), $texte);

	if (strpos($texte, $GLOBALS['balises']['form_recherche']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['form_recherche'], moteur_recherche(''), $texte) ;
	}
	if (strpos($texte, $GLOBALS['balises']['form_calendrier']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['form_calendrier'], afficher_calendrier(), $texte) ;
	}

	// Formulaires
	if (isset($GLOBALS['form_commentaire'])) { $texte = str_replace($GLOBALS['balises']['form_commentaire'], $GLOBALS['form_commentaire'], $texte); }
		else { $texte = str_replace($GLOBALS['balises']['form_commentaire'], '', $texte); }

	$texte = str_replace($GLOBALS['balises']['rss'], $GLOBALS['rss'], $texte);
	$texte = str_replace($GLOBALS['balises']['comm_encart'], encart_commentaires(), $texte);
	$texte = str_replace($GLOBALS['balises']['cat_encart'], encart_categories(), $texte);
	if (isset($GLOBALS['rss_comments'])) { $texte = str_replace($GLOBALS['balises']['rss_comments'], $GLOBALS['rss_comments'], $texte);}

	return $texte;
}


// Commentaire
function conversions_theme_commentaire($texte, $commentaire) {
	$texte = str_replace($GLOBALS['balises']['commentaire_contenu'], $commentaire['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_date'], date_formate($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_heure'], heure_formate($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_email'], $commentaire['bt_email'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_auteur_lien'], $commentaire['auteur_lien'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_auteur'], str_replace("'", "\\'", $commentaire['bt_author']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_webpage'], $commentaire['bt_webpage'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_anchor'], $commentaire['anchor'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_lien'], $commentaire['bt_link'], $texte);
	return $texte;
}

// Article
function conversions_theme_article($texte, $billet) {
	/*
	if (!empty($_GET['q'])) {
		$q = htmlspecialchars($_GET['q']); // FIXME : remplacer uniquement dans le texte, pas les liens.
		$billet['bt_content'] = str_replace($q, '<mark>'.$q.'</mark>', $billet['bt_content']);
	}
	*/

	$texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte);
	$texte = str_replace($GLOBALS['balises']['style'], $GLOBALS['theme_style'], $texte);
	$texte = str_replace($GLOBALS['balises']['rss_comments'], 'rss.php?id='.$billet['bt_id'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_titre'], $billet['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_titre_echape'], urlencode($billet['bt_title']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_chapo'], $billet['bt_abstract'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_contenu'], $billet['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_date'], date_formate($billet['bt_date']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_heure'], heure_formate($billet['bt_date']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_motscles'], $billet['bt_keywords'], $texte);
	// comments closed (globally or only for this article) and no comments => say « comments closed »
	if ( ($billet['bt_allow_comments'] == 0 or $GLOBALS['global_com_rule'] == 1 ) and $billet['bt_nb_comments'] == 0 ) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], $GLOBALS['lang']['note_comment_closed'], $texte); }
	// comments open OR ( comments closed AND comments exists ) => say « nb comments ».
	if ( !($billet['bt_allow_comments'] == 0 or $GLOBALS['global_com_rule'] == 1 ) or $billet['bt_nb_comments'] != 0 ) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], nombre_commentaires($billet['bt_nb_comments']), $texte); }
	$texte = str_replace($GLOBALS['balises']['article_lien'], $billet['lien'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_tags'], liste_tags_article($billet, '1'), $texte);
	$texte = str_replace($GLOBALS['balises']['article_tags_plain'], liste_tags_article($billet, '0'), $texte);
	return $texte;
}

// Liens
function conversions_theme_lien($texte, $lien) {
	$texte = str_replace($GLOBALS['balises']['article_titre'], $lien['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_auteur'], $lien['bt_author'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_titre'], $lien['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_url'], $lien['bt_link'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_date'], date_formate($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_heure'], heure_formate($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_permalink'], $lien['bt_id'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_description'], $lien['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_id'], $lien['ID'], $texte);
	return $texte;
}


// récupère le bout du fichier thème contenant une boucle comme {BOUCLE_commentaires}
//  soit le morceau de HTML retourné est parsé à son tour pour crée le HTML de chaque commentaire ou chaque article.
//  soit le morceau de HTML retourné sert à se faire remplacer par l’ensemble des commentaires constitués
function replace_boucles($fichier_theme, $balise, $incl) {
	if ($theme_page = file_get_contents($fichier_theme)) {
		$len_balise_d = 0 ; $len_balise_f = 0;
		if ($incl == 'excl') { // la $balise est exclue : bli{p}blabla{/p}blo => blabla
			$len_balise_d = strlen('{'.$balise.'}');
		}
		else {// la $balise est inclue : bli{p}blabla{/p}blo => {p}blabla{/p}
			$len_balise_f = strlen('{/'.$balise.'}');
		}
		$debut = strpos($theme_page, '{'.$balise.'}') + $len_balise_d;
		$fin = strpos($theme_page, '{/'.$balise.'}') + $len_balise_f;
		$lenght = $fin - $debut;
		$return = substr($theme_page, $debut, $lenght); 
		return $return;
	} else {
		die($GLOBALS['lang']['err_theme_introuvable']);
	}
}

// only used by the main page of the blog (not on admin) : shows ONE article and its comments.
function afficher_article($id) {
	// 'admin' connected is allowed to see draft articles, but not 'public'. Same for article posted with a date in the future.
	if (empty($_SESSION['user_id'])) {
		$query = "SELECT * FROM articles WHERE bt_id=? AND bt_date <=? AND bt_statut=1";
		$billets = liste_elements($query, array($id, date('YmdHis')), 'articles');
	} else {
		$query = "SELECT * FROM articles WHERE bt_id=?";
		$billets = liste_elements($query, array($id), 'articles');
	}
	if ( !empty($billets[0]) ) {
		// TRAITEMENT new commentaire
		$erreurs_form = array();
		if (isset($_POST['_verif_envoi']) and ($billets[0]['bt_allow_comments'] == '1' )) {
			// COMMENT POST INIT
			$comment = init_post_comment($id, 'public');
			if (isset($_POST['enregistrer'])) {
				$erreurs_form = valider_form_commentaire($comment, $_POST['captcha'], ($_SESSION['captx']+$_SESSION['capty']), 'public');
			}
		}
		afficher_form_commentaire($id, 'public', $erreurs_form);
		if (empty($erreurs_form) and isset($_POST['enregistrer']) and empty($_POST['email-adress'])) {
			traiter_form_commentaire($comment, 'public');
		}
		if (!($theme_page = file_get_contents($GLOBALS['theme_article']))) die($GLOBALS['lang']['err_theme_introuvable']);	
		$HTML_article = conversions_theme($theme_page, $billets[0]);
		$HTML_article = conversions_theme_article($HTML_article, $billets[0]);
		$query = "SELECT * FROM commentaires WHERE bt_article_id=? AND bt_statut=1 ORDER BY bt_id";
		$commentaires = liste_elements($query, array($id), 'commentaires');
		$HTML_comms = '';
		if (!empty($commentaires)) {
			$template_comments = replace_boucles($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'excl');
			foreach ($commentaires as $element) {
				$HTML_comms .=  conversions_theme_commentaire($template_comments, $element);
			}
		}
		$HTML = str_replace(replace_boucles($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'incl'), $HTML_comms, $HTML_article);

		echo $HTML;
	}
	else {
		afficher_index(NULL);
	}
}



// only used by the main page of the blog (not on admin) : shows main blog page : or articles, or comments, or…
function afficher_index($tableau) {
	$HTML_elmts = '';
	$data = array();
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);
	if (!empty($tableau)) {
		if (count($tableau)==1 and !empty($tableau[0]['bt_title'])) $data = $tableau[0];
		$HTML_article = conversions_theme($theme_page, $data);
		if ($tableau[0]['bt_type'] == 'article') {
			if (!($theme_article = file_get_contents($GLOBALS['theme_post_artc']))) die($GLOBALS['lang']['err_theme_introuvable']);
			$conversion_theme_fonction = 'conversions_theme_article';
		}
		if ($tableau[0]['bt_type'] == 'comment') {
			if (!($theme_article = file_get_contents($GLOBALS['theme_post_comm']))) die($GLOBALS['lang']['err_theme_introuvable']);
			$conversion_theme_fonction = 'conversions_theme_commentaire';
		}
		if ($tableau[0]['bt_type'] == 'link' or $tableau[0]['bt_type'] == 'note') {
			if (!($theme_article = file_get_contents($GLOBALS['theme_post_link']))) die($GLOBALS['lang']['err_theme_introuvable']);
			$conversion_theme_fonction = 'conversions_theme_lien';
		}
		foreach ($tableau as $element) {
			$HTML_elmts .=  $conversion_theme_fonction($theme_article, $element);
		}
		$HTML = str_replace(replace_boucles($GLOBALS['theme_liste'], $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
	}
	else {
		$HTML_article = conversions_theme($theme_page, $data);
		$HTML = str_replace(replace_boucles($GLOBALS['theme_liste'], $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
	}
	echo $HTML;
}

function afficher_liste($tableau) {
	$HTML_elmts = '';
	$data = array();
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);
	$HTML_article = conversions_theme($theme_page, $data);
	if (!empty($tableau)) {
		$HTML_elmts .= '<ul>'."\n";
		foreach ($tableau as $e) {
			$short_date = substr($e['bt_date'], 0, 4).'/'.substr($e['bt_date'], 4, 2).'/'.substr($e['bt_date'], 6, 2);
			$HTML_elmts .= "\t".'<li>'.$short_date.' - <a href="'.$e['bt_link'].'">'.$e['bt_title'].'</a></li>'."\n";
		}
		$HTML_elmts .= '</ul>'."\n";
		$HTML = str_replace(replace_boucles($GLOBALS['theme_liste'], $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
	}
	else {
		$HTML = str_replace(replace_boucles($GLOBALS['theme_liste'], $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
	}
	echo $HTML;
}

