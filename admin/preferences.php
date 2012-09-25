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

$begin = microtime(TRUE);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();
if (isset($_POST['_verif_envoi'])) {
	if ($erreurs_form = valider_form_preferences()) {
		afficher_form_prefs($erreurs_form);
	} else {        		
		if ( (fichier_user() === TRUE) and (fichier_prefs() === TRUE) ) {
		redirection($_SERVER['PHP_SELF'].'?msg=confirm_prefs_maj');
		}
	}
} else {	
	afficher_form_prefs();
}



function afficher_form_prefs($erreurs= '') {
	afficher_top($GLOBALS['lang']['preferences']);
	echo '<div id="top">';
	afficher_msg($GLOBALS['lang']['preferences']);
	afficher_menu(pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME));
	echo '</div>';

	echo '<div id="axe">'."\n";
	echo '<div id="page">'."\n";
	erreurs($erreurs);

	echo '<form id="preferences" class="bordered-formbloc" method="post" action="'.$_SERVER['PHP_SELF'].'" >' ;
		$field_user = '<fieldset class="pref">';
		$field_user .= legend($GLOBALS['lang']['prefs_legend_utilisateur'], 'legend-user');
		$field_user .= form_text('auteur', empty($GLOBALS['auteur']) ? $GLOBALS['identifiant'] : $GLOBALS['auteur'], $GLOBALS['lang']['pref_auteur']);
		$field_user .= form_text('email', $GLOBALS['email'], $GLOBALS['lang']['pref_email']);
		$field_user .= form_text('nomsite', $GLOBALS['nom_du_site'], $GLOBALS['lang']['pref_nom_site']);
		$field_user .= form_text('racine', $GLOBALS['racine'], $GLOBALS['lang']['pref_racine']);
		$field_user .= '<p>'."\n";
		$field_user .= "\t".'<label for="description">'.$GLOBALS['lang']['pref_desc'].'</label>'."\n";
		$field_user .= "\t".'<textarea id="description" name="description" cols="35" rows="3" class="text" >'.$GLOBALS['description'].'</textarea>'."\n";
		$field_user .= '</p>'."\n";
		$field_user .= '</fieldset>';
	echo $field_user;

		$field_securite = '<fieldset class="pref">';
		$field_securite .= legend($GLOBALS['lang']['prefs_legend_securite'], 'legend-securite');
		$field_securite .= form_text('identifiant', $GLOBALS['identifiant'], $GLOBALS['lang']['pref_identifiant']);
		$field_securite .= form_password('mdp', '', $GLOBALS['lang']['pref_mdp']);
		$field_securite .= form_password('mdp_rep', '', $GLOBALS['lang']['pref_mdp_nouv']);

		if (in_array('gd', get_loaded_extensions())) { // captcha only possible if GD library is installed.
			$field_securite .= '<p>'."\n";
			$field_securite .= select_yes_no('connexion_captcha', $GLOBALS['connexion_captcha'], $GLOBALS['lang']['pref_connexion_captcha'] );
			$field_securite .= '</p>'."\n";
		} else {
			$field_securite .= '<p>'."\n";
			$field_securite .= hidden_input('connexion_captcha', '0');
			$field_securite .= '</p>'."\n";
		}
		$field_securite .= '</fieldset>';
	echo $field_securite;

		$field_apparence = '<fieldset class="pref">';
		$field_apparence .= legend($GLOBALS['lang']['prefs_legend_apparence'], 'legend-apparence');
		$field_apparence .= '<p>'."\n";
		$field_apparence .= form_select('nb_maxi', array('5'=>'5', '10'=>'10', '15'=>'15', '20'=>'20', '25'=>'25', '50'=>'50'), $GLOBALS['max_bill_acceuil'],$GLOBALS['lang']['pref_nb_maxi']);
		$field_apparence .= '</p>'."\n";
