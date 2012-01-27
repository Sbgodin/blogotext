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

function traiter_form_image() {
	$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'];
	if (!is_dir($dossier)) {
		if (FALSE === creer_dossier($dossier)) die($GLOBALS['lang']['err_file_write']);
	}
	$image = basename($_FILES['fichier']['name']);
	$ext = pathinfo($image, PATHINFO_EXTENSION);
	if (!empty($_POST['nom_entree'])) {
		$nom = htmlspecialchars($_POST['nom_entree']); 
	} else {
		$nom = preg_replace('#\.'.$ext.'$#', '', $image);
	}
	$nom = diacritique($nom, 0, 0);
	$prefix = 'blog-'.date('ymd');
	// pour ne pas ecraser un fichier existant
	while(file_exists($dossier.'/'.$prefix.'-'.$nom.'.'.$ext)) {
		$prefix .= rand(0,9);
	}
	$dest = $prefix.'-'.$nom;
	// copie du fichier
	if (move_uploaded_file($_FILES['fichier']['tmp_name'], $dossier.'/'. $dest.'.'.$ext)) {
		list($width, $height) = getimagesize($dossier.'/'. $dest.'.'.$ext);
		$img = array(
			'racine' => $GLOBALS['racine'],
			'dossie' => $GLOBALS['dossier_images'].'/',
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
	echo '<form id="preferences" enctype="multipart/form-data" method="post" action="'.$_SERVER['PHP_SELF']/*.'?'.$_SERVER['QUERY_STRING']*/.'">'."\n";
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
	if (!empty($image)) { // affichage image juste uploadee
		echo '<fieldset class="pref">'."\n";
		echo legend($GLOBALS['lang']['label_votre_image'], 'legend-image');
		echo '<p>'."\n";
		echo $GLOBALS['lang']['nouvelle_image'].' <a href="'.$image['racine'].$image['dossie'].$image['nomfic'].$image['extens'].'">'.$image['nomdnn'].'</a> '.$GLOBALS['lang']['img_upload_succes'];
		echo '</p>'."\n";
		echo '<p>'."\n";
		echo '<input style="width:100%;" type="text" value=\'<img src="'.$image['racine'].$image['dossie'].$image['nomfic'].$image['extens'].'" alt="'.$image['nomdnn'].'" width="'.$image['iwidth'].'" height="'.$image['height'].'" style="max-width:100%;height:auto;" />\' />';
		echo '<center><img src="'.$image['racine'].$image['dossie'].$image['nomfic'].$image['extens'].'"  alt="'.$image['nomdnn'].'"style="max-width: 400px; border:1px dotted gray;" /></center>';
		echo '</p>'."\n";
		echo '</fieldset>';
	} else { // affichage d'une liste de toutes les images
		echo '<fieldset class="pref">'."\n";
		echo legend($GLOBALS['lang']['img_old'], 'legend-images');
		$contenu = liste_images();
		$nb_images = sizeof($contenu);
		if ($nb_images <= 1) {
			$im = $GLOBALS['lang']['nouvelle_image']; // image
		} else {
			$im = $GLOBALS['lang']['images']; // imageS
		}
		echo '<p>'.$nb_images.' '.$im.'&nbsp;:</p>'."\n";
		echo '<ul>'."\n";
		foreach ($contenu as $image) {
			list($width, $height) = getimagesize($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'.$image);
			$date = substr($image, 5,6);
			$name = substr($image, 12, strlen($image)-12-4);
			$date_formate = (preg_match('#\d{6}#', $date)) ? substr($date, 4,2).'/'.substr($date, 2,2).'/'.substr($date, 0,2) : '';
			$lien = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'.$image;
			$ligne_html  = '<a href="'.$_SERVER['PHP_SELF'].'?image='.$image.'&amp;uid='.sha1(session_id()).'" onclick="return ask_sure()" >'.$GLOBALS['lang']['supprimer'].'</a> - ';

			$id = substr(md5($image), 0,5);

			$ligne_html .= '<a href="#" onclick="popup(\''.$image.'\','.$width.','.$height.',\''.$name.'\'); return false;">code</a> - '.$date_formate.' : ';
			$ligne_html .= '<a href="'.$lien.'" class="image_popup" onmouseover="get_ratio(\''.$id.'\')">'.$image.'<span class="im" style=""><img style="height: auto;max-width:100%;" src="'.$lien.'" id="'.$id.'"/><span id="percent'.$id.'" class="res"></span><span id="span'.$id.'" style="width:'.$width.'px; height:'.$height.'px; display:none;"></span></a>';
			if ($date == date('ymd')) {
				$ligne_html = '<b>'.$ligne_html.'</b>';
			}
			echo '<li>'.$ligne_html.'</li>'."\n";
		}
		echo '</ul>'."\n";
		// javascript
		echo js_image_form_stuff(1); // see in /inc/jasc.php
	}
	echo '</form>'."\n";
}

function find_image($article_id) {
	$image = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'].'/'.$article_id.'/'.$image;
	if (file_exists($image)) {
		$return = '<img src="'.$image.' alt="'.$image.'" />';
	}
}

function resize_img($filename, $destination) {
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	// largeur et hauteur maximale
	$width = '100';
	$height = '100';
	// Cacul des nouvelles dimensions
	list($width_orig, $height_orig) = getimagesize($filename);
	if ($width and ($width_orig < $height_orig)) {
		$width = ($height / $height_orig) * $width_orig;
	} else {
		$height = ($width / $width_orig) * $height_orig;
	}
	// Redimensionnement
	$image_p = imagecreatetruecolor($width, $height);
	if ($ext === 'jpg') {
		$image = imagecreatefromjpeg($filename);
	} elseif ($ext === 'png') {
		$image = imagecreatefrompng($filename);
	} elseif ($ext === 'gif') {
		$image = imagecreatefromgif($filename);
	}

	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	// Enregistrement
	if ($ext === 'jpg') {
		imagejpeg($image_p, $destination, 100);
	} elseif ($ext === 'png') {
		imagepng($image_p, $destination, 100);
	} elseif ($ext === 'gif') {
		imagegif($image_p, $destination, 100);
	}
}

function liste_images() {
	$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_images'];
	if (!is_dir($dossier)) {
		if (FALSE === creer_dossier($dossier)) die($GLOBALS['lang']['err_file_write']);
	}
	$contenu = array();
	if ($ouverture = opendir($dossier)) {
		while (FALSE !== ($fichier = readdir($ouverture))) {
			if (!is_dir($dossier.'/'.$fichier.'/')) {
				$contenu[] = $fichier;
			}
		}
	}
	closedir($ouverture);
	rsort($contenu);
	return $contenu;
}
?>
