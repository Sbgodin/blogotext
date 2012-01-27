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
	$retour = substr($file, 0, 14);
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

function get_path($id) {
	$dec = decode_id($id);
	$retour = $dec['annee'].'/'.$dec['mois'].'/'.$id.'.'.$GLOBALS['ext_data'];
	return $retour;
}

function get_blogpath($id, $domain='') {
	$date = decode_id($id);
	$dom = ($domain == 1) ? $GLOBALS['racine'].'index.php?' : '';
	$path = $dom.$date['annee'].'/'.$date['mois'].'/'.$date['jour'].'/'.$date['heure'].'/'.$date['minutes'].'/'.$date['secondes'].'-';

	return $path;
}

function ww_hach_sha($text, $salt) {
	$out = hash("sha512", $text.$salt);		// PHP 5
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
	$ip = htmlspecialchars($_SERVER["REMOTE_ADDR"]);

	if ((!isset($_SESSION['nom_utilisateur']))
		or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp'])
		or (!isset($_SESSION['antivol']))
		or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip))
		or (!isset($_SESSION['timestamp']))
		or ($_SESSION['timestamp'] < time()-$GLOBALS['session_admin_time'])) {
			return FALSE;
	} else {
		return TRUE;
	}
}

function operate_session() {
	if (check_session() === FALSE) {
		fermer_session();
	} else {
		$_SESSION['prev_ses_id'] = sha1(session_id());
	//	session_regenerate_id(); // seems not to work everywhere
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
	$texte = preg_replace('#[^\w -]#U', '', $texte); // on ne garde que chiffres, lettres _, -, et espaces.
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

// A partir d'un commentaire posté, détermine les emails
// à qui envoyer la notification de nouveau commentaire.
function send_emails($id_comment) {
	$com_directory = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];
	$art_directory = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'];

	$article = parse_xml($com_directory.'/'.get_path($id_comment), $GLOBALS['data_syntax']['comment_article_id']);
	$article_title = parse_xml($art_directory.'/'.get_path($article), $GLOBALS['data_syntax']['article_title']);
	$comm_author = parse_xml($com_directory.'/'.get_path($id_comment), $GLOBALS['data_syntax']['comment_author']);

	$liste_commentaires = liste_commentaires($com_directory, $article, 1);

	sort($liste_commentaires);
	$emails = array();

	/* Récupérrer les emails des commentateurs
	 * le visiteur s'abonne ou se désabonne aux commentaires mais c'est le dernier choix qui est compté.
	 * Pour se désabonner, il faut donc poster un commentaire en désactivant l'abonnement.
	 */
	foreach ($liste_commentaires as $comment) {
		if (get_id($comment) == $id_comment) { // n'envoie pas l'email à soi même pour le commentaire tout juste posté.
			$email = parse_xml($com_directory.'/'.get_path(get_id($comment)), $GLOBALS['data_syntax']['comment_email']);
			$subscr = parse_xml($com_directory.'/'.get_path(get_id($comment)), $GLOBALS['data_syntax']['comment_subscribe']);
			if (!empty($email)) {
				$emails[$email] = $subscr.'-'.get_id($comment);
			}
		}
	}


	$subject = 'New comment on "'.$article_title.'" - '.$GLOBALS['nom_du_site'];

	$headers  = 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset="UTF-8"'."\r\n";
//	$headers  = 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=UTF-8'."\r\n";   
//	$headers  = 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n";

	$headers .= 'From: '.$GLOBALS['email']."\r\n".'X-Mailer: BlogoText - PHP/'.phpversion();


// for debug
// header('Content-type: text/html; charset=UTF-8');
// die(($to. $subject. $message. $headers));
//	echo '<pre>';die(print_r($emails));


// envoyer les emails une fois qu'on a récupéré la liste de tous les commentateurs à qui l'envoyer.
	foreach ($emails as $mail => $is_subscriben) {
		if ($is_subscriben[0] == '1') { // $is_subscribtion is seen as a array of chars here (like in C language).
			$comment = substr($is_subscriben, -14);
			$unsublink = get_blogpath($article, 1).'&amp;unsub=1&amp;article='.$comment.'&amp;mail='.sha1($mail);


			$message = '<html>';
			$message .= '<head><title>'.$subject.'</title></head>';
			$message .= '<body><p>A new comment by <b>'.$comm_author.'</b> has been posted on <b>'.$article_title.'</b> form '.$GLOBALS['nom_du_site'].'.<br/>';
			$message .= 'You can see it by following <a href="'.get_blogpath($article, 1).'">this link</a>.</p>';
			$message .= '<p>To unsubscribe from the comments on that post, you can follow this link: <a href="'.$unsublink.'">'.$unsublink.'</a>.</p>';
//			$message .= '<p>To unsubscribe from the comments on all the posts, follow this link: <a href="'.$unsublink.'&amp;all=1">'.$unsublink.'&amp;all=1</a>.</p>';
			$message .= '<p>Also, do not reply to this email, since it is an automatic generated email.</p><p>Regards.</p></body>';
			$message .= '</html>';


//	$message = utf8_decode($message); // since iso-8859-1 is used in emails…
//	$subject = utf8_decode($subject); // since iso-8859-1 is used in emails…

			mail($mail, $subject, $message, $headers);

		}
	}
	return true;
}


function unsubscribe($file_id, $email_sha) {
	$com_directory = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];
	$comment = init_comment('public', $file_id);
	if ( ($email_sha == sha1($comment['email'])) and ($comment['subscribe'] == 1) ) {
		// mettre à jour le fichier avec un "subscribe" à 0;
		$bal = $GLOBALS['data_syntax']['comment_subscribe'];

		$fichier_data = $com_directory.'/'.get_path($comment['id']);
		$contenu = file_get_contents($fichier_data);
		$contenu = preg_replace('#<'.$bal.'>1</'.$bal.'>#', '<'.$bal.'>0</'.$bal.'>', $contenu);

		$new_file_data = fopen($fichier_data,'wb+');
		if (fwrite($new_file_data, $contenu) === FALSE) {
			return FALSE;
		} else {
			fclose($new_file_data);
			return TRUE;
		}
	}
}



?>
