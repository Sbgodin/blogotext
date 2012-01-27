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


/// menu haut panneau admin /////////
function lien_nav($url, $id, $label, $active) {
	echo "\t".'<li><a href="'.$url.'" id="'.$id.'"';
	echo ($active == $url) ? ' class="current"' : '';
	echo '>'.$label.'</a></li>'."\n";
}

function afficher_menu($active) {
	lien_nav('index.php', 'lien-liste', $GLOBALS['lang']['mesarticles'], $active);
	if ($GLOBALS['onglet_commentaires'] == 1) {
		lien_nav('commentaires.php', 'lien-lscom', $GLOBALS['lang']['titre_commentaires'], $active);
	}
	lien_nav('ecrire.php', 'lien-nouveau', $GLOBALS['lang']['nouveau'], $active);
	lien_nav('preferences.php', 'lien-preferences', $GLOBALS['lang']['preferences'], $active);
	lien_nav($GLOBALS['racine'], 'lien-site', $GLOBALS['lang']['lien_blog'], $active);
	if ($GLOBALS['onglet_images'] == 1) {
		lien_nav('image.php', 'lien-image', $GLOBALS['lang']['nouvelle_image'], $active);
	}
	lien_nav('logout.php', 'lien-deconnexion', $GLOBALS['lang']['deconnexion'], $active);
}

function confirmation($message) {
	echo '<div class="confirmation">'.$message.'</div>'."\n";
}
function no_confirmation($message) {
	echo '<div class="no_confirmation">'.$message.'</div>'."\n";
}

function legend($legend, $class='') {
	return '<legend class="'.$class.'">'.$legend.'</legend>'."\n"; 
}

function label($for, $txt) {
	return '<label for="'.$for.'">'.$txt.'</label>'."\n"; 
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
		$requete = htmlspecialchars(stripslashes($_GET['q']));
	}
	$return = '<form action="'.$_SERVER['PHP_SELF'].'" method="get" id="search">'."\n";
	$return .= '<input id="q" name="q" type="search" size="25" value="'.$requete.'" />'."\n";
	$return .= '<input id="input-rechercher" type="submit" value="'.$GLOBALS['lang']['rechercher'].'" />'."\n";
	$return .= '</form>'."\n\n";
	return $return;
}

function afficher_top($titre) {
	if (isset($GLOBALS['lang']['id'])) {
		$lang_id = $GLOBALS['lang']['id'];
	} else {
		$lang_id = 'fr';
	}
	$txt = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n" ;
	$txt .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
	$txt .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang_id.'">'."\n";
	$txt .= '<head>'."\n";
	$txt .= '<meta http-equiv="content-type" content="text/html; charset='.$GLOBALS['charset'].'" />'."\n";
	$txt .= '<link type="text/css" rel="stylesheet" href="style/ecrire.css" />'."\n";
	$txt .= '<title> '.$GLOBALS['nom_application'].' | '.$titre.'</title>'."\n";
	$txt .= '</head>'."\n";
	$txt .= '<body>'."\n\n";
	echo $txt;
}

function afficher_titre($titre, $id, $niveau) {
	echo '<h'.$niveau.' id="'.$id.'">'.$titre.'</h'.$niveau.'>'."\n";
}

function footer($index='', $begin_time='') {
	if ($index != '') {
		$file = '../config/ip.php';
		if (file_exists($file) and is_readable($file)) {
			include($file);
			$new_ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
			$last_time = strtolower(date_formate($GLOBALS['old_time'])).', '.heure_formate($GLOBALS['old_time']);
			if ($new_ip == $GLOBALS['old_ip']) {
				$msg = '<br/>'.$GLOBALS['lang']['derniere_connexion_le'].' '.$GLOBALS['old_ip'].' ('.$GLOBALS['lang']['cet_ordi'].'), '.$last_time;
			} else {
				$msg = '<br/>'.$GLOBALS['lang']['derniere_connexion_le'].' '.$GLOBALS['old_ip'].' '.$last_time;
			}
		} else {
			$msg = '';
		}
	} else {
		$msg = '';
	}
	if ($begin_time != ''){
		$end = microtime(TRUE);
		$dt = round(($end - $begin_time),6);
		$msg2 = ' - rendered in '.$dt.' seconds';
	} else {
		$msg2 = '';
	}

	echo '</div>'."\n";
	echo '</div>'."\n";
	echo '<p id="footer"><a href="'.$GLOBALS['appsite'].'">'.$GLOBALS['nom_application'].' '.$GLOBALS['version'].'</a>'.$msg2.$msg.'</p>'."\n";
	echo '</body>'."\n";
	echo '</html>'."\n";
}

