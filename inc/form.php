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

/// formulaires GENERIQUES //////////

function form_select($id, $choix, $defaut, $label) {
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<select id="'.$id.'" name="'.$id.'">'."\n";
	foreach ($choix as $valeur => $mot) {
		$form .= '<option value="'.$valeur.'"';
		$form .= ($defaut == $valeur) ? ' selected="selected"' : '';
		$form .= '>'.$mot.'</option>'."\n";
	}
	$form .= '</select>';
	$form .= '</p>'."\n";
	return $form;
}

function form_text($id, $defaut, $label) {
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<input type="text" id="'.$id.'" name="'.$id.'" size="30" value="'.$defaut.'" />'."\n";
	$form .= '</p>'."\n";
	return $form;
}

function form_password($id, $defaut, $label) {
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<input type="password" id="'.$id.'" name="'.$id.'" size="30" value="'.$defaut.'" />'."\n";
	$form .= '</p>'."\n";
	return $form;
}

function form_check($id, $defaut, $label) {
	$checked = ($defaut == 'on') ? 'checked="checked" ' : "";
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<input type="checkbox" id="'.$id.'" name="'.$id.'" '.$checked.'/>'."\n";
	$form .= '</p>'."\n";
	return $form;
}

function form_radio($name, $id, $value, $label, $checked='') {
	$coche = ($checked === TRUE) ? 'checked="checked"' : '';
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<input type="radio" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.$coche.' />'."\n";
	$form .= '</p>'."\n";
	return $form;
}

function textarea($id, $defaut, $label, $cols, $rows) {
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<textarea id="'.$id.'" name="'.$id.'" cols="'.$cols.'" rows="'.$rows.'">'.$defaut.'</textarea>'."\n";
	$form .= '</p>'."\n";
	return $form;
}

function input_supprimer() {
	$form = '<input class="submit submit-suppr" type="submit" name="supprimer" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_article'].'\')" />'."\n";
	return $form;
}

function input_enregistrer() {
	$form = '<input class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['enregistrer'].'" />'."\n";
	return $form;
}

function input_valider() {
	$form = '<input class="submit" type="submit" name="valider" value="'.$GLOBALS['lang']['valider'].'" />'."\n";
	return $form;
}

function input_upload() {
	$form = '<input class="submit" type="submit" name="upload" value="'.$GLOBALS['lang']['img_upload'].'" />'."\n";
	return $form;
}

function hidden_input($nom, $valeur) {
	$form = '<input type="hidden" class="nodisplay" name="'.$nom.'" value="'.$valeur.'" />'."\n";
	return $form;
}

/// formulaires PREFERENCES //////////

function select_yes_no($name, $defaut, $label) {
	$choix = array(
		'1' => $GLOBALS['lang']['oui'],
		'0' => $GLOBALS['lang']['non']
	);
	$form = '<label>'.$label.'</label>'."\n";
	$form .= '<select name="'.$name.'">'."\n" ;
	foreach ($choix as $option => $label) {
		$form .= '<option value="'.htmlentities($option).'"';
		$form .= ($option == $defaut) ? ' selected="selected"' : '';
		$form .= '>' . htmlentities($label) . '</option>';
	}
	$form .= '</select>'."\n";
	return $form;
}

function form_format_date($defaut) {
	$jour_l = jour_en_lettres(date('d'), date('m'), date('Y'));
	$mois_l = mois_en_lettres(date('m'));
	$formats = array (
		'0' => date('d').'/'.date('m').'/'.date('Y'),                     // 05/07/2011
		'1' => date('m').'/'.date('d').'/'.date('Y'),                     // 07/05/2011
		'2' => date('d').' '.$mois_l.' '.date('Y'),                       // 05 juillet 2011
		'3' => $jour_l.' '.date('d').' '.$mois_l.' '.date('Y'),           // mardi 05 juillet 2011
		'4' => $mois_l.' '.date('d').', '.date('Y'),                      // juillet 05, 2011
		'5' => $jour_l.', '.$mois_l.' '.date('d').', '.date('Y'),         // mardi, juillet 05, 2011
		'6' => date('Y').'-'.date('m').'-'.date('d'),                     // 2011-07-05
	);
	$form = '<p>';
	$form .= '<label>'.$GLOBALS['lang']['pref_format_date'].'</label>'."\n";
	$form .= '<select name="format_date">' ;
	foreach ($formats as $option => $label) {
		$form .= '<option value="'.htmlentities($option).'"';
		if ($defaut == $option) {
			$form .= ' selected="selected"';
		}
		$form .= '>' . htmlentities($label) . '</option>'."\n";
	}
	$form .= '</select> '."\n";
	$form .= '</p>'."\n";
	return $form;
}

