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
//error_reporting(E_ALL);
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

// GET DO : que faire : restore ? backup ?
if ((!empty($_GET['do']) and ($_GET['do'] == 'backup')) or (empty($_GET['do']))) {

			// S A U V E G A R D E
		if (isset($_POST['quefaire']) and ($_POST['quefaire'] == 'sauvegarde')) {

				// un nb d'articles à backuper a été spécifié : on procède au backup.
			if (!empty($_POST['combien_articles'])) {
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
				function afficher_tout_xml($tableau) {
							$data = '<bt_backup_database>'."\n";
					if ( (isset($tableau)) AND (!empty($tableau)) ) {
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
									$data .= $comment['xml'];
									$data .= '</bt_backup_comment>'."\n";
								}
							}
							$data .= '</bt_backup_item>'."\n";
						}
					}
					else {
						info($GLOBALS['lang']['note_no_article']);
					}
					$data .= "\n".'</bt_backup_database>'."\n";
					return $data;
				}
				function creer_fich_xml() {
					$dossier_backup = $GLOBALS['dossier_data_backup'];
					if ( !is_dir($dossier_backup) ) {
						if (creer_dossier($dossier_backup) === 'FALSE') {
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
					$data = afficher_tout_xml(table_derniers($GLOBALS['dossier_data_articles'], $limite));
					if (fwrite($new_file, $data) === FALSE) {
						echo $GLOBALS['lang']['err_file_write'];
						return FALSE;
					} else {
						fclose($new_file);
						chmod($path, 0666);
						echo '<form method="post" action="maintenance.php?do=backup"><div>'."\n";
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
					}
				}
				creer_fich_xml();

				// aucun nombre d'articles a été spécifié : on le demande.
			} else {
				echo '<form method="post" action="maintenance.php?do=backup"><div>'."\n";
				echo '<fieldset class="pref">';
				echo legend($GLOBALS['lang']['bak_number_articles'], 'legend-question');
				$nbs= array('1'=>'1', '2'=>'2', '5'=>'5', '10'=>'10', '20'=>'20', '50'=>'50', '100'=>'200', '-1'=> $GLOBALS['lang']['pref_all']);
				echo form_select('combien_articles', $nbs, '-1',$GLOBALS['lang']['bak_combien_articles']);

				echo hidden_input('quefaire','sauvegarde');
				echo '</fieldset>'."\n";
				echo input_valider();
				echo '</div></form>'."\n";
			}
		}

	// R E S T A U R A T I O N
	elseif (isset($_POST['quefaire']) and ($_POST['quefaire'] == 'restore')) {

		// fichier present : on l'analyse, et on ne reaffiche pas le formulaire d'envoie
		if(isset($_FILES['xml_file'])) {

			function enregistrer_donnes($dossier, $donnes, $type_fich) {
				$data2write = '';
				$data2write .= '<?php die("If you were looking for the answer to life, the universe and everything... It is not here..."); ?>';
				$data2write .= "\n";
				$data2write .= $donnes;
				$bt_id = preg_replace('#(.*)<bt_id>(.+)</bt_id>(.*)#is', "$2", $donnes);
				$date = decode_id($bt_id);
				if ( !is_dir($dossier) ) {
					$dossier_ini = creer_dossier($dossier);
				} if ( !is_dir(($dossier).'/'.$date['annee']) ) {
					$dossier_annee = creer_dossier($dossier.'/'.$date['annee']);
				} if ( !is_dir(($dossier).'/'.$date['annee'].'/'.$date['mois']) ) {
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
					return 'FALSE';
				}
				else {
					$new_file_data = fopen($fichier_data,'wb+');
					if (fwrite($new_file_data,$data2write) === 'FALSE') {
						echo '<span style="color: red; float:right; font-weight:bold;">'.$GLOBALS['lang']['echec'].'</span>';
						return 'FALSE';
					} else {
						fclose($new_file_data);
						echo '<span style="color: green; float:right;">'.$GLOBALS['lang']['succes'].'</span>';
						return 'TRUE';
					}
				}
			}

			switch ($_FILES['xml_file']['error']) {
				case 3: $erreurs[] = $GLOBALS['lang']['img_phperr_partial'];		break;
				case 4: $erreurs[] = $GLOBALS['lang']['img_phperr_nofile'];			break;
				case 6: $erreurs[] = $GLOBALS['lang']['img_phperr_tempfolder'];	break;
				case 7: $erreurs[] = $GLOBALS['lang']['img_phperr_DiskWrite'];		break;
			}
			if (!empty($_FILES['xml_file']['type']) and ($_FILES['xml_file']['type'] != 'text/xml')) {
				$erreurs[] = $GLOBALS['lang']['file_format_error'];
				}

			if (empty($erreurs)) {
				echo '<form action="maintenance.php?do=backup" method="post" enctype="multipart/form-data">'."\n";
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
				$content_xml = trim(preg_replace("#<bt_backup_database>#", '', $content_xml));
				$items = explode('</bt_backup_item>', $content_xml);
				$nb_msg = sizeof($items);
				if ($nb_msg > 1) {
					// traitement des articles
					echo '<ul>';
					for ($msg = 0; $msg < $nb_msg -1; $msg++) {
						$msg_content = preg_replace('#(.*)<bt_backup_article>(.+)</bt_backup_article>(.*)#is', "$2", $items[$msg]);
						echo '<li class="bloc_article" style="margin: 2px auto;">';
						echo '<div style="border: gray dashed 1px;">';
						enregistrer_donnes($GLOBALS['dossier_data_articles'], $msg_content, 'article');
						echo '</div>';
						// pour chaque article : traitement des commentaires
						if (preg_match("#<bt_backup_comment>#",$items[$msg])) {
							$comms = explode('<bt_backup_comment>',$items[$msg]);
							$nb_comms = sizeof($comms);
							echo '<ul class="bloc_comment">';
							for ($com = 1; $com < $nb_comms; $com++) {
								$comms[$com] = preg_replace('#</bt_backup_comment>#', '', $comms[$com]);
								echo '<li class="comment" style="border: silver dashed 1px; margin: 1px;">';
								enregistrer_donnes($GLOBALS['dossier_data_commentaires'], $comms[$com], 'commentaire');
								echo '</li>';
							}
							echo '</ul>';
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				else {
					echo $GLOBALS['lang']['note_no_article'];
				}
			}
			// si erreurs
			else {
				echo '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :<ul>';
				foreach($erreurs as $erreur) {
					echo '<li>'.$erreur.'</li>';
				}
				echo '</ul></div>';
			}
		}
		// page restore : si aucun fichier spécié, on en demande un
		if ( !isset($_FILES['xml_file']) or !empty($erreurs) ) {
			echo '<form action="maintenance.php?do=backup" method="post" enctype="multipart/form-data"><fieldset class="pref">';
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
	// page 1 : choix de ce qu'on va faire
	else {
		echo '<form method="post" action="maintenance.php?do=backup"><fieldset class="pref valid-center">';
			echo legend($GLOBALS['lang']['legend_what_doyouwant'], 'legend-question');
			echo form_radio('quefaire', 'sauvegarde', 'sauvegarde', $GLOBALS['lang']['bak_save2xml']);
			echo form_radio('quefaire', 'restore', 'restore', $GLOBALS['lang']['bak_restorefromxml']);
			echo form_radio('quefaire', 'rien', 'rien', $GLOBALS['lang']['bak_nothing']);
			echo input_valider();
		echo '</fieldset></form>'."\n";
	}

}


/* ####################################################################  CLEAN TAG LIST  ####################################### */
if ((!empty($_GET['do']) and ($_GET['do'] == 'clntags')) or (empty($_GET['do']))) {

	echo '<form method="post" action="maintenance.php?do=clntags"><fieldset class="pref valid-center">';
		echo legend($GLOBALS['lang']['maint_clean_tag_list'], 'legend-question');

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
				if ($do == '1') {
					if (!(fichier_tags($usedtags_inline, '1') === 'TRUE')) {
						return '0';
					} else {
						return '1';
					}
				}
		}
		if (isset($_GET['do']) and $_GET['do'] == 'clntags') {
				echo hidden_input('clean','ok');
				if (isset($_POST['clean']) and $_POST['clean'] == 'ok') {
					$msg = clean_taglist('1');
				} else {
					$msg = clean_taglist('0');
				}
		} else {
			$msg = '0';
			echo '<p>'.$GLOBALS['lang']['maint_info_clntags'].'</p>';
		}
		if ($msg == '1') {
			redirection($_SERVER['PHP_SELF'].'?msg=confirm_tags_cleand');
		}
		echo input_valider();
	echo '</fieldset></form>'."\n";
}


/* ####################################################################  CHECK UPDATES  ######################################## */
if ((!empty($_GET['do']) and ($_GET['do'] == 'update')) or (empty($_GET['do']))) {

	echo '<form method="post" action="maintenance.php?do=update"><fieldset class="pref valid-center">';
		echo legend($GLOBALS['lang']['maint_chk_update'], 'legend-question');
		if (file_get_contents('http://lehollandaisvolant.net/blogotext/version.php')) {

		$last_version = trim(file_get_contents('http://lehollandaisvolant.net/blogotext/version.php'));
		if ($GLOBALS['version_timo'] == $last_version) {
			echo '<p>'.$GLOBALS['lang']['maint_update_youisgood'].'</p>';
		} else {
			echo '<p>'.$GLOBALS['lang']['maint_update_youisbad'].' ('.$last_version.')</p>';
			echo '<p>'.$GLOBALS['lang']['maint_update_go_dl_it'].' <a href="http://lehollandaisvolant.net/blogotext/">lehollandaisvolant.net/blogotext/</a>.';
		}
	} else {
			echo '<p><b>Impossible to check if updates are available or not.</b><br/>Please check by yourselve at <a href="http://lehollandaisvolant.net/blogotext/">lehollandaisvolant.net/blogotext/</a>.</p><p>If this error occur again, please send an email at the maitainer of Blogotext : timovneerden@gmail.com.</p>';
	}
	echo '</fieldset></form>'."\n";

}

footer();

?>

