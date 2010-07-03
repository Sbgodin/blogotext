<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

/// formulaires THEMES ///

function afficher_form_recherche($mots_saisis='') {
	if ( (isset($mots_saisis)) AND ($mots_saisis != '') ) {
		$defaut = stripslashes($mots_saisis);
	} else {
		$defaut = '';
	}
	$GLOBALS['form_recherche'] = '';
	$GLOBALS['form_recherche'] .= '<form id="search" action="'.$_SERVER['PHP_SELF'].'" method="get">'."\n";
	$GLOBALS['form_recherche'] .=	'<input name="q" id="q" type="text" size="15" value="'.$defaut.'" />'."\n";
	$GLOBALS['form_recherche'] .=	'<input id="ok" type="submit" value="'.$GLOBALS['lang']['rechercher'].'" />'."\n";
	$GLOBALS['form_recherche'] .= '</form>'."\n";
}

/// formulaires GENERAUX //////////

function lien_nav($url, $id, $label, $active) {
	echo '<li><a href="'.$url.'" id="'.$id.'" ';
	if ($active == $url) {
	echo 'class="current"';
	}
	echo '>'.$label.'</a></li>'."\n";
}

/// formulaires PREFERENCES //////////

function select_yes_no($name, $defaut, $label) {
			$choix = array(
								'1' => $GLOBALS['lang']['oui'],
								'0' => $GLOBALS['lang']['non']
                );
      echo '<p>'."\n";
	     echo '<label>'.$label.'</label>'."\n";
			echo '<select name="'.$name.'">'."\n" ;
			   foreach ($choix as $option => $label) {
        	echo '<option value="'.htmlentities($option).'"';
        	if ($defaut == $option) {
            echo ' selected="selected"';
        	}
        	echo '>' . htmlentities($label) . '</option>';
			   }
			echo '</select>'."\n";
			echo '</p>'."\n";
}

function form_format_date($defaut) {
	$jour_l = jour_en_lettres(date('d'), date('m'), date('Y'));
	$mois_l = mois_en_lettres(date('m'));
		$formats = array (
			'0' => date('d').'/'.date('m').'/'.date('Y'),							// 14/01/1983
			'1' => date('m').'/'.date('d').'/'.date('Y'),							// 14/01/1983
			'2' => date('d').' '.$mois_l.' '.date('Y'),								// 14 janvier 1983
			'3' => $jour_l.' '.date('d').' '.$mois_l.' '.date('Y'),		// vendredi 14 janvier 1983
			'4' => $mois_l.' '.date('d').', '.date('Y'),							// janvier 14, 1983
			'5' => $jour_l.', '.$mois_l.' '.date('d').', '.date('Y'),	// vendredi, janvier 14, 1983
			'6' => date('Y').'-'.date('m').'-'.date('d')							// 1983-01-14
		);
	echo '<p>';
	echo '<label>'.$GLOBALS['lang']['pref_format_date'].'</label>';
	echo '<select name="format_date">' ;
			   foreach ($formats as $option => $label) {
        	echo '<option value="'.htmlentities($option).'"';
        	if ($defaut == $option) {
            echo ' selected="selected"';
        	}
        	echo '>' . htmlentities($label) . '</option>';
			   }
			echo '</select> ';
	echo '</p>';
}

function form_format_heure($defaut) {
		$formats = array (
			'0' => date('H').':'.date('i').':'.date('s'),											// 23:56:04
			'1' => date('H').':'.date('i'),																		// 23:56
			'2' => date('h').':'.date('i').':'.date('s').' '.date('A'),				// 11:56:04 PM
			'3' => date('h').':'.date('i').' '.date('A')										// 11:56 PM
		);
	echo '<p>';
	echo '<label>'.$GLOBALS['lang']['pref_format_heure'].'</label>';
	echo '<select name="format_heure">' ;
			   foreach ($formats as $option => $label) {
        	echo '<option value="'.htmlentities($option).'"';
        	if ($defaut == $option) {
            echo ' selected="selected"';
        	}
        	echo '>' . htmlentities($label) . '</option>';
			   }
			echo '</select> ';
	echo '</p>';
}

