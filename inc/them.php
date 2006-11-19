<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function conversions_theme($texte, $billet='', $commentaire='') {
// General
	if (isset($GLOBALS['charset'])) { $texte = str_replace($GLOBALS['balises']['charset'], $GLOBALS['charset'], $texte) ; }
	if (isset($GLOBALS['version'])) { $texte = str_replace($GLOBALS['balises']['version'], $GLOBALS['version'], $texte) ; }
// Blog
	if (isset($GLOBALS['nom_du_site'])) { $texte = str_replace($GLOBALS['balises']['blog_nom'], $GLOBALS['nom_du_site'], $texte) ; }
	if (isset($GLOBALS['theme_style'])) { $texte = str_replace('{style}', $GLOBALS['theme_style'], $texte) ; }
	if (isset($GLOBALS['description'])) { $texte = str_replace($GLOBALS['balises']['blog_description'], $GLOBALS['description'], $texte) ; }
	if (isset($GLOBALS['racine'])) { $texte = str_replace('{racine_du_site}', $GLOBALS['racine'], $texte) ; }
	if (isset($GLOBALS['auteur'])) { $texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte) ; }
	if (isset($GLOBALS['email'])) { $texte = str_replace($GLOBALS['balises']['blog_email'], $GLOBALS['email'], $texte) ; }
// Formulaires
	if (isset($GLOBALS['form_recherche'])) { $texte = str_replace($GLOBALS['balises']['form_recherche'], $GLOBALS['form_recherche'], $texte) ; }
	if (isset($GLOBALS['calendrier'])) { $texte = str_replace($GLOBALS['balises']['form_calendrier'], $GLOBALS['calendrier'], $texte) ; }
	if (isset($GLOBALS['form_commentaire'])) { $texte = str_replace($GLOBALS['balises']['form_commentaire'], $GLOBALS['form_commentaire'], $texte) ; }
	if (isset($GLOBALS['formulaire_commentaire'])) { $texte = str_replace('{formulaire_commentaire}', $GLOBALS['formulaire_commentaire'], $texte);}
		else { $texte = str_replace('{formulaire_commentaire}', '', $texte);}
	if (isset($GLOBALS['rss'])) { $texte = str_replace('{rss}', $GLOBALS['rss'], $texte) ; }
		else { $texte = str_replace('{rss}', '', $texte); }
// Article
	if (isset($billet)) {
		if (isset($billet['titre'])) {$texte = str_replace($GLOBALS['balises']['article_titre'], $billet['titre'], $texte) ;}
		if (isset($billet['chapo'])) { $texte = str_replace($GLOBALS['balises']['article_chapo'], $billet['chapo'], $texte); }
		if (isset($billet['contenu'])) { $texte = str_replace($GLOBALS['balises']['article_contenu'], $billet['contenu'], $texte); }
		if (isset($billet['id'])) { $texte = str_replace($GLOBALS['balises']['article_date'], date_formate($billet['id']), $texte); }
		if (isset($billet['id'])) { $texte = str_replace($GLOBALS['balises']['article_heure'], heure_formate($billet['id']), $texte); }
		if (isset($billet['mots_cles'])) { $texte = str_replace($GLOBALS['balises']['article_motscles'], $billet['mots_cles'], $texte); }
		if (isset($billet['nb_comments'])) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], nombre_commentaires($billet['nb_comments']), $texte); }
		if (isset($billet['auteur'])) { $texte = str_replace($GLOBALS['balises']['commentaire_auteur'], $billet['auteur'], $texte); }
		if (isset($billet['lien'])) { $texte = str_replace($GLOBALS['balises']['article_lien'], $billet['lien'], $texte); }
	}
