<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

function liste_commentaires($dossier, $article_id) {
	$liste= array();
	if ($liste = table_derniers($dossier)) {
		foreach ($liste as $comm) {
			$path = $dossier.'/'.get_path_no_ext($comm);
			$syntax_version= get_version($path);
     			if (parse_xml($path, $GLOBALS['data_syntax']['comment_article_id'][$syntax_version]) == $article_id )  {
       			$retour[]=$comm;
     			}
		}
	}
	if (isset($retour)) {
		krsort($retour);
		return $retour;
	}
}

function afficher_commentaire($comment) {
	$date = decode_id($comment['id']);
		print '<div class="commentbloc" >'."\n";
		print '<h3 class="titre-commentaire">'.$comment['auteur'].'</h3>'."\n";
		print '<p class="email"><a href="mailto:'.$comment['email'].'">'.$comment['email'].'</a></p>'."\n";
		print '<p class="date">'.date_formate($comment['id']).', '.heure_formate($comment['id']).'</p>';
	print '<p>'.$comment['contenu'].'</p>';
	print '<p class="suppr-commentaire"><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&del='.$comment['id'].'" onclick="return window.confirm(\''.$GLOBALS['lang']['question_suppr_comment'].'\')" />'.$GLOBALS['lang']['supprimer'].'</a></p>'."\n";
	print '</div>'."\n";
		if (isset($_POST['suppr-commentaire'])) {
			print 'ok'.$comment['id'];
		}
}

function supprimer_commentaire($article_id, $comment_id) {
$loc_comment= '../'.$GLOBALS['dossier_commentaires'].'/'.get_path_no_ext($comment_id).'.'.$GLOBALS['ext_data'];
	if (unlink($loc_comment)) {
		redirection($_SERVER['PHP_SELF'].'?post_id='.$article_id.'&msg=confirm_comment_suppr');
		} else {
			erreur('Impossible de supprimer le fichier');
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
			'auteur' => stripslashes($_POST['auteur']),
			'email' => stripslashes($_POST['email']),
			'commentaire' => stripslashes($_POST['commentaire']),
			'captcha' => stripslashes($_POST['captcha'])
			);

	} elseif (isset($mode) AND $mode == 'admin') {
		$defaut = array(
			'auteur' => $GLOBALS['auteur'],
			'email' => $GLOBALS['email'],
			'commentaire' => '',
			'captcha' => $GLOBALS['captcha']['x']+$GLOBALS['captcha']['y']
			);
	} else {
		$defaut = array(
			'auteur' => '',
			'email' => '',
			'commentaire' => '',
			'captcha' => ''
			);
	}
	if (isset($article_id)) {
		// ALLOW COMMENTS ON
		if ($allow_comments == '1') {
		$GLOBALS['form_commentaire'] .= '<form id="form-commentaire" method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" >'."\n";
		$GLOBALS['form_commentaire'] .=	'<div class="field">'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="commentaire">'.$GLOBALS['lang']['comment_contenu'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<textarea id="commentaire" name="commentaire" cols="50" rows="10">'.$defaut['commentaire'].'</textarea>'."\n";
		$GLOBALS['form_commentaire'] .=	'</div>'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="auteur">'.$GLOBALS['lang']['comment_nom'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="auteur" name="auteur" value="'.$defaut['auteur'].'" size="25" />'."\n";
		$GLOBALS['form_commentaire'] .= '<label for="email">'.$GLOBALS['lang']['comment_email'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="email" name="email" value="'.$defaut['email'].'" size="25" />'."\n";
		$GLOBALS['form_commentaire'] .= '<div style="display:none;">';
		$GLOBALS['form_commentaire'] .= '<input type="hidden" name="article_id" value="'.$article_id.'" />'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="hidden" name="statut" value="1" />'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="hidden" class="nodisplay" name="_verif_envoi" value="1" />'."\n";
		$GLOBALS['form_commentaire'] .= '</div>';
		if (isset($mode) AND $mode == 'admin') {
			$GLOBALS['form_commentaire'] .= '<input type="hidden" id="captcha" name="captcha" value="'.$defaut['captcha'].'" />'."\n";
	} else {
		$GLOBALS['form_commentaire'] .= '<label for="captcha">'.$GLOBALS['lang']['comment_captcha'].' '.$GLOBALS['captcha']['x'].' + '.$GLOBALS['captcha']['y'].'</label>'."\n";
		$GLOBALS['form_commentaire'] .= '<input type="text" id="captcha" name="captcha" value="'.$defaut['captcha'].'" size="25" />'."\n";
	}
		$GLOBALS['form_commentaire'] .= '<input class="submit" accesskey="s" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['envoyer'].'" />'."\n";
		$GLOBALS['form_commentaire'] .= '</form>';
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