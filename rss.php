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

header('Content-Type: text/html; charset=UTF-8');
echo "<?".'xml version="1.0" encoding="UTF-8"'."?>"."\n";
require_once 'inc/lang.php';
require_once 'config/user.php';
require_once 'config/prefs.php';
require_once 'inc/conf.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';

echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">'."\n";
echo '<channel>'."\n";

$GLOBALS['BT_ROOT_PATH'] = '';


/* si y'a un ID en paramètre : rss sur fil commentaires */
if (isset($_GET['id']) and preg_match('#^[0-9]{14}$#', $_GET['id'])) {
	$article_id = htmlspecialchars($_GET['id']);
	$date_billet = decode_id($article_id);
	echo '<title>Fil des commentaires sur "'.parse_xml($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles']."/".get_path($article_id), 'bt_title').'" - '.$GLOBALS['nom_du_site'].'</title>'."\n";
	echo '<link>'.$GLOBALS['racine'].'index.php?'.$date_billet['annee'].'/'.$date_billet['mois'].'/'.$date_billet['jour'].'/'.$date_billet['heure'].'/'.$date_billet['minutes'].'/'.$date_billet['secondes'].'</link>'."\n"; 
	echo '<description>'.$GLOBALS['description'].'</description>'."\n"; 
	echo '<language>fr</language>'."\n"; 
	echo '<copyright>'.$GLOBALS['auteur'].'</copyright>'."\n";

	$liste = liste_commentaires($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'], $article_id, '1');
	if ($liste != '') {
		rsort($liste);
		foreach ($liste as $file => $comment) {
			$id = substr($comment, 0, 14);
			$comment = init_comment('public', $id);
			$date_article = decode_id($id);
			$jour_abbr = date("D", mktime(0, 0, 0, $date_article['mois'], $date_article['jour'], $date_article['annee']));
			$mois_abbr = date("M", mktime(0, 0, 0, $date_article['mois'], $date_article['jour'], $date_article['annee']));
			$lien = $date_billet['annee'].'/'.$date_billet['mois'].'/'.$date_billet['jour'].'/'.$date_billet['heure'].'/'.$date_billet['minutes'].'/'.$date_billet['secondes'].'#'.article_anchor($comment['id']);
			echo '<item>'."\n";
				echo '<title>'.$comment['auteur'].'</title>'."\n";
				echo '<guid>'.$GLOBALS['racine'].'index.php?'.$lien.'</guid>'."\n";
				echo '<link>'.$GLOBALS['racine'].'index.php?'.$lien.'</link>'."\n";
				echo '<pubDate>'.$jour_abbr.', '.$comment['jour'].' '.$mois_abbr.' '.$comment['annee'].' '.$comment['heure'].':'.$comment['minutes'].':'.$comment['secondes'].' +0000</pubDate>'."\n";
				echo '<description><![CDATA['.($comment['contenu']).']]></description>'."\n";
			echo '</item>'."\n";
		}
	} else {
		echo '<item>'."\n";
			echo '<title>'.$GLOBALS['lang']['note_no_comment'].'</title>'."\n";
			echo '<guid>'.$GLOBALS['racine'].'index.php</guid>'."\n";
			echo '<link>'.$GLOBALS['racine'].'index.php</link>'."\n";
			echo '<pubDate>'.date('D').', '.date('d').' '.date('m').' '.date('Y').' '.date('h').':'.date('i').':'.date('s').' +0000</pubDate>'."\n";
			echo '<description>'.$GLOBALS['lang']['no_comments'].'</description>'."\n";
		echo '</item>'."\n";

	}
}

/* sinon, fil rss sur les articles */
else {
	echo '<title>'.$GLOBALS['nom_du_site'].'</title>'."\n";
	echo '<link>'.$GLOBALS['racine'].'</link>'."\n"; 
	echo '<description>'.$GLOBALS['description'].'</description>'."\n"; 
	echo '<language>fr</language>'."\n"; 
	echo '<copyright>'.$GLOBALS['auteur'].'</copyright>'."\n";
	$liste = table_derniers($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], '15', '1', 'public');
	foreach ($liste as $id => $article) {
		$extension = substr($article, -3);
		if ($extension == $GLOBALS['ext_data']) {
			$id = substr($article, 0, 14);
			$billet = init_billet('public', $id);
			$dossier= $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.$billet['annee'].'/'.$billet['mois'].'/';
			$fichier = $dossier.$article;
			$jour_abbr = date("D", mktime(0, 0, 0, $billet['mois'], $billet['jour'] , $billet['annee']));
			$mois_abbr = date("M", mktime(0, 0, 0, $billet['mois'], $billet['jour'], $billet['annee']));
			$lien = $billet['annee'].'/'.$billet['mois'].'/'.$billet['jour'].'/'.$billet['heure'].'/'.$billet['minutes'].'/'.$billet['secondes'].'-'.titre_url($billet['titre']);
			echo '<item>'."\n";
				echo '<title>'.$billet['titre'].'</title>'."\n";
				echo '<guid>'.$GLOBALS['racine'].'index.php?'.$lien.'</guid>'."\n";
				echo '<link>'.$GLOBALS['racine'].'index.php?'.$lien.'</link>'."\n";
				echo '<pubDate>'.$jour_abbr.', '.$billet['jour'].' '.$mois_abbr.' '.$billet['annee'].' '.$billet['heure'].':'.$billet['minutes'].':'.$billet['secondes'].' +0000</pubDate>'."\n";
				echo '<description><![CDATA['.nl2br($billet['chapo']).']]></description>'."\n";
				if (isset($_GET['full'])) {
					echo '<content:encoded><![CDATA['.rel2abs($billet['contenu']).']]></content:encoded>'."\n";
				}
			echo '</item>'."\n";
		}
	}
}

echo '</channel>'."\n";
echo '</rss>';
?>
