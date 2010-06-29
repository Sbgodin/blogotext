<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function url_article($url) {
if ( (preg_match('/\d{4}\/\d{2}\/\d{2}\/\d{2}\/\d{2}\/\d{2}/',($url))) ) {
	return 'TRUE';
} else {
	return 'FALSE';
}
}

function url_date($url) {
if ( (preg_match('/\d{4}\/\d{2}\/\d{2}/',($url))) || (preg_match('/\d{4}\/\d{2}/',($url)))   ) {
	return 'TRUE';
} else {
	return 'FALSE';
}
}

function valider_form_commentaire($commentaire, $captcha, $valid_captcha) {
		$erreurs = array();
		if (isset($_GET['post_id'])) {
			if (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_author'][$GLOBALS['syntax_version']]])))  {
		   		$erreurs[] = $GLOBALS['lang']['err_comm_auteur'];
			}
		}

		if (!isset($_GET['post_id'])) {
			if (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_author'][$GLOBALS['syntax_version']]]))) {
	   		$erreurs[] = $GLOBALS['lang']['err_comm_auteur'];
			}
			if ($commentaire[$GLOBALS['data_syntax']['comment_author'][$GLOBALS['syntax_version']]] == $GLOBALS['auteur']) {
	   		$erreurs[] = $GLOBALS['lang']['err_comm_auteur_name'];
			}
		}

		if ( (! preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', trim($commentaire[$GLOBALS['data_syntax']['comment_email'][$GLOBALS['syntax_version']]])))
			OR (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_email'][$GLOBALS['syntax_version']]]))) ) {
			$erreurs[] = $GLOBALS['lang']['err_comm_email'] ;
		}
		if (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_content'][$GLOBALS['syntax_version']]])) or $commentaire[$GLOBALS['data_syntax']['comment_content'][$GLOBALS['syntax_version']]] == "<p></p>") {
			$erreurs[] = $GLOBALS['lang']['err_comm_contenu'];
		}
		if ( (!preg_match('/\d{14}/',$commentaire[$GLOBALS['data_syntax']['comment_article_id'][$GLOBALS['syntax_version']]]))
			OR !is_numeric($commentaire[$GLOBALS['data_syntax']['comment_article_id'][$GLOBALS['syntax_version']]]) ) {
			$erreurs[] = $GLOBALS['lang']['err_comm_article_id'];
		}
		if ( $captcha != $valid_captcha ) {
			$erreurs[] = $GLOBALS['lang']['err_comm_captcha'];
	    }
    return $erreurs;
}

function valider_form_billet($billet) {
	$date= decode_id($billet[$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']]]);
			$erreurs = array();
	    if (!strlen(trim($billet[$GLOBALS['data_syntax']['article_title'][$GLOBALS['syntax_version']]]))) {
	    $erreurs[] = $GLOBALS['lang']['err_titre'];
	    }
	    if (!strlen(trim($billet[$GLOBALS['data_syntax']['article_abstract'][$GLOBALS['syntax_version']]]))) {
	    $erreurs[] = $GLOBALS['lang']['err_chapo'];
	    }
	    if (!strlen(trim($billet[$GLOBALS['data_syntax']['article_content'][$GLOBALS['syntax_version']]]))) {
	    $erreurs[] = $GLOBALS['lang']['err_contenu'];
	    }
	    if (!preg_match('/\d{4}/',$date['annee'])) {
	    $erreurs[] = $GLOBALS['lang']['err_annee'];
	    }
	   	if (!preg_match('/\d{2}/',$date['mois'])) {
	    $erreurs[] = $GLOBALS['lang']['err_mois'];
	    }
	    if ( (!preg_match('/\d{2}/',$date['heure'])) || ($date['heure'] >'23') || !is_numeric($date['heure']) ) {
	    $erreurs[] = $GLOBALS['lang']['err_heure'];
	    }
	    if ( (!preg_match('/\d{2}/',$date['minutes'])) || ($date['minutes'] >'59') || !is_numeric($date['minutes'])) {
 			$erreurs[] = $GLOBALS['lang']['err_minutes'];
 			}
 			if ( (!preg_match('/\d{2}/',$date['secondes'])) || ($date['secondes'] >'59') || !is_numeric($date['secondes'])) {
 			$erreurs[] = $GLOBALS['lang']['err_secondes'];
 			}
    return $erreurs;
}

function valider_form_preferences() {		
			$erreurs = array();
	    if (!strlen(trim($_POST['auteur']))) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_auteur'];
	    }
	    if ( (! preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', trim($_POST['email']))) OR (!strlen(trim($_POST['email']))) ) {
    	$erreurs[] = $GLOBALS['lang']['err_prefs_email'] ;
	    }
	    if (!strlen(trim($_POST['identifiant']))) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_identifiant'];
	    }
	   	if ( ($_POST['identifiant']) !=$GLOBALS['identifiant'] AND (!strlen($_POST['ancien-mdp'])) ) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_id_mdp'];
	    }
	    if ( (strlen(trim($_POST['ancien-mdp']))) AND (ww_hach_sha($_POST['ancien-mdp'], $GLOBALS['salt']) != $GLOBALS['mdp']) ) {
    	$erreurs[] = $GLOBALS['lang']['err_prefs_oldmdp'];
	    }
	    if ( (strlen($_POST['ancien-mdp'])) AND (strlen($_POST['nouveau-mdp']) < '6') ) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_mdp'];
	    }
	    if ( (strlen($_POST['nouveau-mdp'])) AND (!strlen($_POST['ancien-mdp'])) ) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_newmdp'] ;
	    }
	    if ( ($_POST['nb_maxi'] > '50') OR ($_POST['nb_maxi'] < '5') ) {
	    $erreurs[] = $GLOBALS['lang']['err_prefs_nbmax'];
	    }
    return $erreurs;
}

?>
