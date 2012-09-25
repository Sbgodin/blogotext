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


$if_not_exists = ($GLOBALS['sgbd'] == 'mysql') ? 'IF NOT EXISTS' : ''; // SQLite does'nt know these syntaxes.
$auto_increment = ($GLOBALS['sgbd'] == 'mysql') ? 'AUTO_INCREMENT' : ''; // SQLite does'nt know these syntaxes, but MySQL needs it.

$GLOBALS['dbase_structure']['links'] = "CREATE TABLE ".$if_not_exists." links
	(
		ID INTEGER PRIMARY KEY $auto_increment,
		bt_type TEXT,
		bt_id INTEGER, 
		bt_content LONGTEXT,
		bt_wiki_content LONGTEXT,
		bt_author TEXT,
		bt_title TEXT,
		bt_tags TEXT,
		bt_link LONGTEXT,
		bt_statut INTEGER
	);";

$GLOBALS['dbase_structure']['commentaires'] = "CREATE TABLE ".$if_not_exists." commentaires
	(
		ID INTEGER PRIMARY KEY $auto_increment,
		bt_type TEXT,
		bt_id INTEGER, 
		bt_article_id TEXT,
		bt_content LONGTEXT,
		bt_wiki_content LONGTEXT,
		bt_author TEXT,
		bt_link TEXT,
		bt_webpage LONGTEXT,
		bt_email LONGTEXT,
		bt_subscribe INTEGER,
		bt_statut INTEGER
	);";


$GLOBALS['dbase_structure']['articles'] = "CREATE TABLE ".$if_not_exists." articles
	(
		ID INTEGER PRIMARY KEY $auto_increment,
		bt_type TEXT,
		bt_id INTEGER, 
		bt_date INTEGER, 
		bt_title TEXT,
		bt_abstract TEXT,
		bt_notes TEXT,
		bt_link TEXT,
		bt_content LONGTEXT,
		bt_wiki_content LONGTEXT,
		bt_categories TEXT,
		bt_keywords LONGTEXT,
		bt_nb_comments INTEGER,
		bt_allow_comments INTEGER,
		bt_statut INTEGER
	);";


/*  Creates a new BlogoText base.
    if file does not exists, it is created, as well as the tables.
    if file does exists, tables are checked and created if not exists
 */


