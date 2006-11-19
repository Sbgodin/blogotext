<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(E_ALL);
if ( !file_exists('../config/user.php') || !file_exists('../config/prefs.php') ) {
	header('Location: install.php');
}
session_start() ;
if (isset($_POST['_verif_envoi'])) {
	header('Location: index.php');
}
require_once '../inc/inc.php';

afficher_top('Identification');
print '<div id="axe">'."\n";
print '<div id="pageauth">'."\n";
afficher_titre ($GLOBALS['nom_application'], 'logo', '1');

if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form()) {
		afficher_form($erreurs_form);
	} else {
		$_SESSION['nom_utilisateur'] = $_POST['nom_utilisateur'].crypt($_POST['mot_de_passe'], $GLOBALS['salt']);
	}
} else {
	afficher_form();
}

function afficher_form($erreur = '') {
	if ($erreur) {
		erreur($erreur);
	}
		print	'<form method="post" action="'.$_SERVER['PHP_SELF'].'">'."\n";
		print	'<div id="auth">'."\n";
		print	'<p><label for="nom_utilisateur">'.$GLOBALS['lang']['label_identifiant'].'</label>'."\n";
		print	'<input type="text" id="nom_utilisateur" name="nom_utilisateur" value="" /></p>'."\n";
		print	'<p><label for="mot_de_passe">'.$GLOBALS['lang']['label_motdepasse'].'</label>';
		print	'<input type="password" id="mot_de_passe" name="mot_de_passe" value="" /></p>'."\n";
		print	'<input class="inpauth" type="submit" name="submit" value="'.$GLOBALS['lang']['connexion'].'" />';
		print	'<input type="hidden" name="_verif_envoi" value="1" />';
		print	'</div>'."\n";
		print	'</form>';
}
	
function valider_form() {
		$mot_de_passe_ok = $GLOBALS['mdp'];
		$mot_de_passe_essai = crypt($_POST['mot_de_passe'], $GLOBALS['salt']);
			if ( ($mot_de_passe_essai !=  $mot_de_passe_ok) OR ($_POST['nom_utilisateur'] != $GLOBALS['identifiant']) ) {
				$erreur = $GLOBALS['lang']['err_connexion'];
			return $erreur;
		}
}

footer();
?>