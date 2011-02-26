<?php
# *** LICENSE ***
# This file is part of BlogoText.
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
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

function valider_form_commentaire($commentaire, $captcha, $valid_captcha, $mode) {
		$erreurs = array();
		if (isset($_GET['post_id'])) {
			if (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_author']])))  {
					$erreurs[] = $GLOBALS['lang']['err_comm_auteur'];
			}
		}

		if (!isset($_GET['post_id'])) {
			if (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_author']]))) {
				$erreurs[] = $GLOBALS['lang']['err_comm_auteur'];
			}
			if ($commentaire[$GLOBALS['data_syntax']['comment_author']] == $GLOBALS['auteur']) {
				$erreurs[] = $GLOBALS['lang']['err_comm_auteur_name'];
			}
		}

		if (!preg_match('#^[\w.+~\'*-]+@[\w.-]+\.[a-zA-Z]{2,6}$#i', trim($commentaire[$GLOBALS['data_syntax']['comment_email']])) ) {
			$erreurs[] = $GLOBALS['lang']['err_comm_email'] ;
		}
		if (!strlen(trim($commentaire[$GLOBALS['data_syntax']['comment_content']])) or $commentaire[$GLOBALS['data_syntax']['comment_content']] == "<p></p>") {
			$erreurs[] = $GLOBALS['lang']['err_comm_contenu'];
		}
		if ( (!preg_match('/\d{14}/',$commentaire[$GLOBALS['data_syntax']['comment_article_id']]))
			or !is_numeric($commentaire[$GLOBALS['data_syntax']['comment_article_id']]) ) {
			$erreurs[] = $GLOBALS['lang']['err_comm_article_id'];
		}

		if (trim($commentaire[$GLOBALS['data_syntax']['comment_webpage']]) != "") {
			if (!preg_match('#^(https?://[\w.-]+)[a-z]{2,6}[-\#_\w?%*:.;=+\(\)/&~$,]*$#', trim($commentaire[$GLOBALS['data_syntax']['comment_webpage']])) ) {
				$erreurs[] = $GLOBALS['lang']['err_comm_webpage'];
			}
		}
	if ($mode != 'admin') {
		if ( $captcha != $valid_captcha or $captcha != is_numeric($captcha)) {
			$erreurs[] = $GLOBALS['lang']['err_comm_captcha'];
		}
	}
    return $erreurs;
}

function valider_form_billet($billet) {
	$date= decode_id($billet[$GLOBALS['data_syntax']['article_id']]);
	$erreurs = array();
	if (!strlen(trim($billet[$GLOBALS['data_syntax']['article_title']]))) {
		$erreurs[] = $GLOBALS['lang']['err_titre'];
	}
	if (!strlen(trim($billet[$GLOBALS['data_syntax']['article_abstract']]))) {
		$erreurs[] = $GLOBALS['lang']['err_chapo'];
	}
	if (!strlen(trim($billet[$GLOBALS['data_syntax']['article_content']]))) {
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
	if (!preg_match('#^[\w.+~\'*-]+@[\w.-]+\.[a-zA-Z]{2,6}$#i', trim($_POST['email']))) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_email'] ;
	}
	if (!strlen(trim($_POST['identifiant']))) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_identifiant'];
	}
	if ( ($_POST['identifiant']) !=$GLOBALS['identifiant'] and (!strlen($_POST['ancien-mdp'])) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_id_mdp'];
	}
	if ( (strlen(trim($_POST['ancien-mdp']))) and (ww_hach_sha($_POST['ancien-mdp'], $GLOBALS['salt']) != $GLOBALS['mdp']) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_oldmdp'];
	}
	if ( (strlen($_POST['ancien-mdp'])) and (strlen($_POST['nouveau-mdp']) < '6') ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_mdp'];
	}
	if ( (strlen($_POST['nouveau-mdp'])) and (!strlen($_POST['ancien-mdp'])) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_newmdp'] ;
	}
	if ( ($_POST['nb_maxi'] > '50') or ($_POST['nb_maxi'] < '5') ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_nbmax'];
	}
	return $erreurs;
}

?>
