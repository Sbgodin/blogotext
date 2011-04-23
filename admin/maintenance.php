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
error_reporting(-1);
require_once '../inc/inc.php';

check_session();

if (isset($_POST['supprimer_fichier_source']) and ($_POST['supprimer_fichier_source'] == '1' )) {
	$file = htmlspecialchars($_POST['filetodelete']);
	if (file_exists($file)) {
		$ouverture = fopen($file, 'w');
		fwrite($ouverture, 'erased');
		fclose($ouverture);
		if (unlink($file)) {
			redirection($_SERVER['PHP_SELF'].'?msg=confirm_backupfile_suppr');
		} else {        		
			redirection($_SERVER['PHP_SELF'].'?msg=error_backupfile_suppr');
		}
	}
}


$titre_page= $GLOBALS['lang']['titre_maintenance'];
afficher_top($titre_page);
afficher_msg();
echo '<div id="top">';
echo '<ul id="nav">';
afficher_menu('preferences.php');
echo '</ul>';
echo '</div>';

echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";


/* #################################################################### MAKE BACKUP FILE ####################################### */
// structure of output file : documentation at http://lehollandaisvolant.net/blogotext/backupdoc.php (in french)


// misc funtions

// this function "parse_xml_str" is the same as "parse_xml" in file /inc/fich.php
// it only uses a string instead of a file as first parameter.
function parse_xml_str($string, $balise) {
	$sizeitem = strlen('<'.$balise.'>');
	$debut = strpos($string, '<'.$balise.'>') + $sizeitem;
	$fin = strpos($string, '</'.$balise.'>');
	if (($debut and $fin) !== FALSE) {
		$lenght = $fin - $debut;
		$return = substr($string, $debut, $lenght); 
	} else {
		$return = '';
	}
	return $return;
}