function form_fuseau_horaire($defaut) {
	if ($GLOBALS['etes_vous_chez_freefr'] == 1) {
		$liste_fuseau = timezone_identifiers_list();
		$form = '<p>';
		$form .= '<label>'.$GLOBALS['lang']['pref_fuseau_horaire'].'</label>';
		$form .= '<select name="fuseau_horaire">' ;
		foreach ($liste_fuseau as $option) {
			$form .= '<option value="'.htmlentities($option).'"';
			$form .= ($defaut == $option) ? ' selected="selected"' : '';
			$form .= '>' . htmlentities($option) . '</option>'."\n";
		}
		$form .= '</select> '."\n";
		$form .= '</p>'."\n";
		return $form;
	}
}

function form_format_heure($defaut) {
	$formats = array (
		'0' => date('H\:i\:s'),		// 23:56:04
		'1' => date('H\:i'),			// 23:56
		'2' => date('h\:i\:s A'),		// 11:56:04 PM
		'3' => date('h\:i A'),			// 11:56 PM
	);
	$form = '<p>';
	$form .= '<label>'.$GLOBALS['lang']['pref_format_heure'].'</label>';
	$form .= '<select name="format_heure">' ."\n";
	foreach ($formats as $option => $label) {
		$form .= '<option value="'.htmlentities($option).'"';
		if ($defaut == $option) {
			$form .= ' selected="selected"';
		}
		$form .= '>' . htmlentities($label) . '</option>'."\n";
	}
	$form .= '</select> '."\n";
	$form .= '</p>'."\n";
	return $form;
}

function form_langue($defaut) {
	$form = '<p>';
	$form .= '<label>'.$GLOBALS['lang']['pref_langue'].'</label>';
	$form .= '<select name="langue">' ;
	foreach ($GLOBALS['langs'] as $option => $label) {
		$form .= '<option value="'.htmlentities($option).'"';
		if ($defaut == $option) {
			$form .= ' selected="selected"';
		}
		$form .= '>'.$label.'</option>';
	}
	$form .= '</select> ';
	$form .= '</p>';
	return $form;
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
	if ( $ouverture = opendir($chemin) ) { 
		while ($dossiers=readdir($ouverture) ) {
			if ( file_exists($chemin.'/'.$dossiers.'/list.html') ) {
				$themes[$dossiers] = $dossiers;
			}
		}
		closedir($ouverture);
	}
	if (isset($themes)) {
		return $themes;
	} else {
		return '';
	}
}


// formulaires ARTICLES //////////

function afficher_form_filtre($type, $filtre, $mode) {
	echo '<form method="get" action="'.$_SERVER['PHP_SELF'].'" >'."\n";
	echo '<div id="form-filtre">'."\n";
		filtre($type, $filtre, $mode);
	echo '</div>'."\n";
	echo '</form>'."\n";
}

function filtre($type, $filtre, $mode) {
	if ($type == 'articles') {
		$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'];
	} elseif ($type == 'commentaires') {
		$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];
	}

	$tableau = table_derniers($dossier, '-1', '', $mode);
	foreach ($tableau as $file ) $tableau_mois[substr($file, 0, 6)] = mois_en_lettres(str_pad(substr($file, 4, 2), 2, "0", STR_PAD_LEFT)).' '.substr($file, 0, 4); // array[201005] => "May 2010", uzw
	if (isset($tableau_mois)) {
		$dossier_mois = array_unique($tableau_mois);
	}
	echo '<label>'.$GLOBALS['lang']['label_afficher'].'</label>';
	echo "\n".'<select name="filtre">'."\n" ;
	if ($type == 'articles') {
		echo '<option value="">'.$GLOBALS['lang']['label_article_derniers'].'</option>'."\n";
	} elseif ($type == 'commentaires') {
		echo '<option value="">'.$GLOBALS['lang']['label_comment_derniers'].'</option>'."\n";
	}

	/// BROUILLONS
	echo '<option value="draft"';
	echo ($filtre == 'draft') ? ' selected="selected"' : '';
	echo '>'.$GLOBALS['lang']['label_brouillons'].'</option>'."\n";

	/// PUBLIES
	echo '<option value="pub"';
	echo ($filtre == 'pub') ? ' selected="selected"' : '';
	echo '>'.$GLOBALS['lang']['label_publies'].'</option>'."\n";

	/// PAR DATE
	if (isset($dossier_mois)) {
		krsort($dossier_mois);

		echo '<optgroup label="'.$GLOBALS['lang']['label_date'].'">';
		foreach ($dossier_mois as $mois => $label) {
			echo '<option value="' . htmlentities($mois) . '"';
			echo ($filtre == $mois) ? ' selected="selected"' : '';
			echo '>'.$label.'</option>'."\n";
		}
		echo '</optgroup>';
	}

	if ($type == 'commentaires') {
		echo '<optgroup label="'.'Auteur'.'">';
		$author_list = table_auteur($dossier, '', '', $mode);
		$author_list = array_count_values($author_list);
		arsort($author_list);
		foreach ($author_list as $nom => $nb) {
			if ($nom != '') {
				echo '<option value="'.$nom.'"';
				echo ($filtre == $nom) ? ' selected="selected"' : '';
				echo '>'.$nom.' ('.$nb.')'.'</option>'."\n";
			}
		}
		echo '</optgroup>';
	}
	echo '</select> '."\n\n";
	echo '<input type="submit" value="'.$GLOBALS['lang']['label_afficher'].'" />'."\n";
}

