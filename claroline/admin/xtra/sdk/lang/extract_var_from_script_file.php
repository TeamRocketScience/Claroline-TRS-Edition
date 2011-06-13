<?php // $Id: extract_var_from_script_file.php 11656 2009-03-05 09:29:35Z dimitrirambout $
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

set_time_limit (0);

/*
 * This script scans and retrieves all the language variables of an existing Claroline
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');

require_once get_path('incRepositorySys') . '/lib/config.lib.inc.php';

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';

// get start time

$starttime = get_time();

// Start content

$nameTools = 'Extract variables from scripts';

$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );


include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

// drop table if exists

$sql = "DROP TABLE IF EXISTS ". $tbl_used_lang ." ";
mysql_query ($sql) or die($problemMessage);

// create table

$sql = "CREATE TABLE ". $tbl_used_lang ." (
 id INTEGER NOT NULL auto_increment,
 varName VARCHAR(250) BINARY NOT NULL,
 langFile VARCHAR(250) NOT NULL,
 sourceFile VARCHAR(250) NOT NULL,
 INDEX index_varName (varName),
 PRIMARY KEY(id))";

mysql_query ($sql) or die($problemMessage . __LINE__);

// get lang vars from fies in claroline folder
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(get_path('clarolineRepositorySys')) );

$allLanguageVarList = array();

echo '<ul>' . "\n";
foreach( $it as $file )
{
    if( $file->isFile() && substr($file->getFilename(), -17) == '.def.conf.inc.php' )
    {
        $languageVarList = get_lang_vars_from_deffile($file->getPathname());
    
        echo '<li>' . count($languageVarList) . ' in DEF file <b>' . $file->getPathname() . '</b></li>' . "\n";
    }
    elseif( $file->isFile() && substr($file->getFilename(), -4) == '.php' )
    {
        $languageVarList = get_lang_vars_from_file($file->getPathname());  

        echo '<li>' . count($languageVarList) . ' in <b>' . $file->getPathname() . '</b></li>' . "\n";
    }
    elseif( $file->isFile() && $file->getFilename() == 'manifest.xml' )
    {
        // find first <name>Exercises</name> using preg_match
        $manifestContent = file_get_contents($file->getPathName());
        
        $languageVarList = array();
        $matches = array();
        if( preg_match('!<name>([^<]+)</name>!i', $manifestContent, $matches ) )
        {
            if( is_array($matches[1]) )
            {
                $languageVarList[] = $matches[1][0];
            }
            else
            {
                $languageVarList[] = $matches[1];
            }
        }
        echo '<li>' . count($languageVarList) . ' in <b>' . $file->getPathname() . '</b></li>' . "\n";
    }
    else
    {
        continue;
    }
    
    // add in main array to compute total number of vars
    $allLanguageVarList = array_merge($allLanguageVarList, $languageVarList);

    // update table
    store_lang_used_in_script($languageVarList,str_replace('\\', '/', realpath($file->getPathname())));
}

// get name of some tools (the ones that are in module directory)
$moduleLabelList = array( 'CLCHAT' );

foreach( $moduleLabelList as $module )
{
    $modulePath = get_module_path($module);
    $manifestFile = $modulePath . '/manifest.xml';

    if( file_exists($manifestFile) )
    {
        $manifestContent = file_get_contents($manifestFile);
        
        $languageVarList = array();
        $matches = array();
        if( preg_match('!<name>([^<]+)</name>!i', $manifestContent, $matches ) )
        {
            if( is_array($matches[1]) )
            {
                $languageVarList[] = $matches[1][0];
            }
            else
            {
                $languageVarList[] = $matches[1];
            }
        }
        
        echo '<li>' . count($languageVarList) . ' in <b>' . $manifestFile . '</b></li>' . "\n";
        
        // add in main array to compute total number of vars
        $allLanguageVarList = array_merge($allLanguageVarList, $languageVarList);
        // update table
        store_lang_used_in_script($languageVarList,str_replace('\\', '/', realpath($file->getPathname())));
    }
    
}


echo '</ul>' . "\n\n";

$allLanguageVarList = array_unique($allLanguageVarList);

echo "<p>Total variables: " . count($allLanguageVarList) . "</p>";

// end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>\n"
.    '<a href="'.$urlTranslation.'">&lt;&lt; Back</a>' . "\n";;

// display footer

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>