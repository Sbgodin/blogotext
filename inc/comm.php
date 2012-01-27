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

function liste_commentaires($dossier, $article_id, $statut) { // returns an ARRAY
	// the use of table_dernier() here is not recommended : it would heavilly affect Blogotext's speed.
	$date = decode_id($article_id);
	$year = date('Y');
	$month = date('m');
	$retour = array();
	for ($i = $date['annee']; $i <= $year; $i++) {
		if ($date['annee'] == $year) {
			$mois_deb = $date['mois'];
			$mois_fin = $month;
		} elseif ($date['annee'] < $year) {
			$mois_deb = '01';
			$mois_fin = '12';
		}
		for ($j = $mois_deb; $j <= $mois_fin ; $j++) {
			$j = str_pad($j, 2, "0", STR_PAD_LEFT);
			if (is_dir($dossier.'/'.$i.'/'.$j)) {
				$liste = parcourir_dossier($dossier.'/'.$i.'/'.$j.'/');
				if (!empty($liste)) {
					foreach ($liste as $comm) {
						if (preg_match('#^\d{14}\.'.$GLOBALS['ext_data'].'$#', $comm)) {
							$path = $dossier.'/'.get_path(get_id($comm));
					  		if (parse_xml($path, $GLOBALS['data_syntax']['comment_article_id']) == $article_id ) {
								if ( $statut != '') {
									$actual_statut = parse_xml($path, $GLOBALS['data_syntax']['comment_status']);
							  		if ($actual_statut == $statut or $actual_statut == '' ) { // test 'or' for old comments w/o $statut (BT version < 24)
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
	return $retour;
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

function protect($text) {
	$return = htmlspecialchars(stripslashes(clean_txt($text)));
	return $return;
}

/* generate the comment form, with params from the admin-side and the visiter-side */
function afficher_form_commentaire($article_id, $mode, $erreurs='', $comm_id='') {
	$GLOBALS['form_commentaire'] = '';
	if (isset($_POST['_verif_envoi']) and !empty($erreurs)) {
		$GLOBALS['form_commentaire'] = '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :'."\n" ;
		$GLOBALS['form_commentaire'].= '<ul><li>'."\n";
		$GLOBALS['form_commentaire'].=  implode('</li><li>', $erreurs);
		$GLOBALS['form_commentaire'].=  '</li></ul></div>'."\n";
		$defaut = array(
			'auteur' => protect($_POST['auteur']),
			'email' => protect($_POST['email']),
			'webpage' => protect($_POST['webpage']),
			'commentaire' => protect($_POST['commentaire']),
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
/* WTF is that here ?? 
			if (!empty($actual_comment['contenu_wiki'])) {
				$commentaire = $actual_comment['contenu_wiki'];
			} else {
				$commentaire = '';
			}
*/
			$defaut = array(
				'auteur' => protect($actual_comment['auteur']),
				'email' => protect($actual_comment['email']),
				'webpage' => protect($actual_comment['webpage']),
				'commentaire' => htmlspecialchars($actual_comment['contenu_wiki']),
				'status' => protect($actual_comment['status']),
				);

		}

	} elseif (isset($_POST['previsualiser'])) { // parses the comment, but does not save it in a file
			$defaut = array(
				'auteur' => protect($_POST['auteur']),
				'email' => protect($_POST['email']),
				'webpage' => protect($_POST['webpage']),
				'commentaire' => protect($_POST['commentaire']),
			);
			$comm['contenu'] = formatage_commentaires(stripslashes(htmlspecialchars(clean_txt($_POST['commentaire']))));
			$comm['id'] = date('YmdHis');
			$comm['email'] = protect($_POST['email']);
			$comm['auteur'] = protect($_POST['auteur']);
			$comm['webpage'] = protect($_POST['webpage']);
			$comm['anchor'] = article_anchor($comm['id']);
			$comm['auteur_lien'] = ($comm['webpage'] != '') ? '<a href="'.$comm['webpage'].'" class="webpage">'.$comm['auteur'].'</a>' : $comm['auteur'];
			$comm['lienreply'] = '<a href="#form-commentaire" onclick="reply(\'[b]@['.$comm['auteur'].'|#'.$comm['anchor'].'] :[/b] \'); ">@</a>';

			$GLOBALS['form_commentaire'] .= '<div id="erreurs"><ul><li>Prévisualisation&nbsp;:</ul></li></div>'."\n";
			$GLOBALS['form_commentaire'] .= '<div id="previsualisation">'."\n";
			$GLOBALS['form_commentaire'] .= conversions_theme_commentaire(charger_template($GLOBALS['theme_article'], $GLOBALS['boucles']['commentaires'], 'liste'), $comm);
			$GLOBALS['form_commentaire'] .= '</div>'."\n";


	} else {
		if (isset($_POST['_verif_envoi'])) {
			header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#top'); // redirection anti repostage;
		}
		$auteur_c = (isset($_COOKIE['auteur_c'])) ? protect($_COOKIE['auteur_c']) : '' ;
		$email_c = (isset($_COOKIE['email_c'])) ? protect($_COOKIE['email_c']) : '' ;
		$webpage_c = (isset($_COOKIE['webpage_c'])) ? protect($_COOKIE['webpage_c']) : '' ;

		$defaut = array(
			'auteur' => "$auteur_c",
			'email' => $email_c,
			'webpage' => $webpage_c,
			'commentaire' => '',
			'captcha' => '',
		);
	}

	// BEGIN GENERATING FORM
	// prelim
	$label_email = ($GLOBALS['require_email'] == 1) ? $GLOBALS['lang']['comment_email_required'] : $GLOBALS['lang']['comment_email']; 
	$required = ($GLOBALS['require_email'] == 1) ? 'required=""' : '';
	$rand = ($mode == 'admin') ? substr(md5(rand(1000,9999)),0,5) : '';
	$cookie_checked = (isset($_COOKIE['cookie_c']) and $_COOKIE['cookie_c'] == 1) ? ' checked="checked"' : '';
	$subscribe_checked = (isset($_COOKIE['subscribe_c']) and $_COOKIE['subscribe_c'] == 1) ? ' checked="checked"' : '';

	// begin of < form > , with some additional stuff on comment "edit".
	if ($mode == 'admin' and isset($actual_comment)) { // edit
		$form_open_tag = "\n".'<form id="form-commentaire-'.$actual_comment['id'].'" class="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#erreurs" style="display:none;">'."\n";
		$form_open_tag .= "\t".'<fieldset class="syst">'."\n";
		$form_open_tag .= "\t\t".hidden_input('is_it_edit', 'yes');
		$form_open_tag .= "\t\t".hidden_input('comment_id', $actual_comment['id']);
		$form_open_tag .= "\t\t".hidden_input('status', $actual_comment['status']);
		$_SESSION['some_time'] = time();
	//	$form_open_tag .= "\t\t".hidden_input('comm_id', $actual_comment['id']);
		$form_open_tag .= "\t\t".hidden_input('security_coin', md5($actual_comment['id'].$_SESSION['some_time']));
		$form_open_tag .= "\t".'</fieldset><!--end syst-->'."\n";
	} else {
		$form_open_tag = "\n".'<form id="form-commentaire" class="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#erreurs" >'."\n";
	}
	if ($mode == 'admin') {
		$form_open_tag .= "\t\t".hidden_input('comment_article_id', $article_id);
	}

	// Formulaire commun
	$form_common  = "\t".'<fieldset class="field">'."\n";
	$form_common .= "\t\t".'<textarea class="commentaire" name="commentaire" required="" placeholder="'.$GLOBALS['lang']['label_commentaire'].'" id="commentaire'.$rand.'" cols="50" rows="10" tabindex="10">'.$defaut['commentaire'].'</textarea>'."\n";
	$form_common .= "\t".'</fieldset>'."\n";
	$form_common .= "\t".'<fieldset class="formatbut">'."\n";
	$form_common .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-gras'].'" type="button" value="B" onclick="insertTag(\'[b]\',\'[/b]\',\'commentaire'.$rand.'\');" />'."\n";
	$form_common .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-ital'].'" type="button" value="I" onclick="insertTag(\'[i]\',\'[/i]\',\'commentaire'.$rand.'\');" />'."\n";
	$form_common .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-soul'].'" type="button" value="U" onclick="insertTag(\'[u]\',\'[/u]\',\'commentaire'.$rand.'\');" />'."\n";
	$form_common .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-barr'].'" type="button" value="S" onclick="insertTag(\'[s]\',\'[/s]\',\'commentaire'.$rand.'\');" />'."\n";
	$form_common .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-lien'].'" type="button" value="'.$GLOBALS['lang']['wiki_lien'].'" onclick="insertTag(\'[\',\'|http://]\',\'commentaire'.$rand.'\');"/>'."\n";
	$form_common .= "\t\t".'<input title="'.$GLOBALS['lang']['bouton-cita'].'" type="button" value="'.$GLOBALS['lang']['wiki_quote'].'" onclick="insertTag(\'[quote]\',\'[/quote]\',\'commentaire'.$rand.'\');" />'."\n";
	$form_common .= "\t\t".'<input title="Code" type="button" value="Code" onclick="insertTag(\'[code]\',\'[/code]\',\'commentaire'.$rand.'\');" />'."\n";
	$form_common .= "\t\t".'<input class="pm" type="button" value="−" onclick="resize(\'commentaire'.$rand.'\', -40); return false;" />'."\n";
	$form_common .= "\t\t".'<input class="pm" type="button" value="+" onclick="resize(\'commentaire'.$rand.'\', 40); return false;" />'."\n";
	$form_common .= "\t".'</fieldset><!--end formatbut-->'."\n";
	$form_common .= "\t".'<fieldset class="infos">'."\n";
	$form_common .= "\t\t".label('auteur', $GLOBALS['lang']['comment_nom'].' :');
	$form_common .= "\t\t".'<input type="text" name="auteur" placeholder="'.$GLOBALS['lang']['comment_nom'].'" required="" id="auteur" value="'.$defaut['auteur'].'" size="25"  tabindex="12"/><br/>'."\n";
	$form_common .= "\t\t".label('email', $label_email.' :');
	$form_common .= "\t\t".'<input type="email" name="email" placeholder="'.$label_email.' " id="email" '.$required.' value="'.$defaut['email'].'" size="25"  tabindex="14"/><br/>'."\n";
	$form_common .= "\t\t".label('webpage', $GLOBALS['lang']['comment_webpage'].' :');
	$form_common .= "\t\t".'<input type="url" name="webpage" placeholder="'.$GLOBALS['lang']['comment_webpage'].'" id="webpage" value="'.$defaut['webpage'].'" size="25"  tabindex="16"/><br/>'."\n";
	$form_common .= ($mode != 'admin') ? "\t\t".label('captcha', $GLOBALS['lang']['comment_captcha'].' <b>'.en_lettres($_SESSION['captx']).'</b> + <b>'.en_lettres($_SESSION['capty']).'</b> ?') : '';
	$form_common .= ($mode != 'admin') ? "\t\t".'<input type="text" id="captcha" name="captcha" placeholder="'.$GLOBALS['lang']['comment_captcha_usenumbers'].'" value="" size="25" tabindex="18" /><br/>'."\n" : '';
	$form_common .= "\t\t".hidden_input('_verif_envoi', '1');
	$form_common .= "\t".'</fieldset><!--end info-->'."\n";
	if ($mode != 'admin') {
		$form_common .= "\t".'<fieldset class="cookie"><!--begin cookie asking -->'."\n";
		$form_common .= "\t\t".'<input class="check" type="checkbox" id="allowcookie" name="allowcookie"'.$cookie_checked.' tabindex="24" />'.label('allowcookie', $GLOBALS['lang']['comment_cookie']).'<br/>'."\n";
		$form_common .= "\t\t".'<input class="check" type="checkbox" id="subscribe" name="subscribe"'.$subscribe_checked.' tabindex="25" />'.label('subscribe', $GLOBALS['lang']['comment_subscribe'])."\n";
		$form_common .= "\t".'</fieldset><!--end cookie asking-->'."\n";
	}
	$form_common .= "\t".'<fieldset class="buttons">'."\n";
	if ($mode == 'admin' and isset($actual_comment)) { // edit
		$form_common .= "\t\t".'<input class="submit submit-suppr" type="submit" name="supprimer_comm" value="'.$GLOBALS['lang']['supprimer'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_comment'].'\')" tabindex="20" />'."\n";
		$text_bouton = ($actual_comment['status'] == 1) ? $GLOBALS['lang']['desactiver'] : $GLOBALS['lang']['activer'];
		$form_common .= "\t\t".'<input class="submit" type="submit" name="activer_comm" value="'.$text_bouton.'" tabindex="22" />'."\n";
	}

	$form_common .= "\t\t".'<input class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['envoyer'].'" tabindex="26" />'."\n";
	if ($mode != 'admin') {
		$form_common .= "\t\t".'<input class="submit" type="submit" name="previsualiser" value="'.$GLOBALS['lang']['preview'].'" tabindex="28" />'."\n";
	}
	$form_common .= "\t".'</fieldset><!--end buttons-->'."\n";

	// mode ADMIN : don't care if comments are closed or not.
	if ($mode == 'admin') {
		$GLOBALS['form_commentaire'] .= $form_open_tag;
		$GLOBALS['form_commentaire'] .= $form_common;
		$GLOBALS['form_commentaire'] .= '</form>'."\n";
	}
	// mode PUBLIC : check if comments closed or not
	else {
		// ALLOW COMMENTS : ON
		if (parse_xml($GLOBALS['dossier_articles'].'/'.get_path($article_id), $GLOBALS['data_syntax']['article_allow_comments']) == '1' and $GLOBALS['global_com_rule'] == '0') {
			$GLOBALS['form_commentaire'] .= $form_open_tag;
			$GLOBALS['form_commentaire'] .= $form_common;
			if ($GLOBALS['comm_defaut_status'] == '0') { // petit message en cas de moderation a-priori
				$GLOBALS['form_commentaire'] .= "\t\t".'<div class="need-validation">'.$GLOBALS['lang']['remarque'].' :'."\n" ;
				$GLOBALS['form_commentaire'] .= "\t\t\t".$GLOBALS['lang']['comment_need_validation']."\n";
				$GLOBALS['form_commentaire'] .= "\t\t".'</div>'."\n";
			}
			$GLOBALS['form_commentaire'] .= '</form>'."\n";
		}
		// ALLOW COMMENTS : OFF
		else {
			$GLOBALS['form_commentaire'] .= ($mode != 'admin') ?'<p>'.$GLOBALS['lang']['comment_not_allowed'].'</p>'."\n" : '';
		}
	}
}

// ceci est traité coté Admin seulement car c'est appellé lors de l'édition ou la suppression d'un commentaire:
function traiter_form_commentaire($dossier, $commentaire) {
	$msg_param_to_trim = (isset($_GET['msg']))	? '&msg='.$_GET['msg'] : '';
	$query_string = str_replace($msg_param_to_trim, '', $_SERVER['QUERY_STRING']);

	$post_id = (isset($_GET['post_id']) and preg_match('#\d{14}#', $_GET['post_id'])) ? 'post_id='.$commentaire[$GLOBALS['data_syntax']['comment_article_id']].'&' : '';
	if (isset($_POST['enregistrer']) or isset($_POST['activer_comm'])) {
		if (isset($_POST['activer_comm'])) {
			if ( htmlspecialchars($_POST['security_coin']) == md5($_POST['comment_id'].$_SESSION['some_time']) ) {
				$commentaire[$GLOBALS['data_syntax']['comment_status']] = abs($commentaire[$GLOBALS['data_syntax']['comment_status']]-1);
			}
		}
		if (fichier_data($dossier, $commentaire) !== FALSE) {
			if (isset($_POST['editer'])) {
				redirection($_SERVER['PHP_SELF'].'?'.$query_string.'&msg=confirm_comment_edit');
			} elseif (isset($_POST['activer_comm'])) {
				send_emails($commentaire[$GLOBALS['data_syntax']['comment_id']]); // send emails (comment activated)
				redirection($_SERVER['PHP_SELF'].'?'.$query_string.'&msg=confirm_comment_valid');
			} elseif (isset($_POST['enregistrer'])) {
				send_emails($commentaire[$GLOBALS['data_syntax']['comment_id']]); // send emails (new comment posted by the admin)
				redirection($_SERVER['PHP_SELF'].'?'.$query_string.'&msg=confirm_comment_ajout');
			}
		} else {
			erreur('Ecriture impossible');
			exit;
		}
	}
	elseif (isset($_POST['supprimer_comm'])) {
		if ( htmlspecialchars($_POST['security_coin']) == md5($_POST['comment_id'].$_SESSION['some_time']) ) {
			if (unlink($GLOBALS['BT_ROOT_PATH'].$GLOBALS['dossier_commentaires'].'/'.get_path(htmlspecialchars($_POST['comment_id'])))) {
				redirection($_SERVER['PHP_SELF'].'?'.$query_string.'&msg=confirm_comment_suppr');
			} else {
				redirection($_SERVER['PHP_SELF'].'?'.$query_string.'&msg=error_comment_suppr_impos');
			}
		}
	}
}

?>
