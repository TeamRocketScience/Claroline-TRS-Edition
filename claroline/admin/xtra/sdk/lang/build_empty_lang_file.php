<?php // $Id: build_empty_lang_file.php 11656 2009-03-05 09:29:35Z dimitrirambout $
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
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

/*
 * This script build the lang files with var without translation for all languages.
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';

// get start time

$starttime = get_time();

// start html content

$nameTools = 'Build an empty language file';

$urlSDK = $rootAdminWeb . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

include($includePath."/claro_init_header.inc.php");

echo claro_html_tool_title($nameTools);

// go to lang folder

$path_lang = $rootSys . "claroline/lang";
chdir ($path_lang);


//-- COMPLETE
$sqlComplete = " SELECT DISTINCT u.varName
         FROM ". $tbl_used_lang . " u
         WHERE u.sourceFile NOT LIKE '%/install/%'
         ORDER BY u.varName";

$completeVarList = claro_sql_query_fetch_all_rows($sqlComplete);

if ( is_array($completeVarList) )
{
    echo '<p>Create file: ' . $path_lang . '/' . LANG_EMPTY_FILENAME . '</p>' . "\n";

    $fileHandle = fopen(LANG_EMPTY_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);
    if ($fileHandle)
    {
        fwrite($fileHandle, "<?php \n");
        
        if ( !empty($completeVarList) )
        {
            foreach( $completeVarList as $completeVar )
            {
                $string = build_translation_line_file($completeVar['varName'],'') ;
                fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
            }
        }

        fwrite($fileHandle, "?>");
    }
}
else
{
    die ("QUERY FAILED: " .  __LINE__);
}

// build language files
fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);

echo '<p><a href="' . $urlAppend . '/claroline/lang/' . LANG_EMPTY_FILENAME . '">Download it</a></p>';



//-- INSTALL
$sqlInstall = "SELECT DISTINCT u.varName
         FROM ". $tbl_used_lang . " u
         WHERE u.sourceFile LIKE '%/install/%'
         ORDER BY u.varName";

$installVarList = claro_sql_query_fetch_all_rows($sqlInstall);

if ( is_array($installVarList)  )
{
    echo '<p>Create file: ' . $path_lang . '/' . LANG_EMPTY_INSTALL_FILENAME . '</p>' . "\n";

    $fileHandle = fopen(LANG_EMPTY_INSTALL_FILENAME, 'w') or die("FILE OPEN FAILED: ". __LINE__);
    if ($fileHandle)
    {
        fwrite($fileHandle, "<?php \n");
        if ( !empty($installVarList) )
        {
            foreach( $installVarList as $installVar )
            {
                $string = build_translation_line_file($installVar['varName'],'') ;
                fwrite($fileHandle, $string) or die ("FILE WRITE FAILED: ". __LINE__);
            }
        }

        fwrite($fileHandle, "?>");
    }
}
else
{
    die ("QUERY FAILED: " .  __LINE__);
}

// build language files
fclose($fileHandle) or die ("FILE CLOSE FAILED: ". __LINE__);

echo '<p><a href="' . $urlAppend . '/claroline/lang/' . LANG_EMPTY_INSTALL_FILENAME . '">Download it</a></p>';

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n"
.    '<a href="'.$urlTranslation.'">&lt;&lt; Back</a>' . "\n";

// display footer
include($includePath."/claro_init_footer.inc.php");

?>