<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function confirmation($message) {
	echo '<div class="confirmation">'.$message.'</div>'."\n";
}
function no_confirmation($message) {
	echo '<div class="no_confirmation">'.$message.'</div>'."\n";
}

function legend($legend, $class='') {
	echo '<legend class="'.$class.'">'.$legend.'</legend>'."\n"; 
}

function label($for, $txt) {
	echo "\n".'<label for="'.$for.'">'.$txt.'</label>'."\n"; 
}

function info($message) {
	echo '<p class="info">'.$message.'</p>'."\n";
}

function erreurs($erreurs) {
	  if ($erreurs) {
    $texte_erreur = '<div id="erreurs">'.'<strong>'.$GLOBALS['lang']['erreurs'].'</strong> :' ;
    $texte_erreur .= '<ul><li>';
    $texte_erreur .= implode('</li><li>', $erreurs);
    $texte_erreur .= '</li></ul></div>'."\n";
    } else {
    $texte_erreur = '';
    }
    echo $texte_erreur; 
}

function erreur($message) {
	  echo '<p class="erreurs">'.$message.'</p>'."\n";
}

function question($message) {
	  echo '<p id="question">'.$message.'</p>';
}

function afficher_msg() {
	if (isset($_GET['msg'])) {
		if (array_key_exists(htmlspecialchars($_GET['msg']), $GLOBALS['lang'])) {
			confirmation($GLOBALS['lang'][$_GET['msg']]);
		}
	}
}
function afficher_msg_error() {
	if (isset($_GET['errmsg'])) {
		if (array_key_exists($_GET['errmsg'], $GLOBALS['lang'])) {
			no_confirmation($GLOBALS['lang'][$_GET['errmsg']]);
		}
	}
}

function moteur_recherche() {
	$requete='';
	if (isset($_GET['q'])) {
		$requete= htmlspecialchars(stripslashes($_GET['q']));
	}
	echo '<form action="index.php" method="get">'."\n";
	echo '<div id="recherche">'."\n";
	echo	'<input id="champ" name="q" type="text" size="25" value="'.$requete.'" />'."\n";
	echo	'<input id="input-rechercher" type="submit" value="'.$GLOBALS['lang']['rechercher'].'" />'."\n";
		echo '</div>'."\n";
	echo	'</form>'."\n\n";
}

function afficher_top($titre) {
	if (isset($GLOBALS['lang']['id'])) {
		$lang_id = $GLOBALS['lang']['id'];
	} else {
		$lang_id = 'fr';
	}
header ('Content-type: text/html; charset=UTF-8');
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n" ;
echo	'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang_id.'">'."\n";
echo '<head>'."\n";
echo '<meta http-equiv="content-type" content="text/html; charset='.$GLOBALS['charset'].'" />'."\n";
echo '<link type="text/css" rel="stylesheet" href="style/ecrire.css" />'."\n";
echo '<title> '.$GLOBALS['nom_application'].' | '.$titre.'</title>'."\n";
echo	'<script type="text/javascript">'."\n";
echo "function ouvre(fichier) {"."\n";
echo "ff=window.open(fichier,\"popup\", \"width=380, height=460, scrollbars=1, resizable=1\") }"."\n";
echo '</script>'."\n";
echo '</head>'."\n\n";
echo '<body>'."\n\n";
}

function afficher_titre($titre, $id, $niveau) {
	echo '<h'.$niveau.' id="'.$id.'">'.$titre.'</h'.$niveau.'>'."\n";
}

function footer() {
echo '</div>'."\n";
echo '</div>'."\n";
echo '<p id="footer">© <a href="'.$GLOBALS['appsite'].'">'.$GLOBALS['nom_application'].'</a> '/*.$GLOBALS['version'].*/.'</p>'."\n";
echo '</body>'."\n";
echo '</html>'."\n";
}