// needs to be a GLOBALS[] because function is called in index.php, and calender used further in the process
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
	$prev_mois = $_SERVER['PHP_SELF'].'?'.$annee.'/'.str_pad($ce_mois-1, 2, "0", STR_PAD_LEFT);
	if ($prev_mois == $_SERVER['PHP_SELF'].'?'.$annee.'/'.'00') {
		$prev_mois = $_SERVER['PHP_SELF'].'?'.($annee-'1').'/'.'12';
	}
	$next_mois = $_SERVER['PHP_SELF'].'?'.$annee.'/'.str_pad($ce_mois+1, 2, "0", STR_PAD_LEFT);
	if ($next_mois == $_SERVER['PHP_SELF'].'?'.$annee.'/'.'13') {
		$next_mois = $_SERVER['PHP_SELF'].'?'.($annee+'1').'/'.'01';
	}
// On verifie si il y a un ou des articles du jour
	$dossier = $depart.'/'.$annee.'/'.$ce_mois.'/';
	if ( is_dir($dossier) and $ouverture = opendir($dossier) ) {
		$jour_fichier = array();
		while ($fichiers = readdir($ouverture)){
			if ( (is_file($dossier.$fichiers)) and (parse_xml($dossier.$fichiers, $GLOBALS['data_syntax']['article_status']) == '1') and (get_id($fichiers) <= date('YmdHis')) ) {
			$jour_fichier[]= substr($fichiers, 6, 2);
			}
		}
	}
	$GLOBALS['calendrier'] = '<table id="calendrier">'."\n";
	$GLOBALS['calendrier'].= '<caption>';
	if ( $annee.$ce_mois > $GLOBALS['date_premier_message_blog']) {
		$GLOBALS['calendrier'].= '<a href="'.$prev_mois.'">&#171;</a>&nbsp;';
	}
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
	// Indique le jour consulte
	for ($jour = 1; $jour <= $jours_dans_mois; $jour++) {
		if ($jour == $ce_jour) {
			$class = ' class="active"';
		} else {
			$class = '';
		}
		if ( (isset($jour_fichier)) and in_array($jour, $jour_fichier) ) {
			$lien= '<a href="'.$_SERVER['PHP_SELF'].'?'.$annee.'/'.$ce_mois.'/'.str_pad($jour, 2, "0", STR_PAD_LEFT).'">'.$jour.'</a>';
		} else {
			$lien= $jour;
		}
		$GLOBALS['calendrier'].= '<td'.$class.'>';
		$GLOBALS['calendrier'].= $lien;
		$GLOBALS['calendrier'].= '</td>';
		$decalage_jour++;
		if ($decalage_jour == 7) {
			$decalage_jour = 0;
			$GLOBALS['calendrier'].=  '</tr>';
			if ($jour < $jours_dans_mois) {
				$GLOBALS['calendrier'].= '<tr>';
			}
		}
	}
	if ($decalage_jour > 0) {
		for ($i = $decalage_jour; $i < 7; $i++) {
			$GLOBALS['calendrier'].= '<td> </td>';
		}
		$GLOBALS['calendrier'].= '</tr>';
	}
	$GLOBALS['calendrier'].= '</table>';
}

function encart_commentaires() {
	$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];
	$tableau = table_derniers($dossier, $GLOBALS['max_comm_encart'], '1', 'public');

	if($tableau != ""){
		$listeLastComments = '<ul class="encart_lastcom">';
		foreach ($tableau as $id) {
			$comment = init_comment('public', get_id($id));
			$comment['contenu_abbr'] = preg_replace('#<.*>#U', '', $comment['contenu']);
			$comment['article_titre'] = parse_xml($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($comment['article_id']), $GLOBALS['data_syntax']['article_title']);
			if (strlen($comment['contenu_abbr']) >= 60) {
				$comment['contenu_abbr'] = diacritique($comment['contenu_abbr'], 1, 1); // EX : œ => oe
				$comment['contenu_abbr'] = substr($comment['contenu_abbr'], 0, 60);
				$comment['contenu_abbr'] .= '…';
			}
			$comment['article_lien'] = get_blogpath($comment['article_id'], 1).'#'.article_anchor($comment['id']);
			$listeLastComments .= '<li title="'.date_formate($comment['id']).'"><b>'.$comment['auteur'].'</b> '.$GLOBALS['lang']['sur'].' <b>'.$comment['article_titre'].'</b><br/><a href="'.$comment['article_lien'].'">'.$comment['contenu_abbr'].'</a>'.'</li>'."\n";
		}
		$listeLastComments .= '</ul>';
		return $listeLastComments;
	} else {
		return $GLOBALS['lang']['no_comments'];
	}
}

