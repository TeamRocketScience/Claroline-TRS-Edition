<?php // $Id: build_devel_lang_file.php 11768 2009-05-19 14:30:25Z dimitrirambout $
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2009 Universite catholique de Louvain (UCL)
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
 * This script build the devel lang files for all languages.
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';
$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

// get start time

$starttime = get_time();

// start html content

$nameTools = "Build development language files";

$urlSDK = $rootAdminWeb . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$out = '';

$out .= claro_html_tool_title($nameTools);

// go to lang folder

$path_lang = $rootSys . "claroline/lang";
chdir ($path_lang);

// browse lang folder

$languagePathList = get_lang_path_list($path_lang);

// display select box

if ( sizeof($languagePathList) > 0)
{
    $out .= "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";
    $out .= "<select name=\"lang\">";
    $out .= '<option value="all" selected="selected">' . get_lang('All') . '</option>'. "\n";
    foreach($languagePathList as $key => $languagePath)
    {

        if (isset($_REQUEST['lang']) && $key == $_REQUEST['lang'] )
        {
            $out .= "<option value=\"" . $key . "\" selected=\"selected\">" . $key . "</option>";
        }
        else
        {
            $out .= "<option value=\"" . $key ."\">" . $key . "</option>";
        }
    }
    $out .= "</select>";
    $out .= "<p><input type=\"submit\" value=\"OK\" /></p>";
    $out .= "</form>";
}
else
{
    $out .= "No language folder";
}

// if select language and laguage exists

if (isset($_REQUEST['lang']))
{

    $languageToBuild = array();

    if ($_REQUEST['lang'] == 'all')
    {
        foreach ($languagePathList as $language => $languagePath)
        {
            $languageToBuild[] = $language;
        }
    }
    else
    {
        $languageToBuild[] = $_REQUEST['lang'];
    }

    $out .= "<ol>\n";

    foreach( $languageToBuild as $language )
    {

        $languagePath = $languagePathList[$language];

        // get language name and display it

        $out .= '<li><strong>' . $language . '</strong>' . "\n"
        .    '<ul>' . "\n";

        //--- BUILD COMPLETE
        // get the different variables

        $sql = " SELECT DISTINCT used.varName, trans.varFullContent
                FROM " . $tbl_used_lang . " used, " . $tbl_translation  . " trans
                WHERE trans.language = '$language'
                  AND used.varName = trans.varName
                  AND used.sourceFile NOT LIKE '%/install/%' 
                  AND trans.sourceFile NOT LIKE '%/install.lang.php%' 
                ORDER BY used.varName, trans.varContent";

        $result = claro_sql_query($sql) or die ("QUERY FAILED: " .  __LINE__);

        if ($result)
        {
            $languageVarList = array();

            while ($row=mysql_fetch_array($result))
            {
                $thisLangVar['name'   ] = $row['varName'       ];
                $thisLangVar['content'] = $row['varFullContent'];

                $languageVarList[] = $thisLangVar;
            }
        }

        chdir ($languagePath);

        $out .= '<li>Create file: ' . $languagePath . '/' . LANG_COMPLETE_FILENAME . '</li>';

        $fileHandle = fopen(LANG_COMPLETE_FILENAME, 'w') or die("FILE OPEN FAILED FOR ".$languagePath." AT LINE ". __LINE__);

        // build language files

        if ($fileHandle && count($languageVarList) > 0)
        {
            fwrite($fileHandle, "<?php \n");

            foreach($languageVarList as $thisLangVar)
            {
                $string = build_translation_line_file($thisLangVar['name'],$thisLangVar['content']) ;
                fwrite($fileHandle, $string) or die ("FILE WRITE FAILED FOR ".$languagePath." AT LINE ". __LINE__);
            }

            fwrite($fileHandle, "?>");
        }

        fclose($fileHandle) or die ("FILE CLOSE FAILED FOR ".$languagePath." AT LINE ". __LINE__);

        //--- BUILD INSTALL
        // get the different variables

        $sql = " SELECT DISTINCT used.varName, trans.varFullContent
                FROM " . $tbl_used_lang . " used, " . $tbl_translation  . " trans
                WHERE trans.language = '$language'
                  AND used.varName = trans.varName
                  AND used.sourceFile LIKE '%/install/%' 
                  AND trans.sourceFile LIKE '%/install.lang.php%' 
                ORDER BY used.varName, trans.varContent";

        $result = claro_sql_query($sql) or die ("QUERY FAILED: " .  __LINE__);

        if ($result)
        {
            $languageVarList = array();

            while ($row = mysql_fetch_array($result))
            {
                $thisLangVar['name'   ] = $row['varName'       ];
                $thisLangVar['content'] = $row['varFullContent'];

                $languageVarList[] = $thisLangVar;
            }
        }

        chdir ($languagePath);

        $out .= '<li>Create file: ' . $languagePath . '/' . LANG_INSTALL_FILENAME . '</li>';

        $fileHandle = fopen(LANG_INSTALL_FILENAME, 'w') or die("FILE OPEN FAILED FOR ".$languagePath." AT LINE ". __LINE__);

        // build language files

        if ($fileHandle && count($languageVarList) > 0)
        {
            fwrite($fileHandle, "<?php \n");

            foreach($languageVarList as $thisLangVar)
            {
                $string = build_translation_line_file($thisLangVar['name'],$thisLangVar['content']) ;
                fwrite($fileHandle, $string) or die ("FILE WRITE FAILED FOR ".$languagePath." AT LINE ". __LINE__);
            }

            fwrite($fileHandle, "?>");
        }

        fclose($fileHandle) or die ("FILE CLOSE FAILED FOR ".$languagePath." AT LINE ". __LINE__);
        
        $out .= '</ul>' . "\n" . '</li>' . "\n";

    }
    $out .= "</ol>\n";

} // end sizeof($languagePathList) > 0

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

$out .= "<p><em>Execution time: $totaltime</em></p>\n"
.    '<a href="'.$urlTranslation.'">&lt;&lt; Back</a>' . "\n";

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>
