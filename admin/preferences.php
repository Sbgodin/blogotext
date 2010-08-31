<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(E_ALL);
require_once '../inc/inc.php';
session_start() ;
/*if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) ) {
	header('Location: auth.php');
	exit;
}*/

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$ip = $_SERVER['REMOTE_ADDR'];
}


if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) or (!isset($_SESSION['antivol'])) or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip)) or (!isset($_SESSION['timestamp'])) or ($_SESSION['timestamp'] < time()-1800)) {
	header('Location: logout.php');
	exit;
}
$_SESSION['timestamp'] = time();

if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form_preferences()) {
		afficher_form($erreurs_form);
	} else {        		
		if ( (fichier_user() == 'TRUE') AND (fichier_prefs() == 'TRUE') ) {
		redirection($_SERVER['PHP_SELF'].'?msg=confirm_prefs_maj');
		}
	}
	} else {	
	afficher_form();
}

function afficher_form($erreurs = '') {
$titre_page= $GLOBALS['lang']['preferences'];
afficher_top($titre_page);
afficher_msg();
echo '<div id="top">';
echo '<ul id="nav">';

afficher_menu('preferences.php');

echo '</ul>';
echo '</div>';

echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

erreurs($erreurs);

echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" >' ;

echo '<fieldset class="pref">';
legend($GLOBALS['lang']['prefs_legend_utilisateur'], 'legend-user');

form_text('auteur', $GLOBALS['auteur'], $GLOBALS['lang']['pref_auteur']);
form_text('email', $GLOBALS['email'], $GLOBALS['lang']['pref_email']);
form_text('nomsite', $GLOBALS['nom_du_site'], $GLOBALS['lang']['pref_nom_site']);
textarea('description', $GLOBALS['description'], $GLOBALS['lang']['pref_desc'], '35', '3');
echo '</fieldset>';

echo '<fieldset class="pref">';
legend($GLOBALS['lang']['prefs_legend_apparence'], 'legend-apparence');

$choix_nb_articles= array('5'=>'5', '10'=>'10', '15'=>'15', '20'=>'20', '25'=>'25');
form_select('nb_maxi', $choix_nb_articles, $GLOBALS['nb_maxi'],$GLOBALS['lang']['pref_nb_maxi']);
$choix_nb_com= array('3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '10'=>'10', '15'=>'15', '20'=>'20');
form_select('nb_maxi_comm', $choix_nb_com, $GLOBALS['nb_maxi_comm'],$GLOBALS['lang']['pref_nb_maxi_comm']);
$themes= liste_themes($GLOBALS['dossier_themes']);
form_select('theme', $themes, $GLOBALS['theme_choisi'],$GLOBALS['lang']['pref_theme']);
echo '</fieldset>';

echo '<fieldset class="pref">';
legend($GLOBALS['lang']['prefs_legend_securite'], 'legend-securite');
form_text('identifiant', $GLOBALS['identifiant'], $GLOBALS['lang']['pref_identifiant']);
form_password('ancien-mdp', '', $GLOBALS['lang']['pref_mdp']);
form_password('nouveau-mdp', '', $GLOBALS['lang']['pref_mdp_nouv']);

select_yes_no('connexion_delay', $GLOBALS['connexion_delai'], $GLOBALS['lang']['pref_connexion_delai'] );
select_yes_no('connexion_captcha', $GLOBALS['connexion_captcha'], $GLOBALS['lang']['pref_connexion_captcha'] );
echo '</fieldset>';

echo '<fieldset class="pref">';
legend($GLOBALS['lang']['prefs_legend_dateheure'], 'legend-dateheure');
form_format_date($GLOBALS['format_date']);
form_format_heure($GLOBALS['format_heure']);
echo '</fieldset>';

echo '<fieldset class="pref">';
legend($GLOBALS['lang']['prefs_legend_config'], 'legend-config');
form_langue($GLOBALS['lang']['id']);
$nbs= array('10'=>'10', '25'=>'25', '50'=>'50', '100'=>'100', '300'=>'300');
form_select('nb_list', $nbs, $GLOBALS['nb_list'],$GLOBALS['lang']['pref_nb_list']);
form_select('nb_list_com', $nbs, $GLOBALS['nb_list_com'],$GLOBALS['lang']['pref_nb_list_com']);
select_yes_no('apercu', $GLOBALS['activer_apercu'], $GLOBALS['lang']['pref_apercu'] );
select_yes_no('global_coments', $GLOBALS['activer_global_comments'], $GLOBALS['lang']['pref_allow_global_coms']);
form_text('racine', $GLOBALS['racine'], $GLOBALS['lang']['pref_racine']);

form_check('onglet_commentaires', $GLOBALS['onglet_commentaires'], $GLOBALS['lang']['pref_aff_onglet_comm']);
form_check('onglet_images', $GLOBALS['onglet_images'], $GLOBALS['lang']['pref_aff_onglet_images']);

echo '</fieldset>';

echo '<fieldset class="pref">';
legend($GLOBALS['lang']['titre_backup'], 'legend-config');
echo '<p><a href="backup.php">'.$GLOBALS['lang']['pref_make_backup'].'</a></p>';

echo '</fieldset>';

echo '<div id="bt">';
hidden_input('_verif_envoi', '1');
input_enregistrer();
echo '</div>';
echo '</form>';
footer();
}

?>
