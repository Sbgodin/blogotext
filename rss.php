<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(E_ALL);
header('Content-Type: text/xml; charset=UTF-8');
print "<?".'xml version="1.0" encoding="UTF-8"'."?>"."\n";
require_once 'inc/lang.php';
require_once 'config/user.php';
require_once 'config/prefs.php';
require_once 'inc/conf.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/list.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';

print "<?".'xml-stylesheet href="http://feeds.feedburner.com/~d/styles/rss1full.xsl" type="text/xsl" media="screen"'."?>"."\n";
print "<?".'xml-stylesheet href="http://feeds.feedburner.com/~d/styles/itemcontent.css" type="text/css" media="screen"'."?>"."\n";
print '<rss version="2.0">'."\n";
print '<channel>'."\n";
print '<title>'.$GLOBALS['nom_du_site'].'</title>'."\n";
print '<link>'.$GLOBALS['racine'].'</link>'."\n"; 
print '<description>'.$GLOBALS['description'].'</description>'."\n"; 
print '<language>fr</language>'."\n"; 
print '<copyright>'.$GLOBALS['auteur'].'</copyright>'."\n";
$liste = table_derniers($GLOBALS['dossier_articles'], '15', '1');
foreach ($liste as $id => $article) {
		$extension = substr($article, '-3');
				if ($extension == $GLOBALS['ext_data']) {
					$id = substr($article, '0', '14');
					$billet = init_billet('public', $id);
						$dossier= $GLOBALS['dossier_articles'].'/'.$billet['annee'].'/'.$billet['mois'].'/';
						$fichier = $dossier.$article;
						$jour_abbr = date("D", mktime('0', '0', '0', $billet['mois'], $billet['jour'] , $billet['annee']));
						$mois_abbr = date("M", mktime('0', '0', '0', $billet['mois'], $billet['jour'], $billet['annee']));
						$lien = $billet['annee'].'/'.$billet['mois'].'/'.$billet['jour'].'/'.$billet['heure'].'/'.$billet['minutes'].'/'.$billet['secondes'].'-'.titre_url($billet['titre']);
						print '<item>'."\n";
						print '<title>'.$billet['titre'].'</title>'."\n";
						print '<guid>'.$GLOBALS['racine'].'index.php?'.$lien.'</guid>'."\n";
						print '<pubDate>'.$jour_abbr.', '.$billet['jour'].' '.$mois_abbr.' '.$billet['annee'].' '.$billet['heure'].':'.$billet['minutes'].':'.$billet['secondes'].' +0000</pubDate>'."\n";
						print '<description>'.$billet['chapo'].'</description>'."\n";
						print '</item>'."\n";
				}
}
print '</channel>'."\n";
print '</rss>';
?>