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

function extraire_mots($texte) {
	$txt = preg_replace("/(\r\n|\n|\r|\s|\t)/", " ", $texte);
	$texte_propre = preg_replace('#[[:punct:]]#i', ' ', $txt);
	$tableau= explode(' ', $texte_propre);
	foreach ($tableau as $mots) {
		if (strlen($mots) >= 3) {
			$table[] = strtolower($mots);
			$tableau = array_unique($table);
			natcasesort($tableau);
		}
	}
	$retour = implode(', ',$tableau);
	return $retour;
}

function titre_url($url) {
	$url = diacritique($url, 0, 0);
	$url = trim($url, '-');
	return $url;
}

function protect_markup($text) {
	$patterns = array(
		'`<bt_(.*?)>`',
		'`</bt_(.*?)>`'
	);
	$result = preg_replace($patterns, '', $text);
	return $result;
}

function formatage_wiki($texte) {
	$texte = preg_replace("/(\r\n|\r\n\r|\n|\r)/", "\r", "\r".$texte."\r"); 
	$tofind= array(
		'`<(.*?)>\r+`',													// html
		'`(\s|p>)((https?|ftps?)://(www\.)?\S+)(\s)?`i',	// Regex URL
		'`@@(.*?)@@`',													// code
		'`\r!!!!!(.*?)\r+`',											// h5
		'`\r!!!!(.*?)\r+`',											// h4
		'`\r!!!(.*?)\r+`',											// h3
		'`\r!!(.*?)\r+`',												// h2
		'`\r!(.*?)\r+`',												// h1
		'`\(\((.*?)\|(.*?)\|(.*?)\|(.*?)\)\)`',				// img
		'`\[\(\(([^[]+)\|([^[]+)\)\)\|([^[]+)\]`',			// img + a href
		'`\r?(.*?)\r\r+`',											// p (laisse une interligne)
		'`(.*?)\r`',													// br : retour à la ligne sans saut de ligne
		'`\[([^[]+)\|([^[]+)\]`',									// a href
		'`\[(https?://)([^[]+)\]`',								// url
		'`\_\_(.*?)\_\_`',											// strong
		'`##(.*?)##`',													// italic
		'`--(.*?)--`',													// strike
		'`\+\+(.*?)\+\+`',											// ins
		'`%%`',															// br
		'`\[quote\](.*?)\[/quote\]`s',							// citation
		'` »`',															// close quote
		'`« `', 															// open quote
		'` !`',															// !
		'` :`',															// :
		'`\n?<p></p>\n?`',											// vide
	);
	$toreplace= array(
		'<$1>'."\n",															// html
		'$1<a href="$2">$2</a>$5',											// url regex
		'<code><pre>$1</pre></code>',										// code
		'<h5>$1</h5>'."\n",													// h5
		'<h4>$1</h4>'."\n",													// h4
		'<h3>$1</h3>'."\n",													// h3
		'<h2>$1</h2>'."\n",													// h2
		'<h1>$1</h1>'."\n",													// h1
		'<img src="$1" alt="$2" class="$3" title="$4" />',			// img
		'<a href="$3"><img src="$1" alt="$2" /></a>',				// img + a href
		"\n".'<p>$1</p>'."\n",												// p (laisse une interligne)
		'$1<br/>'."\n",														// br : retour à la ligne sans saut de ligne
		'<a href="$2">$1</a>',												// a href
		'<a href="$1$2">$2</a>',											// url
		'<span style="font-weight: bold;">$1</span>',				// strong
		'<span style="font-style: italic;">$1</span>',				// italic
		'<span style="text-decoration: line-through;">$1</span>',// barre
		'<span style="text-decoration: underline;">$1</span>',	// souligne
		'<br />',																// br
		'<q>$1</q>',															// citation
		'&nbsp;»',
		'«&nbsp;',
		'&nbsp;!',
		'&nbsp;:',
		'',																		// vide
	);
	$texte_formate = stripslashes(preg_replace($tofind, $toreplace, $texte));
	return $texte_formate;
}

function formatage_commentaires($texte) {
	$texte = " ".$texte;
	$tofindc = array(
		'#\[quote\](.+?)\[/quote\]#s',									// citation
		'#\[code\](.+?)\[/code\]#s',										// citation
		'# »#',																	// close quote
		'#« #', 																	// open quote
		'# !#',																	// !
		'# :#',																	// :
		'`(\s)((https?|ftps?)://(www\.)?\S*)(\s)?`i',				// Regex URL
		'`\[([^[]+)\|([^[]+)\]`',											// a href
		'`\[b\](.*?)\[/b\]`s',												// strong
		'`\[i\](.*?)\[/i\]`s',												// italic
		'`\[s\](.*?)\[/s\]`s',												// strike
		'`\[u\](.*?)\[/u\]`s',												// souligne
	);
	$toreplacec = array(
		'</p>'."\n".'<blockquote>$1</blockquote>'."\n".'<p>',		// citation (</p> and <p> needed for W3C)
		'<code>$1</code>',													// code
		'&nbsp;»',																// close quote
		'«&nbsp;',																// open quote
		'&nbsp;!',																// !
		'&nbsp;:',																// :
		'$1<a href="$2">$2</a>$5',											// url
		'<a href="$2">$1</a>',												// a href
		'<span style="font-weight: bold;">$1</span>',				// strong
		'<span style="font-style: italic;">$1</span>',				// italic
		'<span style="text-decoration: line-through;">$1</span>',// barre
		'<span style="text-decoration: underline;">$1</span>',	// souligne
	);

	$toreplaceArrayLength = sizeof($tofindc);
	for ($i=0; $i < $toreplaceArrayLength; $i++) {
		$texte2 = preg_replace($tofindc["$i"], $toreplacec["$i"], $texte);
		$texte = $texte2;
	}
	$texte = '<p>'.trim(nl2br(stripslashes($texte))).'</p>';
	return $texte;
}