/// formulaires BILLET //////////
function afficher_form_billet($article, $erreurs) {
	if ($article != '') {
		$defaut_jour = $article['jour'];
		$defaut_mois = $article['mois'];
		$defaut_annee = $article['annee'];
		$defaut_heure = $article['heure'];
		$defaut_minutes = $article['minutes'];
		$defaut_secondes = $article['secondes'];
		$titredefaut = $article['titre'];
		$chapodefaut = $article['chapo'];
		$notesdefaut = (isset($article['notes'])) ? $article['notes'] : '';
		$categoriesdefaut = (isset($article['categories'])) ? $article['categories'] : '';
		$contenudefaut = htmlspecialchars($article['contenu_wiki']);
		$motsclesdefaut = $article['mots_cles'];
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
		$motsclesdefaut = '';
		$categoriesdefaut = '';
		$titredefaut = '';
		$notesdefaut = '';
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
		echo label('titre', $GLOBALS['lang']['label_titre']);
		echo '<input id="titre" name="titre" type="text" size="50" value="'.$titredefaut.'" required="" placeholder="'.$GLOBALS['lang']['label_titre'].'" tabindex="30"/>'."\n" ;
	echo '<div id="chapo_note">'."\n";
	echo '<div id="blocnote">'."\n";
		echo label('notes', 'Notes');
		echo '<textarea id="notes" name="notes" rows="5" cols="25" placeholder="Notes" tabindex="40">'.$notesdefaut.'</textarea>'."\n" ;
	echo '</div>'."\n";
	echo '<div id="blocchapo">'."\n";
		echo label('chapo', $GLOBALS['lang']['label_chapo']);
		echo '<textarea id="chapo" name="chapo" rows="5" cols="60" required="" placeholder="'.$GLOBALS['lang']['label_chapo'].'" tabindex="35">'.$chapodefaut.'</textarea>'."\n" ;
	echo '</div>'."\n";
	echo '<br style="clear:both;"/>'."\n".'</div>'."\n";

	if ($GLOBALS['activer_categories'] == '1') {
		echo label('categories', $GLOBALS['lang']['label_categories']);
		form_categories($categoriesdefaut) ;
	} else {
		echo hidden_input('categories', '');
	}
	echo label('contenu', $GLOBALS['lang']['label_contenu']);

	echo '<p style="margin-bottom:4px;" >';
	echo "\t".'<input class="pm" type="button" value="−" onclick="resize(\'contenu\', -40); return false;" />'."\n";
	echo "\t".'<input class="pm" type="button" value="+" onclick="resize(\'contenu\', 40); return false;" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-gras'].'" type="button" value="B" onclick="insertTag(\'[b]\',\'[/b]\',\'contenu\');" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-ital'].'" type="button" value="I" onclick="insertTag(\'[i]\',\'[/i]\',\'contenu\');" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-soul'].'" type="button" value="U" onclick="insertTag(\'[u]\',\'[/u]\',\'contenu\');" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-barr'].'" type="button" value="S" onclick="insertTag(\'[s]\',\'[/s]\',\'contenu\');" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-lien'].'" type="button" value="'.$GLOBALS['lang']['wiki_lien'].'" onclick="insertTag(\'[\',\'|http://]\',\'contenu\');"/>'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-cita'].'" type="button" value="'.$GLOBALS['lang']['wiki_quote'].'" onclick="insertTag(\'[quote]\',\'[/quote]\',\'contenu\');" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-imag'].'" type="button" value="'.$GLOBALS['lang']['wiki_image'].'" onclick="insertTag(\'((http://|\',\'))\',\'contenu\');" />'."\n";

	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-code'].'" type="button" value="Code" onclick="insertTag(\'[code]\',\'[/code]\',\'contenu\');" />'."\n";

	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-center'].'" type="button" value="Centrer" onclick="insertTag(\'[center]\',\'[/center]\',\'contenu\');" />'."\n";
	echo "\t".'<input title="'.$GLOBALS['lang']['bouton-droite'].'" type="button" value="Droite" onclick="insertTag(\'[right]\',\'[/right]\',\'contenu\');" />'."\n";
	echo '</p>';

	echo '<textarea id="contenu" name="contenu" rows="20" cols="60" required="" placeholder="'.$GLOBALS['lang']['label_contenu'].'"  tabindex="55">'.$contenudefaut.'</textarea>'."\n" ;

	if ($GLOBALS['automatic_keywords'] == '0') {
		echo label('mots_cles', $GLOBALS['lang']['label_motscles']);
		echo '<div><input id="mots_cles" name="mots_cles" type="text" size="50" value="'.$motsclesdefaut.'" placeholder="'.$GLOBALS['lang']['label_motscles'].'" tabindex="60"/></div>'."\n";
	}
	if ($statutdefaut == 0 or !$article) {
		echo '<div id="date">'."\n";
		echo '<p id="formdate">';
		form_annee($defaut_annee);
		form_mois($defaut_mois);
		form_jour($defaut_jour);
		echo '</p>'."\n";
		echo '<p id="formheure">';
		form_heure($defaut_heure, $defaut_minutes, $defaut_secondes) ;
		echo '</p>'."\n";
		echo '</div>'."\n";
	}
	else {
		echo '<div id="date">'."\n";
		echo '<p id="formdate">'.date_formate($article['id']).'</p>'."\n";
		echo '<p id="formheure">'.heure_formate($article['id']).'</p>'."\n";
		echo '</div>'."\n";
		echo hidden_input('annee', $article['annee']);
		echo hidden_input('mois', $article['mois']);
		echo hidden_input('jour', $article['jour']);
		echo hidden_input('heure', $article['heure']);
		echo hidden_input('minutes', $article['minutes']);
		echo hidden_input('secondes', $article['secondes']);
	}
	echo '<div id="opts">'."\n";
		form_statut($statutdefaut);
		form_allow_comment($allowcommentdefaut);
	echo '</div>'."\n";

	echo '<div id="bt">';
		echo '<input class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['enregistrer'].'" tabindex="65" />'."\n";
		if ($article) {
			echo input_supprimer();
			$time = time();
			$_SESSION['time_supprimer_article'] = $time;
			echo hidden_input('security_coin_article', md5($article['id'].$time));
			echo hidden_input('article_id', $article['id']);
		}
	echo '</div>';
	echo hidden_input('_verif_envoi', '1');
	echo '</div>'."\n";
	echo '</form>'."\n";
}
// FIN AFFICHER_FORM_BILLET

// ELEMENTS FORM ECRIRE //////////

function form_jour($jour_affiche) {
	$jours = array(
		"01" => '1',  "02" => '2',  "03" => '3',  "04" => '4',  "05" => '5',  "06" => '6',  "07" => '7',  "08" => '8',
		"09" => '9',  "10" => '10', "11" => '11', "12" => '12', "13" => '13', "14" => '14', "15" => '15', "16" => '16',
		"17" => '17', "18" => '18', "19" => '19', "20" => '20', "21" => '21', "22" => '22', "23" => '23', "24" => '24',
		"25" => '25', "26" => '26', "27" => '27', "28" => '28', "29" => '29', "30" => '30', "31" => '31'
	);
	echo '<select name="jour">'."\n";
	foreach ($jours as $option => $label) {
		echo '<option value="'.htmlentities($option).'"';
		echo ($jour_affiche == $option) ? ' selected="selected"' : '';
		echo '>'.htmlentities($label).'</option>'."\n";
	}
	echo '</select>'."\n\n";
}

function form_mois($mois_affiche) {
	$mois = array(
		"01" => $GLOBALS['lang']['janvier'],	"02" => $GLOBALS['lang']['fevrier'], 
		"03" => $GLOBALS['lang']['mars'],		"04" => $GLOBALS['lang']['avril'], 
		"05" => $GLOBALS['lang']['mai'],			"06" => $GLOBALS['lang']['juin'], 
		"07" => $GLOBALS['lang']['juillet'],	"08" => $GLOBALS['lang']['aout'],
		"09" => $GLOBALS['lang']['septembre'],	"10" => $GLOBALS['lang']['octobre'], 
		"11" => $GLOBALS['lang']['novembre'],	"12" => $GLOBALS['lang']['decembre']
	);
	echo '<select name="mois">'."\n" ;
	foreach ($mois as $option => $label) {
		echo '<option value="'.htmlentities($option).'"';
		echo ($mois_affiche == $option) ? ' selected="selected"' : '';
		echo '>'.$label.'</option>'."\n";
	}
	echo '</select>'."\n\n";
}

function form_annee($annee_affiche) {
	$annees = array();
	for ($annee = date('Y') -3, $annee_max = date('Y') +3; $annee <= $annee_max; $annee++) {
		$annees[$annee] = $annee;
	}
	echo '<select name="annee">'."\n" ;
	foreach ($annees as $option => $label) {
		echo '<option value="'.htmlentities($option).'"';
		echo ($annee_affiche == $option) ? ' selected="selected"' : '';
		echo '>'.htmlentities($label).'</option>'."\n";
	}
	echo '</select>'."\n\n";
}

function form_heure($heureaffiche, $minutesaffiche, $secondesaffiche) {
	echo '<input name="heure" type="text" size="2" maxlength="2" value="'.$heureaffiche.'" required="" /> :';
	echo '<input name="minutes" type="text" size="2" maxlength="2" value="'.$minutesaffiche.'" required="" /> :' ;
	echo '<input name="secondes" type="text" size="2" maxlength="2" value="'.$secondesaffiche.'" required="" />' ;
}

function form_statut($etat) {
	echo '<div id="formstatut">'."\n";
	$choix= array(
		'1' => $GLOBALS['lang']['label_publie'],
		'0' => $GLOBALS['lang']['label_brouillon']
	);
	echo form_select('statut', $choix, $etat, $GLOBALS['lang']['label_statut']);
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
	echo form_select('allowcomment', $choix, $etat, $GLOBALS['lang']['label_allowcomment']);
	echo '</div>'."\n";
}

function form_categories($categoriesaffiche) {
	if (!empty($GLOBALS['tags'])) {
		$script = '<script type="text/javascript">'."\n";
		$script .= 'function unfold(box, button) {'."\n";
		$script .='	var mbox = document.getElementById(box);'."\n";
		$script .='	if (mbox.style.display !== \'\') {'."\n";
		$script .='		mbox.style.display = \'\';'."\n";
		$script .='		button.value = \'Masquer la liste\';'."\n";
		$script .='	} else {'."\n";
		$script .='		mbox.style.display = \'none\';'."\n";
		$script .='		button.value = \'Afficher la liste des tags\';'."\n";
		$script .='	}'."\n";
		$script .='}'."\n";
		$script .='function insertCatTag(inputId, tag) {'."\n";
		$script .='	var field = document.getElementById(inputId);'."\n";
		$script .='	if (field.value !== \'\') {'."\n";
		$script .='		field.value += \', \';'."\n";
		$script .='	}'."\n";
		$script .='	field.value += tag;'."\n";
		$script .='}'."\n";
		$script .='</script>'."\n";
		$script .='<input onclick="unfold(\'masknshow\', this);" value="Afficher la liste des tags" type="button" id="showw" />'."\n";
		$script .='<p style="display: none;" id="masknshow">'."\n";
		echo $script;
		$tags = explode(',', $GLOBALS['tags']);
//		$tags = array_map("base64_decode", $tags);
		$tags = array_map("htmlspecialchars", $tags);
		$nb = sizeof($tags);
		for ($i = 0 ; $i < $nb ; $i++) {
			$tags[$i] = trim($tags[$i]);
			echo "\t".'<a class="tags" id="tag'.$i.'" onclick="insertCatTag(\'categories\', \''.addslashes($tags[$i]).'\');">'.$tags[$i]."</a>\n";
		}
		echo '</p>'."\n";
	}
	echo '<input id="categories" name="categories" type="text" size="50" value="'.htmlspecialchars($categoriesaffiche).'" placeholder="'.$GLOBALS['lang']['label_categories'].'" tabindex="45" />'."\n";
}

?>
