<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

// RETOURNE UN TABLEAU SELON RECHERCHE

function table_recherche($depart, $txt, $statut='') {
	if (strlen($txt) > 3) {
		$table_matchs = array();
		$search_words = preg_split('#[[:space:]]#', strtolower(trim(preg_replace('#[[:punct:]]#i', " ", $txt))));
		foreach ($search_words as $n => $search_word ) {
			if (strlen($search_word) <= '3') {
				unset($search_words[$n]);
			}
		}
		$nb = count($search_words);
		$articles = table_derniers($depart, '', $statut);
		if (!empty($txt)) {
			foreach ($articles as $id) {
				$dec = decode_id($id);
				$dossier = $depart.'/'.$dec['annee'].'/'.$dec['mois'].'/';
				$article_mots_all=parse_xml($dossier.$id, $GLOBALS['data_syntax']['article_keywords']);
				$article_mots = explode(', ', $article_mots_all);
				$mot[$id]=$article_mots;
				if (count(array_intersect($search_words, $article_mots)) == $nb) {
					$table_matchs[]= $id;
				}
			}
			if (count($table_matchs) > '0') {
				$retour = $table_matchs;
			}
		}
	}
	if (isset($retour)) {
		return $retour;
	}
}

// RETOURNE UN TABLEAU SELON TAG
function table_tags($depart, $txt, $statut='') {
	$searched = htmlspecialchars($txt);
	$table_matchs = array();
	$articles = table_derniers($depart, '', $statut);
	if (!empty($searched)) {
		foreach ($articles as $id) {
			$date = decode_id($id);
			$dossier = $depart.'/'.$date['annee'].'/'.$date['mois'].'/';
			$article_tags_all = parse_xml($dossier.$id, $GLOBALS['data_syntax']['article_categories']);
			$article_tags = explode(', ', $article_tags_all);
			if (in_array($searched, $article_tags)) {
				$table_matchs[] = $id;
			}
		}
		if (count($table_matchs) > '0') {
			$retour = $table_matchs;
		}
	}
	if (isset($retour)) {
		return $retour;
	}
}

function list_all_tags() {
	$depart = $GLOBALS['dossier_data_articles'];
	$articles = table_derniers($depart, '', '');
	$tags = '';
	foreach ($articles as $id) {
		$date = decode_id($id);
		$dossier = $depart.'/'.$date['annee'].'/'.$date['mois'].'/';
		$article_tags = parse_xml($dossier.$id, $GLOBALS['data_syntax']['article_categories']);
		$tags .= $article_tags.',';
	}
	return $tags;
}


// RETOURNE UN TABLEAU SELON DATE
function table_date($depart, $annee, $mois, $jour='', $statut='') {
	$liste=array();
	$dossier = $depart.'/'.$annee.'/'.$mois.'/';
	if ($jour == '') {
		$files = parcourir_dossier($dossier, $statut);
		if ($depart == $GLOBALS['dossier_articles'] or $depart == $GLOBALS['dossier_commentaires']) {
			if (!empty($files)) {
				foreach ($files as $billet) {
					if (get_id($billet) <= date('YmdHis')) {
						$contenu[] = $billet;
					}
				}
			}
		} else {
			$contenu = $files;
		}
	} else {
		if ( is_dir($dossier) AND $ouverture = opendir($dossier) ) { 
			$contenu = array();
			while ($fichiers = readdir($ouverture)){
				$jour_fichier = substr($fichiers, '6', '2');
				if ( is_file($dossier.$fichiers) and ($jour == $jour_fichier) ) {
					if  ( (isset($statut)) AND ($statut != '') ) {
						if (get_statut($dossier.$fichiers) === $statut) {
		 				$contenu[] = $fichiers;
						}
					} else {
	  					$contenu[] = $fichiers;
					}
				}
			}
			closedir($ouverture);
		}
	}
	if (isset($contenu)) {
		natcasesort($contenu);
		$liste = array_reverse($contenu);
		return $liste;
	}
}

