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
		if ($commentaire[$GLOBALS['data_syntax']['comment_author']] == $GLOBALS['auteur'] and empty($_SESSION['nom_utilisateur'])) {
			$erreurs[] = $GLOBALS['lang']['err_comm_auteur_name'];
		}
	}

	if (!empty($commentaire[$GLOBALS['data_syntax']['comment_email']]) or $GLOBALS['require_email'] == 1) {
		if (!preg_match('#^[\w.+~\'*-]+@[\w.-]+\.[a-zA-Z]{2,6}$#i', trim($commentaire[$GLOBALS['data_syntax']['comment_email']])) ) {
			$erreurs[] = $GLOBALS['lang']['err_comm_email'] ;
		}
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
	$date = decode_id($billet[$GLOBALS['data_syntax']['article_id']]);
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
	if ( (!preg_match('/\d{2}/',$date['mois'])) or ($date['mois'] > '12') ) {
		$erreurs[] = $GLOBALS['lang']['err_mois'];
	}
	if ( (!preg_match('/\d{2}/',$date['jour'])) or ($date['jour'] > date('t', mktime(0, 0, 0, $date['mois'], 1, $date['annee'])))  ) {
		$erreurs[] = $GLOBALS['lang']['err_jour'];
	}
	if ( (!preg_match('/\d{2}/',$date['heure'])) or ($date['heure'] > 23) ) {
		$erreurs[] = $GLOBALS['lang']['err_heure'];
	}
	if ( (!preg_match('/\d{2}/',$date['minutes'])) or ($date['minutes'] > 59) ) {
		$erreurs[] = $GLOBALS['lang']['err_minutes'];
	}
	if ( (!preg_match('/\d{2}/',$date['secondes'])) or ($date['secondes'] > 59) ) {
		$erreurs[] = $GLOBALS['lang']['err_secondes'];
	}
	return $erreurs;
}

function valider_form_preferences() {
	$erreurs = array();
	if (!strlen(trim($_POST['auteur']))) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_auteur'];
	}
	if ($GLOBALS['require_email'] == 1) { 
		if (!preg_match('#^[\w.+~\'*-]+@[\w.-]+\.[a-zA-Z]{2,6}$#i', trim($_POST['email']))) {
			$erreurs[] = $GLOBALS['lang']['err_prefs_email'] ;
		}
	}
	if (!preg_match('#^(https?://).*/$#', $_POST['racine'])) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_racine_slash'];
	}
	if (!strlen(trim($_POST['identifiant']))) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_identifiant'];
	}
	if ( ($_POST['identifiant']) !=$GLOBALS['identifiant'] and (!strlen($_POST['mdp'])) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_id_mdp'];
	}
	if ( (strlen(trim($_POST['mdp']))) and (ww_hach_sha($_POST['mdp'], $GLOBALS['salt']) != $GLOBALS['mdp']) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_oldmdp'];
	}
	if ( (strlen($_POST['mdp'])) and (strlen($_POST['mdp_rep']) < '6') ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_mdp'];
	}
	if ( (strlen($_POST['mdp_rep'])) and (!strlen($_POST['mdp'])) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_newmdp'] ;
	}
	if ( ($_POST['nb_maxi'] > '50') or ($_POST['nb_maxi'] < '5') ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_nbmax'];
	}
	return $erreurs;
}

function valider_form_image() {
	$erreurs = array();
	if (($_FILES['fichier']['error'] == UPLOAD_ERR_INI_SIZE) or ($_FILES['fichier']['error'] == UPLOAD_ERR_FORM_SIZE)) {
		$erreurs[] = 'Fichier trop gros';
	} elseif ($_FILES['fichier']['error'] == UPLOAD_ERR_PARTIAL) {
		$erreurs[] = 'dépot interrompu';
	} elseif ($_FILES['fichier']['error'] == UPLOAD_ERR_NO_FILE) {
		$erreurs[] = 'aucun fichier déposé';
	}
	return $erreurs;
}

?>
