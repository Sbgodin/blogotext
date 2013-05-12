<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2013 Timo Van Neerden <ti-mo@myopera.com>
#
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial 2.0 France Licence
#
# Also, any distributors of non-official releases MUST warn the final user of it, by any visible way before the download.
# *** LICENSE ***

$GLOBALS['langs'] = array("fr" => 'Français', "en" => 'English', "nl" => 'Nederlands', "de" => 'Deutsch');

if (empty($GLOBALS['lang'])) $GLOBALS['lang'] = '';

switch ($GLOBALS['lang']) {
	case 'fr':
		include_once('lang/fr_FR.php');
		$GLOBALS['lang'] = $lang_fr;
		break;
	case 'de':
		include_once('lang/de_DE.php');
		$GLOBALS['lang'] = $lang_de;
		break;
	case 'nl':
		include_once('lang/nl_NL.php');
		$GLOBALS['lang'] = $lang_nl;
		break;
	case 'en':
		include_once('lang/en_EN.php');
		$GLOBALS['lang'] = $lang_en;
		break;
	default:
		include_once('lang/fr_FR.php');
		$GLOBALS['lang'] = $lang_fr;
		break;
}
