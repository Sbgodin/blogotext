<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function find_image($article_id) {
	$image = $GLOBALS['dossier_images'].'/'.$article_id.'/'.$image;
	if (file_exists($image)) {
		$return = '<img src="'.$image.' alt="'.$image.'" />';
	}
}

function resize_img($filename, $destination) {
// largeur et hauteur maximale
$width = '100';
$height = '100';
// Cacul des nouvelles dimensions
list($width_orig, $height_orig) = getimagesize($filename);
if ($width && ($width_orig < $height_orig)) {
   $width = ($height / $height_orig) * $width_orig;
} else {
   $height = ($width / $width_orig) * $height_orig;
}
// Redimensionnement
$image_p = imagecreatetruecolor($width, $height);
	if (get_ext($filename) === 'jpg') {
		$image = imagecreatefromjpeg($filename);
	} elseif (get_ext($filename) === 'png') {
		$image = imagecreatefrompng($filename);
	} elseif (get_ext($filename) === 'gif') {
		$image = imagecreatefromgif($filename);
	}

imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
// Enregistrement
	if (get_ext($filename) === 'jpg') {
		imagejpeg($image_p, $destination, 100);
	} elseif (get_ext($filename) === 'png') {
		imagepng($image_p, $destination, 100);
	} elseif (get_ext($filename) === 'gif') {
		imagegif($image_p, $destination, 100);
	}
}

/// formulaires IMAGES //////////

function afficher_form_image($erreurs='') {
	$taille=array(
		'S' => 'Petite (240x320)',
		'M' => 'Moyenne (640x480)',
		'L' => 'Grande (1280x960)',
		'0' => 'Identique &agrave; l\'originale'
	);
	if ($erreurs) {
		erreurs($erreurs);
	}
	print '<fieldset id="form-image">';
	legend($GLOBALS['lang']['label_image_ajout'], '');
	print '<form enctype="multipart/form-data" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';
	hidden_input('MAX_FILE_SIZE', '1000000');
	print '<p>'."\n";
	print '<label for="fichier">Choisissez une image</label>';
	print '<input name="fichier" type="file" size="10" />';
	print '</p>'."\n";

	form_select('taille-image', $taille, '', 'Taille de l\'image');

	hidden_input('_stage', '1');
			print '<p>'."\n";
	print '<input type="submit" value="Ajouter" />';
			print '</p>'."\n";
	print '</form>';
	print '</fieldset>';

}

function valider_form_image() {
	$erreurs = array();
	if (($_FILES['fichier']['error'] == UPLOAD_ERR_INI_SIZE) ||
			($_FILES['fichier']['error'] == UPLOAD_ERR_FORM_SIZE)) {
				$erreurs[] = 'Fichier trop gros';
			} elseif ($_FILES['fichier']['error'] == UPLOAD_ERR_PARTIAL) {
				$erreurs[] = 'dÈpot interrompu';
			}elseif ($_FILES['fichier']['error'] == UPLOAD_ERR_NO_FILE) {
				$erreurs[] = 'aucun fichier dÈposÈ';
			}
			return $erreurs;
}

function traiter_form_image($id) {
	$nom_fic_ok = str_replace('/', '', $_FILES['fichier']['name']);
		$fic_destination = $GLOBALS['dossier_images'].'/'.$id.'/'.$nom_fic_ok;
		$vignette_dest = $GLOBALS['dossier_images'].'/'.$id.'/'.$GLOBALS['dossier_vignettes'].'/'.$nom_fic_ok;
		// Creation du dossier image au nom de id
		if ( !is_dir(($GLOBALS['dossier_images'].'/'.$id) ) ) {
			$dossier_img = mkdir($GLOBALS['dossier_images'].'/'.$id, 0755);
		}
		if ( !is_dir(($GLOBALS['dossier_images'].'/'.$id.'/'.$GLOBALS['dossier_vignettes']) ) ) {
			$dossier_img = mkdir($GLOBALS['dossier_images'].'/'.$id.'/'.$GLOBALS['dossier_vignettes'], 0755);
		}
		// Creation du sous-dossier vignets
	if (move_uploaded_file($_FILES['fichier']['tmp_name'], $fic_destination)) {
		chmod($fic_destination, 0755);
		resize_img($GLOBALS['dossier_images'].'/'.$id.'/'.$nom_fic_ok, $vignette_dest);
		confirmation($GLOBALS['lang']['confirm_image_ajout']);
	} else {
		erreur('Envoi impossible');
	}
}

function liste_images($id) {
	$liste= array();
	if (isset($GLOBALS['dossier_images'])) {
	if ( ($dossier = $GLOBALS['dossier_images'].'/'.$id.'/'.$GLOBALS['dossier_vignettes']) AND (is_dir($dossier)) ) {
		$formats=array('png', 'jpg', 'gif');
			if ( $ouverture = opendir($dossier) ) { 
      	while ( false !== ($images=readdir($ouverture)) ) {
     			if (in_array(get_ext($images), $formats)) {
       			$liste[$dossier.'/'.$images]=$images;
      		}
      	}
			 closedir($ouverture);
			}
	}
	}
			return $liste;
}

?>
