<?php // $Id: learningPath.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 */

/*======================================
       CLAROLINE MAIN
  ======================================*/

$tlabelReq = 'CLLNP';
require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Learning path list'), Url::Contextualize(get_module_url('CLLNP') . '/learningPathList.php') );

$nameTools = get_lang('Learning path');

// tables names

$TABLELEARNPATH         = claro_get_current_course_data('dbNameGlu') . "lp_learnPath";
$TABLEMODULE            = claro_get_current_course_data('dbNameGlu') . "lp_module";
$TABLELEARNPATHMODULE   = claro_get_current_course_data('dbNameGlu') . "lp_rel_learnPath_module";
$TABLEASSET             = claro_get_current_course_data('dbNameGlu') . "lp_asset";
$TABLEUSERMODULEPROGRESS= claro_get_current_course_data('dbNameGlu') . "lp_user_module_progress";

include_once get_path('incRepositorySys') . '/lib/learnPath.lib.inc.php';
include_once get_path('incRepositorySys') . '/lib/fileDisplay.lib.php';

// $_SESSION
if ( isset($_GET['path_id']) && $_GET['path_id'] > 0)
{
    $_SESSION['path_id'] = (int) $_GET['path_id'];
}
elseif( (!isset($_SESSION['path_id']) || $_SESSION['path_id'] == "") )
{
    // if path id not set, redirect user to the home page of learning path
    claro_redirect( get_module_url('CLLNP') . '/learningPathList.php');
    exit();
}

// use viewMode
claro_set_display_mode_available(true);

// permissions (only for the viewmode, there is nothing to edit here )
if ( claro_is_allowed_to_edit() )
{
    // if the fct return true it means that user is a course manager and than view mode is set to COURSE_ADMIN
    $pathId = (int) $_SESSION['path_id'];

    claro_redirect( get_module_url('CLLNP') . '/learningPathAdmin.php?path_id=' . $pathId );
    exit();
}

// main page
//####################################################################################\\
//############################## MODULE TABLE LIST PREPARATION #######################\\
//####################################################################################\\

if(is_learnpath_accessible( (int) $_SESSION['path_id'] ) && !claro_is_allowed_to_edit() )
{
    claro_die( get_lang('Not allowed'));
}