// RETOURNE UN TABLEAU DE TOUS LES ARTICLES
function table_derniers($dossier, $limite='', $statut='') {
	$contenu = array();

	// listage des dossiers des annees.
	if ( $ouverture = opendir($dossier)) { 
		while ( false !== ($file = readdir($ouverture)) ) {
			if (preg_match('/\d{4}/', $file)) {
				$annees[]=$file;
			}
		}
		closedir($ouverture);
	}

	// listage des dossiers des mois dans chaque dossier des annees
	if (isset($annees)) {
		foreach ($annees as $id => $dossier_annee) {
			$chemin = $dossier.'/'.$dossier_annee.'/';
			for ($mois = 01 ; $mois <= 12 ; $mois++) {
				if (strlen($mois) == '1') {
					$mois = '0'.$mois;
				}
				$file_mois = $chemin.$mois;
				if (is_dir($chemin.$mois) ) { 
					if (preg_match('#'.$chemin.'\d{2}'.'#', $file_mois) ) {
						$dossier_mois[]= $dossier.'/'.$dossier_annee.'/'.$mois;
					}
				}
			}
		}
	}

// listage des fichiers dans chaque dossiers des mois
	if (isset($dossier_mois)) {
		$i= '0';
		foreach ($dossier_mois as $path) { 
				if (is_dir($path) and $ouverture = opendir($path)) {
					while ( ($fichiers = readdir($ouverture)) ) {
						// On verifie Extension
						$chemin= $path.'/'.$fichiers;
						if (preg_match('#^\d{14}\.'.$GLOBALS['ext_data'].'$#',$fichiers)) {

							if ($dossier == $GLOBALS['dossier_data_articles']) { // on affiche les billets programmés (dans le panel par ex)
								if  ( (isset($statut)) and ($statut != '') ) {
									if (get_statut($chemin) === $statut) {
										$contenu[$i++]=$fichiers;
									}
								} else {
									$contenu[$i++]=$fichiers;
								}
							} else { // on affiche pas les billets programes (sur acceuil par exmeple)
								if ($fichiers <= date('YmdHis')) {
									if  ( (isset($statut)) and ($statut != '') ) {
										if (get_statut($chemin) === $statut) {
											$contenu[$i++]=$fichiers;
										}
									} else {
										$contenu[$i++]=$fichiers;
									}
								}
							}
						}
					}
				}
			closedir($ouverture);
		}
		if (isset($contenu)) {
			rsort($contenu);
			if ( (isset($limite)) and ($limite!='') and ($limite != '-1') ) {
				$retour = array_slice($contenu, '0', $limite);
			} else {
					$retour= $contenu;
			}
		}
	}
	if (isset($retour)) {
		return $retour;
	}
}

function traiter_form_billet($billet) {
	if (isset($_POST['enregistrer'])) {
					if (fichier_data($GLOBALS['dossier_data_articles'], $billet) !== 'FALSE') {
					redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&msg=confirm_article_ajout');
					} else {
					erreur('Ecriture impossible');
					exit;
					}
	} elseif (isset($_POST['supprimer'])) {

		if (isset($_POST['security_coin_article']) and htmlspecialchars($_POST['security_coin_article']) == md5($_POST['article_id'].$_SESSION['time_supprimer_article']) and $_SESSION['time_supprimer_article'] >= (time() - 300) ) {

					if (unlink($GLOBALS['dossier_data_articles'].'/'.get_path($billet[$GLOBALS['data_syntax']['article_id']]))) {
						redirection('index.php?msg=confirm_article_suppr');
					}
					else {
						redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&errmsg=error_article_suppr_impos');
						exit;
					}
				$dossier_annee_mois= $GLOBALS['dossier_data_articles'].'/'.$annee.'/'.$mois.'/';
				rmdir($dossier_annee_mois);
				$dossier_annee= $GLOBALS['dossier_data_articles'].'/'.$annee.'/';
				rmdir($dossier_annee);

		} else {
			redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&errmsg=error_article_suppr');
			exit;
		}
	}
}

function fichier_data($dossier, $billet) {
		$article_data = '';
		$article_data .= '<?php die("If you were looking for the answer to life, the universe and everything... It is not here..."); ?>';
		$article_data .= "\n";
		$date= decode_id($billet[$GLOBALS['data_syntax']['article_id']]);
	foreach ($billet as $markup => $content) {
		$article_data .= '<'.$markup.'>'.$content.'</'.$markup.'>'."\n" ;
	}
	if (!empty($billet['bt_categories'])) {
		fichier_tags($billet['bt_categories'], '0');
	}
		if ( !is_dir($dossier) ) {
			$dossier_ini = creer_dossier($dossier);
			fichier_index($dossier);
			fichier_htaccess($dossier);

		} 
		if ( !is_dir(($dossier).'/'.$date['annee']) ) {
			$dossier_annee = creer_dossier($dossier.'/'.$date['annee']);
			fichier_index($dossier.'/'.$date['annee']);
			fichier_htaccess($dossier.'/'.$date['annee']);

		}
		if ( !is_dir(($dossier).'/'.$date['annee'].'/'.$date['mois']) ) {
			$dossier_mois = creer_dossier($dossier.'/'.$date['annee'].'/'.$date['mois']);
			fichier_index($dossier.'/'.$date['annee'].'/'.$date['mois']);
			fichier_htaccess($dossier.'/'.$date['annee'].'/'.$date['mois']);
		}
	$fichier_data = $dossier.'/'.$date['annee'].'/'.$date['mois'].'/'.$billet[$GLOBALS['data_syntax']['article_id']].'.'.$GLOBALS['ext_data'];
		$new_file_data=fopen($fichier_data,'wb+');
		if (fwrite($new_file_data,$article_data) === 'FALSE') {
			return 'FALSE';
		} else {
			fclose($new_file_data);
		}
}