function base642file($b64_data_target, $image_target_name, $image_source_hash) {
	$bin_data_target = base64_decode($b64_data_target);
	$image_target = fopen($image_target_name, 'wb');
	if (fwrite($image_target, $bin_data_target) !== FALSE) {	// writing
		fclose($image_target);
		if ($image_source_hash == sha1_file($image_target_name)) {	// integrity test
			return TRUE;
		} else {
			unlink($image_target_name);
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function file2base64($source) {
	$bin_data = fread(fopen($source, "r"), filesize($source));
	$b64_data = base64_encode($bin_data);
	$b64_data_inline = preg_replace('#.{64}#', "$0\n", $b64_data);
	return $b64_data_inline;
}


function creer_fich_xml() {
	$dossier_backup = $GLOBALS['dossier_data_backup'];
	if ( !is_dir($dossier_backup) ) {
		if (creer_dossier($dossier_backup) === FALSE) {
			echo $GLOBALS['lang']['err_file_write'];
		}
		else { fichier_index($dossier_backup); }
	}
	$fichier = 'backup-'.date('Y').date('m').date('d').'-'.substr(md5(rand(100,999)),3,5).'.xml';
	$path = $dossier_backup.'/'.$fichier;
	$new_file = fopen($path,'wb+');

	if (is_numeric($_POST['combien_articles'])) {
		$limite = $_POST['combien_articles'];
	} else {
		$limite = '';
	}
	$data = creer_data_xml(table_derniers($GLOBALS['dossier_data_articles'], $limite));
	if (fwrite($new_file, $data) === FALSE) {
		echo $GLOBALS['lang']['err_file_write'];
		return FALSE;
	} else {
		fclose($new_file);
		chmod($path, 0644);
		echo '<form method="post" action="maintenance.php?do=backup" id="preferences"><div>'."\n";
			echo '<fieldset class="pref">';
			echo legend($GLOBALS['lang']['bak_succes_save'], 'legend-tic');
			echo '<p>'.$GLOBALS['lang']['bak_youcannowsave'].'</p>'."\n";
			echo '<p style="text-align: center;"><a href="'.$path.'">'.$fichier.'</a></p>'."\n";
			echo hidden_input('filetodelete',$path);
			echo '</fieldset>'."\n";
			echo '<fieldset class="pref">';
			echo legend('&nbsp;', 'legend-question');
			echo '<p>';
			echo select_yes_no('supprimer_fichier_source', '0', $GLOBALS['lang']['bak_delete_source']);
			echo '</p>';
			echo '</fieldset>'."\n";
			echo input_valider();
		echo '</div></form>'."\n";
		return TRUE;
	}
}

// invoqued in function above
function creer_data_xml($tableau) {
	$data = '<bt_backup_database>'."\n\n";
	if ( (isset($tableau)) AND (!empty($tableau)) ) {
		$data .= '<bt_backup_items>'."\n";
		foreach ($tableau as $liste_fichiers) {
			$article= xml_billet(get_id($liste_fichiers));
			$data .= '<bt_backup_item>'."\n";
			$data .= '<bt_backup_article>'."\n";
			$data .= $article['xml'];
			$data .= '</bt_backup_article>'."\n";
			$commentaires = liste_commentaires($GLOBALS['dossier_data_commentaires'], $article['id']);
			if (!empty($commentaires)) {
				foreach ($commentaires as $id => $content) {
					$comment = xml_comment(get_id($content));
					$data .= '<bt_backup_comment>'."\n";
					$data .= $comment['xml']."\n";
					$data .= '</bt_backup_comment>'."\n";
				}
			}
			$data .= '</bt_backup_item>'."\n\n";
		}
		$data .= '</bt_backup_items>'."\n\n";
	}
	else {
		info($GLOBALS['lang']['note_no_article']);
	}
		// restaurrer aussi la liste des tags ?
	if (!empty($_POST['restore_tags']) and ($_POST['restore_tags'] == 1) and !empty($GLOBALS['tags'])) {
		$data .= '<bt_backup_tags>';
			$data .= $GLOBALS['tags'];
		$data .= '</bt_backup_tags>'."\n";
	}
		// restaurer aussi les images sous forme de base64 ?
	if (!empty($_POST['restore_imgs']) and ($_POST['restore_imgs'] == 1) and is_dir($GLOBALS['dossier_images'])) {
		$dir_imgs = opendir($GLOBALS['dossier_images']);
		$data .= '<bt_backup_imgs>'."\n";
		$nb_img = 0;
		if (is_numeric($_POST['combien_images']) and $_POST['combien_images'] >= 0) {
			$nb_max_img = $_POST['combien_images'];
		} else {
			$nb_max_img = 1000;
		}
		while (FALSE !== ($img = readdir($dir_imgs)) and $nb_img < $nb_max_img) {
			if (preg_match('#^blog#', $img)) {
				$data .= '<bt_backup_img>'."\n";
					$data .= '<bt_backup_img_name>'.$img.'</bt_backup_img_name>'."\n";
					$data .= '<bt_backup_img_base64>'."\n".file2base64($GLOBALS['dossier_images'].$img).'</bt_backup_img_base64>'."\n";
					$data .= '<bt_backup_img_hash>'.sha1_file($GLOBALS['dossier_images'].$img).'</bt_backup_img_hash>'."\n";
				$data .= '</bt_backup_img>'."\n";
				$nb_img++;
			}
		}
		$data .= '</bt_backup_imgs>'."\n";
	}
	$data .= "\n".'</bt_backup_database>';
	return $data;
}

function xml_billet($id) {
	$art_directory = $GLOBALS['dossier_data_articles'];
	$file = $art_directory.'/'.get_path($id);
	$billet['id'] = $id;
	$billet['xml'] = file_get_contents($file);
	$billet['xml'] = preg_replace('#<\?php die\("If you [a-zA-Z, .]+ not here\.\.\."\); \?>\n#', '', $billet['xml']);
	return $billet;
}

function xml_comment($id) {
	$com_directory = $GLOBALS['dossier_data_commentaires'];
	$file = $com_directory.'/'.get_path($id);
	$comment['id'] = $id;
	$comment['xml'] = file_get_contents($file);
	$comment['xml'] = preg_replace('#<\?php die\("If you [a-zA-Z, .]+ not here\.\.\."\); \?>\n#', '', $comment['xml']);
	return $comment;
}

function enregistrer_donnees($dossier, $donnes, $type_fich) {
	$data2write = '';
	$data2write .= '<?php die("If you were looking for the answer to life, the universe and everything... It is not here..."); ?>'."\n";
	$data2write .= trim($donnes);
	$bt_id = preg_replace('#(.*)<bt_id>(.+)</bt_id>(.*)#is', "$2", $donnes);
	$date = decode_id($bt_id);
	if ( !is_dir($dossier) ) {
		$dossier_ini = creer_dossier($dossier);
	} if ( !is_dir($dossier.'/'.$date['annee']) ) {
		$dossier_annee = creer_dossier($dossier.'/'.$date['annee']);
	} if ( !is_dir($dossier.'/'.$date['annee'].'/'.$date['mois']) ) {
		$dossier_mois = creer_dossier($dossier.'/'.$date['annee'].'/'.$date['mois']);
	}
	$fichier_data = $dossier.'/'.$date['annee'].'/'.$date['mois'].'/'.$bt_id.'.'.$GLOBALS['ext_data'];

	// traitement commentaire
	if ($type_fich == 'commentaire') {
		$error_syntaxe = '0';
		$balise_bt = array('bt_version', 'bt_id', 'bt_article_id', 'bt_content', 'bt_author', 'bt_email');
		for ($i = 0; $i < 6; $i++) {
			if (!preg_match('#(.*)<'.$balise_bt[$i].'>(.+)</'.$balise_bt[$i].'>(.*)#is',$donnes)) {
				$error_syntaxe = '1';
			}
		}
		$date = ' '.date_formate($bt_id);
		$name = $GLOBALS['lang']['label_commentaire'].' '.$GLOBALS['lang']['du'];
		$author = ' de '.preg_replace('#(.*)<bt_author>(.+)</bt_author>(.*)#is', "$2", $donnes);
		echo $name.$date.$author;

	// traitement article
	} elseif ($type_fich == 'article') {
		$error_syntaxe = '0';
		$balise_bt = array('bt_version', 'bt_id', 'bt_title', 'bt_abstract', 'bt_content', 'bt_wiki_content', 'bt_keywords', 'bt_status', 'bt_allow_comments');
		for ($i = 0; $i < 9; $i++) {
			if (!preg_match('#(.*)<'.$balise_bt[$i].'>(.*)</'.$balise_bt[$i].'>(.*)#is',$donnes)) {
				$error_syntaxe = '1';
			}
		}
		$date = ' '.date_formate($bt_id);
		$name = $GLOBALS['lang']['label_article'].' '.$GLOBALS['lang']['du'];
		$titre = preg_replace('#(.*)<bt_title>(.+)</bt_title>(.*)#is', "$2", $donnes);
		echo $name.$date.' : <b>'.$titre.'</b>';
	}
	if ($error_syntaxe != '0') {
		echo '<span style="color: red; float:right; font-weight:bold;">'.$GLOBALS['lang']['echec'].'</span>';
		return FALSE;
	} else {
		$new_file_data = fopen($fichier_data,'wb+');
		if (fwrite($new_file_data, $data2write) === FALSE) {
			echo '<span style="color: red; float:right; font-weight:bold;">'.$GLOBALS['lang']['echec'].'</span>';
			return FALSE;
		} else {
			fclose($new_file_data);
			echo '<span style="color: green; float:right;">'.$GLOBALS['lang']['succes'].'</span>';
			return TRUE;
		}
	}
}

function clean_taglist($do) {
	$usedtags = list_all_tags();
	$usedtags = explode(',', $usedtags);
	$nb = sizeof($usedtags);
	for ($i=0 ; $i < $nb ; $i++ ) {
		$usedtags[$i] = trim(trim($usedtags[$i], ','));
	}
	$usedtags = array_unique($usedtags);
	sort($usedtags);

	$unused_tags = array_diff(explode(',', $GLOBALS['tags']), $usedtags);
	$unused_tags_inline = implode(', ', $unused_tags);
	$unused_tags_inline = trim($unused_tags_inline);

	if (strlen($unused_tags_inline) == 0) {
			echo $GLOBALS['lang']['maint_no_unused_tags'];
	} else {
			echo $GLOBALS['lang']['maint_unused_tags_are'].': '. $unused_tags_inline;
	}
	$usedtags_inline = implode(',', $usedtags);
	if ($do == 1) {
		if (!(fichier_tags($usedtags_inline, '1') === TRUE)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

function is_file_error() {
	$erreurs = array();

	if (!is_dir($GLOBALS['dossier_images'])) {
		$erreurs[] = $GLOBALS['lang']['prob_no_img_folder'];
	} elseif (!is_readable($GLOBALS['dossier_images']) or !is_writable($GLOBALS['dossier_images'])) {
		$erreurs[] = $GLOBALS['lang']['prob_img_folder_chmod'].' (chmod : '.get_literal_chmod($GLOBALS['dossier_images']).')';
	}
	if (!is_dir($GLOBALS['dossier_data_articles'])) {
		$erreurs[] = $GLOBALS['lang']['prob_no_art_folder'];
	} elseif (!is_readable($GLOBALS['dossier_data_articles']) or !is_writable($GLOBALS['dossier_data_articles'])) {
		$erreurs[] = $GLOBALS['lang']['prob_art_folder_chmod'].' (chmod : '.get_literal_chmod($GLOBALS['dossier_data_articles']).')';
	}
	if (!is_dir($GLOBALS['dossier_data_commentaires'])) {
		$erreurs[] = $GLOBALS['lang']['prob_no_com_folder'];
	} elseif (!is_readable($GLOBALS['dossier_data_commentaires']) or !is_writable($GLOBALS['dossier_data_commentaires'])) {
		$erreurs[] = $GLOBALS['lang']['prob_com_folder_chmod'].' (chmod : '.get_literal_chmod($GLOBALS['dossier_data_commentaires']).')';
	}
	if (!is_writable('../'.'config/prefs.php')) {
		$erreurs[] = $GLOBALS['lang']['prob_pref_file_chmod'].' (chmod : '.get_literal_chmod('../'.'config/prefs.php').')';
	}
	if (!is_writable('../'.'config/user.php')) {
		$erreurs[] = $GLOBALS['lang']['prob_user_file_chmod'].' (chmod : '.get_literal_chmod('../'.'config/user.php').')';
	}
	if (!is_writable('../'.'config/tags.php')) {
		$erreurs[] = $GLOBALS['lang']['prob_tags_file_chmod'].' (chmod : '.get_literal_chmod('../'.'config/tags.php').')';
	}

	return $erreurs;
}


// GET DO : rien n'est spécifié, on demande quoi faire.
if (empty($_GET['do'])) {
	// 1 : choix de ce qu'on va faire dans backup
	echo '<form method="post" action="maintenance.php?do=backup" id="preferences"><fieldset class="pref valid-center">';
		echo legend($GLOBALS['lang']['legend_what_doyouwant'], 'legend-backup');
		echo form_radio('quefaire', 'sauvegarde', 'sauvegarde', $GLOBALS['lang']['bak_save2xml']);
		echo form_radio('quefaire', 'restore', 'restore', $GLOBALS['lang']['bak_restorefromxml']);
		echo form_radio('quefaire', 'rien', 'rien', $GLOBALS['lang']['bak_nothing'], TRUE);
		echo input_valider();
	echo '</fieldset></form>'."\n";

	// 2 : formulaire clean-tags
	echo '<form method="post" action="maintenance.php?do=clntags" id="preferences"><fieldset class="pref valid-center">';
		echo legend($GLOBALS['lang']['maint_clean_tag_list'], 'legend-bin');
		echo '<p>'.$GLOBALS['lang']['maint_info_clntags'].'</p>';
		echo input_valider();
	echo '</fieldset></form>'."\n";

	// 3 : formulaire update
	echo '<div id="preferences"><fieldset class="pref valid-center">';
		echo legend($GLOBALS['lang']['maint_chk_update'], 'legend-tic');
		if (@file_get_contents('http://lehollandaisvolant.net/blogotext/version.php')) {
			$last_version = trim(file_get_contents('http://lehollandaisvolant.net/blogotext/version.php'));
			if ($GLOBALS['version'] == $last_version) {
				echo '<p>'.$GLOBALS['lang']['maint_update_youisgood'].'</p>';
			} else {
				echo '<p>'.$GLOBALS['lang']['maint_update_youisbad'].' ('.$last_version.')</p>';
				echo '<p>'.$GLOBALS['lang']['maint_update_go_dl_it'].' <a href="http://lehollandaisvolant.net/blogotext/">lehollandaisvolant.net/blogotext/</a>.';
		}
		} else {
			echo '<p><br/><b>Impossible to check if updates are available or not.</b><br/>Please check by yourself at <a href="http://lehollandaisvolant.net/blogotext/">lehollandaisvolant.net/blogotext/</a>.</p><p>If this error occur again, please send an email to : timovneerden@gmail.com.</p>';
		}
	echo '</fieldset></div>'."\n";


	// 4 : infos
	$erreurs = is_file_error();
	if (!empty($erreurs)) {
		echo '<div id="preferences"><fieldset class="pref valid-center">';
			echo legend($GLOBALS['lang']['erreurs'], 'legend-tic');
				erreurs($erreurs);
		echo '</fieldset></div>'."\n";
	}

}

// GET DO : ok.
/* --------------------------------------------------------------------  SYSTEME BACKUPAGE ------------------------------------- */
elseif ($_GET['do'] == 'backup' ) {

	// S A U V E G A R D E
	if (isset($_POST['quefaire']) and ($_POST['quefaire'] == 'sauvegarde')) {

		// un nb d'articles a backuper a ete specifie : on procede au backup.
		if (!empty($_POST['combien_articles'])) {
			creer_fich_xml();
		// aucun nombre d'articles a été spécifié : on le demande.
		} else {
			echo '<form method="post" action="maintenance.php?do=backup" id="preferences"><div>'."\n";
			echo '<fieldset class="pref">'."\n";
			  echo legend($GLOBALS['lang']['bak_number_articles'], 'legend-question');
			  $nbs= array('1'=>'1', '2'=>'2', '5'=>'5', '10'=>'10', '20'=>'20', '50'=>'50', '100'=>'100', '-1'=> $GLOBALS['lang']['pref_all']);
			  echo form_select('combien_articles', $nbs, '-1',$GLOBALS['lang']['bak_combien_articles']);
			echo '</fieldset>'."\n";
			echo '<fieldset class="pref">'."\n";
			  echo legend($GLOBALS['lang']['bak_tags_legend'], 'legend-question');
			  echo select_yes_no('restore_tags', '1', $GLOBALS['lang']['bak_tags_too']);
			echo '</fieldset>'."\n";
			echo '<fieldset class="pref">'."\n";
			  echo legend($GLOBALS['lang']['bak_imgs_legend'], 'legend-question');
			  echo select_yes_no('restore_imgs', 1, $GLOBALS['lang']['bak_imgs_too']);
			  $nbs= array('1'=>'1', '5'=>'5', '10'=>'10', '20'=>'20', '50'=>'50', '100'=>'100', '-1'=> $GLOBALS['lang']['pref_all']);
			  echo form_select('combien_images', $nbs, '1',$GLOBALS['lang']['bak_combien_images']);
			echo '</fieldset>'."\n";
			echo hidden_input('quefaire','sauvegarde');
			echo input_valider();
			echo '</div></form>'."\n";
		}
	}

	// R E S T A U R A T I O N
	elseif (isset($_POST['quefaire']) and ($_POST['quefaire'] == 'restore')) {
		// fichier present : on l'analyse, et on ne reaffiche pas le formulaire d'envoie
		if(isset($_FILES['xml_file'])) {
			switch ($_FILES['xml_file']['error']) {
				case 3: $erreurs[] = $GLOBALS['lang']['img_phperr_partial']; break;
				case 4: $erreurs[] = $GLOBALS['lang']['img_phperr_nofile']; break;
				case 6: $erreurs[] = $GLOBALS['lang']['img_phperr_tempfolder']; break;
				case 7: $erreurs[] = $GLOBALS['lang']['img_phperr_DiskWrite']; break;
			}
			if (!empty($_FILES['xml_file']['type']) and ($_FILES['xml_file']['type'] != 'text/xml')) {
				$erreurs[] = $GLOBALS['lang']['file_format_error'];
			}
			// si erreurs
			if (!empty($erreurs)) {
				echo '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :<ul>'."\n";
				foreach($erreurs as $erreur) {
					echo '<li>'.$erreur.'</li>'."\n";
				}
				echo '</ul></div>'."\n";
			}
			// si pas erreurs
			else {
				echo '<form action="maintenance.php" method="post" enctype="multipart/form-data" id="preferences">'."\n";
					echo '<fieldset class="pref valid-center">';
					echo legend($GLOBALS['lang']['bak_restor_done'], 'legend-tic');
					echo '<p>';
					echo $GLOBALS['lang']['bak_restor_done_mesage'];
					echo '</p>'."\n".'<p>'."\n";
					echo input_valider();
					echo '</p>'."\n";
					echo '</fieldset>'."\n";
				echo '</form>'."\n";
				$content_xml = file_get_contents($_FILES['xml_file']['tmp_name']);
				$restore_tags = parse_xml($_FILES['xml_file']['tmp_name'],'bt_backup_tags');
				$restore_text = parse_xml($_FILES['xml_file']['tmp_name'],'bt_backup_items');
				if ($restore_text == '') { $restore_text = $content_xml; } // compatibility with old backups;
				$restore_imgs = parse_xml($_FILES['xml_file']['tmp_name'],'bt_backup_imgs');
				// traitement des images
				if (!empty($restore_imgs)) {
					$images = explode('</bt_backup_img>', $restore_imgs);
					$nb_imgs = sizeof($images);
					if ($nb_imgs > 1) {
						echo '<p>'.$GLOBALS['lang']['images'].' :</p>';
						echo '<ul>'."\n";
						for ($img = 0 ; $img < $nb_imgs -1 ; $img++) {
							$img_base64 = parse_xml_str($images[$img], 'bt_backup_img_base64');
							$img_name = parse_xml_str($images[$img], 'bt_backup_img_name');
							$img_hash = parse_xml_str($images[$img], 'bt_backup_img_hash');
							if (base642file($img_base64, $GLOBALS['dossier_images'].$img_name, $img_hash) === TRUE) {
								echo '<li>'.'<span style="color: green;">'.$GLOBALS['lang']['succes'].'</span> : <a href="'.$GLOBALS['dossier_images'].$img_name.'">'.$img_name.'</a></li>'."\n";
							} else {
								echo '<li>'.'<span style="color: red; font-weight:bold;">'.$GLOBALS['lang']['echec'].'</span> : '.$img_name. '(SHA1 : '.$img_hash.')</li>'."\n";
							}
						}
						echo '</ul>'."\n";
					}
				}

				// traitement des tags
				if (!empty($restore_tags)) {
					echo '<p>Tags :</p>';
					echo '<ul>'."\n";
					if (fichier_tags($restore_tags, 0) === FALSE) {
						echo '<li>'.'<span style="color: red; font-weight:bold;">'.$GLOBALS['lang']['echec'].'</span></li>'."\n";
					} else {
						echo '<li>'.'<span style="color: green;">'.$GLOBALS['lang']['succes'].'</span></li>'."\n";
					}
					echo '</ul>'."\n";
				}
				// traitement des articles
				$items = explode('</bt_backup_item>', $restore_text);
				$nb_msg = sizeof($items);
				if ($nb_msg > 1) {
					echo '<p>'.ucfirst($GLOBALS['lang']['label_articles'].' &amp; '.$GLOBALS['lang']['label_commentaires']).' :</p>';
					echo '<ul>';
					for ($msg = 0; $msg < $nb_msg -1; $msg++) {
						$msg_content = preg_replace('#(.*)<bt_backup_article>(.+)</bt_backup_article>(.*)#is', "$2", $items[$msg]);
						echo '<li class="bloc_article" style="margin: 2px auto;">';
						echo '<div style="border: gray dashed 1px;">';
						enregistrer_donnees($GLOBALS['dossier_data_articles'], $msg_content, 'article');
						echo '</div>'."\n";
						// pour chaque article : traitement des commentaires
						if (preg_match("#<bt_backup_comment>#",$items[$msg])) {
							$comms = explode('<bt_backup_comment>',$items[$msg]);
							$nb_comms = sizeof($comms);
							echo '<ul class="bloc_comment">'."\n";
							for ($com = 1; $com < $nb_comms; $com++) {
								$comms[$com] = preg_replace('#</bt_backup_comment>#', '', $comms[$com]);
								echo '<li class="comment" style="border: silver dashed 1px; margin: 1px;">';
								enregistrer_donnees($GLOBALS['dossier_data_commentaires'], $comms[$com], 'commentaire');
								echo '</li>'."\n";
							}
							echo '</ul>'."\n";
						}
						echo '</li>'."\n";
					}
					echo '</ul>'."\n";
				}
				else {
					echo $GLOBALS['lang']['note_no_article'];
				}
			}
		}
		// page restore : si aucun fichier spécié, on en demande un
		if ( !isset($_FILES['xml_file']) or !empty($erreurs) ) {
			echo '<form action="maintenance.php?do=backup" method="post" enctype="multipart/form-data" id="preferences"><fieldset class="pref">';
			echo legend($GLOBALS['lang']['bak_choosefile'], 'legend-user');
			echo '<p>'."\n";
			echo '<input type="file" name="xml_file" /><br />'."\n";
			echo input_upload();
			echo hidden_input('quefaire','restore');
			echo '</p>'."\n";
			echo '</fieldset>'."\n";
			echo '</form>'."\n";
		}
	}
}

/* ####################################################################  CLEAN TAG LIST  ####################################### */
elseif ($_GET['do'] == 'clntags') {

	echo '<form method="post" action="maintenance.php?do=clntags" id="preferences"><fieldset class="pref valid-center">';
		echo legend($GLOBALS['lang']['maint_clean_tag_list'], 'legend-question');
		echo hidden_input('clean','ok');
		if (isset($_POST['clean']) and $_POST['clean'] == 'ok') {
			$msg = clean_taglist(1);
		} else {
			$msg = clean_taglist(0);
		}
		if ($msg === TRUE) {
			redirection($_SERVER['PHP_SELF'].'?msg=confirm_tags_cleand');
		}
		echo input_valider();
	echo '</fieldset></form>'."\n";
}


footer();

?>
