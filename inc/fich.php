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

// RETOURNE UN TABLEAU SELON RECHERCHE
function table_recherche($depart, $recherche, $statut, $mode) {
	if (strlen(trim($recherche))) {
		$table_matchs = array();

		$articles = table_derniers($depart, '', $statut, $mode);
		foreach ($articles as $id) {
			$dec = decode_id($id);
			$dossier = $depart.'/'.$dec['annee'].'/'.$dec['mois'].'/';
			$article_content = parse_xml($dossier.$id, 'bt_content');
			//	La ligne suivant évite de rechercher les balises elles mêmes (par ex, dans le "href" d'un <a></a>).
			$article_content = preg_replace('#</?.*>#Ui', '', $article_content);
			if (strpos(strtolower($article_content), strtolower(htmlspecialchars($recherche))) !== FALSE ) {
				$table_matchs[]= $id;
			}
		}
	return $table_matchs;
	}
}

// RETOURNE UN TABLEAU SELON AUTEUR DE COMMENTAIRE
// IF NO AUTHOR SPECIFIED : $array[comment_id] => author
// IF AUTHOR SPECIFIED    : $array[i] => comment_id
function table_auteur($depart, $name, $statut, $mode) {
	$comms = table_derniers($depart, '', $statut, $mode);
	$author_list = array();
	if($comms != "") {
		foreach ($comms as $id => $com) {
			$comment = init_comment($mode, get_id($com));
			$author = parse_xml($depart."/".get_path($comment['id']), $GLOBALS['data_syntax']['comment_author']);
			if  (!empty($name)) {
				if ($author == $name) {
					$author_list[] = $comment['id'];
				}
			}
			else $author_list[$comment['id']] = $author;
		}
	}
	return $author_list;
}


// RETOURNE UN TABLEAU SELON TAG
function table_tags($depart, $txt, $statut, $mode) {
	$searched = htmlspecialchars($txt);
	$table_matchs = array();
	$articles = table_derniers($depart, '', $statut, $mode);
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
	$depart = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'];
	$articles = table_derniers($depart, '', '', 'public');
	$tags = '';
	if (!empty($articles)) {
		foreach ($articles as $id) {
			$date = decode_id($id);
			$dossier = $depart.'/'.$date['annee'].'/'.$date['mois'].'/';
			$article_tags = parse_xml($dossier.$id, $GLOBALS['data_syntax']['article_categories']);
			$tags .= $article_tags.',';
		}
	}
	return $tags;
}


