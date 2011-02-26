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

function liste_articles($liste, $template_liste) {
		foreach ($liste as $cle => $article) {
			$extension = get_ext($article);
				if ($extension == $GLOBALS['ext_data']) {
					$id = substr($article, '0', '14');
					$billet = init_billet('public', $id);
					$liste_articles = conversions_theme_article($template_liste, $billet);
				echo $liste_articles;
				}
		}
}

function afficher_liste_articles($tableau) {
if ( (isset($tableau)) AND (!empty($tableau) )) {
$i='0';
echo '<table id="billets">'."\n";
	echo '<tr>';
	// LEGENDE DES COLONNES
		echo '<th>'.$GLOBALS['lang']['label_titre'].'</th>'."\n";
		echo '<th>'.$GLOBALS['lang']['label_date'].'</th>'."\n";
		echo '<th>'.$GLOBALS['lang']['label_time'].'</th>'."\n";
		echo '<th>&nbsp;</th>'."\n";
		echo '<th>&nbsp;</th>'."\n";
	echo '</tr>';
	foreach ($tableau as $liste_fichiers) {
		// INIT BILLET
		$article= init_billet('admin', get_id($liste_fichiers));
		// ICONE SELON STATUT
			if ($article['statut'] === '1') {
				$class='on';
			} else {
				$class='off';
			}
			if (get_ext($liste_fichiers) == $GLOBALS['ext_data']) {
				// ALTERNANCE COULEUR DE FOND
				if ($i % '2' == '0') {
					echo '<tr class="c">'."\n";
				} else {
					echo '<tr>'."\n";
				}
					// TITRE
				echo '	<td class="titre">';
	   			echo '<a class="'.$class.'" href="ecrire.php?post_id='.$article['id'].'" title="'.$article['chapo'].'">'.$article['titre'].'</a>';
    		echo '</td>'."\n";
    		  // DATE
    		echo '<td>'.date_formate($article['id']).'</td>'; 
    		echo '<td>'.heure_formate($article['id']).'</td>'."\n";
    			// NOMBRE COMMENTS
    				if ($nb_comments= count(liste_commentaires($GLOBALS['dossier_data_commentaires'], $article['id']))) {
    					if ($nb_comments == '1') {
    						$texte = $GLOBALS['lang']['label_commentaire'];
    					} else {
	    					$texte = $GLOBALS['lang']['label_commentaires'];
    					}
	    				echo '	<td class="nb-commentaires"><a href="commentaires.php?post_id='.$article['id'].'">'.$nb_comments.' '.$texte.'</a></td>'."\n";
    				} else {
	    				echo '	<td class="nb-commentaires">&nbsp;</td>'."\n";
    				}
    			if ( $article['statut'] == '1') {
    			echo '<td class="lien"><a href="'.get_blogpath($article['id']).'">'.$GLOBALS['lang']['lien_article'].'</a></td>';
    			} else {
	    		echo '<td>&nbsp;</td>';
    			}
			}
			echo '</tr>'."\n";
			$i++;
	}
if ($nb_articles= count($tableau)) {
   if ($nb_articles == '1') {
    	$text_nb_a = $GLOBALS['lang']['label_article'];
    	} else {
	    $text_nb_a = $GLOBALS['lang']['label_articles'];
    	}
echo '<tr><th id="nbart" colspan="5">'.$nb_articles.' '.$text_nb_a.' '.$GLOBALS['lang']['sur'].' '.count(table_derniers($GLOBALS['dossier_data_articles'])).'</th></tr>'."\n";
}
echo '</table>'."\n\n";
} else {
	info($GLOBALS['lang']['note_no_article']);
}
}

?>
