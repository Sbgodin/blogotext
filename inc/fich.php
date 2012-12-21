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

// THIS FILE
// 
// This file contains functions relative to search and list data posts.
// It also contains functions about files : creating, deleting files, etc.
// In addition, functions used when data is saved in files or DB-files are here too.


/* FOR COMMENTS : RETUNS nb_com per author */
function nb_entries_as($table, $what) {
	$result = array();
	$query = "SELECT count($what) AS nb,$what FROM $table GROUP BY $what ORDER BY nb DESC";

	try {
		$result = $GLOBALS['db_handle']->query($query)->fetchAll();
		return $result;
	} catch (Exception $e) {
		die('Erreur 0349 : '.$e->getMessage());
	}
}


// retourne la liste les jours d’un mois que le calendrier doit afficher.
function table_list_date($date, $statut, $mode, $table) {
	$return = array();
	$and_statut = (!empty($statut)) ? 'AND bt_statut=\''.$statut.'\'' : '';

	if ($table == 'articles') {
		$and_date = ($mode == 'admin') ? '' : 'AND bt_date <= '.date('YmdHis');
		$query = "SELECT bt_date FROM $table WHERE bt_date LIKE '$date%' $and_statut $and_date";
	} else {
		$and_date = ($mode == 'admin') ? '' : 'AND bt_id <= '.date('YmdHis');
		$query = "SELECT bt_id FROM $table WHERE bt_id LIKE '$date%' $and_statut $and_date";
	}
	try {
		$return = $GLOBALS['db_handle']->query($query)->fetchAll();
		return $return;
	} catch (Exception $e) {
		die('Erreur 21436 : '.$e->getMessage());
	}
}

// LORS DU POSTAGE D'UN ARTICLE : FIXME : ajouter jeton de sécurité
function traiter_form_billet($billet) {

	if ( isset($_POST['enregistrer']) and !isset($billet['ID']) ) {
		$result = bdd_article($billet, 'enregistrer-nouveau');
		if ($result === TRUE) {
			redirection($_SERVER['PHP_SELF'].'?post_id='.$billet['bt_id'].'&msg=confirm_article_maj');
		}
		else { die($result); }
	}

	elseif ( isset($_POST['enregistrer']) and isset($billet['ID']) ) {
		$result = bdd_article($billet, 'modifier-existant');
		if ($result === TRUE) {
			redirection($_SERVER['PHP_SELF'].'?post_id='.$billet['bt_id'].'&msg=confirm_article_ajout');
		}
		else { die($result); }
	}
	elseif ( isset($_POST['supprimer']) and isset($_POST['ID']) and is_numeric($_POST['ID']) ) {
		$result = bdd_article($billet, 'supprimer-existant');
		if ($result === TRUE) {
			redirection('articles.php?msg=confirm_article_suppr');
		}
		else { die($result); }
	}
}

