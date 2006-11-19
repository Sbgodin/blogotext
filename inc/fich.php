<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***

// RETOURNE UN TABLEAU SELON RECHERCHE
function table_recherche($depart, $txt, $statut='') {
	$mor = array();
	$table = array();
	$txt = eregi_replace('[[:punct:]]', " ", $txt);
		$mor = split('[[:space:]]', strtolower(trim($txt)));
		foreach ($mor as $n => $mo ) {
			if (strlen($mo) <= '3') {
				unset($mor[$n]);
			}
		}
		$nb = count($mor);
		$articles = table_derniers($depart, '', $statut);
if ((isset($txt)) AND ($txt != '') ) {
	foreach ($articles as $id) {
		$dec = decode_id($id);
		$dossier = $depart.'/'.$dec['annee'].'/'.$dec['mois'].'/';
		$syntax_version= get_version($dossier.$id);
		$mots=parse_xml($dossier.$id, $GLOBALS['data_syntax']['article_keywords'][$syntax_version]);

		$mot[$id]=$mots;
		}
		if (isset($mot)) {
			foreach ($mot as $n => $val) {
			$el[$n]= explode(', ', $val);
			}
		}
		if (isset($el)) {
			foreach ($el as $id => $words) {
				if (count(array_intersect($mor, $words)) == $nb) {
					$table[]= $id;
				}
			}
			if (count($table) > '0') {
				$retour = $table;
			}
		}
}
	if (isset($retour)) {
	return $retour;
}
}

