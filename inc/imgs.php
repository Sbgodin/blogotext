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

function traiter_form_image() {
	if (!is_dir($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'])) creer_dossier($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images']);
	$image = basename($_FILES['fichier']['name']);
	$ext = get_extension($image);
	if (!empty($_POST['nom_entree'])) {
		$nom = htmlspecialchars($_POST['nom_entree']); 
	} else {
		$nom = preg_replace('#\.'.$ext.'$#', '', $image);
	}
	$nom = diacritique($nom, 0, 0);
	$prefix = 'blog-'.date('ymd');
	// pour ne pas ecraser un fichier existant
	while(file_exists($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'.$prefix.'-'.$nom.'.'.$ext)) {
		$prefix .= rand(0,9);
	}
	$dest = $prefix.'-'.$nom;
	// copie du fichier
	if(move_uploaded_file($_FILES['fichier']['tmp_name'], $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'. $dest.'.'.$ext)) {
		list($width, $height) = getimagesize($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'. $dest.'.'.$ext);
		$img = array(
			'racine' => $GLOBALS['racine'],
			'dossie' => preg_replace('#\.\./#','',$GLOBALS['dossier_images'].'/'),
			'nomfic' => $dest,
			'nomdnn' => $nom,
			'extens' => '.'.$ext,
			'iwidth' => $width,
			'height' => $height,
		);
		return $img;
	} else {
		return FALSE;
	}
}


function afficher_form_image($erreurs='', $image= '') {
	if ($erreurs) {
		erreurs($erreurs);
	}
	echo '<form id="preferences" enctype="multipart/form-data" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">'."\n";
	echo '<fieldset class="pref" >'."\n";
	echo legend($GLOBALS['lang']['label_image_ajout'], 'legend-addimage');
	echo '<p>'."\n";
	echo '<label for="fichier">'.$GLOBALS['lang']['nouvelle_image'].'</label>'."\n";
	echo '<input name="fichier" type="file" size="25" />'."\n";
	echo '</p>'."\n";
	echo form_text('nom_entree', '', $GLOBALS['lang']['img_nom_donnee'] );
	echo '<p>'."\n";
	echo input_upload();
	echo hidden_input('_verif_envoi', '1');
	echo '</p>'."\n";
	echo '</fieldset>';
	if (!empty($image)) {
		echo '<fieldset class="pref">'."\n";
		echo legend($GLOBALS['lang']['label_votre_image'], 'legend-picture');
		echo '<form enctype="multipart/form-data" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">'."\n";
		echo '<p>'."\n";
		echo $GLOBALS['lang']['nouvelle_image'].' <a href="'.$image['racine'].$image['dossie'].$image['nomfic'].$image['extens'].'">'.$image['nomdnn'].'</a> '.$GLOBALS['lang']['img_upload_succes'];
		echo '</p>'."\n";
		echo '<p>'."\n";
		echo '<input style="width:100%;" type="text" value=\'<img src="'.$image['racine'].$image['dossie'].$image['nomfic'].$image['extens'].'" alt="'.$image['nomdnn'].'" style="width:'.$image['iwidth'].'px; height:'.$image['height'].'px;" />\' />';
		echo '<center><img src="'.$image['racine'].$image['dossie'].$image['nomfic'].$image['extens'].'"  alt="'.$image['nomdnn'].'"style="max-width: 400px; border:1px dotted gray;" /></center>';
		echo '</p>'."\n";
		echo '</fieldset>';
	}
	echo '</form>'."\n";

}

function get_extension($nom) {
	$nom = explode(".", $nom);
	$nb = count($nom);
	return strtolower($nom[$nb-1]);
}

function find_image($article_id) {
	$image = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'.$article_id.'/'.$image;
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

function liste_images($id) {
	$liste= array();
	if (isset($GLOBALS['dossier_images'])) {
		if ( ($dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'.$id.'/'.$GLOBALS['dossier_vignettes']) AND (is_dir($dossier)) ) {
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
