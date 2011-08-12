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

function liste_commentaires($dossier, $article_id, $statut) {
	$date = decode_id($article_id);
	$liste = array();
	$year = date('Y');
	$month = date('m');
	for ($i = $date['annee']; $i <= $year; $i++) {
		if ($date['annee'] == $year) {
			$mois_deb = $date['mois'];
			$mois_fin = $month;
		} elseif ($date['annee'] < $year) {
			$mois_deb = '01';
			$mois_fin = '12';
		}
		for ($j = $mois_deb; $j <= $mois_fin ; $j++) {
			$j = nombre_formate($j);
			if (is_dir($dossier.'/'.$i.'/'.$j)) {
				$liste = parcourir_dossier($dossier.'/'.$i.'/'.$j.'/');
				if ($liste != '') {
					foreach ($liste as $comm) {
						if (preg_match('#^\d{14}\.'.$GLOBALS['ext_data'].'$#', $comm)) {
							$path = $dossier.'/'.get_path_no_ext($comm);
					  		if (parse_xml($path, $GLOBALS['data_syntax']['comment_article_id']) == $article_id ) {
								if ( $statut != '') {
									$actual_statut = parse_xml($path, $GLOBALS['data_syntax']['comment_status']);
							  		if ($actual_statut == $statut or $actual_statut == '' ) { // test empty is for old comments that haven't a $statut
								 		$retour[] = $comm;
									}
								} else {
								 		$retour[] = $comm;
								}
							}
						}
					}
				}
			}
		}
	}
	if (isset($retour)) {
		return $retour;
	}
}

function en_lettres($captchavalue) {
	switch($captchavalue) {
		case 0 : $lettres = $GLOBALS['lang']['0']; break;
		case 1 : $lettres = $GLOBALS['lang']['1']; break;
		case 2 : $lettres = $GLOBALS['lang']['2']; break;
		case 3 : $lettres = $GLOBALS['lang']['3']; break;
		case 4 : $lettres = $GLOBALS['lang']['4']; break;
		case 5 : $lettres = $GLOBALS['lang']['5']; break;
		case 6 : $lettres = $GLOBALS['lang']['6']; break;
		case 7 : $lettres = $GLOBALS['lang']['7']; break;
		case 8 : $lettres = $GLOBALS['lang']['8']; break;
		case 9 : $lettres = $GLOBALS['lang']['9']; break;
		default: $lettres = ""; break;
	}
return $lettres;
}