function form_langue($defaut) {
			echo '<p>';
      echo '<label>'.$GLOBALS['lang']['pref_langue'].'</label>';
			echo '<select name="langue">' ;
			   foreach ($GLOBALS['langs'] as $option => $label) {
        	echo '<option value="'.htmlentities($option).'"';
        	if ($defaut == $option) {
            echo ' selected="selected"';
        	}
        	echo '>'.$label.'</option>';
			   }
			echo '</select> ';
			echo '</p>';
}

function form_langue_install($label) {
			echo '<p>';
      echo '<label>'.$label.'</label>';
			echo '<select name="langue">' ;
			   foreach ($GLOBALS['langs'] as $option => $label) {
        	echo '<option value="'.htmlentities($option).'"';
        	echo '>'.$label.'</option>';
			   }
			echo '</select> ';
			echo '</p>';
}

function liste_themes($chemin) {
	$dossier = '../'.$chemin;
		if ( $ouverture = opendir($dossier) ) { 
      while ($dossiers=readdir($ouverture) ){
      	if ( file_exists($dossier.'/'.$dossiers.'/style.css') ) {
       $themes[$dossiers]=$dossiers;
      	}
      }
			 closedir($ouverture);
		}
return $themes;
}


// formulaires ARTICLES //////////

function afficher_form_filtre($defaut='') {
echo '<form method="get" action="'.$_SERVER['PHP_SELF'].'" >'."\n";
echo '<div id="form-filtre">'."\n";
	filtre($GLOBALS['dossier_data_articles'], $defaut);
echo '</div>'."\n";
echo '</form>'."\n";
}

function filtre($dossier, $defaut='') {
if ( $ouverture = opendir($dossier) ) { 
       while ( false !== ($file = readdir($ouverture)) ) {
       	if (preg_match('/\d{4}/', $file) ){
       		$annees[]=$file;
       	}
       }
       closedir($ouverture);
}
if (isset($annees)) {
foreach ($annees as $id => $dossier_annee) {
 	$chemin = $dossier.'/'.$dossier_annee.'/';
	 if ( $ouverture = opendir($chemin) ) { 
       while ( false !== ($file_mois = readdir($ouverture)) ) {
       	if ($fichiers = opendir($chemin.$file_mois)) {
       		while ($files=readdir($fichiers)) {
       			if ( (preg_match('/\d{2}/', $file_mois)) AND ((substr($files,'-3','3')) == $GLOBALS['ext_data']) ){
       			$dossier_mois[$dossier_annee.$file_mois]= mois_en_lettres($file_mois).' '.$dossier_annee;	
       		}
       		}
       	}
       }
       closedir($ouverture);
		}
}
}
if (isset($dossier_mois)) {
echo '<label>'.$GLOBALS['lang']['label_afficher'].'</label>';
echo "\n".'<select name="filtre">'."\n" ;
echo '<option value="">'.$GLOBALS['lang']['label_derniers'].'</option>'."\n";
/// BROUILLONS
echo '<option value="draft"';
	if ($defaut == 'draft') {
		echo ' selected="selected"';
	}
echo '>'.$GLOBALS['lang']['label_brouillons'].'</option>'."\n";
/// PUBLIES
echo '<option value="pub"';
	if ($defaut == 'pub') {
		echo ' selected="selected"';
	}
echo '>'.$GLOBALS['lang']['label_publies'].'</option>'."\n";
					echo '<optgroup label="'.$GLOBALS['lang']['label_date'].'">';
			   foreach ($dossier_mois as $option => $label) {
        	echo '<option value="' . htmlentities($option) . '"';
        	if ($defaut == $option) {
            echo ' selected="selected"';
        	}
        	echo '>' . htmlentities($label) . '</option>'."\n";
			   }
			   echo '</optgroup>';
			echo '</select> '."\n\n";
			echo '<input type="submit" value="'.$GLOBALS['lang']['label_afficher'].'" />'."\n";
}
}

