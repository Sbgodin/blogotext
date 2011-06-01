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

require_once '../inc/inc.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link type="text/css" rel="stylesheet" href="style/ecrire.css" />
<title> BlogoText | Mise en forme</title>
</head>

<body>
<div id="axe">
<div id="wikipage">

<h1><?php print $GLOBALS['lang']['label_wiki']; ?></h1>

<h2><?php print $GLOBALS['lang']['wiki_titre']; ?></h2>
<pre><?php print $GLOBALS['lang']['wiki_titre_exp']; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_lien']; ?></h2>
<pre><?php print $GLOBALS['lang']['wiki_lien_exp']; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_gras']; ?></h2>
<pre><?php print '__'.$GLOBALS['lang']['wiki_gras'].'__'; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_italique']; ?></h2>
<pre><?php print '##'.$GLOBALS['lang']['wiki_italique'].'##'; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_image']; ?></h2>
<pre><?php print $GLOBALS['lang']['wiki_image_exp']; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_lienimage']; ?></h2>
<pre><?php print $GLOBALS['lang']['wiki_lienimage_exp']; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_souligne']; ?></h2>
<pre><?php print '++'.$GLOBALS['lang']['wiki_souligne'].'++'; ?></pre>

<h2><?php print $GLOBALS['lang']['wiki_barre']; ?></h2>
<pre><?php print '--'.$GLOBALS['lang']['wiki_barre'].'--'; ?></pre>


</div>
</div>

</body>
</html>
