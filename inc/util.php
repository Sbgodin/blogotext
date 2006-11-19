<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
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

/// formulaires GENERIQUES //////////

function form_select($id, $choix, $defaut, $label) {
	print '<p>'."\n";
	print '<label for="'.$id.'">'.$label.'</label>'."\n";
	print '<select id="'.$id.'" name="'.$id.'">'."\n";
		foreach ($choix as $valeur => $mot) {
		print '<option value="'.$valeur.'"';
			if ($defaut == $valeur) {
				print ' selected="selected"';
			}
		print '>'.$mot.'</option>'."\n";
		}
	print '</select>';
	print '</p>'."\n";
}

function form_text($id, $defaut, $label) {
	print '<p>'."\n";
	print '<label for="'.$id.'">'.$label.'</label>'."\n";
	print '<input type="text" id="'.$id.'" name="'.$id.'" size="25" value="'.$defaut.'" />'."\n";
	print '</p>'."\n";
}

function form_password($id, $defaut, $label) {
	print '<p>'."\n";
	print '<label for="'.$id.'">'.$label.'</label>'."\n";
	print '<input type="password" id="'.$id.'" name="'.$id.'" size="25" value="'.$defaut.'" />'."\n";
	print '</p>'."\n";
}


function textarea($id, $defaut, $label, $cols, $rows) {
	print '<p>'."\n";
	print '<label for="'.$id.'">'.$label.'</label>'."\n";
	print '<textarea id="'.$id.'" name="'.$id.'" cols="'.$cols.'" rows="'.$rows.'">'.$defaut.'</textarea>'."\n";
	print '</p>'."\n";
}

function input_supprimer() {
	print '<input class="submit-suppr" type="submit" name="supprimer" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_article'].'\')" />'."\n";
}

function input_enregistrer() {
	print '<input accesskey="s" class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['enregistrer'].'" />'."\n";
}

function hidden_input($nom, $valeur) {
	print '<input type="hidden" class="nodisplay" name="'.$nom.'" value="'.$valeur.'" />'."\n";
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
	$version = parse_xml($file, 'bt_version');
	if ($version == '') {
	$syntax_version = '0';
} elseif ($version== '0.9.3') {
	$syntax_version = '1';
}
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

?>