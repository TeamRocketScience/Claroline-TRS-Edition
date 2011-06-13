<?php // $Id: learnPath_details.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLSTAT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
 
require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
if ( ! claro_is_course_manager() ) claro_die(get_lang('Not allowed')) ; 

// path id can not be empty, return to the list of learning paths
if( empty($_REQUEST['path_id']) )
{
    claro_redirect("../learnPath/learningPathList.php");
    exit();
}

$nameTools = get_lang('Learning paths tracking');

ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, Url::Contextualize('learnPath_details.php?path_id='.$_REQUEST['path_id']) );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Learning path list'), Url::Contextualize(get_module_url('CLLNP') . '/learningPathList.php') );

// regroup table names for maintenance purpose
/*
 * DB tables definition
 */

$tbl_cdb_names               = claro_sql_get_course_tbl();
$tbl_mdb_names               = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;

$TABLECOURSUSER            = $tbl_rel_course_user;
$TABLEUSER              = $tbl_user;

require_once(get_path('incRepositorySys').'/lib/statsUtils.lib.inc.php');
require_once(get_path('incRepositorySys').'/lib/learnPath.lib.inc.php');

$out = '';

if ( get_conf('is_trackingEnabled') )  
{

    if ( !empty($_REQUEST['path_id']) )
    {
        $path_id = (int) $_REQUEST['path_id'];

        // get infos about the learningPath
        $sql = "SELECT `name` 
                FROM `".$TABLELEARNPATH."`
                WHERE `learnPath_id` = ". (int)$path_id;

        $learnPathName = claro_sql_query_get_single_value($sql);
    
        if( $learnPathName )
        {
            // display title
            $titleTab['mainTitle'] = $nameTools;
            $titleTab['subTitle'] = htmlspecialchars($learnPathName);
            $out .= claro_html_tool_title($titleTab);

            // display a list of user and their respective progress    
            $sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
                    FROM `".$TABLEUSER."` AS U, 
                         `".$TABLECOURSUSER."` AS CU
                    WHERE U.`user_id`= CU.`user_id`
                    AND CU.`code_cours` = '". claro_sql_escape(claro_get_current_course_id()) ."'";

            $usersList = claro_sql_query_fetch_all($sql);

            // display tab header
            $out .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'."\n\n"
                   .'<tr class="headerX" align="center" valign="top">'."\n"
                .'<th>'.get_lang('Student').'</th>'."\n"
                .'<th colspan="2">'.get_lang('Progress').'</th>'."\n"
                .'</tr>'."\n\n"
                .'<tbody>'."\n\n";

            // display tab content
            foreach ( $usersList as $user )
            {
                $lpProgress = get_learnPath_progress($path_id,$user['user_id']);
                $out .= '<tr>'."\n"
                    .'<td><a href="lp_modules_details.php?uInfo='.$user['user_id'].'&amp;path_id='.$path_id.'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
                    .'<td align="right">'
                    .claro_html_progress_bar($lpProgress, 1)
                      .'</td>'."\n"
                    .'<td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
                    .'</tr>'."\n\n";
            }
            // foot of table
            $out .= '</tbody>'."\n\n".'</table>'."\n\n";
        }
    }
}
// not allowed
else
{
    $dialogBox = new DialogBox();
    $dialogBox->success( get_lang('Tracking has been disabled by system administrator.') );
    $out .= $dialogBox->render();
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>