function bdd_article($billet, $what) {
	// l'article n'existe pas, on le crée
	if ( $what == 'enregistrer-nouveau' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO articles
				(	bt_type,
					bt_id,
					bt_date,
					bt_title,
					bt_abstract,
					bt_link,
					bt_notes,
					bt_content,
					bt_wiki_content,
					bt_categories,
					bt_keywords,
					bt_allow_comments,
					bt_nb_comments,
					bt_statut
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array(
				'article',
				$billet['bt_id'],
				$billet['bt_date'],
				$billet['bt_title'],
				$billet['bt_abstract'],
				$billet['bt_link'],
				$billet['bt_notes'],
				$billet['bt_content'],
				$billet['bt_wiki_content'],
				$billet['bt_categories'],
				$billet['bt_keywords'],
				$billet['bt_allow_comments'],
				0,
				$billet['bt_statut']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur ajout article: '.$e->getMessage();
		}
	// l'article existe, et il faut le mettre à jour alors.
	} elseif ( $what == 'modifier-existant' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('UPDATE articles SET
				bt_date=?,
				bt_title=?,
				bt_link=?,
				bt_abstract=?,
				bt_notes=?,
				bt_content=?,
				bt_wiki_content=?,
				bt_categories=?,
				bt_keywords=?,
				bt_allow_comments=?,
				bt_statut=?
				WHERE ID=?');
			$req->execute(array(
					$billet['bt_date'],
					$billet['bt_title'],
					$billet['bt_link'],
					$billet['bt_abstract'],
					$billet['bt_notes'],
					$billet['bt_content'],
					$billet['bt_wiki_content'],
					$billet['bt_categories'],
					$billet['bt_keywords'],
					$billet['bt_allow_comments'],
					$billet['bt_statut'],
					$_POST['ID']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur mise à jour de l’article: '.$e->getMessage();
		}
	// Suppression d'un article
	} elseif ( $what == 'supprimer-existant' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('DELETE FROM articles WHERE ID=?');
			$req->execute(array($_POST['ID']));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 123456 : '.$e->getMessage();
		}
	}
}



// traiter un ajout de lien prend deux étapes : 1) on donne le lien > il donne un form avec lien+titre 2) après ajout d'une description, on clic pour l'ajouter à la bdd.
// une fois le lien donné (étape 1) et les champs renseignés (étape 2) on traite dans la BDD
function traiter_form_link($link) {
	// redirection : conserve les param dans l'URL mais supprime le 'msg' (pour pas qu'il y soit plusieurs fois, après les redirections.
	$msg_param_to_trim = (isset($_GET['msg'])) ? '&msg='.$_GET['msg'] : '';
	$query_string = str_replace($msg_param_to_trim, '', $_SERVER['QUERY_STRING']);

	if ( isset($_POST['enregistrer'])) {
		$result = bdd_lien($link, 'enregistrer-nouveau');
		if ($result === TRUE) {
			redirection($_SERVER['PHP_SELF'].'?id='.$link['bt_id'].'&msg=confirm_lien_edit');
		}
		else { die($result); }
	}

	elseif (isset($_POST['editer'])) {
		$result = bdd_lien($link, 'modifier-existant');
		if ($result === TRUE) {
			redirection($_SERVER['PHP_SELF'].'?id='.$link['bt_id'].'&msg=confirm_lien_edit');
		}
		else { die($result); }
	}
	elseif ( isset($_POST['supprimer'])) {
		$result = bdd_lien($link, 'supprimer-existant');
		if ($result === TRUE) {
			redirection($_SERVER['PHP_SELF'].'?msg=confirm_link_suppr');
		}
		else { die($result); }
	}

}


function bdd_lien($link, $what) {
	// ajout d'un nouveau lien
	if ($what == 'enregistrer-nouveau') {
		try {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO links
			(	bt_type,
				bt_id,
				bt_content,
				bt_wiki_content,
				bt_author,
				bt_title,
				bt_link,
				bt_statut
			)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array(
				$link['bt_type'],
				$link['bt_id'],
				$link['bt_content'],
				$link['bt_wiki_content'],
				$link['bt_author'],
				$link['bt_title'],
				$link['bt_link'],
				$link['bt_statut']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 5867 : '.$e->getMessage();
		}

	// Édition d'un lien existant
	} elseif ($what == 'modifier-existant') {
		try {
			$req = $GLOBALS['db_handle']->prepare('UPDATE links SET
				bt_content=?,
				bt_wiki_content=?,
				bt_author=?,
				bt_title=?,
				bt_link=?,
				bt_statut=?
				WHERE ID=?');
			$req->execute(array(
				$link['bt_content'],
				$link['bt_wiki_content'],
				$link['bt_author'],
				$link['bt_title'],
				$link['bt_link'],
				$link['bt_statut'],
				$link['ID']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 435678 : '.$e->getMessage();
		}
	}
	// Suppression d'un lien
	elseif ($what == 'supprimer-existant') {
		try {
			$req = $GLOBALS['db_handle']->prepare('DELETE FROM links WHERE ID=?');
			$req->execute(array($link['ID']));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 97652 : '.$e->getMessage();
		}
	}
}



function list_all_tags() {
	try {
		$res = $GLOBALS['db_handle']->query("SELECT bt_categories FROM articles");
		$liste_tags = '';
		// met tous les tags de tous les articles bout à bout
		while ($entry = $res->fetch()) {
			if (trim($entry['bt_categories']) != '') {
				$liste_tags .= $entry['bt_categories'].',';
			}
		}
		$res->closeCursor();
	} catch (Exception $e) {
		die('Erreur : '.$e->getMessage());
	}
	// en crée un tableau
	$tab_tags = explode(',', $liste_tags);
	// les déboublonne
	// c'est environ 100 fois plus rapide de faire un array_unique() avant ET un après de faire le trim() sur les cases.
	$tab_tags = array_unique($tab_tags);
	foreach($tab_tags as $i => $tag) {
		if (trim($tag) != '') {
			$tab_tags[$i] = trim($tag);
		}
	}
	$tab_tags = array_unique($tab_tags);
	// parfois le explode laisse une case vide en fin de tableau. Le sort() le place alors au début.
	// si la premiere case est vide, on la vire.
	sort($tab_tags);
	if ($tab_tags[0] == '') {
		array_shift($tab_tags);
	}
	return $tab_tags;
}


function creer_dossier($dossier, $make_htaccess='') {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0777) === TRUE) {
			fichier_index($dossier); // fichier index.html pour éviter qu'on puisse lister les fichiers du dossier
			if ($make_htaccess == 1) fichier_htaccess($dossier); // fichier évitant qu'on puisse accéder aux fichiers du dossier directement
			return TRUE;
		} else {
			return FALSE;
		}
	}
	return TRUE; // si le dossier existe déjà.
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


function fichier_prefs() {
	$fichier_prefs = '../config/prefs.php';
	if(!empty($_POST['_verif_envoi'])) {
		$auteur = clean_txt($_POST['auteur']);
		$email = clean_txt($_POST['email']);
		$nomsite = clean_txt($_POST['nomsite']);
		$description = clean_txt($_POST['description']);
		$racine = trim($_POST['racine']);
		$max_bill_acceuil = $_POST['nb_maxi'];
//		$max_linx_accueil = $_POST['nb_maxi_linx'];
//		$max_comm_encart = $_POST['nb_maxi_comm'];
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
		// linx
//		$autoriser_liens_public = $_POST['allow_public_linx'];
//		$linx_defaut_status = $_POST['linx_defaut_status'];
		$nombre_liens_admin = $_POST['nb_list_linx'];
	} else {
		$auteur = $GLOBALS['identifiant'];
		$email = 'mail@example.com';
		$nomsite = 'Blogotext';
		$description = $GLOBALS['lang']['go_to_pref'];
		$racine = trim($_POST['racine']);
		$max_bill_acceuil = '10';
//		$max_linx_accueil = '50';
//		$max_comm_encart = '5';
		$max_bill_admin = '25';
		$max_comm_admin = '50';
		$format_date = '0';
		$format_heure = '0';
		$fuseau_horaire = 'UTC';
		$global_com_rule = '0';
		$connexion_captcha = '0';
		$activer_categories = '1';
		$theme_choisi = 'default';
		$comm_defaut_status = '1';
		$automatic_keywords = '1';
		$require_email = '0';
		// linx
//		$autoriser_liens_public = '0';
//		$linx_defaut_status = '1';
		$nombre_liens_admin = '50';
	}
	$prefs = "<?php\n";
	$prefs .= "\$GLOBALS['auteur'] = '".$auteur."';\n";	
	$prefs .= "\$GLOBALS['email'] = '".$email."';\n";
	$prefs .= "\$GLOBALS['nom_du_site'] = '".$nomsite."';\n";
	$prefs .= "\$GLOBALS['description'] = '".$description."';\n";
	$prefs .= "\$GLOBALS['racine'] = '".$racine."';\n";
	$prefs .= "\$GLOBALS['max_bill_acceuil'] = '".$max_bill_acceuil."';\n";
	$prefs .= "\$GLOBALS['max_bill_admin'] = '".$max_bill_admin."';\n";
//	$prefs .= "\$GLOBALS['max_comm_encart'] = '".$max_comm_encart."';\n";
	$prefs .= "\$GLOBALS['max_comm_admin'] = '".$max_comm_admin."';\n";
//	$prefs .= "\$GLOBALS['max_linx_acceuil'] = '".$max_linx_accueil."';\n";
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
	$prefs .= "\$GLOBALS['max_linx_admin']= '".$nombre_liens_admin."';\n";
//	$prefs .= "\$GLOBALS['allow_public_linx']= '".$autoriser_liens_public."';\n";
//	$prefs .= "\$GLOBALS['linx_defaut_status']= '".$linx_defaut_status."';\n";
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


// dans le panel, l'IP de dernière connexion est affichée. Il est stoqué avec cette fonction.
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


// écrit un fichier cache (diminuer les charges serveur)
function cache_file($file, $text) {
	$text .= "\n".'<!-- Servi par le cache -->'."\n";
	creer_dossier($GLOBALS['dossier_cache'], 1); // le test d'existence du dossier est fait dans creer_dossier()
	$file_handle = fopen($file, "w");
	if ($file_handle) {
		// écriture
		fwrite($file_handle, $text);
		fclose($file_handle);
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


// à partir de l’extension du fichier, trouve le "type" correspondant.
// les "type" et le tableau des extensions est le $GLOBALS['files_ext'] dans conf.php
function detection_type_fichier($extension) {
	$good_type = 'other'; // par défaut
	foreach($GLOBALS['files_ext'] as $type => $exts) {
		if ( in_array($extension, $exts) ) {
			$good_type = $type;
			break; // sort du foreach au premier 'match'
		}
	}
	return $good_type;
}


function open_file_db_fichiers($fichier) {
	$liste  = (file_exists($fichier)) ? unserialize(base64_decode(substr(file_get_contents($fichier),strlen('<?php /* '), -strlen(' */')))) : array();
	return $liste;
}

function get_external_file($url, $timeout) {
	$context = stream_context_create(array('http'=>array('timeout' => $timeout))); // Timeout : time until we stop waiting for the response.
	$data = @file_get_contents($url, false, $context, -1, 1000000); // We download at most 4 Mb from source.
	if (isset($data) and isset($http_response_header) and (strpos($http_response_header[0], '200 OK') !== FALSE) ) {
		return $data;
	}
	else {
		return array();
	}
}

