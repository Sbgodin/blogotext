<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

//error_reporting(E_ALL);
if ( (file_exists('../config/user.php')) and (file_exists('../config/prefs.php')) and (file_exists('../config/tags.php')) ) {
	header('Location: auth.php');
	exit;
}
$GLOBALS['BT_ROOT_PATH'] = '../';

require_once '../inc/conf.php';
require_once '../inc/lang.php';
require_once '../inc/html.php';
require_once '../inc/form.php';
require_once '../inc/conv.php';
require_once '../inc/fich.php';
require_once '../inc/veri.php';
require_once '../inc/util.php';

if (isset($_GET['l'])) {
	$lang = $_GET['l'];
	if ($lang == $lang_fr['id']) {
		$GLOBALS['lang'] = $lang_fr;
	} elseif ($lang == $lang_en['id']) {
		$GLOBALS['lang'] = $lang_en;
	} elseif ($lang == $lang_nl['id']) {
		$GLOBALS['lang'] = $lang_nl;
	}
}

if (isset($_GET['s'])) {
	$step = $_GET['s'];
} else { 
	$step = '1';
}

if ($step == '1') {
	// LANGUE
	if (isset($_POST['verif_envoi_1'])) {
		if ($err_1 = valid_install_1()) {
				afficher_form_1($err_1);
		} else {
			redirection('install.php?s=2&l='.$_POST['langue']);
		}
	} else {
		afficher_form_1();
	}
} elseif ($step == '2') {
	// ID + MOT DE PASSE
		if (isset($_POST['verif_envoi_2'])) {
		if ($err_2 = valid_install_2()) {
				afficher_form_2($err_2);
		} else {
			traiter_install_2();
			redirection('auth.php');
		}
	} else {
		afficher_form_2();
	}
}

function afficher_form_1($erreurs = '') {
	afficher_top('Install');
	echo '<div id="axe">'."\n";
	echo '<div id="pageauth">'."\n";
	afficher_titre ($GLOBALS['nom_application'], 'logo', '1');
	afficher_titre ('Bienvenue Welcome', 'step', '1');
	erreurs($erreurs);
	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" >' ;
	form_langue_install('Choisissez votre langue / Choose your language');
	echo hidden_input('verif_envoi_1', '1');
	echo '<input class="inpauth" type="submit" name="enregistrer" value="Ok" />';
	echo '</form>' ;
}

function afficher_form_2($erreurs = '') {
	afficher_top('Install');
	echo '<div id="axe">'."\n";
	echo '<div id="pageauth">'."\n";
	afficher_titre ($GLOBALS['nom_application'], 'logo', '1');
	afficher_titre ($GLOBALS['lang']['install'], 'step', '1');
	erreurs($erreurs);
	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" onsubmit="return verifForm2(this)">'."\n".'<div id="erreurs_js" class="erreurs"></div>'."\n";
	echo form_text('identifiant', '', $GLOBALS['lang']['install_id']);
	echo form_password('mdp', '', $GLOBALS['lang']['install_mdp']);
	echo form_password('mdp_rep', '', $GLOBALS['lang']['install_remdp']);
	echo form_text('racine', 'http://', $GLOBALS['lang']['pref_racine']);
	echo hidden_input('verif_envoi_3', '1');
	echo hidden_input('comm_defaut_status', '1');

	echo hidden_input('langue', $_GET['l']);
	echo hidden_input('verif_envoi_2', '1');
	echo '<input class="inpauth" type="submit" name="enregistrer" value="Ok" />';
	echo '</form>' ;
}