//		$field_apparence .= '<p>'."\n";
//		$field_apparence .= form_select('nb_maxi_linx', array('20'=>'20', '50'=>'50', '100'=>'100', '150'=>'150'), $GLOBALS['max_linx_acceuil'],$GLOBALS['lang']['pref_nblinx_maxi']);
//		$field_apparence .= '</p>'."\n";
//		$field_apparence .= '<p>'."\n";
//		$field_apparence .= form_select('nb_maxi_comm', array('3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '10'=>'10', '15'=>'15', '20'=>'20'), $GLOBALS['max_comm_encart'],$GLOBALS['lang']['pref_nb_maxi_comm']);
//		$field_apparence .= '</p>'."\n";
		$field_apparence .= '<p>'."\n";
		$field_apparence .= form_select('theme', liste_themes($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_themes']), $GLOBALS['theme_choisi'],$GLOBALS['lang']['pref_theme']);
		$field_apparence .= '</p>'."\n";
		$field_apparence .= '</fieldset>';
	echo $field_apparence;

		$field_dateheure = '<fieldset class="pref">';
		$field_dateheure .= legend($GLOBALS['lang']['prefs_legend_langdateheure'], 'legend-dateheure');
		$field_dateheure .= form_langue($GLOBALS['lang']['id']);
		$field_dateheure .= form_format_date($GLOBALS['format_date']);
		$field_dateheure .= form_format_heure($GLOBALS['format_heure']);
		$field_dateheure .= form_fuseau_horaire($GLOBALS['fuseau_horaire']);
		$field_dateheure .= '</fieldset>';
	echo $field_dateheure;

		$field_config_blog = '<fieldset class="pref">';
		$field_config_blog .= legend($GLOBALS['lang']['prefs_legend_configblog'], 'legend-config');
		$nbs = array('10'=>'10', '25'=>'25', '50'=>'50', '100'=>'100', '300'=>'300', '-1' => $GLOBALS['lang']['pref_all']);
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= form_select('nb_list', $nbs, $GLOBALS['max_bill_admin'],$GLOBALS['lang']['pref_nb_list']);
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= form_select('nb_list_com', $nbs, $GLOBALS['max_comm_admin'],$GLOBALS['lang']['pref_nb_list_com']);
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= select_yes_no('activer_categories', $GLOBALS['activer_categories'], $GLOBALS['lang']['pref_categories'] );
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= select_yes_no('auto_keywords', $GLOBALS['automatic_keywords'], $GLOBALS['lang']['pref_automatic_keywords'] );
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= select_yes_no('global_comments', $GLOBALS['global_com_rule'], $GLOBALS['lang']['pref_allow_global_coms']);
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= select_yes_no('require_email', $GLOBALS['require_email'], $GLOBALS['lang']['pref_force_email']);
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '<p>'."\n";
		$field_config_blog .= form_select('comm_defaut_status', array('1' => $GLOBALS['lang']['pref_comm_black_list'], '0' => $GLOBALS['lang']['pref_comm_white_list']), $GLOBALS['comm_defaut_status'],$GLOBALS['lang']['pref_comm_BoW_list']);
		$field_config_blog .= '</p>'."\n";
		$field_config_blog .= '</fieldset>';
	echo $field_config_blog;


		$field_config_linx = '<fieldset class="pref">';
		$field_config_linx .= legend($GLOBALS['lang']['prefs_legend_configlinx'], 'legend-config');
		// nb liens côté admin
		$nbs = array('50'=>'50', '100'=>'100', '200'=>'200', '300'=>'300', '500'=>'500', '-1' => $GLOBALS['lang']['pref_all']);
		$field_config_linx .= '<p>'."\n";
		$field_config_linx .= form_select('nb_list_linx', $nbs, $GLOBALS['max_linx_admin'], $GLOBALS['lang']['pref_nb_list_linx']);
		$field_config_linx .= '</p>'."\n";
		// publication de lien côté visiteur autorisé
//		$field_config_linx .= '<p>'."\n";
//		$field_config_linx .= select_yes_no('allow_public_linx', $GLOBALS['allow_public_linx'], $GLOBALS['lang']['pref_allow_global_linx']);
//		$field_config_linx .= '</p>'."\n";
		// les liens publiés côté public doivent être validés par l’admin avant d’être visibles ?
//		$field_config_linx .= '<p>'."\n";
//		$field_config_linx .= form_select('linx_defaut_status', array('1' => $GLOBALS['lang']['pref_comm_black_list'], '0' => $GLOBALS['lang']['pref_comm_white_list']), $GLOBALS['linx_defaut_status'], $GLOBALS['lang']['pref_linx_BoW_list']);
//		$field_config_linx .= '</p>'."\n";
		$field_config_linx .= '</fieldset>';
	echo $field_config_linx;

		$field_maintenance = '<fieldset class="pref">';
		$field_maintenance .= legend($GLOBALS['lang']['titre_maintenance'], 'legend-sweep');
		$field_maintenance .= '<p><a href="maintenance.php">'.$GLOBALS['lang']['pref_go_to_mainteance'].'</a></p>';
		$field_maintenance .= '</fieldset>';
	echo $field_maintenance;


	// might cause page to load slowly ; limited at 6 seconds
	$last_version = get_external_file('http://lehollandaisvolant.net/blogotext/version.php', 6);
	if ( !empty($last_version) ) {
		if ($GLOBALS['version'] < $last_version) {
			$field_update = '<fieldset class="pref">';
			$field_update .=  legend($GLOBALS['lang']['maint_chk_update'], 'legend-update');
			$field_update .= '<p style="font-weight: bold;">'.$GLOBALS['lang']['maint_update_youisbad'].' ('.$last_version.')<br/>'."\n";
			$field_update .= $GLOBALS['lang']['maint_update_go_dl_it'].' <a href="http://lehollandaisvolant.net/blogotext/">lehollandaisvolant.net/blogotext/</a>.</p>';
			$field_update .= '</fieldset></div>'."\n";
			echo $field_update;
		}
	}

	echo '<div class="submit">';
	echo hidden_input('_verif_envoi', '1');
	echo '<input class="submit blue-square" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['enregistrer'].'" />'."\n";
	echo '</div>';
	echo '</form>';
}

footer('', $begin);

