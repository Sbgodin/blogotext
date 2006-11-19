<?php
# *** LICENSE ***
# This file is part of BlogoText.
# Copyright (c) 2006 Frederic Nassar.
# All rights reserved.
# BlogoText is free software, you can redistribute it under the terms of the
# Creative Commons Attribution-NonCommercial-NoDerivs 2.0 France Licence
# *** LICENSE ***
//error_reporting(E_ALL);
session_start() ;
	unset($_SESSION['nom_utilisateur']);
	header('Location: index.php');
?>