function back_list() {
	echo '<a id="backlist" href="index.php">'.$GLOBALS['lang']['retour_liste'].'</a>';
}

/// formulaires BILLET //////////

function afficher_form_billet($article='', $erreurs= '') {
// Valeurs par defaut
    if (isset($_POST['_verif_envoi'])) {
			$defaut_jour = $_POST['jour'];
			$defaut_mois = $_POST['mois'];
			$defaut_annee = $_POST['annee'];
			$defaut_heure = $_POST['heure'];
			$defaut_minutes = $_POST['minutes'];
			$defaut_secondes = $_POST['secondes'];
			$titredefaut = stripslashes($_POST['titre']);
			$chapodefaut = stripslashes($_POST['chapo']);
			$contenudefaut = stripslashes($_POST['contenu']);
			$statutdefaut = $_POST['statut'];
			$allowcommentdefaut = $_POST['allowcomment'];
    	} elseif ($article != '') {
    		$titredefaut = $article['titre'];
    		$chapodefaut = $article['chapo'];
				$contenudefaut = $article['contenu_wiki'];
				$statutdefaut = $article['statut'];
				$allowcommentdefaut = $article['allow_comments'];
				} else {
					$defaut_jour = date('d');
					$defaut_mois = date('m');
					$defaut_annee = date('Y');
					$defaut_heure = date('H');
					$defaut_minutes = date('i');
					$defaut_secondes = date('s');
					$chapodefaut = '';
					$contenudefaut = '';
					$titredefaut = '';
					$statutdefaut = '1';
					$allowcommentdefaut = '1';
		}
if ($erreurs) {
	erreurs($erreurs);
}
if (isset($article['id'])) {
echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?post_id='.$article['id'].'" >'."\n";
} else {
echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" >'."\n";
}
echo '<div id="form">'."\n";
label('titre', $GLOBALS['lang']['label_titre']);
form_titre($titredefaut) ;
label('chapo', $GLOBALS['lang']['label_chapo']);
form_chapo($chapodefaut) ;
echo '<p id="wiki" ><a href="javascript:ouvre(\'wiki.php\')">'.$GLOBALS['lang']['label_wiki'].'</a></p>'."\n";
label('contenu', $GLOBALS['lang']['label_contenu']);
form_contenu($contenudefaut) ;
if 	(!$article) {
			echo '<div id="date">'."\n";
			echo '<div id="formdate">';
				form_annee($defaut_annee) ;
				form_mois($defaut_mois) ;
				form_jour($defaut_jour) ;
			echo '</div>';
			echo '<div id="formheure">';
				form_heure($defaut_heure, $defaut_minutes, $defaut_secondes) ;
			echo '</div>';
			echo '</div>'."\n";
} else {
echo '<div id="date">';
echo '<p id="formdate">'.date_formate($article['id']).'</p>';
echo '<p id="formheure">'.heure_formate($article['id']).'</p>';
echo '</div>';
hidden_input('annee', $article['annee']);
hidden_input('mois', $article['mois']);
hidden_input('jour', $article['jour']);
hidden_input('heure', $article['heure']);
hidden_input('minutes', $article['minutes']);
hidden_input('secondes', $article['secondes']);
}
			echo '<div id="opts">'."\n";
			form_statut($statutdefaut);
			form_allow_comment($allowcommentdefaut);
			echo '</div>'."\n";
	echo '<div id="bt">';
		input_enregistrer();
		if ($article) {
		input_supprimer();
		}
	echo '</div>';
hidden_input('_verif_envoi', '1');
echo '</div>'."\n";
echo '</form>'."\n";
}
// FIN AFFICHER_FORM_BILLET

// ELEMENTS FORM ECRIRE //////////