function afficher_form_commentaire($article_id, $mode, $erreurs='', $comm_id='') {
	if (empty($_POST['supprimer_comm'])) {
		$article = init_billet($mode, $article_id);


		$GLOBALS['form_commentaire'] = '';

		if ( (isset($_POST['_verif_envoi'])) and (isset($erreurs)) and ($erreurs != '') ) {
			$GLOBALS['form_commentaire'] = '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :'."\n" ;
			$GLOBALS['form_commentaire'].= '<ul><li>'."\n";
			$GLOBALS['form_commentaire'].=  implode('</li><li>', $erreurs);
			$GLOBALS['form_commentaire'].=  '</li></ul></div>'."\n";
			$defaut = array(
				'auteur' => htmlspecialchars(stripslashes(clean_txt($_POST['auteur']))),
				'email' => htmlspecialchars(stripslashes(clean_txt($_POST['email']))),
				'webpage' => htmlspecialchars(stripslashes(clean_txt($_POST['webpage']))),
				'commentaire' => htmlspecialchars(stripslashes(clean_txt($_POST['commentaire']))),
			);

		} elseif (isset($mode) and $mode == 'admin') {
			if (empty($comm_id)) {
				$defaut = array(
					'auteur' => $GLOBALS['auteur'],
					'email' => $GLOBALS['email'],
					'webpage' => $GLOBALS['racine'],
					'commentaire' => '',
					);
			} else {
				$actual_comment = init_comment($mode, $comm_id);
				if (!empty($actual_comment['contenu_wiki'])) {
					$commentaire = $actual_comment['contenu_wiki'];
				} else {
					$commentaire = '';
				}
				$defaut = array(
					'auteur' => $actual_comment['auteur'],
					'email' => $actual_comment['email'],
					'webpage' => $actual_comment['webpage'],
					'commentaire' => $actual_comment['contenu_wiki'],
					'status' => $actual_comment['status'],
					);

			}		
		} else {
			if (isset($_POST['_verif_envoi'])) {
					header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#top'); // redirection anti repostage;
				}
			if (isset($_COOKIE['auteur_c'])) {
				$auteur_c = htmlspecialchars(stripslashes(clean_txt($_COOKIE['auteur_c'])));
			} else {
				$auteur_c = "";
			}
			if (isset($_COOKIE['email_c'])) {
				$email_c = htmlspecialchars(stripslashes(clean_txt($_COOKIE['email_c'])));
			} else {
				$email_c = "";
			}
			if (isset($_COOKIE['webpage_c'])) {
				$webpage_c = htmlspecialchars(stripslashes(clean_txt($_COOKIE['webpage_c'])));
			} else {
				$webpage_c = "";
				}
			$defaut = array(
				'auteur' => "$auteur_c",
				'email' => $email_c,
				'webpage' => $webpage_c,
				'commentaire' => '',
				'captcha' => '',
				);
		}
		if ( isset($article_id) ) {
			// ALLOW COMMENTS ON
			if ($article['allow_comments'] == '1' and $GLOBALS['global_com_rule'] == '0') {
				if ( isset($mode) and $mode == 'admin' and isset($actual_comment) ) {
					$GLOBALS['form_commentaire'] .= "\n".'<form id="form-commentaire-'.$actual_comment['id'].'" class="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#erreurs" style="display:none;">'."\n";
					}
			$label_email = ($GLOBALS['require_email'] == 1) ? $GLOBALS['lang']['comment_email_required'] : $GLOBALS['lang']['comment_email']; 
			$required = ($GLOBALS['require_email'] == 1) ? 'required="" ' : ''; 


			$GLOBALS['form_commentaire'] .= "\n".'<form id="form-commentaire" class="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#erreurs" >'."\n";
			$GLOBALS['form_commentaire'] .= "\t".'<fieldset class="field">'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<textarea class="commentaire" name="commentaire" required="" placeholder="'.$GLOBALS['lang']['label_commentaire'].'" id="commentaire" cols="50" rows="10">'.$defaut['commentaire'].'</textarea>'."\n";
			$GLOBALS['form_commentaire'] .= "\t".'</fieldset>'."\n";
			$GLOBALS['form_commentaire'] .= "\t".'<fieldset class="formatbut">'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-gras'].'" type="button" value="B" onclick="insertTag(\'[b]\',\'[/b]\',this);" />'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-ital'].'" type="button" value="I" onclick="insertTag(\'[i]\',\'[/i]\',this);" />'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-soul'].'" type="button" value="U" onclick="insertTag(\'[u]\',\'[/u]\',this);" />'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-barr'].'" type="button" value=" S " onclick="insertTag(\'[s]\',\'[/s]\',this);" />'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-lien'].'" type="button" value="'.$GLOBALS['lang']['wiki_lien'].'" onclick="insertTag(\'[\',\'|http://]\',this);"/>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-cita'].'" type="button" value="'.$GLOBALS['lang']['wiki_quote'].'" onclick="insertTag(\'[quote]\',\'[/quote]\',this);" />'."\n";
			$GLOBALS['form_commentaire'] .= "\t".'</fieldset><!--end formatbut-->'."\n";

			$GLOBALS['form_commentaire'] .= "\t".'<fieldset class="infos">'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<label for="auteur">'.$GLOBALS['lang']['comment_nom'].' :</label>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input type="text" name="auteur" placeholder="'.$GLOBALS['lang']['comment_nom'].'" required="" id="auteur" value="'.$defaut['auteur'].'" size="25" /><br/>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<label for="email">'.$label_email.' :</label>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input type="email" name="email" placeholder="'.$label_email.'"id="email" '.$required.'value="'.$defaut['email'].'" size="25" /><br/>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<label for="webpage">'.$GLOBALS['lang']['comment_webpage'].' :</label>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input type="url" name="webpage" placeholder="'.$GLOBALS['lang']['comment_webpage'].'" id="webpage" value="'.$defaut['webpage'].'" size="25" /><br/>'."\n";
			$GLOBALS['form_commentaire'] .= "\t\t".'<input type="hidden" name="_verif_envoi" value="1" />'."\n";

			if (isset($mode) and $mode == 'admin') { // admin
				$GLOBALS['form_commentaire'] .= "\t".'</fieldset><!--end info-->'."\n";
				$GLOBALS['form_commentaire'] .= "\t".'<fieldset class="buttons">'."\n";
				if (isset($actual_comment)) { // c'est une édition et non une création
					$GLOBALS['form_commentaire'] .= "\t\t".'<input type="hidden" name="is_it_edit" value="yes" />'."\n";
					$GLOBALS['form_commentaire'] .= "\t\t".'<input type="hidden" name="comment_id" value="'.$actual_comment['id'].'" />'."\n";
					$GLOBALS['form_commentaire'] .= "\t\t".'<input type="hidden" name="status" value="'.$actual_comment['status'].'" />'."\n";
					$GLOBALS['form_commentaire'] .= "\t\t".'<input class="submit" type="submit" name="editer" value="'.$GLOBALS['lang']['enregistrer'].'" />'."\n";
					$GLOBALS['form_commentaire'] .= "\t\t".'<br style="clear: right;"/>'."\n";
				} else {
					$GLOBALS['form_commentaire'] .= "\t\t".'<p id="wiki" ><a href="inc/wiki.php" onclick="ouvre(\'../inc/wiki.php\');return false">'.$GLOBALS['lang']['label_wiki'].'</a></p>'."\n";
					$GLOBALS['form_commentaire'] .= "\t\t".'<input class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['envoyer'].'" />'."\n";
				}
				$GLOBALS['form_commentaire'] .= "\t".'</fieldset><!--end buttons-->'."\n";
			} else { // not admin
				$GLOBALS['form_commentaire'] .= "\t\t".'<label for="captcha">'.$GLOBALS['lang']['comment_captcha'].' <b>'.en_lettres($_SESSION['captx']).'</b> + <b>'.en_lettres($_SESSION['capty']).'</b> ?</label>'."\n";
				$GLOBALS['form_commentaire'] .= "\t\t".'<input type="text" id="captcha" name="captcha" required="" value="" size="25" /><br/>'."\n";
				$GLOBALS['form_commentaire'] .= "\t".'</fieldset><!--end info-->'."\n";
				$GLOBALS['form_commentaire'] .= "\t".'<fieldset class="buttons">'."\n";
				$GLOBALS['form_commentaire'] .= "\t\t".'<p id="wiki" ><a href="inc/wiki.php" onclick="ouvre(\'inc/wiki.php\');return false">'.$GLOBALS['lang']['label_wiki'].'</a></p>'."\n";
				$GLOBALS['form_commentaire'] .= "\t\t".'<input class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['envoyer'].'" />'."\n";
				if ($GLOBALS['comm_defaut_status'] == '0')  {
					$GLOBALS['form_commentaire'] .= "\t\t".'<div class="need-validation">'.$GLOBALS['lang']['remarque'].' :'."\n" ;
					$GLOBALS['form_commentaire'] .= "\t\t\t".$GLOBALS['lang']['comment_need_validation']."\n";
					$GLOBALS['form_commentaire'] .= "\t\t".'</div>'."\n";
				}
				$GLOBALS['form_commentaire'] .= "\t".'</fieldset><!--end buttons-->'."\n";
			}
			$GLOBALS['form_commentaire'] .= '</form>'."\n";
			// ALLOW COMMENTS OFF
			} else {
				$GLOBALS['form_commentaire'] .= '<p>'.$GLOBALS['lang']['comment_not_allowed'].'</p>'."\n";
			}
		}
	} elseif (!empty($_POST['supprimer_comm'])) {
		echo 'seeing this is not good…';
	}
}

function traiter_form_commentaire($dossier, $commentaire) {
	if (isset($_POST['enregistrer']) or isset($_POST['editer']) ) {
		if (fichier_data($dossier, $commentaire) !== FALSE) {
			if (isset($_POST['editer'])) {
				redirection($_SERVER['PHP_SELF'].'?post_id='.$commentaire[$GLOBALS['data_syntax']['comment_article_id']].'&msg=confirm_comment_edit');
			} else {
				redirection($_SERVER['PHP_SELF'].'?post_id='.$commentaire[$GLOBALS['data_syntax']['comment_article_id']].'&msg=confirm_comment_ajout');
			}
		} else {
			erreur('Ecriture impossible');
			exit;
		}
	}
	elseif (isset($_POST['supprimer_comm'])) {
		if ( htmlspecialchars($_POST['security_coin']) == md5($_POST['comm_id'].$_SESSION['some_time']) ) {
			$commentaire = htmlspecialchars($_POST['comm_id']);
			$article = (!empty($_GET['post_id'])) ? 'post_id='.htmlspecialchars($_GET['post_id']).'&' : '';
			$comment_file = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'].'/'.get_path($commentaire);
			if (unlink($comment_file)) {
				redirection($_SERVER['PHP_SELF'].'?'.$article.'msg=confirm_comment_suppr');
			} else {
				redirection($_SERVER['PHP_SELF'].'?'.$article.'errmsg=error_comment_suppr_impos');
			}
		}
	}
	elseif (isset($_POST['activer_comm'])) {
		if ( htmlspecialchars($_POST['security_coin']) == md5($_POST['comm_id'].$_SESSION['some_time']) ) {
			$commentaire = htmlspecialchars($_POST['comm_id']);
			$article = (!empty($_GET['post_id'])) ? 'post_id='.htmlspecialchars($_GET['post_id']).'&' : '';
			$comment_file = $GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'].'/'.get_path($commentaire);
			if (validate_comment($comment_file, $_POST['activer_comm_choix'])) {
				redirection($_SERVER['PHP_SELF'].'?'.$article.'msg=confirm_comment_valid');
			} else {
				redirection($_SERVER['PHP_SELF'].'?'.$article.'errmsg=error_comment_edit');
			}
		}
	}
}

?>
