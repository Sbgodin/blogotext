<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function extraire_mots($texte) {
$txt = preg_replace("/(\r\n|\n|\r|\s|\t)/", " ", $texte);
$texte_propre = preg_replace('#[[:punct:]]#i', ' ', $txt);
$tableau= explode(' ', $texte_propre);
foreach ($tableau as $mots) {
	if (strlen($mots) >= '3') {
		$table[] = strtolower($mots);
		$tableau = array_unique($table);
		natcasesort($tableau);
	}
}
$retour = implode(', ',$tableau);
return $retour;
}

function titre_url($texte) {
$texte = utf8_decode($texte);
$tofind = utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ');
$replac = utf8_decode('AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');
$texte_pre_pre_pre = trim(strtolower(strtr($texte,$tofind,$replac)));
$texte_pre_pre = preg_replace('#[^a-zA-Z0-9_]#i', '-', $texte_pre_pre_pre);
$texte_pre = preg_replace('#-+#', '-', $texte_pre_pre);
$texte_final = substr($texte_pre, '0', '128');
return $texte_final;
}

function protect_markup($text) {
$patterns = array(
		'`<bt_(.*?)>`', '`</bt_(.*?)>`'
	);
$result = preg_replace($patterns, '', $text);
return $result;
}

function formatage_wiki($texte) {
$texte .= "\r";
$texte = preg_replace("/(\r\n|\r\n\r|\n|\r)/", "\r", "\r".$texte."\r"); 
$tofind= array(
	'` »`',											// close quote
	'`« `', 										// open quote
	'` !`',											// !
	'` :`',											// :
	'`<(.*?)>\r+`',									// html
	'`@@(.*?)@@`',									// code
	'`\r!!!!!(.*?)\r+`',							// h5
	'`\r!!!!(.*?)\r+`',								// h4
	'`\r!!!(.*?)\r+`',								// h3
	'`\r!!(.*?)\r+`',								// h2
	'`\r!(.*?)\r+`',								// h1
	'`\(\((.*?)\|(.*?)\|(.*?)\|(.*?)\)\)`',			// img
	'`\[\(\(([^[]+)\|([^[]+)\)\)\|([^[]+)\]`',		// img + a href
	'`\r?(.*?)\r\r+`',									// p (laisse une interligne)
	'`(.*?)\r`',										// br : retour à la ligne sans saut de ligne
	'`\[([^[]+)\|([^[]+)\]`',						// a href
	'`\[(http://)([^[]+)\]`',						// url
	'`\_\_(.*?)\_\_`',								// strong
	'`##(.*?)##`',									// italic
	'`--(.*?)--`',									// strike
	'`\+\+(.*?)\+\+`',								// ins
	'`%%`',											// br
	'`\[quote\](.*?)\[/quote\]`s',					// citation
	'`\n?<p></p>\n?`',									// vide
);
$toreplace= array(
	'&nbsp;»',
	'«&nbsp;',
	'&nbsp;!',
	'&nbsp;:',
	'<$1>'."\n",												// html
	'<code><pre>$1</pre></code>',								// code
	'<h5>$1</h5>'."\n",											// h5
	'<h4>$1</h4>'."\n",											// h4
	'<h3>$1</h3>'."\n",											// h3
	'<h2>$1</h2>'."\n",											// h2
	'<h1>$1</h1>'."\n",											// h1
	'<img src="$1" alt="$2" class="$3" title="$4" />',			// img
	'<a href="$3"><img src="$1" alt="$2" /></a>',				// img + a href
	"\n".'<p>$1</p>'."\n",												// p (laisse une interligne)
	'$1<br/>'."\n",													// br : retour à la ligne sans saut de ligne
	'<a href="$2">$1</a>',										// a href
	'<a href="$1$2">$2</a>',									// url
	'<span style="font-weight: bold;">$1</span>',				// strong
	'<span style="font-style: italic;">$1</span>',				// italic
	'<span style="text-decoration: line-through;">$1</span>',	// barre
	'<span style="text-decoration: underline;">$1</span>',		// souligne
	'<br />',													// br
	'<q>$1</q>',												// citation
	'',															// vide
);
$texte_formate = stripslashes(preg_replace($tofind, $toreplace, $texte));
return $texte_formate;
}

function formatage_commentaires($texte) {
$tofindc= array(
	'`\[quote\](.+?)\[/quote\]`s',			// citation
	'` »`',											// close quote
	'`« `', 											// open quote
	'` !`',											// !
	'` :`',											// :
	'`[^\||\[]((https?|ftps?)://(www\.)?([a-z0-9._-]){2,64}\.[a-z]{2,4}/[\w/\?,;&\#\.:=%-]*)`i', // url regex
	'`\[([^[]+)\|([^[]+)\]`',						// a href
	'`\_\_(.*?)\_\_`',								// strong
	'`##(.*?)##`',										// italic
	'`--(.*?)--`',										// strike
	'`\+\+(.*?)\+\+`',								// ins
);
$toreplacec= array(
	'<q>$1</q>',																// citation
	'&nbsp;»',																	// close quote
	'«&nbsp;',																	// open quote
	'&nbsp;!',																	// !
	'&nbsp;:',																	// :
	'<a href="$1">$1</a>',														// url
	'<a href="$2">$1</a>',														// a href
	'<span style="font-weight: bold;">$1</span>',						// strong
	'<span style="font-style: italic;">$1</span>',						// italic
	'<span style="text-decoration: line-through;">$1</span>',		// barre
	'<span style="text-decoration: underline;">$1</span>',			// souligne
);
	$texte = nl2br(stripslashes(preg_replace($tofindc, $toreplacec, $texte)));
	$texte = '<p>'.$texte.'</p>';
return $texte;
}