function encart_categories() {
	if (!empty($GLOBALS['tags']) and ($GLOBALS['activer_categories'] == '1')) {
		$liste = explode(',' , $GLOBALS['tags']);
//		$liste = array_map("base64_decode", $liste);
		$uliste = '<ul>'."\n";
		foreach($liste as $tag) {
			$tagurl = urlencode(trim($tag));
			$uliste .= "\t".'<li><a href="'.$_SERVER['PHP_SELF'].'?tag='.$tagurl.'">'.ucfirst($tag).'</a></li>'."\n";
		}
		$uliste .= '</ul>'."\n";
		return $uliste;
	}
}

function liste_tags_article($billet, $html_link) {
	if (!empty($billet['categories'])) {
		$tag_list = explode(',', $billet['categories']);
		$nb_tags = sizeof($tag_list);
		$liste = '';
		if ($html_link == 1) {
			foreach($tag_list as $tag) {
				$tag = trim($tag);
				$tagurl = urlencode(trim($tag));
				$liste .= '<a href="'.$_SERVER['PHP_SELF'].'?tag='.$tagurl.'">'.$tag.'</a>, ';
			}
			$liste = trim($liste, ', ');
		} else {
			foreach($tag_list as $tag) {
				$tag = trim($tag);
				$tag = diacritique($tag, 0, 0);
				$liste .= ' tag_'.$tag;
			}
			$liste = trim($liste);
		}
	} else {
		$liste = '';
	}
	return $liste;
}

function afficher_liste_articles($tableau) {
	$dossier = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'];
	if (!empty($tableau)) {
		$i = 0;
		echo '<table id="billets">'."\n";
		echo '<tr>';
			// LEGENDE DES COLONNES
			echo '<th>'.$GLOBALS['lang']['label_titre'].'</th>'."\n";
			echo '<th>'.$GLOBALS['lang']['label_date'].'</th>'."\n";
			echo '<th>'.$GLOBALS['lang']['label_time'].'</th>'."\n";
			echo '<th>&nbsp;</th>'."\n";
			echo '<th>&nbsp;</th>'."\n";
		echo '</tr>';
		foreach ($tableau as $fichier) {
			// INIT BILLET
			$article = init_billet('admin', get_id($fichier));
			// ICONE SELON STATUT
			if ($article['statut'] === '1') {
				$class='on';
			} else {
				$class='off';
			}
			// ALTERNANCE COULEUR DE FOND
			$alt = ($i % 2 == 0) ? '<tr class="c">'."\n" : '<tr>'."\n";
			echo $alt;
			// TITRE
			echo '<td class="titre">';
			echo '<a class="'.$class.'" href="ecrire.php?post_id='.$article['id'].'" title="'.$article['chapo'].'">'.$article['titre'].'</a>';
			echo '</td>'."\n";
			// DATE
			echo '<td><a class="black" href="index.php?filtre='.substr($article['id'],0,8).'">'.date_formate($article['id']).'</a></td>'; 
			echo '<td>'.heure_formate($article['id']).'</td>'."\n";
			// NOMBRE COMMENTS
			$nb_comments = count($article['nb_comments']);
			if ($nb_comments == 1) {
				$texte = $nb_comments.' '.$GLOBALS['lang']['label_commentaire'];
			} elseif ($nb_comments > 1) {
				$texte = $nb_comments.' '.$GLOBALS['lang']['label_commentaires'];
			} else {
				$texte = '&nbsp;';
			}
			echo '<td class="nb-commentaires"><a href="commentaires.php?post_id='.$article['id'].'">'.$texte.'</a></td>'."\n";
			// STATUT
			if ( $article['statut'] == '1') {
				echo '<td class="lien"><a href="'.get_blogpath($article['id'], 1).'">'.$GLOBALS['lang']['lien_article'].'</a></td>';
			} else {
				echo '<td class="lien"><a href="'.get_blogpath($article['id'], 1).'">'.$GLOBALS['lang']['preview'].'</a></td>';
			}
			echo '</tr>'."\n";
			$i++;
		}
		$nb_articles = count($tableau);
		if ($nb_articles == 1) {
			$text_nb = $GLOBALS['lang']['label_article'];
		} else {
			$text_nb = $GLOBALS['lang']['label_articles'];
		}
		echo '<tr><th id="nbart" colspan="5">'.$nb_articles.' '.$text_nb.' '.$GLOBALS['lang']['sur'].' '.count(table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], -1, '', 'admin')).'</th></tr>'."\n";
		echo '</table>'."\n\n";
	} else {
		info($GLOBALS['lang']['note_no_article']);
	}
}

?>
