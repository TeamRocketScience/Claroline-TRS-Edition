<?php // $Id: question_pool.php 13023 2011-03-31 13:39:17Z dkp1060 $
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision: 13023 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 */

$tlabelReq = 'CLQWZ';

require '../../inc/claro_init_global.inc.php';

if ( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// courseadmin reserved page
if( !$is_allowedToEdit )
{
    header("Location: ../exercise.php");
    exit();
}

require_once '../lib/add_missing_table.lib.php';
init_qwz_questions_categories ();

// tool libraries
include_once '../lib/exercise.class.php';
include_once '../lib/question.class.php';
include_once '../lib/exercise.lib.php';

// claroline libraries
include_once get_path('incRepositorySys').'/lib/form.lib.php';
include_once get_path('incRepositorySys').'/lib/pager.lib.php';
include_once get_path('incRepositorySys').'/lib/fileManage.lib.php';

/*
 * DB tables definition for list query
 */
$tbl_cdb_names = get_module_course_tbl( array( 'qwz_exercise', 'qwz_question', 'qwz_rel_exercise_question' ), claro_get_current_course_id() );
$tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];
$tbl_quiz_question = $tbl_cdb_names['qwz_question'];
$tbl_quiz_rel_exercise_question = $tbl_cdb_names['qwz_rel_exercise_question'];

/*
 * Init request vars
 */
if ( isset($_REQUEST['cmd']) )    $cmd = $_REQUEST['cmd'];
else                            $cmd = '';

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else                                                            $exId = null;

if( isset($_REQUEST['quId']) && is_numeric($_REQUEST['quId']) ) $quId = (int) $_REQUEST['quId'];
else                                                            $quId = null;

if( isset($_REQUEST['filter']) )     $filter = $_REQUEST['filter'];
else                                $filter = 'all';

$categoryId = (substr($filter,0,10) == 'categoryId')&& is_numeric(substr($filter,10))?substr($filter,10):null;

/*
 * Init other vars
 */
$exercise = new Exercise();
if( !is_null($exId) )
{
    $exercise->load($exId);
}

$dialogBox = new DialogBox();

/*
 * Execute commands
 */
// use question in exercise
if( $cmd == 'rqUse' && !is_null($quId) && !is_null($exId) )
{
    if( $exercise->addQuestion($quId) )
    {
        // TODO show confirmation and back link
        header('Location: edit_exercise.php?exId='.$exId);
    }
}

// delete question
if( $cmd == 'delQu' && !is_null($quId) )
{
    $question = new Question();
    if( $question->load($quId) )
    {
        if( !$question->delete() )
        {
            // TODO show confirmation and list
        }
    }
}

// export question
if( $cmd == 'exExport' && get_conf('enableExerciseExportQTI') )
{
    require_once '../export/qti2/qti2_export.php';
    require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
    require_once get_path('incRepositorySys') . '/lib/file.lib.php';
    require_once get_path('incRepositorySys') . '/lib/thirdparty/pclzip/pclzip.lib.php';

    $question = new Qti2Question();
    $question->load($quId);

    // contruction of XML flow
    $xml = $question->export();

    // remove trailing slash
    if( substr($question->questionDirSys, -1) == '/' )
    {
        $question->questionDirSys = substr($question->questionDirSys, 0, -1);
    }

    //save question xml file
    if( !file_exists($question->questionDirSys) )
    {
        claro_mkdir($question->questionDirSys,CLARO_FILE_PERMISSIONS);
    }

    if( $fp = @fopen($question->questionDirSys."/question_".$quId.".xml", 'w') )
    {
        fwrite($fp, $xml);
        fclose($fp);
    }
    else
    {
        // interrupt process
    }

    // list of dirs to add in archive
    $filePathList[] = $question->questionDirSys;


    /*
     * BUILD THE ZIP ARCHIVE
     */

    // build and send the zip
    if( sendZip($question->getTitle(), $filePathList, $question->questionDirSys) )
    {
        exit();
    }
    else
    {
        $dialogBox->error( get_lang("Unable to send zip file") );
    }
}

/*
 * Get list
 */
//-- pager init
if( !isset($_REQUEST['offset']) )    $offset = 0;
else                                $offset = $_REQUEST['offset'];

//-- filters handling
if( !is_null($exId) )    $filterList = get_filter_list($exId);
else                    $filterList = get_filter_list();

