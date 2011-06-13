<?php // $Id: language.conf.php 11067 2008-09-01 07:46:27Z zefredz $
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// Files and path

define ('LANG_COMPLETE_FILENAME','complete.lang.php'); 
define ('LANG_INSTALL_FILENAME', 'install.lang.php');
define ('LANG_MISSING_FILENAME','missing.lang.php'); 
define ('LANG_INSTALL_MISSING_FILENAME','missing.install.lang.php');
define ('LANG_EMPTY_FILENAME','new.complete.file.txt'); 
define ('LANG_EMPTY_INSTALL_FILENAME', 'new.install.file.txt');

// Default values 

define ('DEFAULT_LANGUAGE','english'); 
 
// database authentification data

define('TABLE_TRANSLATION','translation');
define('TABLE_TR_LANG_LIST','tr_lang_list');
define('TABLE_USED_LANG_VAR','used_language');

// message

$problemMessage = "Problem with the database.";
