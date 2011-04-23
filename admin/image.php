<?php
# *** LICENSE ***
# This file is part of BlogoText.
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(-1);
require_once '../inc/inc.php';

check_session();
$erreurs = array();
$uploaded_image = '';

if (isset($_POST['_verif_envoi'])) {
	$erreurs = valider_form_image();
	if (empty($erreurs)) {
		$image = traiter_form_image();
		if($image === FALSE) {
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

footer();
?>