if( is_numeric($filter) )
{
    $filterCondition = " AND REQ.`exerciseId` = ".$filter;
}
elseif( $filter == 'orphan' )
{
    $filterCondition = " AND REQ.`exerciseId` IS NULL ";
}
else if (! is_null($categoryId) )
{
    $filterCondition = "AND id_category='".(int)$categoryId."' ";
}
else // $filter == 'all'
{
    $filterCondition = "";
}

//-- prepare query
if ( !is_null($categoryId))
{
     // Filter on categories 
         $sql = "SELECT Q.`id`, Q.`title`, Q.`type`, Q.`id_category`
              FROM `".$tbl_quiz_question."` AS Q
              WHERE 1 = 1
             " . $filterCondition . "
          GROUP BY Q.`id`
          ORDER BY Q.`title`, Q.`id`";
}
else if( !is_null($exId) )
{
    $questionList = $exercise->getQuestionList();

    if( is_array($questionList) && !empty($questionList) )
    {
        foreach( $questionList as $aQuestion )
        {
            $questionIdList[] = $aQuestion['id'];
        }
        $questionCondition = " AND Q.`id` NOT IN ("  . implode(', ', array_map( 'intval', $questionIdList) ) . ") ";
    }
    else
    {
        $questionCondition = "";
    }

    // TODO probably need to adapt query with a left join on rel_exercise_question for filter

    $sql = "SELECT Q.`id`, Q.`title`, Q.`type`, Q.`id_category`
              FROM `".$tbl_quiz_question."` AS Q
              LEFT JOIN `".$tbl_quiz_rel_exercise_question."` AS REQ
              ON REQ.`questionId` = Q.`id`
              WHERE 1 = 1
             " . $questionCondition . "
             " . $filterCondition . "
          GROUP BY Q.`id`
          ORDER BY Q.`title`, Q.`id`";

}
else
{
    $sql = "SELECT Q.`id`, Q.`title`, Q.`type`, Q.`id_category`
              FROM `".$tbl_quiz_question."` AS Q
              LEFT JOIN `".$tbl_quiz_rel_exercise_question."` AS REQ
              ON REQ.`questionId` = Q.`id`
              WHERE 1 = 1
             " . $filterCondition . "
          GROUP BY Q.`id`
          ORDER BY Q.`title`, Q.`id`";
}

// get list
$myPager = new claro_sql_pager($sql, $offset, get_conf('questionPoolPager',25));
$questionList = $myPager->get_result_list();

/*
 * Output
 */

if( !is_null($exId) )
{
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercise'), './edit_exercise.php?exId='.$exId );
    ClaroBreadCrumbs::getInstance()->setCurrent( get_lang('Question pool'), $_SERVER['PHP_SELF'].'?exId='.$exId );
    $pagerUrl = $_SERVER['PHP_SELF'].'?exId='.$exId;
}
else if ( !is_null($categoryId) )
{
	$pagerUrl = $_SERVER['PHP_SELF'].'?filter='.$filter;
}
else
{
    ClaroBreadCrumbs::getInstance()->setCurrent( get_lang('Question pool'), $_SERVER['PHP_SELF'] );
    $pagerUrl = $_SERVER['PHP_SELF'];
}

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercises'), get_module_url('CLQWZ').'/exercise.php' );

$nameTools = get_lang('Question pool');

$out = '';

$out .= claro_html_tool_title($nameTools);

$out .= $dialogBox->render();

//-- filter listbox
$attr['onchange'] = 'filterForm.submit()';

$out .= "\n"
.     '<form method="get" name="filterForm" action="question_pool.php">' . "\n"
.     '<input type="hidden" name="exId" value="'.$exId.'" />' . "\n"
.     '<p align="right">' . "\n"
.     '<label for="filter">'.get_lang('Filter').'&nbsp;:&nbsp;</label>' . "\n"
.     claro_html_form_select('filter',$filterList, $filter, $attr) . "\n"
.     '<noscript>' . "\n"
.     '<input type="submit" value="'.get_lang('Ok').'" />' . "\n"
.     '</noscript>' . "\n"
.     '</p>' . "\n"
.     '</form>' . "\n\n";

if( !is_null($exId) )
{
    $cmd_menu[] = '<a class="claroCmd" href="./edit_exercise.php?exId='.$exId.'">&lt;&lt; '.get_lang('Go back to the exercise').'</a>';
}
$cmd_menu[] = '<a class="claroCmd" href="./edit_question.php?cmd=rqEdit">'.get_lang('New question').'</a>';

