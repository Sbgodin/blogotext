<?php
/**********************
Sitemap Blogotext
**********************/
header('Content-Type: text/html; charset=UTF-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
$GLOBALS['BT_ROOT_PATH'] = '';
error_reporting(-1);
require_once 'inc/lang.php';
require_once 'config/user.php';
require_once 'config/prefs.php';
require_once 'inc/conf.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';
require_once 'inc/sqli.php';
$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);
$liste = liste_base_articles('', '', 'public', '1', 0, 999);
foreach ($liste as $billet)
{
	$time = (isset($billet['bt_date'])) ? $billet['bt_date'] : $billet['bt_id'];
	$dec = decode_id($time);
	$item = '<url>'."\n";
	$item .= '	<loc>'.$billet['bt_link'].'</loc>'."\n";
	$item .= "	<lastmod>$dec[annee]-$dec[mois]-$dec[jour]</lastmod>\n";
	$item .= '	<changefreq>yearly</changefreq>'."\n";
	$item .= '	<priority>0.8</priority>'."\n";
	$item .= '</url>'."\n";
	echo $item;
}
echo "</urlset>";
?>