function date_formate($id) {
	$retour ='';
	$date= decode_id($id);
		$time_article = mktime(0, 0, 0, $date['mois'], $date['jour'], $date['annee']);
		$auj = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$hier = mktime(0, 0, 0, date('m'), date('d')-'1', date('Y'));
	if ( $time_article == $auj ) {
		$retour = $GLOBALS['lang']['aujourdhui'];
	} elseif ( $time_article == $hier ) {
		$retour = $GLOBALS['lang']['hier'];
	} else {
		$jour_l = jour_en_lettres($date['jour'], $date['mois'], $date['annee']);
		$mois_l = mois_en_lettres($date['mois']);
			$format = array (
				'0' => $date['jour'].'/'.$date['mois'].'/'.$date['annee'],                                                        // 14/01/1983
				'1' => $date['mois'].'/'.$date['jour'].'/'.$date['annee'],                                                        // 01/14/1983
				'2' => $date['jour'].' '.$mois_l.' '.$date['annee'],                                                              // 14 janvier 1983
				'3' => $jour_l.' '.$date['jour'].' '.$mois_l.' '.$date['annee'],                                                  // vendredi 14 janvier 1983
				'4' => $mois_l.' '.$date['jour'].', '.$date['annee'],                                                             // janvier 14, 1983
				'5' => $jour_l.', '.$mois_l.' '.$date['jour'].', '.$date['annee'],                                                // vendredi, janvier 14, 1983
				'6' => $date['annee'].'-'.$date['mois'].'-'.$date['jour'],                                                        // 1983-01-14
//				'7' => mktime($date['heure'], $date['minutes'], $date['secondes'], $date['mois'], $date['jour'], $date['annee']), // (timestamp)
			);

	$retour = $format[$GLOBALS['format_date']];
	}
	return ucfirst($retour);
}

function heure_formate($id) {
	$date = decode_id($id);
	$ts = mktime($date['heure'], $date['minutes'], $date['secondes'], $date['mois'], $date['jour'], $date['annee']); // ts : timestamp
	$format = array (
		'0' => date('H',$ts).':'.date('i',$ts).':'.date('s',$ts),							// 23:56:04
		'1' => date('H',$ts).':'.date('i',$ts),													// 23:56
		'2' => date('h',$ts).':'.date('i',$ts).':'.date('s',$ts).' '.date('A',$ts),	// 11:56:04 PM
		'3' => date('h',$ts).':'.date('i',$ts).' '.date('A',$ts),							// 11:56 PM
	);
	$valeur = $format[$GLOBALS['format_heure']];
	return $valeur;
}

function jour_en_lettres($jour, $mois, $annee) {
	$date = date('w', mktime(0, 0, 0, $mois, $jour, $annee));
	switch($date) {
		case '0': $nom = $GLOBALS['lang']['dimanche']; break;
		case '1': $nom = $GLOBALS['lang']['lundi']; break;
		case '2': $nom = $GLOBALS['lang']['mardi']; break;
		case '3': $nom = $GLOBALS['lang']['mercredi']; break;
		case '4': $nom = $GLOBALS['lang']['jeudi']; break;
		case '5': $nom = $GLOBALS['lang']['vendredi']; break;
		case '6': $nom = $GLOBALS['lang']['samedi']; break;
		default: $nom = "(?jour?)"; break;
	}
	return $nom;
}

function mois_en_lettres($numero) {
	switch($numero) {
		case '01': $nom = $GLOBALS['lang']['janvier']; break;
		case '02': $nom = $GLOBALS['lang']['fevrier']; break;
		case '03': $nom = $GLOBALS['lang']['mars']; break;
		case '04': $nom = $GLOBALS['lang']['avril']; break;
		case '05': $nom = $GLOBALS['lang']['mai']; break;
		case '06': $nom = $GLOBALS['lang']['juin']; break;
		case '07': $nom = $GLOBALS['lang']['juillet']; break;
		case '08': $nom = $GLOBALS['lang']['aout']; break;
		case '09': $nom = $GLOBALS['lang']['septembre']; break;
		case '10': $nom = $GLOBALS['lang']['octobre']; break;
		case '11': $nom = $GLOBALS['lang']['novembre']; break;
		case '12': $nom = $GLOBALS['lang']['decembre']; break;
		default: $nom = "(?mois?)"; break;
	}
	return $nom;
}

function nombre_formate($chaine) {
	if (strlen($chaine) == '1') {
		$retour = '0'.$chaine;
	} else {
		$retour = $chaine;
	}
	return $retour;
}

function nombre_articles($nb) {
	if ($nb == '0') {
		$retour = $GLOBALS['lang']['aucun'].' '.$GLOBALS['lang']['label_article'];
	} elseif ($nb == '1') {
		$retour = $nb.' '.$GLOBALS['lang']['label_article'];
	} elseif ($nb > '1') {
		$retour = $nb.' '.$GLOBALS['lang']['label_articles'];
	}
	return $retour;
}

function nombre_commentaires($nb) {
	if ($nb == '0') {
		$retour = $GLOBALS['lang']['note_no_comment'];
	} elseif ($nb == '1') {
		$retour = $nb.' '.$GLOBALS['lang']['label_commentaire'];
	} elseif ($nb > '1') {
		$retour = $nb.' '.$GLOBALS['lang']['label_commentaires'];
	}
	return $retour;
}

?>