$out .= claro_html_menu_horizontal($cmd_menu);

//-- pager
$out .= $myPager->disp_pager_tool_bar($pagerUrl);

//-- list
$out .= '<table class="claroTable emphaseLine" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">' . "\n\n"
.     '<thead>' . "\n"
.     '<tr class="headerX">' . "\n"
.     '<th>' . get_lang('Id') . '</th>' . "\n"
.     '<th>' . get_lang('Question') . '</th>' . "\n"
.     '<th>' . get_lang('Category') . '</th>' . "\n"
.     '<th>' . get_lang('Answer type') . '</th>' . "\n";
$colspan = 2;
if( !is_null($exId) )
{
    $out .= '<th>' . get_lang('Reuse') . '</th>' . "\n";
    $colspan++;
}
else
{
    $out .= '<th>' . get_lang('Modify') . '</th>' . "\n"
    .     '<th>' . get_lang('Delete') . '</th>' . "\n";
    $colspan += 2;

    if( get_conf('enableExerciseExportQTI') )
    {
        $out .= '<th colspan="2">' . get_lang('Export') . '</th>' . "\n";
        $colspan++;
    }
}

$out .= '</tr>' . "\n"
.     '</thead>' . "\n\n"
.     '<tbody>' . "\n";

if( !empty($questionList) )
{
    $questionTypeLang['MCUA'] = get_lang('Multiple choice (Unique answer)');
    $questionTypeLang['MCMA'] = get_lang('Multiple choice (Multiple answers)');
    $questionTypeLang['TF'] = get_lang('True/False');
    $questionTypeLang['FIB'] = get_lang('Fill in blanks');
    $questionTypeLang['MATCHING'] = get_lang('Matching');

    foreach( $questionList as $question )
    {
        $out .= '<tr>'
        .   '<td align="center">' . $question['id'] . '</td>' . "\n"
        .     '<td>'.$question['title'].'</td>' . "\n"
        ;
        
        $out .=  '<td>'.getCategoryTitle( $question['id_category']) . '</td>' . "\n";
        

        // answer type
        $out .= '<td><small>'.$questionTypeLang[$question['type']].'</small></td>' . "\n";

        if( !is_null($exId) )
        {
            // re-use
            $out .= '<td align="center">'
            .     '<a href="question_pool.php?exId='.$exId.'&amp;cmd=rqUse&amp;quId='.$question['id'].'">'
            .     '<img src="' . get_icon_url('select') . '" alt="'.get_lang('Modify').'" />'
            .     '</a>'
            .     '</td>' . "\n";
        }
        else
        {
            // edit
            $out .= '<td align="center">'
            .     '<a href="edit_question.php?quId='.$question['id'].'">'
            .     '<img src="' . get_icon_url('edit') . '" alt="'.get_lang('Modify').'" />'
            .     '</a>'
            .     '</td>' . "\n";

            // delete question from database
            $confirmString = get_lang('Are you sure you want to completely delete this question ?');

            $out .= '<td align="center">'
            .     '<a href="question_pool.php?exId='.$exId.'&amp;cmd=delQu&amp;quId='.$question['id'].'" onclick="javascript:if(!confirm(\''.clean_str_for_javascript($confirmString).'\')) return false;">'
            .     '<img src="' . get_icon_url('delete') . '" alt="'.get_lang('Delete').'" />'
            .     '</a>'
            .     '</td>' . "\n";

            if( get_conf('enableExerciseExportQTI') )
            {
                // export
                $out .= '<td align="center">'
                .     '<a href="question_pool.php?exId='.$exId.'&amp;cmd=exExport&amp;quId='.$question['id'].'">'
                .     '<img src="' . get_icon_url('export') . '" alt="'.get_lang('Export').'" />'
                .     '</a>'
                .     '</td>' . "\n";
            }
        }
        $out .= '</tr>';

    }

}
else
{
    $out .= '<tr>' . "\n"
    .     '<td colspan="'.$colspan.'">' . get_lang('Empty') . '</td>' . "\n"
    .     '</tr>' . "\n\n";
}
$out .= '</tbody>' . "\n\n"
.     '</table>' . "\n\n";

//-- pager
$out .= $myPager->disp_pager_tool_bar($pagerUrl);

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>