function traiter_install_2() {
	$config_dir = '../config';
	if ( !is_dir($config_dir)) {
		creer_dossier($config_dir);
	}
	fichier_user();
	fichier_index($config_dir, '1');
	fichier_htaccess($config_dir);

	fichier_prefs();
	fichier_tags($_POST['tags'], '0');
	creer_dossier($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles']);
	creer_dossier($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires']);
	creer_dossier($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images']);
	$first_post= array (
		$GLOBALS['data_syntax']['bt_version'] => $GLOBALS['version'],
		$GLOBALS['data_syntax']['article_id'] => date('Y').date('m').date('d').date('H').date('i').date('s'),
		$GLOBALS['data_syntax']['article_title'] => $GLOBALS['lang']['first_titre'],
		$GLOBALS['data_syntax']['article_abstract'] => $GLOBALS['lang']['first_edit'],
		$GLOBALS['data_syntax']['article_content'] => $GLOBALS['lang']['first_edit'],
		$GLOBALS['data_syntax']['article_wiki_content'] => $GLOBALS['lang']['first_edit'],
		$GLOBALS['data_syntax']['article_keywords'] => '',
		$GLOBALS['data_syntax']['article_status'] => '1',
	$GLOBALS['data_syntax']['article_allow_comments'] => '1'
	);
	fichier_data($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], $first_post);
	fichier_index($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], '1');
	fichier_htaccess($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles']);
	fichier_index($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], '1');
	fichier_htaccess($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires']);
}

function valid_install_1() {
	$erreurs = array();
	if (!strlen(trim($_POST['langue']))) {
		$erreurs[] = 'Vous devez choisir une langue';
	}
	return $erreurs;
}

function valid_install_2() {
	$erreurs = array();
	if (!strlen(trim($_POST['identifiant']))) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_identifiant'];
	}	
	if ( (strlen($_POST['mdp']) < 6) OR (strlen($_POST['mdp_rep']) < 6) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_mdp'] ;
	}
	if ( ($_POST['mdp']) !== ($_POST['mdp_rep']) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_mdp_diff'] ;
	}

	if ( !strlen(trim($_POST['racine'])) or !preg_match('/^http:\/\/[a-zA-Z0-9_.-]/', $_POST['racine']) ) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_racine'];
	} elseif (!preg_match('/^https?:\/\//', $_POST['racine'])) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_racine_http'];
	} elseif (!preg_match('/\/$/', $_POST['racine'])) {
		$erreurs[] = $GLOBALS['lang']['err_prefs_racine_slash'];
	}
	return $erreurs;
}

if (!empty($_GET['s'])) {
echo '<script type="text/javascript">'."\n".'
function surligne(champ, erreur) {
	if(erreur)
		champ.style.backgroundColor = "#fba";
	else
		champ.style.backgroundColor = "";
}';
}

if (!empty($_GET['s']) and $_GET['s'] == 2) {
echo '
function verifForm2(form) {
	var identifiantOk = false;
	var mdp1Ok = false;
	var mdp2Ok = false;
	var mdpOk = false;
	var url = false;
	var regexend = /[a-zA-Z0-9]\/$/;
	var regexbeg = /^https?:\/{2}/;
	var msg = "";


	if (form.identifiant.value.length < 1) {
		surligne(form.identifiant, true);
		msg = msg + "<li>'.$GLOBALS['lang']['err_prefs_identifiant'].'</li>\n";
	} else {
		surligne(form.identifiant, false);
		identifiantOk = true;
	}

	if (form.mdp.value.length < 6 || !form.mdp.value.length) {
		surligne(form.mdp, true);
		msg = msg + "<li>'.$GLOBALS['lang']['err_prefs_mdp'].'</li>\n";
	} else {
		surligne(form.mdp, false);
		mdp1Ok = true;
	}

	if (form.mdp_rep.value != form.mdp.value || !form.mdp_rep.value.length) {
		surligne(form.mdp_rep, true);
		msg = msg + "<li>'.$GLOBALS['lang']['err_prefs_mdp_diff'].'</li>\n";
	} else {
		surligne(form.mdp_rep, false);
		mdp2Ok = true;
	}

	if (mdp1Ok && mdp2Ok) {
		mdpOk = true;
	}

	if (!regexend.test(form.racine.value)) {
		surligne(form.racine, true);
		msg = msg + "<li>'.preg_replace('#"#', '\"', $GLOBALS['lang']['err_prefs_racine_slash']).'</li>\n";
	} else {
		if (!regexbeg.test(form.racine.value)) {
			surligne(form.racine, true);
			msg = msg + "<li>'.preg_replace('#(/|")#', '\\\$1', $GLOBALS['lang']['err_prefs_racine_http']).'</li>\n";
		} else {
			surligne(form.racine, false);
			url = true;
		}
	}


	if(identifiantOk && mdpOk && url) {
		var regexw = /[a-z]/;
		var regexW = /[A-Z]/;
		var regexd = /[0-9]/;
		var regexc = /[^a-zA-Z0-9]/;
		if (!regexw.test(form.mdp.value) || !regexW.test(form.mdp.value) || !regexd.test(form.mdp.value) || !regexc.test(form.mdp.value)) {
			return window.confirm(\''.$GLOBALS['lang']['err_prefs_mdp_weak'].'\');
		} else {
			return true;
		}
	} else {
		msg = "<strong>'.$GLOBALS['lang']['erreurs'].'</strong> :<ul>\n" + msg + "</ul>\n";
		window.document.getElementById("erreurs_js").innerHTML = msg;
		return false;
	}

}</script>';

}
footer();
?>
