<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
error_reporting(E_ALL);
if ( !file_exists('../config/user.php') || !file_exists('../config/prefs.php') ) {
	header('Location: install.php');
}
session_start() ;

if (isset($_POST['_verif_envoi'])) {
	session_regenerate_id();
	header('Location: index.php');
}

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$ip = $_SERVER['REMOTE_ADDR'];
}
$_SESSION['antivol'] = md5($_SERVER['HTTP_USER_AGENT'].$ip);
$_SESSION['timestamp'] = time();

require_once '../inc/inc.php';

afficher_top('Identification');
echo '<div id="axe">'."\n";
echo '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');

if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form()) {
		afficher_form($erreurs_form);
	} else {
		$_SESSION['nom_utilisateur'] = $_POST['nom_utilisateur'].ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']);
	}
} else {
	afficher_form();
}

function afficher_form($erreur = '') {
	if ($erreur) {
		erreur($erreur);
	}
		echo	'<form method="post" action="'.$_SERVER['PHP_SELF'].'">'."\n";
		echo	'<div id="auth">'."\n";
		echo	'<p><label for="nom_utilisateur">'.$GLOBALS['lang']['label_identifiant'].'</label>'."\n";
		echo	'<input type="text" id="nom_utilisateur" name="nom_utilisateur" value="" /></p>'."\n";
		echo	'<p><label for="mot_de_passe">'.$GLOBALS['lang']['label_motdepasse'].'</label>';
		echo	'<input type="password" id="mot_de_passe" name="mot_de_passe" value="" /></p>'."\n";
		echo	'<input class="inpauth" type="submit" name="submit" value="'.$GLOBALS['lang']['connexion'].'" />';
		echo	'<input type="hidden" name="_verif_envoi" value="1" />';
		echo	'</div>'."\n";
		echo	'</form>';
}
	
function valider_form() {
		$mot_de_passe_ok = $GLOBALS['mdp'].$GLOBALS['identifiant'];
		$mot_de_passe_essai = ww_hach_sha($_POST['mot_de_passe'], $GLOBALS['salt']).$GLOBALS['identifiant'];
			if ( ($mot_de_passe_essai !=  $mot_de_passe_ok) OR ($_POST['nom_utilisateur'] != $GLOBALS['identifiant']) ) {
				$erreur = $GLOBALS['lang']['err_connexion'];
			return $erreur;
		}
}

footer();
?>
