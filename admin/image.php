<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(E_ALL);
require_once '../inc/inc.php';
session_start() ;
if ( (!isset($_SESSION['nom_utilisateur'])) || ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) ) {
	header('Location: auth.php');
	exit;
}

// TITRE PAGE
if ( isset($fichier_data) ) {
	$titre_image = $GLOBALS['lang']['titre_maj'];
} else {
	$titre_image = $GLOBALS['lang']['titre_image'];
}

// DEBUT PAGE
afficher_top($GLOBALS['lang']['titre_image']);
afficher_msg();

/**
 * Script d'upload d'image PHP
 * http://damienalexandre.fr/
 * Novembre 2007 - v1.3
 * http://damienalexandre.fr/Upload-d-image-en-PHP.html
 */

function get_extension($nom) {
	$nom = explode(".", $nom);
	$nb = count($nom);
	return strtolower($nom[$nb-1]);
}

// --------------------- Options diverses //

// Extensions images autorisé
$extensions_ok = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
// MimeType autorisé
/* 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF (Ordre des octets Intel), 8 = TIFF (Ordre des octets Motorola), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF */
$typeimages_ok = array(1,2,3,4,5);

$taille_ko = 1024; // Taille en kilo octect (ko)
$taille_max = $taille_ko*1024; // En octects

// verifie si le dossier des images de destination (defini dans /inc/conf.php) existe. Sinon il le cree.
if ( !file_exists($GLOBALS['dossier_images'])) {
	creer_dossier($GLOBALS['dossier_images']);
}

$dest_dossier = $GLOBALS['dossier_images']; // Creez ce dossier et chmoodez le !
//echo_r($_FILES['photo']);
if(isset($_FILES['photo'])) // Formulaire envoyé
{
	// Les erreurs que PHP renvoi
	if($_FILES['photo']['error'] !== "0") {
		switch ($_FILES['photo']['error']) {
			case 1:
				$erreurs[] = $GLOBALS['lang']['img_err_size'].$taille_ko.'Ko !';
				break;
			case 2:
				$erreurs[] = $GLOBALS['lang']['img_err_size'].$taille_ko.'Ko !';
				break;
			case 3:
				$erreurs[] = $GLOBALS['lang']['img_phperr_partial'];
				break;
			case 4:
			$erreurs[] = $GLOBALS['lang']['img_phperr_nofile'];
				break; // Pas de 5, ne pas demander pourquoi ^^ (voir doc PHP)
			case 6:
				$erreurs[] = $GLOBALS['lang']['img_phperr_tempfolder'];
				break;
			case 7:
				$erreurs[] = $GLOBALS['lang']['img_phperr_DiskWrite'];
				break;
		}
	}

        // getimagesize arrive à traiter le fichier ?
        if(!$getimagesize = getimagesize($_FILES['photo']['tmp_name'])) {
            $erreurs[] = $GLOBALS['lang']['img_err_file'];
        }
        // on vérifie le type de l'image
        if( (!in_array( get_extension($_FILES['photo']['name']), $extensions_ok ))
           or (!in_array($getimagesize[2], $typeimages_ok )))
        {
            foreach($extensions_ok as $text) { $extensions_string .= $text.', '; }
            $erreurs[] = $GLOBALS['lang']['img_err_format'].substr($extensions_string, 0, -2).'.';
        }
        // on vérifie le poids de l'image
        if( file_exists($_FILES['photo']['tmp_name']) 
                  and filesize($_FILES['photo']['tmp_name']) > $taille_max)
        {
            $erreurs[] = $GLOBALS['lang']['img_err_size'].$taille_ko.'Ko !';
        }

        // copie du fichier si aucune erreur !
		if(!isset($erreurs) or empty($erreurs))
        {

            $dest_fichier = basename($_FILES['photo']['name']);
				if (isset($_POST['nom_entree']) and $_POST['nom_entree'] != '') {
					$nom_entree = htmlspecialchars($_POST['nom_entree']); 
					$fichier = explode(".",$dest_fichier);
					$numb = count($fichier);
					$img_extension = ".".$fichier[$numb-1];

					$dest_fichier = $_POST['nom_entree'].$img_extension;
				}
				else {
					$nom_entree = $GLOBALS['lang']['nouvelle_image']; //inutile car les caractères speciaux seront remplacés un peu plus loin.				
				}

			$dest_fichier = strtr($dest_fichier, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
			// un chtit regex pour remplacer tous ce qui n'est ni chiffre ni lettre par "_"
			$dest_fichier = preg_replace('/([^.a-z0-9]+)/i', '_', $dest_fichier);
			$dest_fichier = "blog-".date('ymd').'-'.$dest_fichier;
            
			// pour ne pas ecraser un fichier existant
			while(file_exists($dest_dossier . $dest_fichier)) {
				$dest_fichier = rand(0,9).$dest_fichier;
			}
            
			// copie du fichier
			if(move_uploaded_file($_FILES['photo']['tmp_name'], $dest_dossier . $dest_fichier)) {
				$valid[]  = $GLOBALS['lang']['img_upload_succes'].'<br/>';
				$valid[] .= '<a href="'.$dest_dossier . $dest_fichier.'">/'.$dest_fichier.'</a><br/>';
				$nom_dossier = preg_replace('#\.\./#','',$GLOBALS['dossier_images']);
				$valid[] .= '<input style="width:500px;" type="text" value=\'<img src="'.$GLOBALS['racine'].$nom_dossier.$dest_fichier.'" alt="'.$nom_entree.'" />\' />';
				$valid[] .= '<center><img src="'.$dest_dossier.$dest_fichier.'" style="max-width: 400px;" /></center>';
			} else {
				$erreurs[] = $GLOBALS['lang']['img_err_chmod'].$dest_dossier;
			}
		}
	}
if(!empty($valid)) {
	confirmation($GLOBALS['lang']['confirm_image_ajout']);
}
echo '<div id="top">'."\n";
moteur_recherche();
echo '<ul id="nav">'."\n";

afficher_menu('image.php');
echo '</ul></div>';
echo '<div id="axe">'."\n".'<div id="page">'."\n";



echo '<form method="post" action="" enctype="multipart/form-data" >';
echo '<fieldset class="pref">';
legend($GLOBALS['lang']['prefs_legend_image'], 'legend-image');
echo '<p>'."\n";
echo '<label for="photo">'.$GLOBALS['lang']['nouvelle_image'].' : </label>'."\n";
echo '<input type="file" name="photo" id="photo" />'."\n".'</p>'."\n".'<p>';
echo '<label for="nom_entree">'.$GLOBALS['lang']['img_nom_donnee'].'</label>';
echo '<input type="text" name="nom_entree" /> '.$GLOBALS['lang']['img_avert_format_nom']."\n";
echo '</p>'."\n";

if(!empty($erreurs)) {
	echo '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :<ul>';
	foreach($erreurs as $erreur) {
		echo '<li>'.$erreur.'</li>';
	}
	echo '</ul></div>';
}

if(!empty($valid)) {
	echo '<div id="succes"><strong>'.$GLOBALS['lang']['succes'].' : </strong><ul>';
	foreach($valid as $text) {
		echo '<li>'.$text.'</li>';
	}
	echo '</ul></div>';
}

echo '</fieldset>';
echo '<div id="bt">';
input_upload();
echo '</div>';
echo '</form>';

footer();
?>
