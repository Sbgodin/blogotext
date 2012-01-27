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

function conversions_theme($texte) {
// General
	if (isset($GLOBALS['charset'])) { $texte = str_replace($GLOBALS['balises']['charset'], $GLOBALS['charset'], $texte) ; }
	if (isset($GLOBALS['version'])) { $texte = str_replace($GLOBALS['balises']['version'], $GLOBALS['version'], $texte) ; }
// Blog
	if (isset($GLOBALS['nom_du_site'])) { $texte = str_replace($GLOBALS['balises']['blog_nom'], $GLOBALS['nom_du_site'], $texte) ; }
	if (isset($GLOBALS['theme_style'])) { $texte = str_replace($GLOBALS['balises']['style'], $GLOBALS['theme_style'], $texte) ; }
	if (isset($GLOBALS['description'])) { $texte = str_replace($GLOBALS['balises']['blog_description'], $GLOBALS['description'], $texte) ; }
	if (isset($GLOBALS['racine'])) { $texte = str_replace($GLOBALS['balises']['racine_du_site'], $GLOBALS['racine'], $texte) ; }
	if (isset($GLOBALS['auteur'])) { $texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte) ; }
	if (isset($GLOBALS['email'])) { $texte = str_replace($GLOBALS['balises']['blog_email'], $GLOBALS['email'], $texte) ; }
// Formulaires
	$texte = str_replace($GLOBALS['balises']['form_recherche'], moteur_recherche(), $texte) ;
	if (isset($GLOBALS['calendrier']) and preg_match('#'.$GLOBALS['balises']['form_calendrier'].'#', $texte)) { $texte = str_replace($GLOBALS['balises']['form_calendrier'], $GLOBALS['calendrier'], $texte) ;}
	if (isset($GLOBALS['form_commentaire']) and preg_match('#'.$GLOBALS['balises']['form_commentaire'].'#', $texte)) { $texte = str_replace($GLOBALS['balises']['form_commentaire'], $GLOBALS['form_commentaire'], $texte) ; }
		else { $texte = str_replace($GLOBALS['balises']['form_commentaire'], '', $texte);}
	if (isset($GLOBALS['rss'])) { $texte = str_replace($GLOBALS['balises']['rss'], $GLOBALS['rss'], $texte) ; }
		else { $texte = str_replace($GLOBALS['balises']['rss'], '', $texte); }
	if (isset($GLOBALS['balises']['commentaires_encart']) and preg_match('#'.$GLOBALS['balises']['commentaires_encart'].'#', $texte)) { $texte = str_replace($GLOBALS['balises']['commentaires_encart'], encart_commentaires(), $texte);}
	if (isset($GLOBALS['balises']['categories_encart']) and preg_match('#'.$GLOBALS['balises']['categories_encart'].'#', $texte)) { $texte = str_replace($GLOBALS['balises']['categories_encart'], encart_categories(), $texte);}
	if (isset($GLOBALS['rss_comments'])) { $texte = str_replace($GLOBALS['balises']['rss_comments'], $GLOBALS['rss_comments'], $texte);}

 return $texte;
}


function conversions_theme_commentaire($texte, $commentaire) {
// Commentaire
	if (isset($commentaire)) {
		if (isset($commentaire['contenu'])) {	$texte = str_replace($GLOBALS['balises']['commentaire_contenu'], $commentaire['contenu'], $texte); }
		if (isset($commentaire['id'])) {			$texte = str_replace($GLOBALS['balises']['commentaire_date'], date_formate($commentaire['id']), $texte); }
		if (isset($commentaire['id'])) {			$texte = str_replace($GLOBALS['balises']['commentaire_heure'], heure_formate($commentaire['id']), $texte); }
		if (isset($commentaire['email'])) {		$texte = str_replace($GLOBALS['balises']['commentaire_email'], $commentaire['email'], $texte); }
		if (isset($commentaire['auteur'])) {	$texte = str_replace($GLOBALS['balises']['commentaire_auteur'], $commentaire['auteur_lien'], $texte); }
		if (isset($commentaire['webpage'])) {	$texte = str_replace($GLOBALS['balises']['commentaire_webpage'], $commentaire['webpage'], $texte); }
		if (isset($commentaire['lienreply'])) {$texte = str_replace($GLOBALS['balises']['commentaire_reply'], $commentaire['lienreply'], $texte); }
		if (isset($commentaire['anchor'])) {	$texte = str_replace($GLOBALS['balises']['commentaire_anchor'], $commentaire['anchor'], $texte); }
	}
	 return $texte;
}


