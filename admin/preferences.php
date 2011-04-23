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
//error_reporting(E_ALL);
require_once '../inc/inc.php';

check_session();

if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form_preferences()) {
		afficher_form($erreurs_form);
	} else {        		
		if ( (fichier_user() === TRUE) and (fichier_prefs() === TRUE) ) {
		redirection($_SERVER['PHP_SELF'].'?msg=confirm_prefs_maj');
		}
	}
} else {	
	afficher_form();
}

function afficher_form($erreurs= '') {
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

	echo '<form id="preferences" method="post" action="'.$_SERVER['PHP_SELF'].'" >' ;
		$field_user = '<fieldset class="pref">';
		$field_user .= legend($GLOBALS['lang']['prefs_legend_utilisateur'], 'legend-user');
		$field_user .= form_text('auteur', $GLOBALS['auteur'], $GLOBALS['lang']['pref_auteur']);
		$field_user .= form_text('email', $GLOBALS['email'], $GLOBALS['lang']['pref_email']);
		$field_user .= form_text('nomsite', $GLOBALS['nom_du_site'], $GLOBALS['lang']['pref_nom_site']);
		$field_user .= form_text('racine', $GLOBALS['racine'], $GLOBALS['lang']['pref_racine']);
		$field_user .= textarea('description', $GLOBALS['description'], $GLOBALS['lang']['pref_desc'], '35', '3');
		$field_user .= '</fieldset>';
	echo $field_user;

		$field_apparence = '<fieldset class="pref">';
		$field_apparence .= legend($GLOBALS['lang']['prefs_legend_apparence'], 'legend-apparence');
		$choix_nb_articles= array('5'=>'5', '10'=>'10', '15'=>'15', '20'=>'20', '25'=>'25');
		$field_apparence .= form_select('nb_maxi', $choix_nb_articles, $GLOBALS['max_bill_acceuil'],$GLOBALS['lang']['pref_nb_maxi']);
		$choix_nb_com= array('3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '10'=>'10', '15'=>'15', '20'=>'20');
		$field_apparence .= form_select('nb_maxi_comm', $choix_nb_com, $GLOBALS['max_comm_encart'],$GLOBALS['lang']['pref_nb_maxi_comm']);
		$themes = liste_themes($GLOBALS['dossier_themes']);
		$field_apparence .= form_select('theme', $themes, $GLOBALS['theme_choisi'],$GLOBALS['lang']['pref_theme']);
		$field_apparence .= '</fieldset>';
	echo $field_apparence;

		$field_securite = '<fieldset class="pref">';
		$field_securite .= legend($GLOBALS['lang']['prefs_legend_securite'], 'legend-securite');
		$field_securite .= form_text('identifiant', $GLOBALS['identifiant'], $GLOBALS['lang']['pref_identifiant']);
		$field_securite .= form_password('ancien-mdp', '', $GLOBALS['lang']['pref_mdp']);
		$field_securite .= form_password('nouveau-mdp', '', $GLOBALS['lang']['pref_mdp_nouv']);
		$field_securite .= '<p>'."\n";
		$field_securite .= select_yes_no('connexion_captcha', $GLOBALS['connexion_captcha'], $GLOBALS['lang']['pref_connexion_captcha'] );
		$field_securite .= '</p>'."\n";
		$field_securite .= '<p>'."\n";
		$nbss = array('1' => $GLOBALS['lang']['pref_comm_black_list'], '0' => $GLOBALS['lang']['pref_comm_white_list']);
		$field_securite .= form_select('comm_defaut_status', $nbss, $GLOBALS['comm_defaut_status'],$GLOBALS['lang']['pref_comm_BoW_list']);
		$field_securite .= '</p>'."\n";
		$field_securite .= '</fieldset>';
	echo $field_securite;

		$field_dateheure = '<fieldset class="pref">';
		$field_dateheure .= legend($GLOBALS['lang']['prefs_legend_dateheure'], 'legend-dateheure');
		$field_dateheure .= form_format_date($GLOBALS['format_date']);
		$field_dateheure .= form_format_heure($GLOBALS['format_heure']);
		// the line below is unavailable on PHP 4 and lower
		$field_dateheure .= form_fuseau_horaire($GLOBALS['fuseau_horaire']);
		$field_dateheure .= '</fieldset>';
	echo $field_dateheure;

		$field_config = '<fieldset class="pref">';
		$field_config .= legend($GLOBALS['lang']['prefs_legend_config'], 'legend-config');
		$field_config .= form_langue($GLOBALS['lang']['id']);
		$nbs = array('10'=>'10', '25'=>'25', '50'=>'50', '100'=>'100', '300'=>'300', '-1' => $GLOBALS['lang']['pref_all']);
		$field_config .= form_select('nb_list', $nbs, $GLOBALS['max_bill_admin'],$GLOBALS['lang']['pref_nb_list']);
		$field_config .= form_select('nb_list_com', $nbs, $GLOBALS['max_comm_admin'],$GLOBALS['lang']['pref_nb_list_com']);
		$field_config .= '<p>'."\n";
		$field_config .= select_yes_no('activer_categories', $GLOBALS['activer_categories'], $GLOBALS['lang']['pref_categories'] );
		$field_config .= '</p>'."\n";
		$field_config .= '<p>'."\n";
		$field_config .= select_yes_no('auto_keywords', $GLOBALS['automatic_keywords'], $GLOBALS['lang']['pref_automatic_keywords'] );
		$field_config .= '</p>'."\n";
		$field_config .= '<p>'."\n";
		$field_config .= select_yes_no('global_comments', $GLOBALS['global_com_rule'], $GLOBALS['lang']['pref_allow_global_coms']);
		$field_config .= '</p>'."\n";
		$field_config .= '</fieldset>';
	echo $field_config;

		$field_maintenance = '<fieldset class="pref">';
		$field_maintenance .= legend($GLOBALS['lang']['titre_maintenance'], 'legend-config');
		$field_maintenance .= '<p><a href="maintenance.php">'.$GLOBALS['lang']['pref_go_to_mainteance'].'</a></p>';
		$field_maintenance .= '</fieldset>';
	echo $field_maintenance;

	echo '<div id="bt">';
	echo hidden_input('_verif_envoi', '1');
	echo input_enregistrer();
	echo '</div>';
	echo '</form>';
	footer();
}

?>