function afficher_calendrier($depart, $ce_mois, $annee, $ce_jour='') {    		    
	    	$jours_semaine = array(
	    		$GLOBALS['lang']['lu'],
	    		$GLOBALS['lang']['ma'],
	    		$GLOBALS['lang']['me'],
	    		$GLOBALS['lang']['je'],
	    		$GLOBALS['lang']['ve'],
	    		$GLOBALS['lang']['sa'],
	    		$GLOBALS['lang']['di']
	    	);
	    	$premier_jour = mktime('0', '0', '0', $ce_mois, '1', $annee);
	    	$jours_dans_mois = date('t', $premier_jour);
	    	$decalage_jour = date('w', $premier_jour-'1');
	    	$prev_mois = $_SERVER['PHP_SELF'].'?'.$annee.'/'.nombre_formate($ce_mois-'1');
	    		if ($prev_mois == $_SERVER['PHP_SELF'].'?'.$annee.'/'.'00') {
	    			$prev_mois = $_SERVER['PHP_SELF'].'?'.($annee-'1').'/'.'12';
	    		}
		    $next_mois = $_SERVER['PHP_SELF'].'?'.$annee.'/'.nombre_formate($ce_mois+'1');
	    		if ($next_mois == $_SERVER['PHP_SELF'].'?'.$annee.'/'.'13') {
	    			$next_mois = $_SERVER['PHP_SELF'].'?'.($annee+'1').'/'.'01';
	    		}
// On verifie si il y a un ou des articles du jour	    	
$dossier = $depart.'/'.$annee.'/'.$ce_mois.'/';
if ( is_dir($dossier) AND $ouverture = opendir($dossier) ) { 
		$jour_fichier = array();
			while ($fichiers = readdir($ouverture)){
				if ( (is_file($dossier.$fichiers)) AND (get_statut($dossier.$fichiers) == '1') ) {
				$jour_fichier[]= substr($fichiers, '6', '2');
				}
			}
}
	    	$GLOBALS['calendrier'] = '<table id="calendrier">'."\n";
	    	$GLOBALS['calendrier'].= '<caption>';
	    	$GLOBALS['calendrier'].= '<a href="'.$prev_mois.'">&#171;</a>&nbsp;';
// Si on affiche un jour on ajoute le lien sur le mois
	    	if ($ce_jour) {
	    			$GLOBALS['calendrier'].= '<a href="'.$_SERVER['PHP_SELF'].'?'.$annee.'/'.$ce_mois.'">'.mois_en_lettres($ce_mois).' '.$annee.'</a>';
	    	} else {
	    			$GLOBALS['calendrier'].= mois_en_lettres($ce_mois).' '.$annee;
	    	}
// On ne peut pas aller dans le futur
	    	if ( ($ce_mois != date('m')) || ($annee != date('Y')) ) {
	    			$GLOBALS['calendrier'].= '&nbsp;<a href="'.$next_mois.'">&#187;</a>';
	    	}
	    	$GLOBALS['calendrier'].= '</caption>'."\n";
	    	$GLOBALS['calendrier'].= '<tr><th><abbr>';
	    	$GLOBALS['calendrier'].= implode('</abbr></th><th><abbr>', $jours_semaine);
	    	$GLOBALS['calendrier'].= '</abbr></th></tr><tr>';
	    	if ($decalage_jour > 0) {	
	    		for ($i = 0; $i < $decalage_jour; $i++) {
	    			$GLOBALS['calendrier'].=  '<td></td>';
	    		}
	    	}
	    	// Indique le jour consultÈ
	    	for ($jour = 1; $jour <= $jours_dans_mois; $jour++) {
	    		if ($jour == $ce_jour) {
	    			$class = ' class="active"';
	    		} else {
	    			$class = '';
	    		}
	    	if ( (isset($jour_fichier)) AND in_array($jour, $jour_fichier) ) {
					$lien= '<a href="'.$_SERVER['PHP_SELF'].'?'.$annee.'/'.$ce_mois.'/'.nombre_formate($jour).'">'.$jour.'</a>';
				} else {
					$lien= $jour;
				}
	    		$GLOBALS['calendrier'].= '<td'.$class.'>';
	    		$GLOBALS['calendrier'].= $lien;
	    		$GLOBALS['calendrier'].= '</td>';
	    		$decalage_jour++;
	    		if ($decalage_jour == '7') {
	    			$decalage_jour = '0';
	    			$GLOBALS['calendrier'].=  '</tr>';
	    			if ($jour < $jours_dans_mois) {
	    				$GLOBALS['calendrier'].= '<tr>';
	    			}
	    		}
	    	}
	    	if ($decalage_jour > '0') {
	    		for ($i = $decalage_jour; $i < '7'; $i++) {
	    			$GLOBALS['calendrier'].= '<td> </td>';
	    		}
	    		$GLOBALS['calendrier'].= '</tr>';
	    	}
	    	$GLOBALS['calendrier'].= '</table>';
}
?>