function conversions_theme_article($texte, $billet) {
	if (isset($GLOBALS['rss_comments'])) { $texte = str_replace($GLOBALS['balises']['rss_comments'], $GLOBALS['rss_comments'], $texte); }
// Article
	if (isset($billet)) {
		if (isset($billet['titre'])) {
			$texte = str_replace($GLOBALS['balises']['article_titre'], $billet['titre'], $texte);
			$billet['article_titre_url'] = urlencode(strtolower(strip_tags($billet['titre'])));
		}
		if (isset($billet['chapo'])) {				$texte = str_replace($GLOBALS['balises']['article_chapo'], $billet['chapo'], $texte); }
		if (isset($billet['contenu'])) {				$texte = str_replace($GLOBALS['balises']['article_contenu'], $billet['contenu'], $texte); }
		if (isset($billet['id'])) {					$texte = str_replace($GLOBALS['balises']['article_date'], date_formate($billet['id']), $texte); }
		if (isset($billet['id'])) {					$texte = str_replace($GLOBALS['balises']['article_heure'], heure_formate($billet['id']), $texte); }
		if (isset($billet['mots_cles'])) {			$texte = str_replace($GLOBALS['balises']['article_motscles'], $billet['mots_cles'], $texte); }
		if (isset($billet['nb_comments'])) {		$texte = str_replace($GLOBALS['balises']['nb_commentaires'], nombre_commentaires(count($billet['nb_comments'])), $texte); }
		if (isset($billet['auteur'])) {				$texte = str_replace($GLOBALS['balises']['commentaire_auteur'], $billet['auteur'], $texte); }
		if (isset($billet['lien'])) {					$texte = str_replace($GLOBALS['balises']['article_lien'], $billet['lien'], $texte); }
		if (isset($billet['article_titre_url'])) {$texte = str_replace($GLOBALS['balises']['article_titre_url'], $billet['article_titre_url'], $texte); }
		if (isset($billet['categories'])) {			$texte = str_replace($GLOBALS['balises']['article_tags'], liste_tags_article($billet, '1'), $texte); }
		if (isset($billet['categories'])) {			$texte = str_replace($GLOBALS['balises']['article_tags_plain'], liste_tags_article($billet, '0'), $texte); }
	}
	return $texte;
}

function charger_template($fichier_theme, $balise, $renvoi) {
	if ($theme_page = file_get_contents($fichier_theme)) {
		if (isset($balise)) {
			$theme_page = str_replace($balise, $balise, $theme_page) ;
		}
		$template_liste = parse_theme($theme_page, $balise);
		$balise_debut = strpos($theme_page, '{'.$balise.'}');
		$balise_fin = strpos($theme_page, '{/'.$balise.'}') + strlen($balise) + 3;
		if ($renvoi == 'liste') {
			return $template_liste;
		} elseif ($renvoi == 'debut') {
			$debut = conversions_theme(substr($theme_page, 0, $balise_debut));
			return $debut;
		} elseif ($renvoi == 'fin') {
			$fin = conversions_theme(substr($theme_page, $balise_fin));
			return $fin;
		}
	} else {
		echo 'Fichier theme liste introuvable ou illisible';
	}
}

function parse_theme($fichier, $balise) {
	if (isset($fichier)) {
		if (preg_match('#\{'.$balise.'\}#',$fichier)) {
			$sizeitem = strlen('{'.$balise.'}');
			$debut = strpos($fichier, '{'.$balise.'}') + $sizeitem;
			$fin = strpos($fichier, '{/'.$balise.'}');
			$lenght = $fin - $debut;
			$return = substr($fichier, $debut, $lenght); 
			return $return;
		} else {
			return '';
		}
	} else {
		erreur('Impossible de lire le fichier');
	}
}

function afficher_index($tableau) {
	if ($debut = charger_template($GLOBALS['theme_liste'], $GLOBALS['boucles']['articles'], 'debut') and
		($template_liste = charger_template($GLOBALS['theme_liste'], $GLOBALS['boucles']['articles'], 'liste')) and
		($fin = charger_template($GLOBALS['theme_liste'], $GLOBALS['boucles']['articles'], 'fin')) ) {
		echo $debut;
		if (!empty($tableau)) {
			liste_articles($tableau, $template_liste);
		} else {
			erreur($GLOBALS['lang']['note_no_article']);
		}
	echo $fin;
	}
}

// only used by the main page of the blog
function afficher_article($id) {
	$file_id = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($id);
	if ((file_exists($file_id) and parse_xml($file_id, $GLOBALS['data_syntax']['article_status']) != 0 ) or !empty($_SESSION['nom_utilisateur'])) {
		// POST INIT
		$billet = init_billet('public', $id);

		// TRAITEMENT
		$erreurs_form = array();
		if (isset($_POST['_verif_envoi']) and (parse_xml($file_id, $GLOBALS['data_syntax']['article_allow_comments']) == '1' )) {
			// COMMENT POST INIT
			$comment = init_post_comment($id, 'public');
			if (isset($_POST['enregistrer'])) {
				$erreurs_form = valider_form_commentaire($comment, $_POST['captcha'], ($_SESSION['captx']+$_SESSION['capty']), 'public');
			}
		}
		if (empty($erreurs_form)) {
			afficher_form_commentaire($id, 'public');
			if (isset($_POST['enregistrer'])) {
				$done = fichier_data($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $comment);
				if ($done == TRUE and $GLOBALS['comm_defaut_status'] == 1) { // if file saved and comment published...
					send_emails($comment[$GLOBALS['data_syntax']['article_id']]); // ...send emails to people who are subscriben to "email on new comment"
				}
			}
		} else {
			afficher_form_commentaire($id, 'public', $erreurs_form);
		}
		// COMMENT INIT
		if (!empty($billet['nb_comments'])) {
			foreach ($billet['nb_comments'] as $uid => $com) {
				$commentaire[$uid] = init_comment('public', get_id($com));
			}
		}
		$theme_page = file_get_contents($GLOBALS['theme_article']);

		$debut = charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'debut');
		$template_comments = charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'liste');
		$fin = charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'fin');

		echo conversions_theme_article($debut, $billet);
		if (isset($commentaire)) {
			foreach ($commentaire as $element) {
				$comm = conversions_theme_commentaire($template_comments, $element);
				echo $comm;
			}
		}
		echo conversions_theme($fin);

	} else {
		afficher_index(NULL);
	}

}

?>
