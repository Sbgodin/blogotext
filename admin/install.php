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

/*
// This may be needed for testing PDO SQLite & GD lib : 
if (in_array('pdo_sqlite', get_loaded_extensions())) {
	echo 'PDO SQLite yes';
} else {
	echo 'PDO SQLite no';
}



*/

if ( (file_exists('../config/user.php')) and (file_exists('../config/prefs.php')) ) {
	header('Location: auth.php');
	exit;
}
$GLOBALS['BT_ROOT_PATH'] = '../';

require_once '../inc/conf.php';
error_reporting($GLOBALS['show_errors']); // MUST be after including "conf.php"...
require_once '../inc/lang.php';
require_once '../inc/html.php';
require_once '../inc/form.php';
require_once '../inc/conv.php';
require_once '../inc/fich.php';
require_once '../inc/veri.php';
require_once '../inc/util.php';
require_once '../inc/jasc.php';
require_once '../inc/sqli.php';

if (isset($_GET['l'])) {
	$lang = $_GET['l'];
	if ($lang == $lang_fr['id']) {
		$GLOBALS['lang'] = $lang_fr;
	} elseif ($lang == $lang_en['id']) {
		$GLOBALS['lang'] = $lang_en;
	} elseif ($lang == $lang_nl['id']) {
		$GLOBALS['lang'] = $lang_nl;
	} elseif ($lang == $lang_de['id']) {
		$GLOBALS['lang'] = $lang_de;
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
			$config_dir = '../config';
			if (!is_dir($config_dir)) creer_dossier($config_dir, 1);
			if (!is_dir('../'.$GLOBALS['dossier_images'])) creer_dossier('../'.$GLOBALS['dossier_images'], 0);
			if (!is_dir('../'.$GLOBALS['dossier_fichiers'])) creer_dossier('../'.$GLOBALS['dossier_fichiers'], 0);
			fichier_user();
			include_once($config_dir.'/user.php');

			traiter_install_2();
			redirection('auth.php');
		}
	} else {
		afficher_form_2();
	}
}

function afficher_form_1($erreurs='') {
	afficher_top('Install');
	echo '<div id="axe">'."\n";
	echo '<div id="pageauth">'."\n";
	echo '<h1>'.$GLOBALS['nom_application'].'</h1>'."\n";
	echo '<h1 id="step">Bienvenue / Welcome</h1>'."\n";
	erreurs($erreurs);
	/*
	if (in_array('gd', get_loaded_extensions())) {
		echo 'GD yes';
	} else {
		echo 'GD no';
	}
	*/

	$conferrors = array();
	// check PHP version
	if (version_compare(PHP_VERSION, $GLOBALS['minimal_php_version'], '<')) {
		$conferrors[] = "\t".'<li>Your PHP Version is '.PHP_VERSION.'. BlogoText requires '.$GLOBALS['minimal_php_version'].'.</li>'."\n";
	}
	// pdo_sqlite (required)
	if (!extension_loaded('pdo_sqlite') ) {
		$conferrors[] = "\t".'<li>Required PHP-extension "<b>pdo_sqlite</b>" is not loaded.</li>'."\n";
	}
	// check directory readability
	if (!is_writable('../') ) {
		$conferrors[] = "\t".'<li>Blogotext has no write rights (chmod of home folder must be 644 at least, 777 recommended).</li>'."\n";
	}
	if (!empty($conferrors)) {
		echo '<ol>'."\n";
		echo implode($conferrors, '');
		echo '</ol>'."\n";
		echo '<p style="color: red;"><b>Installation aborded.</b></p>'."\n";
		echo '</div>'."\n".'</div>'."\n".'</html>';
		die;
	}

	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" >' ;
	form_langue_install('Choisissez votre langue / Choose your language');
	echo hidden_input('verif_envoi_1', '1');
	echo '<input class="inpauth blue-square" type="submit" name="enregistrer" value="Ok" />';
	echo '</form>' ;
}

