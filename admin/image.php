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
	This file is almost the same as fichiers.php.
	It only retains the images instead of the reste of the files.


*/

$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();
$begin = microtime(TRUE);

$GLOBALS['liste_fichiers'] = open_file_db_fichiers($GLOBALS['fichier_liste_fichiers']);

// suppression directe d’un fichier (FIXME: ajouter une sécurité)
if (isset($_GET['suppr']) and isset($_GET['file_id']) and preg_match('#\d{14}#',($_GET['file_id'])) ) {
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
		redirection($_SERVER['PHP_SELF'].'?errmsg=error_fichier_suppr&what=session_expire');
	}
}

/* filtres de recherche */
if ( isset($_GET['filtre']) and $_GET['filtre'] !== '' ) {
	if ( preg_match('/\d{6}/',($_GET['filtre'])) ) {
		$annee = substr($_GET['filtre'], 0, 4);
		$mois = substr($_GET['filtre'], 4, 2);
		$jour = substr($_GET['filtre'], 6, 2);
		$images = liste_base_files('date', $annee.$mois.$jour, '');
	} elseif ($_GET['filtre'] == 'draft') {
		$images = liste_base_files('statut', '0', '');
	} elseif ($_GET['filtre'] == 'pub') {
		$images = liste_base_files('statut', '1', '');
	} else { // liste selon type
		$images = liste_base_files('type', htmlspecialchars($_GET['filtre']), '');
	}

} elseif (isset($_GET['q']) and $_GET['q'] !== '') {
	$images = liste_base_files('recherche', htmlspecialchars(urldecode($_GET['q'])), '');

} elseif (isset($_GET['extension']) and $_GET['extension'] !== '') {
	$images = liste_base_files('extension', htmlspecialchars($_GET['extension']), '');

} elseif (isset($_GET['file_id']) and preg_match('/\d{14}/',($_GET['file_id']))) {
	foreach ($GLOBALS['liste_fichiers'] as $img) {
		if ($img['bt_id'] == $_GET['file_id']) {
			$image = $img;
			break;
		}
	}
	$images[$_GET['file_id']] = $image;

} else { // no filter, so list'em all
	$images = $GLOBALS['liste_fichiers'];
}


// traitement d’une action sur l’image
$erreurs = array();
if (isset($_POST['_verif_envoi'])) {
	$image = init_post_fichier();
	$erreurs = valider_form_fichier($image);
	if (empty($erreurs)) {
		traiter_form_fichier($image);
	}
}

// vérifie que les images de la liste sont bien présents sur le disque dur
$real_images = array();
foreach ($images as $i => $file) {
	if (is_file($GLOBALS['BT_ROOT_PATH'].'/'.$GLOBALS['dossier_images'].'/'.$file['bt_filename']) and ($file['bt_filename'] != 'index.html') ) {
		$real_images[] = $file;
	}
}

afficher_top($GLOBALS['lang']['titre_image']);

echo '<div id="top">'."\n";
afficher_msg($GLOBALS['lang']['titre_image']);
echo moteur_recherche($GLOBALS['lang']['search_in_images']);
afficher_menu(pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME));
echo '</div>'."\n";

echo '<div id="axe">'."\n";

// SUBNAV
echo '<div id="subnav">'."\n";
// Affichage formulaire filtrage commentaires
if (isset($_GET['filtre'])) {
	afficher_form_filtre('images', htmlspecialchars($_GET['filtre']), 'admin');
} else {
	afficher_form_filtre('images', '', 'admin');
}
echo '</div>'."\n";

echo '<div id="page">'."\n";


// ajout d'une nouvelle image : affichage formulaire, pas des anciennes images.
if ( isset($_GET['ajout']) ) {
	afficher_form_fichier('', '', 'image');
}
// édition d'une nouvelle image : affichage formulaire + image en question
elseif ( isset($_GET['file_id']) ) {
	afficher_form_fichier($erreurs, $real_images, 'image');
}
// affichage de la liste des images.
else {
	afficher_form_fichier($erreurs, '', 'image');
	afficher_liste_images($real_images);
}



footer('', $begin);
?>
