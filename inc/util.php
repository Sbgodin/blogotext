<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2011 Timo Van Neerden <timovneerden@gmail.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
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

/// DECODAGES //////////

// function get_ext($file) REPLACED WITH "pathinfo($file, PATHINFO_EXTENSION);"

function get_id($file) {
	$retour= substr($file, 0, 14);
	return $retour;
}

function decode_id($id) {
	$retour = array(
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
	if ($dec = explode('/', $_SERVER['QUERY_STRING'])) {
		return $dec[$niveau];
	}
}

function get_path_no_ext($id) {
	$dec = decode_id($id);
	$retour = $dec['annee'].'/'.$dec['mois'].'/'.$id;
	return $retour;
}

function get_path($id) {
	$dec = decode_id($id);
	$retour = $dec['annee'].'/'.$dec['mois'].'/'.$id.'.'.$GLOBALS['ext_data'];
	return $retour;
}

function get_blogpath($id) {
	$date = decode_id($id);
	$path = $GLOBALS['racine'].'index.php?'.$date['annee'].'/'.$date['mois'].'/'.$date['jour'].'/'.$date['heure'].'/'.$date['minutes'].'/'.$date['secondes'].'-'.titre_url(parse_xml($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($id), 'titre'));
	return $path;
}

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

	if ((!isset($_SESSION['nom_utilisateur']))
		or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp'])
		or (!isset($_SESSION['antivol']))
		or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip))
		or (!isset($_SESSION['timestamp']))
		or ($_SESSION['timestamp'] < time()-$GLOBALS['session_admin_time'])) { // session older than 30min
			return FALSE;
	} else {
		return TRUE;
	}
}

function operate_session() {
	if (check_session() === FALSE) {
		fermer_session();
	} else {
		$_SESSION['timestamp'] = time();
	}
}

function fermer_session() {
	unset($_SESSION['nom_utilisateur'],$_SESSION['antivol'],$_SESSION['timestamp']);
	$_SESSION = array();
	session_destroy();
	redirection('auth.php'); // cookies are destroyed in auth.php
	exit();
}

function diacritique($texte, $majuscules, $espaces) {
	$texte = strip_tags($texte);
	if ($majuscules == '0')
		$texte = strtolower($texte);
	$texte = html_entity_decode($texte, ENT_QUOTES, 'UTF-8'); // &eacute => é ; é => é ; (uniformise)
	$texte = htmlentities($texte, ENT_QUOTES, 'UTF-8'); // é => &eacute;
	$texte = preg_replace('#&(.)(acute|grave|circ|uml|cedil|tilde|ring|slash|caron);#', '$1', $texte); // &eacute => e
	$texte = preg_replace('#(\t|\n|\r)#', ' ' , $texte); // retours à la ligne => espaces
	$texte = preg_replace('#&([a-z]{2})lig;#i', '$1', $texte); // EX : œ => oe ; æ => ae 
	$texte = preg_replace('#&[\w\#]*;#U', '', $texte); // les autres (&quote; par exemple) sont virés
	$texte = preg_replace('#[^\w -]#U', '', $texte); // on ne garde que chiffres, lettres _ et - et espaces.
	if ($espaces == '0')
		$texte = preg_replace('#[ ]+#', '-', $texte); // les espaces deviennent des tirets.
	return $texte;
}

function rel2abs($article) { // convertit les URL relatives en absolues
	$article = str_replace(' src="/', ' src="http://'.$_SERVER['HTTP_HOST'].'/' , $article);
	$article = str_replace(' href="/', ' href="http://'.$_SERVER['HTTP_HOST'].'/' , $article);
	$base = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	$article = preg_replace('/(src|href)=\"(?!http)/i','src="'.$base.'/',$article);
	return $article;
}

?>