function creer_dossier($dossier) {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0700) === FALSE) {
			return 'FALSE';
		} else {
			return 'TRUE';
		}
	}
}

function parcourir_dossier($dossier, $statut='') {
	if ( is_dir($dossier) AND $ouverture = opendir($dossier) ) {
		while ($fichiers = readdir($ouverture)) {
			if (is_file($dossier.$fichiers)) {
				if (preg_match('#^\d{14}\.'.$GLOBALS['ext_data'].'$#',$fichiers)) {
					if ( (isset($statut)) AND ($statut != '') ) {
						if (get_statut($dossier.$fichiers) === $statut) {
		 				$contenu[] = $fichiers;
						}
					}
					else {
		 				$contenu[] = $fichiers;
					}
				}
			}
		}
		closedir($ouverture);
		if (isset($contenu)) {
			natcasesort($contenu);
			return $contenu;
		}
	} else {
		$erreur = 'Aucun article';
	}
}

function fichier_user() {
		$user='';
	if (!strlen(trim($_POST['nouveau-mdp']))) {
	$new_mdp = $GLOBALS['mdp']; 
} else {
	$new_mdp = ww_hach_sha($_POST['nouveau-mdp'], $GLOBALS['salt']);
}
		$user .= "<?php\n";
		$user .= "\$GLOBALS['lang']=\$lang_".$_POST['langue'].";\n";
		$user .= "\$GLOBALS['identifiant'] = '".clean_txt($_POST['identifiant'])."';\n";
		$user .= "\$GLOBALS['mdp'] = '".$new_mdp."';\n";
		$user .= "?>";
		$fichier_user = '../config/user.php';
		$new_file_user=fopen($fichier_user,'wb+');
	if (fwrite($new_file_user,$user) === FALSE) {
		return 'FALSE';
		} else {
			fclose($new_file_user);
			return 'TRUE' ;
		}
}

function fichier_tags($new_tags, $reset) {
/* new tags */
		$new_tags_array = explode(',' , $new_tags);
		$nb = sizeof($new_tags_array);
		for ($i = 0 ; $i < $nb ; $i ++) {
			$new_tags_array[$i] = trim($new_tags_array[$i]);
		}
/* old tags */
		if (isset($GLOBALS['tags']) and ($reset == '0')) {
			$old_tags = $GLOBALS['tags'];
			$old_tags_array = explode(',' , $old_tags);
			$nb2 = sizeof($old_tags_array);
		} else {
			$old_tags_array[] = '';
			$nb2 = 1;
		}

	$all_tags = array_unique(array_merge($new_tags_array, $old_tags_array));
	sort($all_tags);
		$inline_tags = implode(',' , $all_tags);
		$inline_tags = trim($inline_tags, ',');
		$tags='';
		$tags .= "<?php\n";
		$tags .= "\$GLOBALS['tags'] = '".$inline_tags."';\n";
		$tags .= "?>";

		$fichier_tags = '../config/tags.php';
		$new_file_tags=fopen($fichier_tags,'wb+');
		if (($new_file_tags === FALSE) or (fwrite($new_file_tags,$tags) === FALSE)) {
			return 'FALSE';
		} else {
			fclose($new_file_tags);
			return 'TRUE' ;
		}
}

