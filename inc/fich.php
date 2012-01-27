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

// RETOURNE UN TABLEAU SELON RECHERCHE
function table_recherche($depart, $recherche, $statut, $mode) {
	if (strlen(trim($recherche))) {
		$table_matchs = array();
		$articles = table_derniers($depart, '-1', $statut, $mode);
		foreach ($articles as $id) {
			$dec = decode_id($id);
			$dossier = $depart.'/'.$dec['annee'].'/'.$dec['mois'].'/';
			$article = parse_xml($dossier.$id, 'bt_content');
			//	La ligne suivant évite de rechercher les balises (par ex, dans le "href" d'un <a></a>).
			$article = preg_replace('#</?.*>#Ui', '', $article);
			if (strpos(strtolower($article), strtolower($recherche)) !== FALSE ) {
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
	$comms = table_derniers($depart, '-1', $statut, $mode);
	$author_list = array();
	if ($comms != "") {
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
	$searched = htmlspecialchars($txt); // essential to escape txt here ???
	$articles = table_derniers($depart, '-1', $statut, $mode);
	foreach ($articles as $id) {
		$date = decode_id($id);
		$dossier = $depart.'/'.$date['annee'].'/'.$date['mois'].'/';
		$article_tags_all = parse_xml($dossier.$id, $GLOBALS['data_syntax']['article_categories']);
		$article_tags = explode(', ', $article_tags_all);
		$article_tags = array_map("htmlspecialchars", $article_tags);
		if (in_array($searched, $article_tags)) {
			$table_matchs[] = $id;
		}
	}
	if (!empty($table_matchs)) {
		$retour = $table_matchs;
	} else {
		$retour = '';
	}

	return $retour;
}

function list_all_tags() {
	$depart = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'];
	$articles = table_derniers($depart, '-1', '', 'public');
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
function table_date($depart, $annee, $mois, $jour, $statut) {
	$dossier = $depart.'/'.$annee.'/'.$mois.'/';
	$files = parcourir_dossier($dossier);
	$contenu = array();
	if ($statut != -1) {
		foreach ($files as $file) {
			if (get_id($file) <= date('YmdHis')) {
				if (parse_xml($dossier.$file, $GLOBALS['data_syntax']['article_status']) == $statut) {
					$contenu[] = $file;
				}
			}
		}
	} else {
		$contenu = $files;
	}
	if ($jour != '') { // jours, donc selection des messages
		foreach ($contenu as $id) {
			if (substr($id, 6, 2) == $jour) $contenu_j[] = $id;
		}
	} else {
		$contenu_j = $contenu;
	}
	if (isset($contenu_j)) {
		natcasesort($contenu_j);
		$liste = array_reverse($contenu_j); // faster than rsort()
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
			for ($mois = 1 ; $mois <= 12 ; $mois++) {
				$mois = str_pad($mois, 2, "0", STR_PAD_LEFT);
				$file_mois = $chemin.$mois;
				if (is_dir($chemin.$mois) ) { 
					if (preg_match('#'.$chemin.'\d{2}'.'#', $file_mois) ) {
						$dossiers_mois[]= $dossier.'/'.$dossier_annee.'/'.$mois;
					}
				}
	}	}	}
	// listage des fichiers dans chaque dossiers des mois
	if (isset($dossiers_mois)) {
		foreach ($dossiers_mois as $path) {
			$contenu = array_merge(parcourir_dossier($path), $contenu);
		}
	}
	rsort($contenu);
	if ($statut != '') { // if statut
		$contenu_statut = array();
		foreach ($contenu as $file) {
			if (parse_xml($dossier.'/'.get_path(get_id($file)), $GLOBALS['data_syntax']['article_status']) == $statut) {
				$contenu_statut[] = $file;
			}
		}
	} else {
		$contenu_statut = $contenu;
	}
	if ($mode == 'admin') {
		$contenu_mode = $contenu_statut;
	} else {
		$contenu_mode = array();
		foreach ($contenu_statut as $file) {
			if (get_id($file) <= date('YmdHis')) {
				$contenu_mode[] = $file;
	}	}	}
	if ($limite != '-1' ) {
		$retour = array_slice($contenu_mode, 0, $limite);
	} else {
		$retour = $contenu_mode;
	}
	if (isset($retour)) {
		return $retour;
	}
}

function traiter_form_billet($billet) {
	if (isset($_POST['enregistrer'])) {
		if (fichier_data($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'], $billet) !== FALSE) {
			$id = $billet[$GLOBALS['data_syntax']['article_id']];
			if (isset($_POST['article_id']) and ($billet[$GLOBALS['data_syntax']['article_id']] != $_POST['article_id'] )) {
				unlink($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($_POST['article_id']));
				$id = $billet[$GLOBALS['data_syntax']['article_id']];
			}
			redirection($_SERVER['PHP_SELF'].'?post_id='.$id.'&msg=confirm_article_ajout');
		} else {
			erreur('Ecriture impossible');
			exit;
		}
	}
	elseif (isset($_POST['supprimer'])) {
		if (isset($_POST['security_coin_article']) and htmlspecialchars($_POST['security_coin_article']) == md5($_POST['article_id'].$_SESSION['time_supprimer_article']) and $_SESSION['time_supprimer_article'] >= (time() - 300) ) {
			if (unlink($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_articles'].'/'.get_path($billet[$GLOBALS['data_syntax']['article_id']]))) {
				redirection('index.php?msg=confirm_article_suppr');
			} else {
				redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&errmsg=error_article_suppr_impos');
				exit;
			}
		} else {
			redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id']].'&errmsg=error_article_suppr');
			exit;
		}
	}
}

function fichier_data($dossier, $billet) {
	$article_data = '<?php die("If you were looking for the answer to life, the universe and everything... It is not here."); ?>';
	$article_data .= "\n";
	$date = decode_id($billet[$GLOBALS['data_syntax']['article_id']]);

	foreach ($billet as $markup => $content) {
		$article_data .= '<'.$markup.'>'.$content.'</'.$markup.'>'."\n" ;
	}
	if (!empty($billet['bt_categories']) and $billet['bt_status'] == 1) {
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
	$new_file_data = fopen($fichier_data,'wb+');
	if (fwrite($new_file_data,$article_data) === FALSE) {
		return FALSE;
	} else {
		fclose($new_file_data);
		return TRUE;
	}
}

function creer_dossier($dossier) {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0755) === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		return TRUE;
	}
}

function parcourir_dossier($dossier) {
	$contenu = array();
	if (is_dir($dossier)) {
		$listage = scandir($dossier);	
		foreach ($listage as $fichier) {
			if (preg_match('#^\d{14}\.'.$GLOBALS['ext_data'].'$#', $fichier)) {
				$contenu[] = $fichier;
			}
		}
	}
	sort($contenu);
	return $contenu;
}

function fichier_user() {
	$fichier_user = '../config/user.php';
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
	$new_file_user = fopen($fichier_user,'wb+');
	if (fwrite($new_file_user,$user) === FALSE) {
		return FALSE;
	} else {
		fclose($new_file_user);
		return TRUE;
	}
}

function fichier_tags($new_tags, $reset) {
	$fichier_tags = '../config/tags.php';
	/* new tags */
	$new_tags_array = explode(',' , $new_tags);
	$new_tags_array = array_map("trim", $new_tags_array);
//	$new_tags_array = array_map("base64_encode", $new_tags_array);
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
	$new_file_tags = fopen($fichier_tags,'wb+');
	if (($new_file_tags === FALSE) or (fwrite($new_file_tags,$inline_tags) === FALSE)) {
		return FALSE;
	} else {
		fclose($new_file_tags);
		return TRUE ;
	}
}

function fichier_prefs() {
	$fichier_prefs = '../config/prefs.php';
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
		$require_email = $_POST['require_email'];
	} else {
		$auteur = $GLOBALS['identifiant'];
		$email = 'nom@mail.com';
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
		$require_email = '0';
	}

	$prefs = "<?php\n";
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
	$prefs .= "\$GLOBALS['require_email']= '".$require_email."';\n";
	$prefs .= "?>";
	$new_file_pref = fopen($fichier_prefs,'wb+');
	if (fwrite($new_file_pref,$prefs) === FALSE) {
		return FALSE;
	} else {
		fclose($new_file_pref);
		return TRUE;
	}
}

function fichier_index($dossier) {
	$content = '<html>'."\n";
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
	$content = '<Files *>'."\n";
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
	$new_ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
	$new_time = date('YmdHis');
	$content = "<?php\n";
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
		$info = 's'; // Socket
	} elseif (($perms & 0xA000) == 0xA000) {
		$info = 'l'; // Lien symbolique
	} elseif (($perms & 0x8000) == 0x8000) {
		$info = '-'; // Régulier
	} elseif (($perms & 0x6000) == 0x6000) {
		$info = 'b'; // Block special
	} elseif (($perms & 0x4000) == 0x4000) {
		$info = 'd'; // Dossier
	} elseif (($perms & 0x2000) == 0x2000) {
		$info = 'c'; // Caractère spécial
	} elseif (($perms & 0x1000) == 0x1000) {
		$info = 'p'; // pipe FIFO
	} else {
		$info = 'u'; // Inconnu
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

function liste_articles($liste, $template_liste) {
	foreach ($liste as $cle => $article) {
		$extension = pathinfo($article, PATHINFO_EXTENSION);
		if ($extension == $GLOBALS['ext_data']) {
			$id = substr($article, 0, 14);
			$billet = init_billet('public', $id);
			$liste_articles = conversions_theme_article($template_liste, $billet);
			echo $liste_articles;
		}
	}
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
