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
// SUPPRIMER
if (isset($_GET['del'])) {
		supprimer_commentaire($article_id, $_GET['del']);
}


// DEBUT PAGE
afficher_top($GLOBALS['lang']['titre_commentaires']);
afficher_msg();

print '<div id="top">'."\n";
moteur_recherche();

print '<ul id="nav">'."\n";

afficher_menu('lastcom.php');

print '</ul>'."\n";
print '</div>'."\n";

// SUBNAV
print '<div id="subnav">';
back_list();
print '<ul id="mode">';
	print '<li id="lien-edit"><a href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].'</a></li>';
	print '<li id="lien-comments">'.ucfirst(nombre_commentaires($post['nb_comments'])).'</li>';
print '</ul>';
print '</div>';
 	
print '<div id="axe">'."\n";
print '<div id="page">'."\n";

// COMMENTAIRES
$tableau=table_derniers($GLOBALS['dossier_data_commentaires'], $GLOBALS['nb_list_com']);
	if($tableau != ""){
		foreach ($tableau as $id => $content) {
			$comment = init_comment('admin', remove_ext($content));
			afficher_commentaire($comment, 1);
		}
	}
	else {
		echo $GLOBALS['lang']['no_comments'];
	}
footer();
?>
