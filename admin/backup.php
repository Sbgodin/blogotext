<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
error_reporting(E_ALL);
require_once '../inc/inc.php';
session_start() ;

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$ip = $_SERVER['REMOTE_ADDR'];
}
if ( (!isset($_SESSION['nom_utilisateur'])) or ($_SESSION['nom_utilisateur'] != $GLOBALS['identifiant'].$GLOBALS['mdp']) or (!isset($_SESSION['antivol'])) or ($_SESSION['antivol'] != md5($_SERVER['HTTP_USER_AGENT'].$ip)) or (!isset($_SESSION['timestamp'])) or ($_SESSION['timestamp'] < time()-1800)) {
	header('Location: logout.php');
	exit;
}
$_SESSION['timestamp'] = time();

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

afficher_top($GLOBALS['lang']['titre_backup']);
afficher_msg();
echo '<div id="top">';
echo '<ul id="nav">';
afficher_menu('index.php');
echo '</ul>';
echo '</div>';
echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// S A U V E G A R D E
if (isset($_POST['quefaire']) and ($_POST['quefaire'] == 'sauvegarde')) {
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
				$article= xml_billet(remove_ext($liste_fichiers));
				$data .= '<bt_backup_item>'."\n";
				$data .= '<bt_backup_article>'."\n";
				$data .= $article['xml'];
				$data .= '</bt_backup_article>'."\n";
				$commentaires = liste_commentaires($GLOBALS['dossier_data_commentaires'], $article['id']);
				if (!empty($commentaires)) {
					foreach ($commentaires as $id => $content) {
						$comment = xml_comment(remove_ext($content));
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
		}
		$fichier = 'backup-'.date('Y').date('m').date('d').'-'.substr(md5(rand(100,999)),3,5).'.xml';
		$path = $dossier_backup.'/'.$fichier;
		$new_file = fopen($path,'wb+');
		$data = afficher_tout_xml(table_derniers($GLOBALS['dossier_data_articles']));
		if (fwrite($new_file, $data) === FALSE) {
			echo $GLOBALS['lang']['err_file_write'];
			return FALSE;
		} else {
			fclose($new_file);
			chmod($path, 0666);
			echo '<form method="post" action="backup.php"><div>'."\n";
				echo '<fieldset class="pref">';
				legend($GLOBALS['lang']['bak_succes_save'], 'legend-tic');
				echo '<p>'.$GLOBALS['lang']['bak_youcannowsave'].'</p>'."\n";
				echo '<p style="text-align: center;"><a href="'.$path.'">'.$fichier.'</a></p>'."\n";
				hidden_input('filetodelete',$path);
				echo '</fieldset>'."\n";
				echo '<fieldset class="pref">';
				legend('&nbsp;', 'legend-question');
				select_yes_no('supprimer_fichier_source', '0', $GLOBALS['lang']['bak_delete_source']);
				echo '</fieldset>'."\n";
				input_valider();
			echo '</div></form>'."\n";
		}
	}
	creer_fich_xml();
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
			echo '<form action="backup.php" method="post" enctype="multipart/form-data">'."\n";
			echo '<fieldset class="pref valid-center">';
			legend($GLOBALS['lang']['bak_restor_done'], 'legend-tic');
			echo '<p>';
			echo $GLOBALS['lang']['bak_restor_done_mesage'];
			echo '</p>'."\n".'<p>'."\n";
			input_valider();
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
	// page restore : upload xml
	if ( !isset($_FILES['xml_file']) or !empty($erreurs) ) {
		echo '<form action="backup.php" method="post" enctype="multipart/form-data"><fieldset class="pref">';
		legend($GLOBALS['lang']['bak_choosefile'], 'legend-user');
		echo '<p>'."\n";
		echo '<input type="file" name="xml_file" /><br />'."\n";
		input_upload();
		hidden_input('quefaire','restore');
		echo '</p>'."\n";
		echo '</fieldset>'."\n";
		echo '</form>'."\n";
	}
}
// page 1 : choix de ce qu'on va faire
else {
	echo '<form method="post" action="backup.php"><fieldset class="pref valid-center">';
		legend($GLOBALS['lang']['legend_what_doyouwant'], 'legend-question');
		form_radio('quefaire', 'sauvegarde', 'sauvegarde', $GLOBALS['lang']['bak_save2xml']);
		form_radio('quefaire', 'restore', 'restore', $GLOBALS['lang']['bak_restorefromxml']);
		form_radio('quefaire', 'rien', 'rien', $GLOBALS['lang']['bak_nothing']);
		input_valider();
	echo '</fieldset></form>'."\n";
}


footer();
?>
