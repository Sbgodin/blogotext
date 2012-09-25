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

$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();
$begin = microtime(TRUE);

$GLOBALS['liste_fichiers'] = open_file_db_fichiers($GLOBALS['fichier_liste_fichiers']);

// suppression directe d’un fichier (FIXME: ajouter une sécurité)
if (isset($_GET['file_id']) and preg_match('#\d{14}#',($_GET['file_id'])) and isset($_GET['suppr']) and isset($_GET['type']) ) {
	if ($_GET['type'] == 'img') {
		$page_renvoi = 'image.php';
	} else {
		$page_renvoi = 'fichiers.php';
	}

	// test sur la durée de la session
	if (isset($_GET['av']) and $_GET['av'] <= time() and $_GET['av'] > time()-600 ) {
		foreach ($GLOBALS['liste_fichiers'] as $fich) {
			if ($fich['bt_id'] == $_GET['file_id']) {
				$fichier = $fich;
				break;
			}
		}
		traiter_form_fichier($fichier);
	}
	// temps de session probablement expiré...
	else {
		redirection($page_renvoi.'?errmsg=error_fichier_suppr&what=session_expire');
	}
}


// recherche / tri
if ( isset($_GET['filtre']) and $_GET['filtre'] !== '' ) {
	// selon date
	if ( preg_match('/\d{6}/',($_GET['filtre'])) ) {
		$annee = substr($_GET['filtre'], 0, 4);
		$mois = substr($_GET['filtre'], 4, 2);
		$jour = substr($_GET['filtre'], 6, 2);
		$fichiers = liste_base_files('date', $annee.$mois.$jour, '');
	// brouillons
	} elseif ($_GET['filtre'] == 'draft') {
		$fichiers = liste_base_files('statut', '0', '');
	// publiés
	} elseif ($_GET['filtre'] == 'pub') {
		$fichiers = liste_base_files('statut', '1', '');
	// liste selon type de fichier
	} else {
		$fichiers = liste_base_files('type', htmlspecialchars($_GET['filtre']), '');
	}
// recheche par mot clé
} elseif (isset($_GET['q']) and $_GET['q'] !== '') {
	$fichiers = liste_base_files('recherche', htmlspecialchars(urldecode($_GET['q'])), '');
// par extension
} elseif (isset($_GET['extension']) and $_GET['extension'] !== '') {
	$fichiers = liste_base_files('extension', htmlspecialchars($_GET['extension']), '');
// par fichier unique (id)
} elseif (isset($_GET['file_id']) and preg_match('/\d{14}/',($_GET['file_id']))) {
	foreach ($GLOBALS['liste_fichiers'] as $fich) {
		if ($fich['bt_id'] == $_GET['file_id']) {
			$fichier = $fich;
			break;
		}
	}
	$fichiers[$_GET['file_id']] = $fichier;
// aucun filtre, les affiche tous
} else {
	$fichiers = $GLOBALS['liste_fichiers'];
}

// traitement d’une action sur le fichier
$erreurs = array();
if (isset($_POST['_verif_envoi'])) {
	$fichier = init_post_fichier();
	$erreurs = valider_form_fichier($fichier);
	if (empty($erreurs)) {
		traiter_form_fichier($fichier);
	}
}

afficher_top($GLOBALS['lang']['titre_fichier']);

echo '<div id="top">'."\n";
afficher_msg($GLOBALS['lang']['titre_fichier']);
echo moteur_recherche($GLOBALS['lang']['search_in_files']);
afficher_menu(pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME));
echo '</div>'."\n";

echo '<div id="axe">'."\n";

// SUBNAV
echo '<div id="subnav">'."\n";
// Affichage formulaire filtrage fichiers
if (isset($_GET['filtre'])) {
	afficher_form_filtre('fichiers', htmlspecialchars($_GET['filtre']), 'admin');
} else {
	afficher_form_filtre('fichiers', '', 'admin');
}
echo '</div>'."\n";

echo '<div id="page">'."\n";


// vérifie que les fichiers de la liste sont bien présents sur le disque dur
$real_fichiers = array();
foreach ($fichiers as $i => $file) {
	$dossier = ($file['bt_type'] == 'image') ? $GLOBALS['dossier_images'] : $GLOBALS['dossier_fichiers'];
	if (is_file($GLOBALS['BT_ROOT_PATH'].'/'.$dossier.'/'.$file['bt_filename']) and ($file['bt_filename'] != 'index.html') ) {
		$real_fichiers[] = $file;
	}
}

// ajout d'un nouveau fichier : affichage formulaire, pas des anciens.
if ( isset($_GET['ajout']) ) {
	afficher_form_fichier('', '', 'fichier');
}
// édition d'un fichier
elseif ( isset($_GET['file_id']) ) {
	afficher_form_fichier($erreurs, $real_fichiers, 'fichier');
}
// affichage de la liste des fichiers.
else {
	afficher_form_fichier($erreurs, '', 'fichier');
	afficher_liste_fichiers($real_fichiers);
}

footer('', $begin);
?>