// Commentaire
	if (isset($commentaire)) {
		if (isset($commentaire['contenu'])) { $texte = str_replace($GLOBALS['balises']['commentaire_contenu'], $commentaire['contenu'], $texte); }
		if (isset($commentaire['id'])) { $texte = str_replace($GLOBALS['balises']['commentaire_date'], date_formate($commentaire['id']), $texte); }
		if (isset($commentaire['id'])) { $texte = str_replace($GLOBALS['balises']['commentaire_heure'], heure_formate($commentaire['id']), $texte); }
		if (isset($commentaire['email'])) { $texte = str_replace($GLOBALS['balises']['commentaire_email'], $commentaire['email'], $texte); }
		if (isset($commentaire['auteur'])) { $texte = str_replace($GLOBALS['balises']['commentaire_auteur'], $commentaire['auteur'], $texte); }
	}
	 return $texte;
}

function charger_template($fichier_theme, $balise, $renvoi) {
if ($theme_page = file_get_contents($fichier_theme)) {
	if (isset($balise)) {
		$theme_page = str_replace($balise, $balise['0'], $theme_page) ;
		}
			$template_liste = parse_theme($theme_page, $balise['0']);
				$balise_debut = strpos($theme_page, '{'.$balise['0'].'}');
				$balise_fin = strpos($theme_page, '{/'.$balise['0'].'}') + strlen($balise['0']) + '3';
  		$debut = conversions_theme(substr($theme_page, '0', $balise_debut));
  		$fin = conversions_theme(substr($theme_page, $balise_fin));
  		if ($renvoi == 'liste') {
  			return $template_liste;
  		} else if ($renvoi == 'debut') {
  			return $debut;
  		} else if ($renvoi == 'fin') {
  			return $fin;
  		}
	 } else {
			print 'Fichier theme liste introuvable ou illisible';
	}
}

function parse_theme($fichier, $balise) {
	if (isset($fichier)) {
			if (ereg('\{'.$balise.'\}',$fichier)) {
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

// AFFICHAGE INDEX.PHP

function afficher_index($tableau) {
header ('Content-type: text/html; charset=UTF-8');
if 	($template_liste = charger_template($GLOBALS['theme_liste'], $GLOBALS['boucles']['articles'], 'liste') AND 
		($debut = charger_template($GLOBALS['theme_liste'], $GLOBALS['boucles']['articles'], 'debut')) AND
		($fin = charger_template($GLOBALS['theme_liste'], $GLOBALS['boucles']['articles'], 'fin')) ) {
	print $debut;
	if (isset($tableau)) {
		liste_articles($tableau, $template_liste);
	} else {
	erreur($GLOBALS['lang']['note_no_article']);
	}
	print $fin;
		}
}

function afficher_article($id) {
header ('Content-type: text/html; charset=UTF-8');

// POST INIT
$billet = init_billet('public', $id);
// COMMENT POST INIT
$comment= init_post_comment($id);

// TRAITEMENT
$erreurs_form= array();
if (isset($_POST['_verif_envoi'])) {
		$erreurs_form= valider_form_commentaire($comment, $_POST['captcha'], ($GLOBALS['captcha']['x']+$GLOBALS['captcha']['y']));
}
if ( empty($erreurs_form) )  {
		afficher_form_commentaire($id, 'public', $billet['allow_comments']);
		if (isset($_POST['enregistrer'])) {
			fichier_data($GLOBALS['dossier_commentaires'], $comment);
		}
} else {
			afficher_form_commentaire($id, 'public', $billet['allow_comments'], $erreurs_form);
} // FIN TREATMENT

// COMMENT INIT
if ($liste_commentaires=liste_commentaires($GLOBALS['dossier_commentaires'], $id)) {
	foreach ($liste_commentaires as $nb => $comment) {
		$commentaire[$nb] = init_comment('public', remove_ext($comment));
	}
}
$theme_page = file_get_contents($GLOBALS['theme_article']);
$template_comments = charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'liste');
				$debut = charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'debut');
				$fin = charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'fin');
				print conversions_theme($debut, $billet);
				if (isset($commentaire)) {
					foreach ($commentaire as $element) {
						$comm = conversions_theme($template_comments, '', $element);
						print $comm;
					}
				}
				print conversions_theme($fin, $billet);
}

?>