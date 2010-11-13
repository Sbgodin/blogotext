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
/*if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) ) {
	header('Location: auth.php');
	exit;
}*/

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$ip = $_SERVER['REMOTE_ADDR'];
}


if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) or (!isset($_SESSION['antivol'])) or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip)) or (!isset($_SESSION['timestamp'])) or ($_SESSION['timestamp'] < time()-1800)) {
	header('Location: logout.php');
	exit;
}
$_SESSION['timestamp'] = time();

// SUPPRIMER
if (isset($_POST['supprimer_comm'])) {
	if (isset($_POST['security_coin']) and htmlspecialchars($_POST['security_coin']) == md5($_POST['comm_id'].$_SESSION['time_supprimer_commentaire']) and $_SESSION['time_supprimer_commentaire'] >= (time() - 300) ) {
		supprimer_commentaire(htmlspecialchars($article_id), htmlspecialchars($_POST['comm_id']));
	}
	else {
		redirection($_SERVER['PHP_SELF'].'?post_id='.$article_id.'&errmsg=error_comment_suppr');
	}
}

// DEBUT PAGE
afficher_top($GLOBALS['lang']['titre_commentaires']);
afficher_msg();
afficher_msg_error();

echo '<div id="top">'."\n";
echo '<ul id="nav">'."\n";

afficher_menu('lastcom.php');

echo '</ul>'."\n";
echo '</div>'."\n";

// SUBNAV
echo '<div id="subnav">';
back_list();
echo '</div>';
 	
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// COMMENTAIRES
$tableau=table_derniers($GLOBALS['dossier_data_commentaires'], $GLOBALS['nb_list_com']);
	if($tableau != ""){
		$nb = 0;
		foreach ($tableau as $id => $content) {
			$nb++;
			$comment = init_comment('admin', remove_ext($content));
			afficher_commentaire($comment, 1);
		}
echo '<p style="text-align: center;">'.$nb.' '.$GLOBALS['lang']['label_commentaires'].' '.$GLOBALS['lang']['sur'].' '.count(table_derniers($GLOBALS['dossier_data_commentaires']))."</p>\n";

	}
	else {
		echo $GLOBALS['lang']['no_comments'];
	}
footer();
?>
