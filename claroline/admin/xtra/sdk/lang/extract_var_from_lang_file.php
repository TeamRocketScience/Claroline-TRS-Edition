<?php // $Id: extract_var_from_lang_file.php 11656 2009-03-05 09:29:35Z dimitrirambout $
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

require '../../../../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

/*
 * This script retrieves all the existing translation of an existing Claroline
 * It scans all the files of the 'lang' directory and stored the get_lang(' variables')
 * content into a mySQL database.
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');

// table
$tbl_translation =  '`' . get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . TABLE_TRANSLATION . '`';

// get start time

$starttime = get_time();

// Start content

$nameTools = 'Extract variables from language files';

$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

// drop table if exists

$sql = "DROP TABLE IF EXISTS ". $tbl_translation ." ";

claro_sql_query ($sql);

// create table 

$sql = "CREATE TABLE ". $tbl_translation ." (
 id INTEGER NOT NULL auto_increment,
 language VARCHAR(250) NOT NULL,
 varName VARCHAR(250) BINARY NOT NULL,
 varContent VARCHAR(250) NOT NULL,
 varFullContent TEXT NOT NULL,
 sourceFile VARCHAR(250) NOT NULL,
 used tinyint(4) default 0,
 INDEX index_language (language,varName),
 INDEX index_content  (language,varContent),
 PRIMARY KEY(id))";

claro_sql_query($sql);

// go to & browse lang path

$path_lang = get_path('rootSys') . "claroline/lang";

$it = new DirectoryIterator($path_lang);

echo '<ol>' . "\n";
foreach( $it as $file )
{
    if( $file->isDir() && !$file->isDot() && $file->getFilename() != '.svn' )
    {
        $completeFile = $file->getPathname() . '/' . LANG_COMPLETE_FILENAME;
        if( file_exists( $completeFile ) )
        {
            retrieve_lang_var($completeFile, $file->getFilename());
            echo '<li>' . $completeFile . '</li>' . "\n";
        }
    
        $installFile = $file->getPathname() . '/' . LANG_INSTALL_FILENAME;
        if( file_exists( $installFile ) )
        {
            retrieve_lang_var($installFile, $file->getFilename());
            echo '<li>' . $installFile . '</li>' . "\n";
        }
    }    
}
echo '</ol>' . "\n";

// get and display end time

$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n"
.    '<a href="'.$urlTranslation.'">&lt;&lt; Back</a>' . "\n";

// display footer

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>