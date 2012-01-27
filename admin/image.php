<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***


$begin = microtime(TRUE);
//error_reporting(-1);
$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';

operate_session();

$erreurs = array();
$uploaded_image = '';

if (isset($_GET['image'])) {
	if ( isset($_GET['uid'])   ){// and ($_GET['uid'] == $_SESSION['prev_ses_id']) ) {
		$image = htmlspecialchars($_GET['image']);
		$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'];
		if (is_file($dossier.'/'.$image)) {
			$liste_fichiers = scandir($dossier);	
			$nb_fichier = count($liste_fichiers);
			for ($i = 0 ; $i < $nb_fichier ; $i++) {
				if ($liste_fichiers[$i] == $image and !($liste_fichiers[$i] == '..' or $liste_fichiers[$i] == '.')) {
					if (TRUE === unlink($dossier.'/'.$liste_fichiers[$i])) {
						redirection($_SERVER['PHP_SELF'].'?msg=confirm_image_suppr');
					}
				}
			}
		}
	}
}

if (isset($_POST['_verif_envoi'])) {
	$erreurs = valider_form_image();
	if (empty($erreurs)) {
		$image = traiter_form_image();
		if ($image === FALSE) {
			erreur('Envoi impossible');
		} else {
			confirmation($GLOBALS['lang']['confirm_image_ajout']);
			$uploaded_image = $image;
		}
	}
}

afficher_top($GLOBALS['lang']['titre_image']);
afficher_msg();

echo '<div id="top">'."\n";
echo '<ul id="nav">'."\n";
afficher_menu('image.php');
echo '</ul>'."\n".'</div>'."\n";

echo '<div id="axe">'."\n".'<div id="page">'."\n";

afficher_form_image($erreurs, $uploaded_image);

footer('', $begin);

?>