if(claro_is_user_authenticated())
{
    $uidCheckString = "AND UMP.`user_id` = " . (int) claro_get_current_user_id();
}
else // anonymous
{
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

$sql = "SELECT LPM.`learnPath_module_id`,
                LPM.`parent`,
                LPM.`lock`,
                M.`module_id`,
                M.`contentType`,
                M.`name`,
                UMP.`lesson_status`, UMP.`raw`,
                UMP.`scoreMax`, UMP.`credit`,
                A.`path`
           FROM (`".$TABLEMODULE."` AS M,
                   `".$TABLELEARNPATHMODULE."` AS LPM)
     LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
             ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
             ".$uidCheckString."
     LEFT JOIN `".$TABLEASSET."` AS A
            ON M.`startAsset_id` = A.`asset_id`
          WHERE LPM.`module_id` = M.`module_id`
            AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
            AND LPM.`visibility` = 'SHOW'
            AND LPM.`module_id` = M.`module_id`
       GROUP BY LPM.`module_id`
       ORDER BY LPM.`rank`";

$extendedList = claro_sql_query_fetch_all($sql);
// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$atleastOne = false;
$moduleNb = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for( $i = 0 ; $i < sizeof($flatElementList) ; $i++ )
{
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

/*================================================================
                      OUTPUT STARTS HERE
 ================================================================*/

$out = '';

// display title
$out .= claro_html_tool_title($nameTools);

//####################################################################################\\
//##################################### TITLE ########################################\\
//####################################################################################\\
$out .= nameBox(LEARNINGPATH_, DISPLAY_);
// and comment !
$out .= commentBox(LEARNINGPATH_, DISPLAY_);

//####################################################################################\\
//############################## MODULE TABLE HEADER #################################\\
//####################################################################################\\

$out .= '<br />' . "\n"
.    '<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n"
.    '<tr class="headerX" align="center" valign="top">'."\n"
.    '<th colspan="'.($maxDeep+1).'">' . get_lang('Module') . '</th>'."\n"
;


if ( claro_is_user_authenticated() )
{
    // show only progress column for authenticated users
    $out .= '<th colspan="2">'.get_lang('Progress').'</th>'."\n";
}

$out .= '</tr>'."\n\n"
    .'<tbody>'."\n\n";


  //####################################################################################\\
  //############################## MODULE TABLE LIST DISPLAY ###########################\\
  //####################################################################################\\

if (!isset($globalProg)) $globalProg = 0;

foreach ($flatElementList as $module)
{ 
    if( $module['scoreMax'] > 0 && $module['raw'] > 0 )
    {
        $raw = min($module['raw'],$module['scoreMax']); // fix when raw is > than scoreMax (it can be ...) 
        $progress = round($raw/$module['scoreMax']*100);
    }
    else
    {
        $progress = 0;
    }

    if ( $module['contentType'] == CTEXERCISE_ )
    {
        $passExercise = ($module['credit'] == "CREDIT");
    }
    else
    {
        $passExercise = false;
    }

    if ( $module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0)
    {
        if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
        {
            $progress = 100;
            $passExercise = true;
        }
        else
        {
            $progress = 0;
            $passExercise = false;
        }
    }

    // display the current module name (and link if allowed)

    $spacingString = "";
    for($i = 0; $i < $module['children']; $i++)
    {
        $spacingString .= '<td width="5">&nbsp;</td>'."\n";
    }

    $colspan = $maxDeep - $module['children']+1;

    $out .= '<tr align="center">'."\n"
        .$spacingString
        .'<td colspan="'.$colspan.'" align="left">'."\n";

    //-- if chapter head
    if ( $module['contentType'] == CTLABEL_ )
    {
        $out .= '<b>'.htmlspecialchars( claro_utf8_decode( $module['name'], get_conf( 'charset' ) ) ).'</b>'."\n";
    }
    //-- if user can access module
    elseif ( !$is_blocked )
    {
        if($module['contentType'] == CTEXERCISE_ )
            $moduleImg = get_icon_url( 'quiz', 'CLQWZ' );
        else
            $moduleImg = get_icon_url( choose_image(basename($module['path'])) );

        $contentType_alt = selectAlt($module['contentType']);
        $out .= '<a href="module.php?module_id='.$module['module_id'].'">'
            .'<img src="' . $moduleImg . '" alt="'.$contentType_alt.'" border="0" />'
            .htmlspecialchars( claro_utf8_decode( $module['name'], get_conf( 'charset' ) ) ).'</a>'."\n";
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

        if( $module['lock'] == 'CLOSE' && $module['credit'] != 'CREDIT'
            && $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'
            && !$passExercise
          )
        {
            if(claro_is_user_authenticated())
            {
                $is_blocked = true; // following modules will be unlinked
            }
            else // anonymous : don't display the modules that are unreachable
            {
                $atleastOne = true; // trick to avoid having the "no modules" msg to be displayed
                break ;
            }
        }
    }
    //-- user is blocked by previous module, don't display link
    else
    {
        if($module['contentType'] == CTEXERCISE_ )
            $moduleImg = get_icon_url( 'quiz', 'CLQWZ' );
        else
            $moduleImg = get_icon_url( choose_image(basename($module['path'])) );

        $out .= '<img src="' . $moduleImg . '" alt="'.$contentType_alt.'" border="0" />'."\n"
             .htmlspecialchars($module['name']);
    }
    $out .= '</td>'."\n";

    if( claro_is_user_authenticated() && ($module['contentType'] != CTLABEL_) )
    {
        // display the progress value for current module
        $out .= '<td align="right">'.claro_html_progress_bar ($progress, 1).'</td>'."\n"
        .    '<td align="left">'
        .    '<small>&nbsp;' . $progress . '%</small>'
        .    '</td>' . "\n"
        ;
    }
    elseif( claro_is_user_authenticated() && $module['contentType'] == CTLABEL_ )
    {
        $out .= '<td colspan="2">&nbsp;</td>'."\n";
    }

    if ($progress > 0)
    {
        $globalProg =  $globalProg+$progress;
    }

    if($module['contentType'] != CTLABEL_)
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title

    $out .= '</tr>' . "\n\n";
    $atleastOne = true;
}

$out .= '</tbody>' . "\n\n";

if ($atleastOne == false)
{
    $out .= '<tfoot>'."\n\n"
    .    '<tr>'."\n"
    .    '<td align="center" colspan="3">'.get_lang('No module').'</td>'."\n"
    .    '</tr>'."\n\n"
    .    '</tfoot>'."\n\n"
    ;
}
elseif(claro_is_user_authenticated() && $moduleNb > 0)
{
    // add a blank line between module progression and global progression
    $out .= '<tfoot>'."\n\n"
        .'<tr>'."\n"
        .'<td colspan="'.($maxDeep+3).'">&nbsp;</td>'."\n"
        .'</tr>'."\n\n"
        // display progression
        .'<tr>'."\n"
        .'<td align="right" colspan="'.($maxDeep+1).'">'.get_lang('Learning path progression :').'</td>'."\n"
        .'<td align="right">'
        .claro_html_progress_bar(round($globalProg / ($moduleNb) ), 1 )
        .'</td>'."\n"
        .'<td align="left">'
        .'<small>&nbsp;'.round($globalProg / ($moduleNb) ) .'%</small>'
        .'</td>'."\n"
        .'</tr>'."\n\n"
        .'</tfoot>'."\n\n";
}
$out .= '</table>'."\n\n";

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>