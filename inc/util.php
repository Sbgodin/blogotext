<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function redirection($url) {
		header('Location: '.$url);
}

function clean_txt($text) {
if (!get_magic_quotes_gpc()) {
   $return = trim(addslashes($text));
} else {
   $return = trim($text);
}
return $return;
}

/// menu panneau admin /////////

function afficher_menu($active) {
	lien_nav('index.php', 'lien-liste', $GLOBALS['lang']['mesarticles'], $active);
	if ($GLOBALS['onglet_commentaires'] == 'on') {
		lien_nav('lastcom.php', 'lien-lscom', $GLOBALS['lang']['titre_commentaires'], $active);
	}
	lien_nav('ecrire.php', 'lien-nouveau', $GLOBALS['lang']['nouveau'], $active);
	lien_nav('preferences.php', 'lien-preferences', $GLOBALS['lang']['preferences'], $active);
	lien_nav($GLOBALS['racine'], 'lien-site', $GLOBALS['lang']['lien_blog'], $active);
	if ($GLOBALS['onglet_images'] == 'on') {
		lien_nav('image.php', 'lien-image', $GLOBALS['lang']['nouvelle_image'], $active);
	}
	lien_nav('logout.php', 'lien-deconnexion', $GLOBALS['lang']['deconnexion'], $active);
}

/// formulaires GENERIQUES //////////

function form_select($id, $choix, $defaut, $label) {
	echo '<p>'."\n";
	echo '<label for="'.$id.'">'.$label.'</label>'."\n";
	echo '<select id="'.$id.'" name="'.$id.'">'."\n";
		foreach ($choix as $valeur => $mot) {
		echo '<option value="'.$valeur.'"';
			if ($defaut == $valeur) {
				echo ' selected="selected"';
			}
		echo '>'.$mot.'</option>'."\n";
		}
	echo '</select>';
	echo '</p>'."\n";
}

function form_text($id, $defaut, $label) {
	echo '<p>'."\n";
	echo '<label for="'.$id.'">'.$label.'</label>'."\n";
	echo '<input type="text" id="'.$id.'" name="'.$id.'" size="25" value="'.$defaut.'" />'."\n";
	echo '</p>'."\n";
}

function form_check($id, $defaut, $label) {
	$checked = ($defaut == 'on') ? 'checked="checked" ' : "";

	echo '<p>'."\n";
	echo '<label for="'.$id.'">'.$label.'</label>'."\n";
	echo '<input type="checkbox" id="'.$id.'" name="'.$id.'" '.$checked.'/>'."\n";
	echo '</p>'."\n";
}

function form_radio($name, $id, $value, $label) {
	echo '<p>'."\n";
	echo '<label for="'.$id.'">'.$label.'</label>'."\n";
	echo '<input type="radio" name="'.$name.'" value="'.$value.'" id="'.$id.'" />'."\n";
	echo '</p>'."\n";
}

function form_password($id, $defaut, $label) {
	echo '<p>'."\n";
	echo '<label for="'.$id.'">'.$label.'</label>'."\n";
	echo '<input type="password" id="'.$id.'" name="'.$id.'" size="25" value="'.$defaut.'" />'."\n";
	echo '</p>'."\n";
}


function textarea($id, $defaut, $label, $cols, $rows) {
	echo '<p>'."\n";
	echo '<label for="'.$id.'">'.$label.'</label>'."\n";
	echo '<textarea id="'.$id.'" name="'.$id.'" cols="'.$cols.'" rows="'.$rows.'">'.$defaut.'</textarea>'."\n";
	echo '</p>'."\n";
}

function input_supprimer() {
	echo '<input class="submit-suppr" type="submit" name="supprimer" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_article'].'\')" />'."\n";
}

function input_supprimer_comment() {
	echo '<input class="submit-suppr-comm" type="submit" name="supprimer_comm" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_comment'].'\')" />'."\n";
}

function input_enregistrer() {
	echo '<input accesskey="s" class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['enregistrer'].'" />'."\n";
}

function input_valider() {
	echo '<input accesskey="s" class="submit" type="submit" name="valider" value="'.$GLOBALS['lang']['valider'].'" />'."\n";
}

function input_upload() {
	echo '<input accesskey="s" class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['img_upload'].'" />'."\n";
}

function hidden_input($nom, $valeur) {
	echo '<input type="hidden" class="nodisplay" name="'.$nom.'" value="'.$valeur.'" />'."\n";
}

/// DECODAGES //////////

function get_ext($file) {
	$retour= substr($file, '-3', '3');
		return $retour;
}

function get_id($file) {
	$retour= substr($file, '0', '14');
		return $retour;
}

function get_version($file) {
/*	$version = parse_xml($file, 'bt_version');
	if ($version == '') {
	$syntax_version = '0';
} elseif ($version== '0.9.3') {*/
	$syntax_version = '1';
//}
return $syntax_version;
}

function get_statut($file) {
	$syntax_version =  get_version($file);
	$retour= parse_xml($file, $GLOBALS['data_syntax']['article_status'][$syntax_version]);
		return $retour;
}

function decode_id($id) {
	$retour=array(
		'annee' => substr($id, '0', '4'),
		'mois' => substr($id, '4', '2'),
		'jour' => substr($id, '6', '2'),
		'heure' => substr($id, '8', '2'),
		'minutes' => substr($id, '10', '2'),
		'secondes' => substr($id, '12', '2')
		);
		return $retour;
}

function url($niveau) {
	if ($dec=explode('/', $_SERVER['QUERY_STRING'])) {
	return $dec[$niveau];
	}
}

function get_path_no_ext($id) {
	$dec=decode_id($id);
	$retour = $dec['annee'].'/'.$dec['mois'].'/'.$id;
	return $retour;
}

function get_path($id) {
	$dec=decode_id($id);
	$retour = $dec['annee'].'/'.$dec['mois'].'/'.$id.'.'.$GLOBALS['ext_data'];
	return $retour;
}

function get_comment_path($article_id, $comment_id) {
	$dec=decode_id($article_id);
	$path=$GLOBALS['dossier_data_articles'].'/'.$dec['annee'].'/'.$dec['mois'].'/'.$article_id.'/'.$comment_id.'.'.$GLOBALS['ext_data'];
	return $path;
}

function remove_ext($file) {
	$retour=substr($file, '0', '-4');
	return $retour;
}

function get_blogpath($id) {
	$date= decode_id($id);
	$path= $GLOBALS['racine'].'index.php?'.$date['annee'].'/'.$date['mois'].'/'.$date['jour'].'/'.$date['heure'].'/'.$date['minutes'].'/'.$date['secondes'].'-'.titre_url(parse_xml($GLOBALS['dossier_data_articles'].'/'.get_path($id), 'titre'));
	return $path;
}

function get_blogpath_from_blog($id) {
	$date= decode_id($id);
	$path= $GLOBALS['racine'].'index.php?'.$date['annee'].'/'.$date['mois'].'/'.$date['jour'].'/'.$date['heure'].'/'.$date['minutes'].'/'.$date['secondes'].'-'.titre_url(parse_xml($GLOBALS['dossier_articles'].'/'.get_path($id), 'titre'));
	return $path;
}

function get_titre($id) {
	$titre = parse_xml($GLOBALS['dossier_data_articles']."/".get_path($id), 'bt_title');
	return $titre;
}

function ww_hach_sha($text) {
	$out = hash("sha512", $text);
	return $out;
}

function article_anchor($id) {
	$anchor = substr(md5($id), '0', '6');
	return $anchor;
}

?>