// RETOURNE UN TABLEAU SELON DATE
function table_date($depart, $annee, $mois, $jour='', $statut='') {
	$liste = array();
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
				$jour_fichier = substr($fichiers, 6, 2);
				if ( is_file($dossier.$fichiers) and ($jour == $jour_fichier) ) {
					if  ( (isset($statut)) AND ($statut != '') ) {
						if (parse_xml($dossier.$fichiers, $GLOBALS['data_syntax']['article_status']) === $statut) {
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
function table_derniers($dossier, $limite, $statut, $mode) {
	$contenu = array();

	// listage des dossiers des annees.
	if ( $ouverture = opendir($dossier)) { 
		while ( false !== ($file = readdir($ouverture)) ) {
			if (preg_match('/\d{4}/', $file)) {
				$annees[]=$file;
		}	}
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
	}	}	}

	// listage des fichiers dans chaque dossiers des mois
	if (isset($dossier_mois)) {
		$i= 0;
		foreach ($dossier_mois as $path) {
			if (is_dir($path) and $ouverture = opendir($path)) {
				while ( FALSE !== ($fichiers = readdir($ouverture)) ) {
					// On verifie Extension
					$chemin= $path.'/'.$fichiers;
					if (preg_match('#^\d{14}'.'.'.$GLOBALS['ext_data'].'$#',$fichiers)) {

						if ($mode == 'admin') {
							if ( $statut != '' ) {
								if (parse_xml($chemin, $GLOBALS['data_syntax']['article_status']) === $statut) {
									$contenu[$i] = $fichiers;
									$i++;
								}
							} else {
								$contenu[$i] = $fichiers;
								$i++;
							}
						} elseif ($mode == 'public' or $mode == '') {
							if ($fichiers <= date('YmdHis')) {
								if ( $statut != '' ) {
									if (parse_xml($chemin, $GLOBALS['data_syntax']['article_status']) === $statut) {
										$contenu[$i] = $fichiers;
										$i++;
									}
								} else {
									$contenu[$i] = $fichiers;
									$i++;
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

			if ( ($limite != '') and ($limite != '-1') ) {
				$retour = array_slice($contenu, '0', $limite);
			} else {
					$retour= $contenu;
		}	}
	}
	if (isset($retour)) {
		return $retour;
	}
}

function traiter_form_billet($billet) {
	if (isset($_POST['enregistrer'])) {
		if (fichier_data($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], $billet) !== FALSE) {
			redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&msg=confirm_article_ajout');
		} else {
			erreur('Ecriture impossible');
			exit;
		}
	} elseif (isset($_POST['supprimer'])) {
		if (isset($_POST['security_coin_article']) and htmlspecialchars($_POST['security_coin_article']) == md5($_POST['article_id'].$_SESSION['time_supprimer_article']) and $_SESSION['time_supprimer_article'] >= (time() - 300) ) {

			if (unlink($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($billet[$GLOBALS['data_syntax']['article_id']]))) {
				redirection('index.php?msg=confirm_article_suppr');
			} else {
				redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&errmsg=error_article_suppr_impos');
				exit;
			}
			$dossier_annee_mois = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.$annee.'/'.$mois.'/';
			rmdir($dossier_annee_mois);
			$dossier_annee= $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.$annee.'/';
			rmdir($dossier_annee);
		} else {
			redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&errmsg=error_article_suppr');
			exit;
		}
	}
}

function validate_comment($id_file, $choix) {
	if ($choix == '0') $new_choix = '1';
	elseif ($choix == '1') $new_choix = '0';
	else $new_choix = $GLOBALS['comm_defaut_status'];

	if (preg_match('#<bt_status>.*</bt_status>#', file_get_contents($id_file))) {
		$content = preg_replace('#<bt_status>.*</bt_status>#', '<bt_status>'.$new_choix.'</bt_status>', file_get_contents($id_file));
	} else {
		$content = file_get_contents($id_file).'<bt_status>'.$GLOBALS['comm_defaut_status'].'</bt_status>';
	}
	$new_file=fopen($id_file,'wb+');
	if (fwrite($new_file,$content) === FALSE) {
		return FALSE;
	} else {
		fclose($new_file);
		return TRUE;
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
	if (fwrite($new_file_data,$article_data) === FALSE) {
		return FALSE;
	} else {
		fclose($new_file_data);
	}
}

function creer_dossier($dossier) {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0755) === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

function parcourir_dossier($dossier, $statut='') {
	if (is_dir($dossier)) {
		if ($ouverture = opendir($dossier) ) {
			while (FALSE !== ($fichiers = readdir($ouverture))) {
				if (preg_match('#^\d{14}\.'.$GLOBALS['ext_data'].'$#',$fichiers)) {
					if ( (isset($statut)) and ($statut != '') ) {
						if (parse_xml($dossier.$fichiers, $GLOBALS['data_syntax']['article_status']) === $statut) {
							$contenu[] = $fichiers;
						}
					}
					else {
						$contenu[] = $fichiers;
					}
				}
			}
			closedir($ouverture);
			if (isset($contenu)) {
				sort($contenu);
				return $contenu;
			}
		}
	} else {
		$erreur = $GLOBALS['lang']['note_no_article'];
	}
}

function fichier_user() {
		$user='';
	if (strlen(trim($_POST['mdp'])) == 0) {
		$new_mdp = $GLOBALS['mdp']; 
	} else {
		$new_mdp = ww_hach_sha($_POST['mdp_rep'], $GLOBALS['salt']);
	}
	$user .= "<?php\n";
	$user .= "\$GLOBALS['lang']=\$lang_".$_POST['langue'].";\n";
	$user .= "\$GLOBALS['identifiant'] = '".clean_txt($_POST['identifiant'])."';\n";
	$user .= "\$GLOBALS['mdp'] = '".$new_mdp."';\n";
	$user .= "?>";
	$fichier_user = '../config/user.php';
	$new_file_user=fopen($fichier_user,'wb+');
	if (fwrite($new_file_user,$user) === FALSE) {
		return FALSE;
		} else {
			fclose($new_file_user);
			return TRUE;
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
		return FALSE;
	} else {
		fclose($new_file_tags);
		return TRUE ;
	}
}

function fichier_prefs() {
	$prefs='';
	if(!empty($_POST['_verif_envoi'])) {
		$auteur = clean_txt($_POST['auteur']);
		$email = clean_txt($_POST['email']);
		$nomsite = clean_txt($_POST['nomsite']);
		$description = clean_txt($_POST['description']);
		$racine = trim($_POST['racine']);
		$max_bill_acceuil = $_POST['nb_maxi'];
		$max_comm_encart = $_POST['nb_maxi_comm'];
		$max_bill_admin = $_POST['nb_list'];
		$max_comm_admin = $_POST['nb_list_com'];
		$format_date = $_POST['format_date'];
		$format_heure = $_POST['format_heure'];
		$fuseau_horaire = $_POST['fuseau_horaire'];
		$global_com_rule = $_POST['global_comments'];
		$connexion_captcha = $_POST['connexion_captcha'];
		$activer_categories = $_POST['activer_categories'];
		$theme_choisi = $_POST['theme'];
		$comm_defaut_status = $_POST['comm_defaut_status'];
		$automatic_keywords = $_POST['auto_keywords'];
	} else {
		$auteur = '';
		$email = '';
		$nomsite = 'Blogotext';
		$description = $GLOBALS['lang']['go_to_pref'];
		$racine = trim($_POST['racine']);
		$max_bill_acceuil = '10';
		$max_comm_encart = '5';
		$max_bill_admin = '25';
		$max_comm_admin = '50';
		$format_date = '0';
		$format_heure = '0';
		$fuseau_horaire = 'UTC';
		$global_com_rule = '0';
		$connexion_captcha = '0';
		$activer_categories = '1';
		$theme_choisi = 'defaut';
		$comm_defaut_status = '1';
		$automatic_keywords = '1';
	}

	$prefs .= "<?php\n";
	$prefs .= "\$GLOBALS['auteur'] = '".$auteur."';\n";	
	$prefs .= "\$GLOBALS['email'] = '".$email."';\n";
	$prefs .= "\$GLOBALS['nom_du_site'] = '".$nomsite."';\n";
	$prefs .= "\$GLOBALS['description'] = '".$description."';\n";
	$prefs .= "\$GLOBALS['racine'] = '".$racine."';\n";
	$prefs .= "\$GLOBALS['max_bill_acceuil'] = '".$max_bill_acceuil."';\n";
	$prefs .= "\$GLOBALS['max_bill_admin'] = '".$max_bill_admin."';\n";
	$prefs .= "\$GLOBALS['max_comm_encart'] = '".$max_comm_encart."';\n";
	$prefs .= "\$GLOBALS['max_comm_admin'] = '".$max_comm_admin."';\n";
	$prefs .= "\$GLOBALS['format_date'] = '".$format_date."';\n";
	$prefs .= "\$GLOBALS['format_heure'] = '".$format_heure."';\n";
	$prefs .= "\$GLOBALS['fuseau_horaire'] = '".$fuseau_horaire."';\n";
	$prefs .= "\$GLOBALS['connexion_captcha']= '".$connexion_captcha."';\n";
	$prefs .= "\$GLOBALS['activer_categories']= '".$activer_categories."';\n";
	$prefs .= "\$GLOBALS['theme_choisi']= '".$theme_choisi."';\n";
	$prefs .= "\$GLOBALS['global_com_rule']= '".$global_com_rule."';\n";
	$prefs .= "\$GLOBALS['comm_defaut_status']= '".$comm_defaut_status."';\n";
	$prefs .= "\$GLOBALS['automatic_keywords']= '".$automatic_keywords."';\n";

	$prefs .= "?>";
	$fichier_prefs = '../config/prefs.php';
	$new_file_pref = fopen($fichier_prefs,'wb+');
	if (fwrite($new_file_pref,$prefs) === FALSE) {
		return FALSE;
	} else {
		fclose($new_file_pref);
		return TRUE;
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
	$dest_file = fopen($index_html,'wb+');
	if (fwrite($dest_file,$content) === FALSE) {
		return FALSE;
	} else {
		fclose($dest_file);
		return TRUE;
	}
}

function fichier_htaccess($dossier) {
	$content = '';
	$content .= '<Files *>'."\n";
	$content .= 'Order allow,deny'."\n";
	$content .= 'Deny from all'."\n";
	$content .= '</Files>'."\n";
	$htaccess = $dossier.'/.htaccess';
	$dest_file = fopen($htaccess,'wb+');
	if (fwrite($dest_file,$content) === FALSE) {
		return FALSE;
	} else {
		fclose($dest_file);
		return TRUE;
	}
}

function fichier_ip() {
	$new_ip = $_SERVER['REMOTE_ADDR'];
	$new_time = date('YmdHis');
	$content .= "<?php\n";
	$content .= "\$GLOBALS['old_ip'] = '".$new_ip."';\n";	
	$content .= "\$GLOBALS['old_time'] = '".$new_time."';\n";	
	$content .= "?>";
	$fichier = '../config/ip.php';
	$dest_file = fopen($fichier,'wb+');
	if (fwrite($dest_file,$content) === FALSE) {
		return FALSE;
	} else {
		fclose($dest_file);
		return TRUE;
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

function get_literal_chmod($file) {
	$perms = fileperms($file);

	if (($perms & 0xC000) == 0xC000) {
		// Socket
		$info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
		// Lien symbolique
		$info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
		// Régulier
		$info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
		// Block special
		$info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
		// Dossier
		$info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
		// Caractère spécial
		$info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
		// pipe FIFO
		$info = 'p';
	} else {
		// Inconnu
		$info = 'u';
	}

	// Autres
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
	// Groupe
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

	// Tout le monde
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

	return $info;
}


function parse_xml($fichier, $balise) {
	if (is_file($fichier)) {
		if ($openfile = file_get_contents($fichier)) {
				$sizeitem = strlen('<'.$balise.'>');
				$debut = strpos($openfile, '<'.$balise.'>') + $sizeitem;
				$fin = strpos($openfile, '</'.$balise.'>');
			if (($debut and $fin) !== FALSE) {
				$lenght = $fin - $debut;
				$return = substr($openfile, $debut, $lenght); 

				return $return;
			} else {
				return '';
			}
		} else {
			erreur('Impossible de lire le fichier '.$fichier);
		}
	}
}

?>
