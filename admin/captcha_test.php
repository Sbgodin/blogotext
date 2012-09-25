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

$GLOBALS['BT_ROOT_PATH'] = '../';
require_once '../inc/inc.php';
error_reporting($GLOBALS['show_errors']);

operate_session();
$begin = microtime(TRUE);

//afficher_form_captcha();

//function afficher_form_captcha($erreurs = '') {
	afficher_top($GLOBALS['lang']['preferences']);
	echo '<div id="top">';
	afficher_msg($GLOBALS['lang']['preferences']);
	afficher_menu('preferences.php');
	echo '</div>';

	echo '<div id="axe">'."\n";
	echo '<div id="page">'."\n";
	if (!empty($_SESSION['freecap_word_hash']) and !empty($_POST['word'])) {
		if (sha1(strtolower($_POST['word'])) == $_SESSION['freecap_word_hash']) {
			$_SESSION['freecap_word_hash'] = false;
			$word_ok = "yes";
		} else {
			$word_ok = "no";
		}
	} else {
		$word_ok = FALSE;
	}
	echo js_reload_captcha(1);
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
	echo '<fieldset class="pref">';
	echo legend('Captcha', 'legend-config');
	echo '<p>';
	if ($word_ok !== FALSE) {
		if ($word_ok == "yes") {
			echo '<b style="color: green;">you got the word correct, rock on.</b>';
		} else {
			echo '<b style="color: red;">sorry, that\'s not the right word, try again.</b>';
		}
	}
	echo '</p>';
	echo '<p><img src="../inc/freecap/freecap.php" id="freecap" alt="freecap"/></p>'."\n";
	echo '<p>If you can\'t read the word, <a href="#" onclick="new_freecap();return false;">click here to change image</a></p>'."\n";
	echo '<p>word above:<input type="text" name="word" /></p>'."\n";
	echo '<input class="submit blue-square" type="submit" name="valider" value="'.$GLOBALS['lang']['envoyer'].'" />'."\n";
	echo '</fieldset>';
	echo '</form>'."\n";

	footer('', $begin);
//}

?>