function date_formate($id) {
	$retour ='';
	$date= decode_id($id);
		$time_article = mktime('0', '0', '0', $date['mois'], $date['jour'], $date['annee']);
		$auj = mktime('0', '0', '0', date('m'), date('d'), date('Y'));
		$hier = mktime('0', '0', '0', date('m'), date('d')-'1', date('Y'));
	if ( $time_article == $auj ) {
		$retour = $GLOBALS['lang']['aujourdhui'];
	} elseif ( $time_article == $hier ) {
		$retour = $GLOBALS['lang']['hier'];
	} else {
		$jour_l = jour_en_lettres($date['jour'], $date['mois'], $date['annee']);
		$mois_l = mois_en_lettres($date['mois']);
			$format = array (
				'0' => $date['jour'].'/'.$date['mois'].'/'.$date['annee'],						// 14/01/1983
				'1' => $date['mois'].'/'.$date['jour'].'/'.$date['annee'],						// 01/14/1983
				'2' => $date['jour'].' '.$mois_l.' '.$date['annee'],									// 14 janvier 1983
				'3' => $jour_l.' '.$date['jour'].' '.$mois_l.' '.$date['annee'],			// vendredi 14 janvier 1983
				'4' => $mois_l.' '.$date['jour'].', '.$date['annee'],									// janvier 14, 1983
				'5' => $jour_l.', '.$mois_l.' '.$date['jour'].', '.$date['annee'],		// vendredi, janvier 14, 1983
				'6' => $date['annee'].'-'.$date['mois'].'-'.$date['jour'],						// 1983-01-14
			);
  foreach ($format as $cle => $valeur) {
	  if ($GLOBALS['format_date'] == $cle) {
	  	$retour = $valeur;
	  }
  }
	}
  return ucfirst($retour);
}

function am_pm($heure) {
	$code='';
	if ( ($heure) >= '12' ) {
		$code= 'PM';
	} elseif ( ($heure) <= '12' ) {
		$code= 'AM';
	}
	return $code;
}

function ampm_time($heure) {
	$time='';
		if ( ($heure) > '12' ) {
			$time = $heure-'12';
		} elseif ( ($heure) == '00' ) {
			$time = '12';
		} else {
		$time = $heure;
		}
	return $time;
}

function heure_formate($id) {
	$date= decode_id($id);
		$format = array (
			'0' => $date['heure'].':'.$date['minutes'].':'.$date['secondes'],																				// 23:56:04
			'1' => $date['heure'].':'.$date['minutes'],																															// 23:56
			'2' => ampm_time($date['heure']).':'.$date['minutes'].':'.$date['secondes'].' '.am_pm($date['heure']),	// 11:56:04 PM
			'3' => ampm_time($date['heure']).':'.$date['minutes'].' '.am_pm($date['heure']),												// 11:56 PM
		);
  foreach ($format as $cle => $valeur) {
	  if ($GLOBALS['format_heure'] == $cle) {
	  return $valeur;
	  }
  }
}

function jour_en_lettres($jour, $mois, $annee) {
	$date = date('w', mktime('0', '0', '0', $mois, $jour, $annee));
			$jour = array(
			'0' => $GLOBALS['lang']['dimanche'],
			'1' => $GLOBALS['lang']['lundi'],
			'2' => $GLOBALS['lang']['mardi'], 
			'3' => $GLOBALS['lang']['mercredi'], 
      '4' => $GLOBALS['lang']['jeudi'],
      '5' => $GLOBALS['lang']['vendredi'],
      '6' => $GLOBALS['lang']['samedi']
      );
       foreach ($jour as $cle => $valeur) {
	       if ($date == $cle) {
	        return $valeur;
	       }
       }
}

function mois_en_lettres($chaine) {
	$mois = array(
		'01' => $GLOBALS['lang']['janvier'],
		'02' => $GLOBALS['lang']['fevrier'],
		'03' => $GLOBALS['lang']['mars'], 
		'04' => $GLOBALS['lang']['avril'], 
		'05' => $GLOBALS['lang']['mai'],
		'06' => $GLOBALS['lang']['juin'],
		'07' => $GLOBALS['lang']['juillet'],
		'08' => $GLOBALS['lang']['aout'],
		'09' => $GLOBALS['lang']['septembre'],
		'10' => $GLOBALS['lang']['octobre'],
		'11' => $GLOBALS['lang']['novembre'], 
		'12' => $GLOBALS['lang']['decembre']
	);
	foreach ($mois as $cle => $valeur) {
		if ($chaine == $cle) {
			return $valeur;
		}
	}
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