// RETOURNE UN TABLEAU SELON DATE
function table_date($depart, $annee, $mois, $jour='', $statut='') {
$liste=array();
$dossier = $depart.'/'.$annee.'/'.$mois.'/';
if ($jour == '') {
$contenu = parcourir_dossier($dossier, $statut);
} else {
	if ( is_dir($dossier) AND $ouverture = opendir($dossier) ) { 
		$contenu = array();
			while ($fichiers = readdir($ouverture)){
				$jour_fichier= substr($fichiers, '6', '2');
   			if (is_file($dossier.$fichiers) AND ($jour == $jour_fichier) ){
   				if  ( (isset($statut)) AND ($statut != '') ) {
						if (get_statut($dossier.$fichiers) === $statut) {
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
function table_derniers($dossier, $limite='', $statut='') {
	$contenu= array();
if ( $ouverture = opendir($dossier) ) { 
       while ( false !== ($file = readdir($ouverture)) ) {
       	if (preg_match('/\d{4}/', $file) ){
       		$annees[]=$file;
       	}
       }
       closedir($ouverture);
}
if (isset($annees)) {
foreach ($annees as $id => $dossier_annee) {
 	$chemin = $dossier.'/'.$dossier_annee.'/';
	 if ( $ouverture = opendir($chemin) ) { 
       while ( false !== ($file_mois = readdir($ouverture)) ) {
       		if (preg_match('/\d{2}/', $file_mois) ){
       		$dossier_mois[]= $dossier.'/'.$dossier_annee.'/'.$file_mois;
	       	rsort($dossier_mois);
       		}
       }
       closedir($ouverture);
		}
}
}
$i= '0';
if (isset($dossier_mois)) {
foreach ($dossier_mois as $id => $path) {
		if ( $ouverture = opendir($path) ) {
			while ( ($fichiers = readdir($ouverture)) ) {
				// On verifie Extension
				$chemin= $path.'/'.$fichiers;
				if ((get_ext($fichiers)) == $GLOBALS['ext_data']) {
					if  ( (isset($statut)) AND ($statut != '') ) {
						if (get_statut($chemin) === $statut) {
							$contenu[$i++]=$fichiers;
       				rsort($contenu);
						}
					} else {
						$contenu[$i++]=$fichiers;
       			rsort($contenu);
					}
				}
			}
			closedir($ouverture);
		}
}
}
if ( (isset($limite)) AND ($limite!='') ) {
foreach ($contenu as $num => $entry) {
	if ($num < $limite) {
		$retour[$num]= $entry;
	}
}
} else {
		$retour= $contenu;
}
if (isset($retour)) {
	return $retour;
}
}

function traiter_form_billet($billet) {
	if (isset($_POST['enregistrer'])) {
					if (fichier_data($GLOBALS['dossier_data_articles'], $billet) !== 'FALSE') {
					redirection($_SERVER['PHP_SELF'].'?post_id='.$billet[$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']]].'&msg=confirm_article_ajout');
					} else {
					erreur('Ecriture impossible');
					exit;
					}
	} else if (isset($_POST['supprimer'])) {
							if (unlink($GLOBALS['dossier_data_articles'].'/'.get_path($billet[$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']]]))) {
								redirection('index.php?msg=confirm_article_suppr');
							} else {
								erreur('Impossible de supprimer le fichier');
								exit;
							}
							$dossier_annee_mois= $GLOBALS['dossier_data_articles'].'/'.$annee.'/'.$mois.'/';
							rmdir($dossier_annee_mois);
							$dossier_annee= $GLOBALS['dossier_data_articles'].'/'.$annee.'/';
							rmdir($dossier_annee);
						}
}

function fichier_data($dossier, $billet) {
		$article_data = '';
		$date= decode_id($billet[$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']]]);
	foreach ($billet as $markup => $content) {
		$article_data .= '<'.$markup.'>'.$content.'</'.$markup.'>'."\n" ;
	}
		if ( !is_dir($dossier) ) {
			$dossier_ini = creer_dossier($dossier);
		} 
		if ( !is_dir(($dossier).'/'.$date['annee']) ) {
			$dossier_annee = creer_dossier($dossier.'/'.$date['annee']);
		}
		if ( !is_dir(($dossier).'/'.$date['annee'].'/'.$date['mois']) ) {
			$dossier_mois = creer_dossier($dossier.'/'.$date['annee'].'/'.$date['mois']);
		}
		$fichier_data = $dossier.'/'.$date['annee'].'/'.$date['mois'].'/'.$billet[$GLOBALS['data_syntax']['article_id'][$GLOBALS['syntax_version']]].'.'.$GLOBALS['ext_data'];
		$new_file_data=fopen($fichier_data,'wb+');
		if (fwrite($new_file_data,$article_data) === 'FALSE') {
			return 'FALSE';
		} else {
			fclose($new_file_data);
		}
}

function creer_dossier($dossier) {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0755) === FALSE) {
			return 'FALSE';
		} else {
		return 'TRUE';
		}
	}
}

function parcourir_dossier($dossier, $statut='') {
	if ( is_dir($dossier) AND $ouverture = opendir($dossier) ) { 
			while ($fichiers = readdir($ouverture)){
   			if (is_file($dossier.$fichiers)){
   				if ( (isset($statut)) AND ($statut != '') ) {
   					if (get_statut($dossier.$fichiers) === $statut) {
       				$contenu[] = $fichiers;
   					}
   				} else {
       		$contenu[] = $fichiers;
   				}
   			}
			}
			closedir($ouverture);
			if (isset($contenu)) {
				natcasesort($contenu);
				return $contenu;
			}
			} else {
				$erreur = 'Aucun article';
			}
}

function fichier_user() {
		$user='';
	if (!strlen(trim($_POST['nouveau-mdp']))) {
	$new_mdp = $GLOBALS['mdp']; 
} else {
	$new_mdp = crypt($_POST['nouveau-mdp'], $GLOBALS['salt']);
}
		$user .= "<?php\n";
		$user .= "\$GLOBALS['lang']=\$lang_".$_POST['langue'].";												\n";
		$user .= "\$GLOBALS['identifiant'] = '".clean_txt($_POST['identifiant'])."';		\n";
		$user .= "\$GLOBALS['mdp'] = '".$new_mdp."';																		\n";
		$user .= "?>";
		$fichier_user = '../config/user.php';
		$new_file_user=fopen($fichier_user,'wb+');
	if (fwrite($new_file_user,$user) === FALSE) {
		return 'FALSE';
		} else {
			fclose($new_file_user);
			return 'TRUE' ;
		}
}

function fichier_prefs() {
		$prefs='';
		$prefs .= "<?php\n";
		$prefs .= "\$GLOBALS['auteur'] = '".clean_txt($_POST['auteur'])."';								\n";	
		$prefs .= "\$GLOBALS['email'] = '".clean_txt($_POST['email'])."';									\n";
		$prefs .= "\$GLOBALS['nom_du_site'] = '".clean_txt($_POST['nomsite'])."';					\n";
		$prefs .= "\$GLOBALS['description'] = '".clean_txt($_POST['description'])."';			\n";
		$prefs .= "\$GLOBALS['racine'] = '".trim($_POST['racine'])."';										\n";
		$prefs .= "\$GLOBALS['nb_maxi'] = '".$_POST['nb_maxi']."';												\n";
		$prefs .= "\$GLOBALS['nb_list'] = '".$_POST['nb_list']."';												\n";
		$prefs .= "\$GLOBALS['format_date'] = '".$_POST['format_date']."';								\n";
		$prefs .= "\$GLOBALS['format_heure'] = '".$_POST['format_heure']."';							\n";
		$prefs .= "\$GLOBALS['activer_apercu']= '".$_POST['apercu']."';										\n";
		$prefs .= "\$GLOBALS['theme_choisi']= '".$_POST['theme']."';											\n";	
		$prefs .= "?>";
		$fichier_prefs = '../config/prefs.php';
		$new_file_pref=fopen($fichier_prefs,'wb+');
		if (fwrite($new_file_pref,$prefs) === FALSE) {
			return 'FALSE';
		} else {
			fclose($new_file_pref);
			return 'TRUE';
		}
}

function apercu($article) {
			if (isset($article)) {
				$apercu = '<h1>'.$article['titre'].'</h1>'."\n";
    		$apercu .= '<div><strong>'.$article['chapo'].'</strong></div>'."\n";
    		$apercu .= '<div>'.$article['contenu'].'</div>'."\n";
    	print '<div id="apercu">'."\n".$apercu.'</div>'."\n\n";
	    }
}

function parse_xml($fichier, $balise) {
	if ($openfile = file_get_contents($fichier)) {
			if (ereg('<'.$balise.'>',$openfile)) {
  			$sizeitem = strlen('<'.$balise.'>');
  			$debut = strpos($openfile, '<'.$balise.'>') + $sizeitem;
  			$fin = strpos($openfile, '</'.$balise.'>');
  			$lenght = $fin - $debut;
  			$return = substr($openfile, $debut, $lenght); 
  		return $return;
			} else {
				return '';
			}
	} else {
		erreur('Impossible de lire le fichier');
	}
}

?>