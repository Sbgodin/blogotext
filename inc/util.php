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

/// menu haut panneau admin /////////

function afficher_menu($active) {
	lien_nav('index.php', 'lien-liste', $GLOBALS['lang']['mesarticles'], $active);
	if ($GLOBALS['onglet_commentaires'] == 'on') {
		lien_nav('commentaires.php', 'lien-lscom', $GLOBALS['lang']['titre_commentaires'], $active);
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
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<select id="'.$id.'" name="'.$id.'">'."\n";
		foreach ($choix as $valeur => $mot) {
		$form .= '<option value="'.$valeur.'"';
			if ($defaut == $valeur) {
				$form .= ' selected="selected"';
			}
		$form .= '>'.$mot.'</option>'."\n";
		}
	$form .= '</select>';
	$form .= '</p>'."\n";
	return $form;
}

function form_text($id, $defaut, $label) {
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<input type="text" id="'.$id.'" name="'.$id.'" size="25" value="'.$defaut.'" />'."\n";
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

function form_password($id, $defaut, $label) {
	$form = '<p>'."\n";
	$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= '<input type="password" id="'.$id.'" name="'.$id.'" size="25" value="'.$defaut.'" />'."\n";
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
	$form = '<input class="submit-suppr" type="submit" name="supprimer" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_article'].'\')" />'."\n";
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

/// DECODAGES //////////

function get_ext($file) {
	$retour= substr($file, -3, 3);
	return $retour;
}

function get_id($file) {
	$retour= substr($file, 0, 14);
	return $retour;
}

/*
Function no more used.
Instead use : 
parse_xml($dossier.$fichier, $GLOBALS['data_syntax']['article_status'])


function get_statut($file) {
	$retour= parse_xml($file, $GLOBALS['data_syntax']['article_status']);
		return $retour;
}
*/

function decode_id($id) {
	$retour=array(
		'annee' => substr($id, 0, 4),
		'mois' => substr($id, 4, 2),
		'jour' => substr($id, 6, 2),
		'heure' => substr($id, 8, 2),
		'minutes' => substr($id, 10, 2),
		'secondes' => substr($id, 12, 2)
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

/*
FUNCTION NO MORE USED.
Instead, use :
parse_xml($GLOBALS['dossier_data_articles']."/".get_path($id), 'bt_title')

function get_titre($id) {
	$titre = parse_xml($GLOBALS['dossier_data_articles']."/".get_path($id), 'bt_title');
	return $titre;
}

*/

function ww_hach_sha($text) {
	if ($GLOBALS['version_PHP'] >= '5') {
		$out = hash("sha512", $text);		// PHP 5
	} else {
		$out = sha1($text).md5($text);	// PHP 4
	}
	return $out;
}

function article_anchor($id) {
	$anchor = 'id'.substr(md5($id), 0, 6);
	return $anchor;
}

function traiter_tags($tags) {
	$tags_array = explode(',' , trim($tags, ','));
	$nb = sizeof($tags_array);
	for ($i = 0 ; $i < $nb ; $i ++) {
		$tags_array[$i] = trim($tags_array[$i]);
	}
	$tags_array = array_unique($tags_array);
	sort($tags_array);
	$str_tags = implode(', ' , $tags_array);
	return $str_tags;
}

function check_session() {
	session_start();
    $ip = $_SERVER["REMOTE_ADDR"];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip .= '_'.$_SERVER['HTTP_X_FORWARDED_FOR']; }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip .= '_'.$_SERVER['HTTP_CLIENT_IP']; }

	if ((!isset($_SESSION['nom_utilisateur']))
		or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp'])
		or (!isset($_SESSION['antivol']))
		or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip))
		or (!isset($_SESSION['timestamp']))
		or ($_SESSION['timestamp'] < time()-1800)) {
			fermer_session();
			exit;
		}
	$_SESSION['timestamp'] = time();
}

function fermer_session() {
	unset($_SESSION['nom_utilisateur'],$_SESSION['antivol'],$_SESSION['timestamp']);
	session_destroy();
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		if ($GLOBALS['version_PHP'] >= '5') {
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);	// PHP >=5
		} else {
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"]);								// PHP < 5
		}
	}
	header('Location: auth.php');
	exit();
// see here for some other tips : http://sebsauvage.net/wiki/doku.php?id=php:session
/*
	- is the 'session_destroy()' here above really needed ?

*/
}

function diacritique($texte, $majuscules, $espaces) {
	$texte = strip_tags($texte);
	if ($majuscules == '0')
		$texte = strtolower($texte);
	$texte = html_entity_decode($texte, ENT_QUOTES, 'UTF-8'); // &eacute => é ; é => é ; (uniformise)
	$texte = htmlentities($texte, ENT_QUOTES, 'UTF-8'); // é => &eacute;
	$texte = preg_replace('#&(.)(acute|grave|circ|uml|cedil|tilde|ring|slash|caron);#', '$1', $texte); // &eacute => e
	$texte = preg_replace('#&([a-z]{2})lig;#i', '$1', $texte); // EX : œ => oe ; æ => ae 
	$texte = preg_replace('#&[\w\#]*;#U', '', $texte); // les autres (&quote; par exemple) sont virés
	$texte = preg_replace('#[^\w -]#U', '', $texte); // on ne garde que chiffres, lettres _ et - et espaces.
	if ($espaces == '0')
		$texte = preg_replace('#[ ]+#', '-', $texte); // les espaces deviennent des tirets.
	return $texte;
}

?>