function afficher_form_2($erreurs='') {
	afficher_top('Install');
	echo '<div id="axe">'."\n";
	echo '<div id="pageauth">'."\n";
	echo '<h1>'.$GLOBALS['nom_application'].'</h1>'."\n";
	echo '<h1 id="step">'.$GLOBALS['lang']['install'].'</h1>'."\n";
	erreurs($erreurs);
	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" onsubmit="return verifForm2(this)">'."\n".'<div id="erreurs_js" class="erreurs"></div>'."\n";
	echo form_text('identifiant', '', $GLOBALS['lang']['install_id']);
	echo form_password('mdp', '', $GLOBALS['lang']['install_mdp']);
	echo form_password('mdp_rep', '', $GLOBALS['lang']['install_remdp']);
	$lien = str_replace('?'.$_SERVER['QUERY_STRING'],'','http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
	$lien = str_replace('admin/install.php', '', $lien);
	echo form_text('racine', $lien, $GLOBALS['lang']['pref_racine']);
	echo hidden_input('verif_envoi_3', '1');
	echo hidden_input('comm_defaut_status', '1');
	echo hidden_input('langue', $_GET['l']);
	echo hidden_input('verif_envoi_2', '1');
	echo '<input class="inpauth blue-square" type="submit" name="enregistrer" value="Ok" />';
	echo '</form>' ;
}


function traiter_install_2() {
	$config_dir = '../config';
	if (!is_file($config_dir.'/prefs.php')) fichier_prefs();
	$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);

	if ($GLOBALS['db_handle']) {
		$first_post = array (
			'bt_id' => date('YmdHis', time()),
			'bt_date' => date('YmdHis', time()),
			'bt_title' => $GLOBALS['lang']['first_titre'],
			'bt_abstract' => $GLOBALS['lang']['first_edit'],
			'bt_content' => $GLOBALS['lang']['first_edit'],
			'bt_wiki_content' => $GLOBALS['lang']['first_edit'],
			'bt_keywords' => '',
			'bt_categories' => '',
			'bt_link' => '',
			'bt_notes' => '',
			'bt_statut' => '1',
			'bt_allow_comments' => '1'
		);
		$readme_post = array (
			'bt_notes' => '',
			'bt_link' => '',
			'bt_categories' => '',
			'bt_link' => '',
			'bt_id' => date('YmdHis', time()+2),
			'bt_date' => date('YmdHis', time()+2),
			'bt_title' => 'README / LISEZ-MOI',
			'bt_abstract' => 'Instructions / Instructions',
			'bt_content' => '
These are some instructions for the safety of your blog.<br/>
In order to protect your personal blog against attacks, Blogotext allows you to <b>rename the "admin" folder</b>. Using the FTP connection to your web-hosting, you should really rename the "admin" folder to whatever you want. <b>It\'s not forced, but it will increase heavily the strength of Blogotext against attacks</b>.<br/>
Please, after you renamed the folder (if you do), remember the new name because that will now be the folder you have to go to in order to access the admin panel of Blogotext.
<br/>
****************************************************************************<br/>
<br/>
Voici quelques conseils pour la sécurité de votre blog.<br/>
Afin de protéger votre blog contre d\'éventuelles attaques, Blogotext vous permet de <b>renommer le dossier « admin »</b>. En utilisant la connexion FTP à votre espace d\'hébergement, vous devriez renommer le dossier « admin » en un autre nom que vous voudrez. <b>Ceci n\'est pas obligatoire mais cela améliorera de manière drastique la sécurité de votre blog contre les attaques.</b><br/>
S\'il vous plaît, veuillez retenir le nouveau nom que vous donnez au dossier, car c\'est ce nom qu\'il faudra utiliser comme dossier "admin" pour accéder au panel.

',
			'bt_wiki_content' => 'Once readed, you may delete this post / Une fois que vous avez lu ceci, vous pouvez supprimer l\'article',
			'bt_keywords' => '',
			'bt_statut' => '0',
			'bt_allow_comments' => '0'
		);
		$res1 = bdd_article($first_post, 'enregistrer-nouveau'); // billet "Mon premier article"
		$res2 = bdd_article($readme_post, 'enregistrer-nouveau'); // billet "read me" avec les instructions

		if ($res1 !== TRUE or $res2 !== TRUE) {
			echo $res1.' /// '. $res2;
			die();
		}
	}
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

	if ( !strlen(trim($_POST['racine'])) or !preg_match('#^https?://[a-zA-Z0-9_/.-]*/$#', $_POST['racine']) ) {
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
