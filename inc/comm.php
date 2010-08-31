<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
#               2010 Timo Van Neerden
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function liste_commentaires($dossier, $article_id) {
	$date = decode_id($article_id);
	$liste= array();
	$year = date('Y');
	$month = date('m');
	for ($i = $date['annee']; $i <= $year; $i++) {
		if ($date['annee'] == $year) {
			for ($j = $date['mois']; $j <= $month ; $j++) {
				if (strlen($j) == '1') {
					$j = '0'.$j;
				}
				if(is_dir($dossier.'/'.$i.'/'.$j)) {
					$liste = (parcourir_dossier($dossier.'/'.$i.'/'.$j.'/'));
					if ($liste != "") {
						foreach ($liste as $comm) {
							if (preg_match('#'.$GLOBALS['ext_data'].'$#',$comm)) {
								$path = $dossier.'/'.get_path_no_ext($comm);
								$syntax_version= get_version($path);
						  		if (parse_xml($path, $GLOBALS['data_syntax']['comment_article_id'][$syntax_version]) == $article_id )  {
							 		$retour[] =$comm;
								}
							}
						}
					}
				}
			}
		}
		elseif ($date['annee'] < $year) {
			for ($j = '01'; $j <= '12' ; $j++) {
				if (strlen($j) == '1') {
					$j = '0'.$j;
				}
				if(is_dir($dossier.'/'.$i.'/'.$j)) {
					$liste = (parcourir_dossier($dossier.'/'.$i.'/'.$j.'/'));
					if ($liste != "") {
						foreach ($liste as $comm) {
							if (preg_match('#'.$GLOBALS['ext_data'].'$#',$comm)) {
								$path = $dossier.'/'.get_path_no_ext($comm);
								$syntax_version= get_version($path);
						  		if (parse_xml($path, $GLOBALS['data_syntax']['comment_article_id'][$syntax_version]) == $article_id )  {
							 		$retour[] =$comm;
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

function liste_derniers_comm($nb_comm) {
	$dossier = $GLOBALS['dossier_commentaires'];
	$liste= array();
	$year = date('Y');
	$month = date('m');
	if (date('m') == '01') {
		$month = '12';
		$year = date('Y')-1;
	}
	for ($i = $year; $i <= date('Y'); $i++) {
		for ($j = $month-1; $j <= date('m') ; $j++) {
			if (strlen($j) == '1') {
				$j = '0'.$j;
			}
			if(is_dir($dossier.'/'.$i.'/'.$j)) {
				$liste = (parcourir_dossier($dossier.'/'.$i.'/'.$j.'/'));
				if ($liste != "") {
					foreach ($liste as $comm) {
						if (preg_match('#'.$GLOBALS['ext_data'].'$#',$comm)) {
							$path = $dossier.'/'.get_path_no_ext($comm);
							$syntax_version= get_version($path);
					 		$retour[] =$comm;
						}
					}
				}
			}
		}
	}
	if (isset($retour)) {
		$retour = array_slice($retour, -$nb_comm, $nb_comm);
		rsort($retour);
		return $retour;
	}
}

function en_lettres($captchavalue) {

	switch($captchavalue) {
		case '0': $lettres = $GLOBALS['lang']['0']; break;
		case '1': $lettres = $GLOBALS['lang']['1']; break;
		case '2': $lettres = $GLOBALS['lang']['2']; break;
		case '3': $lettres = $GLOBALS['lang']['3']; break;
		case '4': $lettres = $GLOBALS['lang']['4']; break;
		case '5': $lettres = $GLOBALS['lang']['5']; break;
		case '6': $lettres = $GLOBALS['lang']['6']; break;
		case '7': $lettres = $GLOBALS['lang']['7']; break;
		case '8': $lettres = $GLOBALS['lang']['8']; break;
		case '9': $lettres = $GLOBALS['lang']['9']; break;
		default: $lettres = ""; break;
	}
return $lettres;
}

function afficher_commentaire($comment, $with_link) {
	$date = decode_id($comment['id']);
		echo '<div class="commentbloc" >'."\n";
		echo '<h3 class="titre-commentaire">'.$comment['auteur'].'</h3>'."\n";
		echo '<p class="email"><a href="mailto:'.$comment['email'].'">'.$comment['email'].'</a></p>'."\n";
		if ($with_link == 1) {
			echo '<p class="lien_article_de_com"><a href="commentaires.php?post_id='.$comment['article_id'].'">'.get_titre($comment['article_id']).'</a></p>'."\n";
		}
		echo '<p class="date">'.date_formate($comment['id']).', '.heure_formate($comment['id']).'</p>';
		echo $comment['contenu'];
		echo "\n".'<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';
			echo '<p>';
				input_supprimer_comment();
				hidden_input('comm_id', $comment['id']);
				$time = time();
				$_SESSION['time_supprimer_commentaire'] = $time;
				hidden_input('security_coin', md5($comment['id'].$time));
				echo '<br style="clear: right;"/>';
			echo '</p>';
		echo '</form>';
	echo '</div>'."\n";
		if (isset($_POST['suppr-commentaire'])) {
			echo 'ok'.$comment['id'];
		}
}

function supprimer_commentaire($article_id, $comment_id) {
$loc_comment= '../'.$GLOBALS['dossier_commentaires'].'/'.get_path_no_ext($comment_id).'.'.$GLOBALS['ext_data'];
	if (unlink($loc_comment)) {
		redirection($_SERVER['PHP_SELF'].'?post_id='.$article_id.'&msg=confirm_comment_suppr');
	} else {
		erreur('Impossible de supprimer le fichier');
		redirection($_SERVER['PHP_SELF'].'?post_id='.$article_id.'&errmsg=error_comment_suppr_impos');
	}
}

function afficher_form_commentaire($article_id, $mode, $allow_comments, $erreurs='') {
		$GLOBALS['form_commentaire'] = '';
	if ($erreurs) {
		$GLOBALS['form_commentaire'] = '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :'."\n" ;
		$GLOBALS['form_commentaire'].= '<ul><li>'."\n";
		$GLOBALS['form_commentaire'].=  implode('</li><li>', $erreurs);
		$GLOBALS['form_commentaire'].=  '</li></ul></div>'."\n";
	}

	if ( (isset($_POST['_verif_envoi'])) AND (isset($erreurs)) AND ($erreurs != '') ) {
		$defaut = array(
			'auteur' => htmlspecialchars(stripslashes(clean_txt($_POST['auteur']))),
			'email' => htmlspecialchars(stripslashes(clean_txt($_POST['email']))),
			'webpage' => htmlspecialchars(stripslashes(clean_txt($_POST['webpage']))),
			'commentaire' => htmlspecialchars(stripslashes(clean_txt($_POST['commentaire']))),
			'captcha' => htmlspecialchars(stripslashes(clean_txt($_POST['captcha']))),
			);

	} elseif (isset($mode) AND $mode == 'admin') {
		$defaut = array(
			'auteur' => $GLOBALS['auteur'],
			'email' => $GLOBALS['email'],
			'webpage' => $GLOBALS['racine'],
			'commentaire' => '',
			'captcha' => mk_captcha('x')+mk_captcha('y'),

			);
	} else {
		if (isset($_GET['n'])) {
				$arguments = htmlspecialchars(stripslashes($_SERVER['QUERY_STRING']));
				$ntab = explode('&',$arguments);
				$page = $ntab['0'];
				header('Location: '.'index.php?'.$page.'#top');
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
	if (isset($article_id)) {
		// ALLOW COMMENTS ON
		if ($allow_comments == '1' and $GLOBALS['activer_global_comments'] == '0') {
			if (isset($_GET['n'])) {
				$GLOBALS['form_commentaire'] .= '<form id="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'#erreurs" >'."\n";
			}
			else {
				$GLOBALS['form_commentaire'] .= '<form id="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;n=mp#erreurs" >'."\n";
			}
		$GLOBALS['form_commentaire'] .= '<div class="formulaire">'."\n";
		$GLOBALS['form_commentaire'] .= '<div class="field">'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="commentaire">'.$GLOBALS['lang']['comment_contenu'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<textarea id="commentaire" name="commentaire" cols="50" rows="10">'.$defaut['commentaire'].'</textarea>'."\n";
		$GLOBALS['form_commentaire'] .= '</div>'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="auteur">'.$GLOBALS['lang']['comment_nom'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="auteur" name="auteur" value="'.$defaut['auteur'].'" size="25" /><br/>'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="email">'.$GLOBALS['lang']['comment_email'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="email" name="email" value="'.$defaut['email'].'" size="25" /><br/>'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="webpage">'.$GLOBALS['lang']['comment_webpage'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="webpage" name="webpage" value="'.$defaut['webpage'].'" size="25" /><br/>'."\n";
		$GLOBALS['form_commentaire'] .= '<div style="display:none;">';
		$GLOBALS['form_commentaire'] .= '<input type="hidden" name="article_id" value="'.$article_id.'" />'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="hidden" name="statut" value="1" />'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="hidden" class="nodisplay" name="_verif_envoi" value="1" />'."\n";
		$GLOBALS['form_commentaire'] .= '</div>';
		if (isset($mode) AND $mode == 'admin') {
			$GLOBALS['form_commentaire'] .= '<input type="hidden" id="captcha" name="captcha" value="'.$defaut['captcha'].'" />'."\n";
	} else {
		$GLOBALS['form_commentaire'] .= '<label for="captcha">'.$GLOBALS['lang']['comment_captcha'].': <b>'.en_lettres(mk_captcha('x')).'</b> + <b>'.en_lettres(mk_captcha('y')).'</b> ?</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="captcha" name="captcha" value="'.$defaut['captcha'].'" size="25" /><br/>'."\n";
	}
		$GLOBALS['form_commentaire'] .= '<input class="submit" accesskey="s" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['envoyer'].'" />'."\n";
		$GLOBALS['form_commentaire'] .= '</div>';
		$GLOBALS['form_commentaire'] .= '<p id="wiki" ><a href="inc/wiki.php" onclick="ouvre(\'inc/wiki.php\');return false">'.$GLOBALS['lang']['label_wiki'].'</a></p>'."\n".'</form>';
		// ALLOW COMMENTS OFF
		} else {
		$GLOBALS['form_commentaire'] .= '<p>'.$GLOBALS['lang']['comment_not_allowed'].'</p>'."\n";
		}
	} 
}

function traiter_form_commentaire($dossier, $commentaire) {
	if (isset($_POST['enregistrer'])) {
					fichier_data($dossier, $commentaire);
					redirection($_SERVER['PHP_SELF'].'?post_id='.$commentaire[$GLOBALS['data_syntax']['comment_article_id'][$GLOBALS['syntax_version']]].'&msg=confirm_comment_ajout');
	} 
}

?>
