<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function liste_articles($liste, $template_liste) {
		foreach ($liste as $cle => $article) {
			$extension = get_ext($article);
				if ($extension == $GLOBALS['ext_data']) {
					$id = substr($article, '0', '14');
					$billet = init_billet('public', $id);
					$liste_articles = conversions_theme($template_liste, $billet);
				print $liste_articles;
				}
		}
}

function afficher_liste_articles($tableau) {
if ( (isset($tableau)) AND (!empty($tableau) )) {
$i='0';
print '<table id="billets">'."\n";
	print '<tr>';
	// LEGENDE DES COLONNES
		print '<th>'.$GLOBALS['lang']['label_titre'].'</th>'."\n";
		print '<th>'.$GLOBALS['lang']['label_date'].'</th>'."\n";
		print '<th>'.$GLOBALS['lang']['label_time'].'</th>'."\n";
		print '<th>&nbsp;</th>'."\n";
		print '<th>&nbsp;</th>'."\n";
	print '</tr>';
	foreach ($tableau as $liste_fichiers) {
		// INIT BILLET
		$article= init_billet('admin', remove_ext($liste_fichiers));
		// ICONE SELON STATUT
			if ($article['statut'] === '1') {
				$class='on';
			} else {
				$class='off';
			}
			if (get_ext($liste_fichiers) == $GLOBALS['ext_data']) {
				// ALTERNANCE COULEUR DE FOND
				if ($i % '2' == '0') {
					print '<tr class="c">'."\n";
				} else {
					print '<tr>'."\n";
				}
					// TITRE
				print '	<td class="titre">';
	   			print '<a class="'.$class.'" href="ecrire.php?post_id='.$article['id'].'" title="'.$article['chapo'].'">'.$article['titre'].'</a>';
    		print '</td>'."\n";
    		  // DATE
    		print '<td>'.date_formate($article['id']).'</td>'; 
    		print '<td>'.heure_formate($article['id']).'</td>'."\n";
    			// NOMBRE COMMENTS
    				if ($nb_comments= count(liste_commentaires($GLOBALS['dossier_data_commentaires'], $article['id']))) {
    					if ($nb_comments == '1') {
    						$texte = $GLOBALS['lang']['label_commentaire'];
    					} else {
	    					$texte = $GLOBALS['lang']['label_commentaires'];
    					}
	    				print '	<td class="nb-commentaires"><a href="commentaires.php?post_id='.$article['id'].'">'.$nb_comments.' '.$texte.'</a></td>'."\n";
    				} else {
	    				print '	<td class="nb-commentaires">&nbsp;</td>'."\n";
    				}
    			if ( $article['statut'] == '1') {
    			print '<td class="lien"><a href="'.get_blogpath($article['id']).'">'.$GLOBALS['lang']['lien_article'].'</a></td>';
    			} else {
	    		print '<td>&nbsp;</td>';
    			}
			}
			print '</tr>'."\n";
			$i++;
	}
if ($nb_articles= count($tableau)) {
   if ($nb_articles == '1') {
    	$text_nb_a = $GLOBALS['lang']['label_article'];
    	} else {
	    $text_nb_a = $GLOBALS['lang']['label_articles'];
    	}
print '<tr><th id="nbart" colspan="5">'.$nb_articles.' '.$text_nb_a.' '.$GLOBALS['lang']['sur'].' '.count(table_derniers($GLOBALS['dossier_data_articles'])).'</th></tr>'."\n";
}
print '</table>'."\n\n";
} else {
	info($GLOBALS['lang']['note_no_article']);
}
}

?>