function create_tables() {

	/*
	* SQLite : opens file, check tables by listing them, create the one that miss.
	*
	*/
	switch ($GLOBALS['sgbd']) {
		case 'sqlite':

				if (!creer_dossier($GLOBALS['BT_ROOT_PATH'].''.$GLOBALS['dossier_db'])) {
					die('Impossible de creer le dossier databases (chmod?)');
				}

				$file = $GLOBALS['BT_ROOT_PATH'].''.$GLOBALS['dossier_db'].'/'.$GLOBALS['db_location'];
				// open tables

				try {
					$db_handle = new PDO('sqlite:'.$file);
					$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$db_handle->query("PRAGMA temp_store=MEMORY; PRAGMA synchronous=OFF;");
					// list tables
					$list_tbl = $db_handle->query("SELECT name FROM sqlite_master WHERE type='table'");
					// make an normal array, need for "in_array()"
					$tables = array();
					foreach($list_tbl as $j) {
						$tables[] = $j['name'];
					}

					// check each wanted table (this is because the "IF NOT EXISTS" condition doesn’t exist in lower versions of SQLite.
					$wanted_tables = array('commentaires', 'articles', 'links');
					foreach ($wanted_tables as $i => $name) {
						if (!in_array($name, $tables)) {
							$results = $db_handle->exec($GLOBALS['dbase_structure'][$name]);
						}
					}
				} catch (Exception $e) {
					die('Erreur 1: '.$e->getMessage());
				}
			break;

		/*
		* MySQL : create tables with the IF NOT EXISTS condition. Easy.
		*
		*/
		case 'mysql':
				try {
					$options_pdo[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
					$db_handle = new PDO('mysql:host=localhost;dbname=blogotext', 'blogotext_u', 'pass', $options_pdo);

					// check each wanted table 
					$wanted_tables = array('commentaires', 'articles', 'links');
					foreach ($wanted_tables as $i => $name) {
							$results = $db_handle->exec($GLOBALS['dbase_structure'][$name]);
					}

			
				} catch (Exception $e) {
					die('Erreur 2: '.$e->getMessage());
				}
			break;
	}

	return $db_handle;
}


/* Open a base
 */
function open_base() {
	$handle = create_tables();
	return $handle;
}

/* // to determine the first entry of callender
function oldest($table) {
	try {
		$query = "SELECT min(bt_id) FROM $table"; // for public : only allows non draft|futur articles

		$res = $GLOBALS['db_handle']->query($query);
		$result = $res->fetch();
	} catch (Exception $e) {
		die('Erreur : '.$e->getMessage());
	}

}*/



/* Fully list the articles DB. Returns a big Array (with array of comments for each article) */

function liste_base_articles($tri_selon, $motif, $mode, $statut, $offset, $nombre_voulu) {
	$chapo = 0;
	$and_statut = ( ($statut != '') or ($mode == "public") ) ? 'AND bt_statut='.$statut : '';
	$where_stat = ($statut != '') ? 'WHERE bt_statut='.$statut : '';

	if ($mode == 'public') { // si public, ajout de la condition sur la date
		$and_statut .= ' AND bt_date <= '.date('YmdHis');

		if ($where_stat != '') {
			$where_stat .= ' AND bt_date <= '.date('YmdHis');
		} else {
			$where_stat .= 'WHERE bt_date <= '.date('YmdHis');
		}
	}

	$limite = (is_numeric($offset) and is_numeric($nombre_voulu)) ? 'LIMIT '.$offset.', '.$nombre_voulu : '';

	switch($tri_selon) {

		case 'nb': // simple le nombre d’articles
			$query = "SELECT count(*) AS nbr FROM articles $where_stat";
			try {
				$req = $GLOBALS['db_handle']->prepare($query);
				$req->execute();
				$result = $req->fetchAll();
				return $result[0]['nbr'];
			} catch (Exception $e) {
				die('Erreur : '.$e->getMessage());
			}
			break;
			exit;

		case 'statut':
			$query = "SELECT * FROM articles WHERE bt_statut=? ORDER BY bt_date DESC $limite";
			$array = array($motif);
			break;

		case 'tags':
			$query = "SELECT * FROM articles WHERE bt_categories LIKE ? $and_statut ORDER BY bt_date DESC $limite";
			$array = array('%'.$motif.'%');
			break;

		case 'date':
		  	$query = "SELECT * FROM articles WHERE bt_date LIKE ? $and_statut ORDER BY bt_date DESC $limite";
			$array = array($motif.'%');
			break;

		case 'id':
		  	$query = "SELECT * FROM articles WHERE bt_id=?";
			$array = array($motif);
			$chapo = 1;
			break;

		case 'recherche':
			$query = "SELECT * FROM articles WHERE ( bt_content LIKE ? OR bt_title LIKE ? ) $and_statut ORDER BY bt_date DESC $limite";
			$array = array('%'.$motif.'%', '%'.$motif.'%');
			break;

		case 'random': // always on public side
			$query = "SELECT * FROM articles $where_stat ORDER BY random() LIMIT 0, 1";
			$array = array();
			break;

		default : // only on statut and limite
		  	$query = "SELECT * FROM articles $where_stat ORDER BY bt_date DESC $limite";
			$array = array();
	}

	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
		$result = $req->fetchAll();
		$result = init_list_articles($result, $mode, $chapo);
		return $result;
	} catch (Exception $e) {
		die('Erreur : '.$e->getMessage());
	}

}



/* Fully list the comments DB. Returns an Array
 */