function form_jour($jour_affiche) {
			$jours = array("01" => '1', "02" => '2', "03" => '3', "04" => '4', "05" => '5', "06" => '6', "07" => '7', "08" => '8',
                "09" => '9', "10" => '10', "11" => '11', "12" => '12', "13" => '13', "14" => '14', "15" => '15', "16" => '16',
                "17" => '17', "18" => '18', "19" => '19', "20" => '20', "21" => '21', "22" => '22', "23" => '23', "24" => '24',
                "25" => '25', "26" => '26', "27" => '27', "28" => '28', "29" => '29', "30" => '30', "31" => '31');
			echo '<select name="jour">'."\n";
			   foreach ($jours as $option => $label) {
        	echo '<option value="' . htmlentities($option) . '"';
        	if ($jour_affiche == $option) {
            echo ' selected="selected"';
        	}
        	echo '>' . htmlentities($label) . '</option>'."\n";
			   }
			echo '</select>'."\n\n";
}

function form_mois($mois_affiche) {
			$mois = array(
								"01" => $GLOBALS['lang']['janvier'], "02" => $GLOBALS['lang']['fevrier'], 
								"03" => $GLOBALS['lang']['mars'], "04" => $GLOBALS['lang']['avril'], 
                "05" => $GLOBALS['lang']['mai'], "06" => $GLOBALS['lang']['juin'], 
                "07" => $GLOBALS['lang']['juillet'], "08" => $GLOBALS['lang']['aout'],
                "09" => $GLOBALS['lang']['septembre'], "10" => $GLOBALS['lang']['octobre'], 
                "11" => $GLOBALS['lang']['novembre'], "12" => $GLOBALS['lang']['decembre']
                );
			echo '<select name="mois">'."\n" ;
			   foreach ($mois as $option => $label) {
        	echo '<option value="' . htmlentities($option) . '"';
        	if ($mois_affiche == $option) {
            echo ' selected="selected"';
        	}
        	echo '>'.$label.'</option>'."\n";
			   }
			echo '</select>'."\n\n";
}

function form_annee($annee_affiche) {
			$annees = array();
			for ($annee = date('Y') -3, $annee_max = date('Y') +4; $annee < $annee_max; $annee++) {
    	$annees[$annee] = $annee;
	    }
	    echo '<select name="annee">'."\n" ;
			   foreach ($annees as $option => $label) {
        	echo '<option value="' . htmlentities($option) . '"';
         	if ($annee_affiche == $option) {
            echo ' selected="selected"';
        	}
        	echo '>' . htmlentities($label) . '</option>'."\n";
			   }
			echo '</select>'."\n\n";
}

function form_heure($heureaffiche, $minutesaffiche, $secondesaffiche) {
		echo '<input name="heure" type="text" size="2" maxlength="2" value="'.$heureaffiche.'" /> :';
		echo '<input name="minutes" type="text" size="2" maxlength="2" value="'.$minutesaffiche.'" /> :' ;
		echo '<input name="secondes" type="text" size="2" maxlength="2" value="'.$secondesaffiche.'" />' ;
}


function form_statut($etat) {
		echo '<div id="formstatut">'."\n";
		$choix= array(
			'1' => $GLOBALS['lang']['label_publie'],
			'0' => $GLOBALS['lang']['label_brouillon']
		);
		form_select('statut', $choix, $etat, $GLOBALS['lang']['label_statut']);
		echo '</div>'."\n";
}

function form_allow_comment($etat) {
		echo '<div id="formallowcomment">'."\n";
		$choix= array(
			'1' => $GLOBALS['lang']['ouverts'],
			'0' => $GLOBALS['lang']['fermes']
		);
		// Compatibilite version sans
		if ($etat == '') {
			$etat= '1';
		}
		form_select('allowcomment', $choix, $etat, $GLOBALS['lang']['label_allowcomment']);
		echo '</div>'."\n";
}

function form_titre($titreaffiche) {
		echo '<input id="titre" name="titre" type="text" size="50" value="'.$titreaffiche.'" />'."\n" ;
}

function form_chapo($chapoaffiche) {
    echo '<textarea id="chapo" name="chapo" rows="5" cols="60">'.$chapoaffiche.'</textarea>'."\n" ;
}

function form_contenu($contenuaffiche) {
    echo '<textarea id="contenu" name="contenu" rows="20" cols="60">'.$contenuaffiche.'</textarea>'."\n" ;
}

?>
