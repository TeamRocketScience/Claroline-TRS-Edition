<?php // $Id: display_content_diff.php 11656 2009-03-05 09:29:35Z dimitrirambout $
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
 * This script displays all the variables 
 * with the same content and a different name.
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');
require_once(get_path( 'includePath' )."/lib/pager.lib.php");

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';
$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

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
$dialogBox = new DialogBox();
$nameTools = 'Display different variables with the same content';

$urlSDK = $rootAdminWeb . 'xtra/sdk/'; 
$urlTranslation = $urlSDK . 'translation_index.php';

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

include($includePath."/claro_init_header.inc.php");

echo claro_html_tool_title($nameTools);

$dialogBox->title('Change language');
// start form

$form = "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";

if (isset($_REQUEST['language'])) 
{
    $language = $_REQUEST['language'];
}
else 
{
    $language = DEFAULT_LANGUAGE ;
}

// display select box with language in the table


$sql = "SELECT DISTINCT language 
        FROM ". $tbl_translation . "
        ORDER BY language ";
$results = claro_sql_query($sql);

$form .= "<select name=\"language\">";
while($result=mysql_fetch_row($results))
{
    if ($result[0] == $language) 
    {
        $form .= "<option value=$result[0] selected=\"selected\">" . $result[0] . "</option>";
    }
    else
    {
        $form .= "<option value=$result[0]>" . $result[0] . "</option>";
    }
}
$form .= "</select>";

$form .= "<input type=\"submit\" value=\"OK\" />";
$form .= "</form>";


$dialogBox->form($form);
echo $dialogBox->render();

// select variables with same content

$sql = " SELECT DISTINCT L1.language , L1.varContent , L1.varName , L1.sourceFile
    FROM ". $tbl_translation . " L1,
         ". $tbl_translation . " L2,
         ". $tbl_used_lang . " U
    WHERE L1.language = \"" . $language . "\" and
        L1.language = L2.language and
        L1.varContent = L2.varContent and
        L1.varName <> L2.varName and
        L1.varName = U.varName
    ORDER BY L1.varContent, L1.varName";

// build pager

$myPager = new claro_sql_pager($sql, $offset, $resultPerPage);
$results = $myPager->get_result_list();

// display nb results

echo '<p>' . get_lang('Total') . ': ' . $myPager->totalItemCount . '</p>' ;

// display pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?language='.$language);

// display table header 

echo "<table class=\"claroTable\" width=\"100%\">
<thead>
<tr class=\"headerX\">
<th>N°</th>
<th>language</th>
<th>varName</th>
<th>varContent</th>
<th>sourceFile</th>
</tr>
</thead>
<tbody>";

$varContent="";
$i = $offset;
$color = true;

foreach ($results as $result)
{
     if ($result['varContent'] != $varContent)
     {
        $varContent = $result['varContent'];
        $color = !$color;
     }
     if ($color == true)
     {
        echo "<tr style=\"background-color: #ccc;\">";
     } 
     else
     {
        echo "<tr>";
     }
     echo  "<td>" . ++$i . "</td>
           <td>" . $result['language'] . "</td>
           <td>" . $result['varName'] . "</td>
           <td>" . $result['varContent'] . "</td>
           <td>" . $result['sourceFile'] . "</td>";
     echo "</tr>";
}

echo "</tbody>\n</table>\n";

// display pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?language='.$language);

// display nb results

echo '<p>' . get_lang('Total') . ': ' . $myPager->totalItemCount . '</p>' ;

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>";

// display footer 

include($includePath."/claro_init_footer.inc.php");

?>