function liste_base_comms($tri_selon, $motif, $mode, $statut, $offset, $nombre_voulu) {
	$and_statut = ( ($statut != '') or ($mode == "public") ) ? 'AND bt_statut='.$statut : '';
	$where_stat = ($statut != '') ? 'WHERE bt_statut='.$statut : '';
	$limite = (is_numeric($offset) and is_numeric($nombre_voulu)) ? 'LIMIT '.$offset.', '.$nombre_voulu : '';

	switch($tri_selon) {

		case 'nb': // simple le nombre de commentaires (selon article, ou pas)
			$where_art_id = (preg_match('#\d{14}#', $motif)) ? ($statut != '') ? "AND bt_article_id=?" : "WHERE bt_article_id=?" : '' ;

			$query = "SELECT count(*) AS nbr FROM commentaires $where_stat $where_art_id";
			$array = (preg_match('#\d{14}#', $motif)) ? array($motif) : array() ;
			try {
				$req = $GLOBALS['db_handle']->prepare($query);
				$req->execute($array);
				$result = $req->fetchAll();
				return $result[0]['nbr'];
			} catch (Exception $e) {
				die('Erreur : '.$e->getMessage());
			}
			break;
			exit;
		
		case 'statut':
			$query = "SELECT * FROM commentaires WHERE bt_statut=? ORDER BY bt_id DESC $limite";
			$array = array($motif);
			break;

		case 'auteur':
			$query = "SELECT * FROM commentaires WHERE bt_author=? $and_statut ORDER BY bt_id $limite";
			$array = array($motif);
			break;

		case 'date':
		  	$query = "SELECT * FROM commentaires WHERE bt_id LIKE ? $and_statut ORDER BY bt_id DESC $limite";
			$array = array($motif.'%');
			break;

		case 'id':
		  	$query = "SELECT * FROM commentaires WHERE bt_id=?";
			$array = array($motif);
			break;

		case 'recherche':
			$query = "SELECT * FROM commentaires WHERE bt_content LIKE ? $and_statut ORDER BY bt_id DESC $limite";
			$array = array('%'.$motif.'%');
			break;

		case 'assos_art':
			$query = "SELECT * FROM commentaires WHERE bt_article_id=? $and_statut ORDER BY bt_id $limite";
			$array = array($motif);
			break;

		default : // only on statut and limite
		  	$query = "SELECT * FROM commentaires $where_stat ORDER BY bt_id DESC $limite";
			$array = array();
	}

	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
		$result = $req->fetchAll();
		$result = init_list_comments($result);
		return $result;
	} catch (Exception $e) {
		die('Erreur : '.$e->getMessage());
	}

}


/* LISTE BASE LIENS ====================================--------------------=
 * This big function for the links
 * this is the one that uses SQL
 *
 * It returns an PHP array, not an object.
 *
*/

function liste_base_liens($tri_selon, $motif, $mode, $statut, $offset, $nombre_voulu) {
	$and_statut = ($statut != '') ? 'AND bt_statut='.$statut : '';
	$where_stat = ($statut != '') ? 'WHERE bt_statut='.$statut : '';
	$limite = (is_numeric($offset) and is_numeric($nombre_voulu)) ? 'LIMIT '.$offset.', '.$nombre_voulu : '';

	switch($tri_selon) {

		case 'nb': // simple le nombre de liens
			$query = "SELECT count(*) AS nbr FROM links $where_stat";
			$array = array();
			try {
				$req = $GLOBALS['db_handle']->prepare($query);
				$req->execute($array);
				$result = $req->fetchAll();
				return $result[0]['nbr'];
			} catch (Exception $e) {
				die('Erreur : '.$e->getMessage());
			}
			break;
			exit;
		
		case 'statut':
			$query = "SELECT * FROM links WHERE bt_statut=? ORDER BY bt_id DESC $limite";
			$array = array($motif);
			break;

		case 'auteur':
			$query = "SELECT * FROM links WHERE bt_author=? $and_statut ORDER BY bt_id DESC $limite";
			$array = array($motif);
			break;

		case 'tags':
			$query = "SELECT * FROM links WHERE bt_tags LIKE ? $and_statut ORDER BY bt_id DESC $limite";
			$array = array('%'.$motif.'%');
			break;

		case 'date':
		  	$query = "SELECT * FROM links WHERE bt_id LIKE ? $and_statut ORDER BY bt_id DESC $limite";
			$array = array($motif.'%');
			break;

		case 'id':
		  	$query = "SELECT * FROM links WHERE bt_id=?";
			$array = array($motif);
			break;

		case 'recherche':
			$query = "SELECT * FROM links WHERE ( bt_content LIKE ? OR bt_title LIKE ? ) $and_statut ORDER BY bt_id DESC $limite";
			$array = array('%'.$motif.'%', '%'.$motif.'%');
			break;

		default : // only on statut and limite
		  	$query = "SELECT * FROM links $where_stat ORDER BY bt_id DESC $limite";
			$array = array();
	}

	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
		$result = $req->fetchAll();

		return $result;
	} catch (Exception $e) {
		die('Erreur 966785 : '.$e->getMessage());
	}

}


// returns or prints an entry of some element of some table (very basic)
function get_entry($base_handle, $table, $entry, $id, $retour_mode) {
	$query = "SELECT $entry FROM $table WHERE bt_id=?";
	try {
		$req = $base_handle->prepare($query);
		$req->execute(array($id)); // Y U NO work ? 
		$result = $req->fetchAll();
	} catch (Exception $e) {
		die('Erreur : '.$e->getMessage());
	}

	if ($retour_mode == 'return' and !empty($result[0][$entry])) {
		return $result[0][$entry];
	}
	if ($retour_mode == 'echo' and !empty($result[0][$entry])) {
		echo $result[0][$entry];
	}
	return '';
}


