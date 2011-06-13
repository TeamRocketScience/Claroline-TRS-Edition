<?php // $Id: progression_translation.php 11656 2009-03-05 09:29:35Z dimitrirambout $
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
 * This script display progression of all language.
 */

// include configuration and library file
include ('language.conf.php');
require_once ('language.lib.php');
require_once(get_path('incRepositorySys')."/lib/pager.lib.php");

// table


$tbl_used_lang = '`' . get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . TABLE_USED_LANG_VAR . '`';
$tbl_translation =  '`' . get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . TABLE_TRANSLATION . '`';

// get start time
$starttime = get_time();

// pager params

$resultPerPage = 50;

if (isset($_REQUEST['offset'])) 
{
    $offset = $_REQUEST['offset'];
}
else
{
    $offset = 0;
}

// start content
$nameTools = 'Display Progression of Translations';

$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

include get_path('incRepositorySys')."/claro_init_header.inc.php";

// count different variables in script
$sql = " SELECT count(DISTINCT varName) 
        FROM " . $tbl_used_lang . "";

$results = claro_sql_query($sql);
$row = mysql_fetch_row($results);
$count_total_diff_var = $row[0];

echo claro_html_tool_title($nameTools)
.    '<p>Total variables in Claroline scripts: <strong>' . $count_total_diff_var . '</strong></p>';

if ( isset($_REQUEST['exCmd']) && $_REQUEST['exCmd'] == 'ToTranslate' )
{

    if ( isset($_REQUEST['lang']))
    {
        $language = $_REQUEST['lang'];
    }
    else 
    {
        $language = DEFAULT_LANGUAGE ;
    }

    printf("<p><a href=\"%s\">Back</a></p>",$_SERVER['PHP_SELF']);
    printf("<h4>Missing variables in %s</h4>",$language);
    
    // count missing lang var in devel complete file for this language
    $sql = " SELECT DISTINCT u.varName, u.sourceFile 
             FROM ". $tbl_used_lang . " u 
             LEFT JOIN " . $tbl_translation . " t ON 
             (
                u.varName = t.varName 
                AND t.language=\"" . $language . "\"
             ) 
             WHERE t.varContent is NULL
             ORDER BY u.varName, u.sourceFile ";
    
    // build pager
    $myPager = new claro_sql_pager($sql, $offset, $resultPerPage);
    $result_missing_var = $myPager->get_result_list();

    // display pager
    echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?exCmd=ToTranslate&lang='.$language);
    
    // display table header
    echo "<table class=\"claroTable\" width=\"100%\" >\n";
    echo "<thead>"
         . "<tr class=\"headerX\">"
         . "<th>VarName</th>"
         . "<th>SourceFile</th>"
         . "</tr>"
         . "</thead>"
         . "<tbody>\n";

    // variables used to switch background color
    $varName = '';
    $color = true;
    
    // browse missing variables
    foreach ( $result_missing_var as $row_missing_var ) 
    {
        // get values
        $sourceFile = $row_missing_var['sourceFile'];
        if ($row_missing_var['varName'] != $varName)
        {
            $varName = $row_missing_var['varName'];
            $color = !$color;
        }
        
        // display row
        if ($color)
        {
            echo "<tr style=\"background-color: #ccc;\">\n";
        } 
        else
        {
            echo "<tr>\n";
        }

        echo "<td>". $varName ."</td>\n"
            . "<td>". $sourceFile ."</td>\n"
            . "</tr>\n";
    }

    // display table footer
    echo "</tbody>";
    echo "</table>";
    
    // display pager
    echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?exCmd=ToTranslate&lang='.$language);
    
    // display nb results
    echo '<p>' . get_lang('Total') . ': ' . $myPager->totalResultCount . '</p>' ;

}
else
{

    /*
     * Display a table and display each language variable translated, to translate and complete pourcentage of the translation
     */

    // get all languages
    $sql = " SELECT DISTINCT language 
             FROM " . $tbl_translation . "";
    $result_language = claro_sql_query($sql);

    // display table header
    echo "<table class=\"claroTable\">\n";
    echo "<thead>
          <tr class=\"headerX\">
           <th>Language</th>
           <th>Translated</th>
           <th>To translate</th>
           <th>Complete %</th>
          </tr>
          </thead>
          <tbody>\n";
    
    while ($row_language = mysql_fetch_array($result_language)) 
    {
        // get language
        $language = $row_language['language'];
    
        // count missing lang var in devel complete file for this language
        $sql = " SELECT count(DISTINCT u.varName) 
                 FROM ". $tbl_used_lang . " u 
                 LEFT JOIN " . $tbl_translation . " t ON 
                 (
                    u.varName = t.varName 
                    AND t.language=\"" . $language . "\"
                 ) 
                 WHERE t.varContent is NOT NULL ";
        
        // execute query and get result
        $result_missing_var_count = claro_sql_query($sql);
        $row_missing_var_count  = mysql_fetch_row($result_missing_var_count);

        // compute field
        $count_var_translated = $row_missing_var_count[0];
        $count_var_to_translate = $count_total_diff_var - $count_var_translated;
        $pourcent_progession = (float) round (1000 * $count_var_translated / $count_total_diff_var) / 10;
    
        // display row

        if ( $pourcent_progession > 60 ) echo "<tr style=\"font-weight: bold;\">\n";
        else echo "<tr>\n";

        echo "<td>" . $language . "</td>\n"
             . "<td style=\"text-align: right\">" . $count_var_translated . "</td>\n"
             . "<td style=\"text-align: right\">" ;

        if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'export' )
        {
            echo $count_var_to_translate;
        } 
        else
        {
            echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?exCmd=ToTranslate&lang=" . $language . "\">" . $count_var_to_translate . "</a>";
        }
        
        echo "</td>\n" 
             . "<td style=\"text-align: right\">" . $pourcent_progession . " %</td>\n"
             . "</tr>\n";
    }
    
    echo "</tbody>";
    echo "</table>";
}

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>";

// display footer 

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>