function fichier_prefs() {
		$prefs='';
		$prefs .= "<?php\n";
		$prefs .= "\$GLOBALS['auteur'] = '".clean_txt($_POST['auteur'])."';\n";	
		$prefs .= "\$GLOBALS['email'] = '".clean_txt($_POST['email'])."';\n";
		$prefs .= "\$GLOBALS['nom_du_site'] = '".clean_txt($_POST['nomsite'])."';\n";
		$prefs .= "\$GLOBALS['description'] = '".clean_txt($_POST['description'])."';\n";
		$prefs .= "\$GLOBALS['racine'] = '".trim($_POST['racine'])."';\n";
		$prefs .= "\$GLOBALS['nb_maxi'] = '".$_POST['nb_maxi']."';\n";
		$prefs .= "\$GLOBALS['nb_maxi_comm'] = '".$_POST['nb_maxi_comm']."';\n";
		$prefs .= "\$GLOBALS['nb_list'] = '".$_POST['nb_list']."';\n";
		$prefs .= "\$GLOBALS['nb_list_com'] = '".$_POST['nb_list_com']."';\n";
		$prefs .= "\$GLOBALS['onglet_commentaires'] = '".$_POST['onglet_commentaires']."';\n";
		$prefs .= "\$GLOBALS['onglet_images'] = '".$_POST['onglet_images']."';\n";
		$prefs .= "\$GLOBALS['format_date'] = '".$_POST['format_date']."';\n";
		$prefs .= "\$GLOBALS['format_heure'] = '".$_POST['format_heure']."';\n";
		$prefs .= "\$GLOBALS['fuseau_horaire'] = '".$_POST['fuseau_horaire']."';\n";
		$prefs .= "\$GLOBALS['activer_global_comments']= '".$_POST['global_coments']."';\n";
		$prefs .= "\$GLOBALS['connexion_delai']= '".$_POST['connexion_delay']."';\n";
		$prefs .= "\$GLOBALS['connexion_captcha']= '".$_POST['connexion_captcha']."';\n";
//		$prefs .= "\$GLOBALS['activer_apercu']= '".$_POST['apercu']."';\n";
		$prefs .= "\$GLOBALS['activer_categories']= '".$_POST['categories']."';\n";
		$prefs .= "\$GLOBALS['theme_choisi']= '".$_POST['theme']."';\n";

		$prefs .= "?>";
		$fichier_prefs = '../config/prefs.php';
		$new_file_pref=fopen($fichier_prefs,'wb+');
		if (fwrite($new_file_pref,$prefs) === FALSE) {
			return 'FALSE';
		} else {
			fclose($new_file_pref);
			return 'TRUE';
		}
}

function fichier_index($dossier) {
	$content = '';
	$content .= '<html>'."\n";
	$content .= "\t".'<head>'."\n";
	$content .= "\t\t".'<title>Access denied</title>'."\n";
	$content .= "\t".'</head>'."\n";
	$content .= "\t".'<body>'."\n";
	$content .= "\t\t".'<a href="/">Retour a la racine du site</a>'."\n";
	$content .= "\t".'</body>'."\n";
	$content .= '</html>';
	$index_html = $dossier.'/index.html';
	$dest_file=fopen($index_html,'wb+');
	if (fwrite($dest_file,$content) === FALSE) {
		return 'FALSE';
	} else {
		fclose($dest_file);
		return 'TRUE';
	}
}

function fichier_htaccess($dossier) {
	$content = '';
	$content .= 'Allow from none'."\n";
	$content .= 'Deny from all'."\n";
	$htaccess = $dossier.'/.htaccess';
	$dest_file=fopen($htaccess,'wb+');
	if (fwrite($dest_file,$content) === FALSE) {
		return 'FALSE';
	} else {
		fclose($dest_file);
		return 'TRUE';
	}
}


function apercu($article) {
			if (isset($article)) {
				$apercu = '<h1>'.$article['titre'].'</h1>'."\n";
    		$apercu .= '<div><strong>'.$article['chapo'].'</strong></div>'."\n";
    		$apercu .= '<div>'.$article['contenu'].'</div>'."\n";
    	echo '<div id="apercu">'."\n".$apercu.'</div>'."\n\n";
	    }
}

function parse_xml($fichier, $balise) {
	if (is_file($fichier)) {
		if ($openfile = file_get_contents($fichier)) {
				if (preg_match('#<'.$balise.'>#',$openfile)) {
	  			$sizeitem = strlen('<'.$balise.'>');
	  			$debut = strpos($openfile, '<'.$balise.'>') + $sizeitem;
	  			$fin = strpos($openfile, '</'.$balise.'>');
	  			$lenght = $fin - $debut;
	  			$return = substr($openfile, $debut, $lenght); 
	  		return $return;
				} else {
					return '';
				}
		} else {
			erreur('Impossible de lire le fichier'.$fichier);
		}
	}
}

?>
