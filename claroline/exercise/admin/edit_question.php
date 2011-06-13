<?php // $Id: edit_question.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$tlabelReq = 'CLQWZ';

require '../../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// courseadmin reserved page
if( !$is_allowedToEdit )
{
    header("Location: ../exercise.php");
    exit();
}

// tool libraries
include_once '../lib/exercise.class.php';
include_once '../lib/question.class.php';

include_once '../lib/exercise.lib.php';

// claroline libraries
include_once get_path('incRepositorySys') . '/lib/form.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileDisplay.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileUpload.lib.php';
include_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
include_once get_path('incRepositorySys') . '/lib/htmlxtra.lib.php';

/*
 * Init request vars
 */
if ( isset($_REQUEST['cmd']) )    $cmd = $_REQUEST['cmd'];
else                            $cmd = '';

if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) ) $exId = (int) $_REQUEST['exId'];
else                                                            $exId = null;

if( isset($_REQUEST['quId']) && is_numeric($_REQUEST['quId']) ) $quId = (int) $_REQUEST['quId'];
else                                                            $quId = null;

/*
 * Init other vars
 */
$question = new Question();

if( !is_null($quId) && !$question->load($quId) )
{
    // question cannot be load, display new question creation form
    $cmd = 'rqEdit';
    $quId = null;
}

if( !is_null($exId) )
{
    $exercise = new Exercise();
    // if exercise cannot be load set exId to null , it probably don't exist
    if( !$exercise->load($exId) ) $exId = null;
}

$askDuplicate = false;
// quId and exId have been specified and load operations worked
if( !is_null($quId) && !is_null($exId) )
{
    // do not duplicate when there is no $exId,
    // it means that we modify the question from pool

    // do not duplicate when there is no $quId,
    // it means that question is a new one

    // check that question is used in several exercises
    if( count_exercise_using_question($quId) > 1 )
    {
        if( isset($_REQUEST['duplicate']) && $_REQUEST['duplicate'] == 'true' )
        {
            // duplicate object if used in several exercises
            $duplicated = $question->duplicate();

            // make exercise use the new created question object instead of the new one
            $exercise->removeQuestion($quId);
            $quId = $duplicated->getId(); // and reset $quId
            $exercise->addQuestion($quId);

            $question = $duplicated;
        }
        else
        {
            $askDuplicate = true;
        }
    }
}

$dialogBox = new DialogBox();
$displayForm = false;

/*
 * Execute commands
 */
if( $cmd == 'exEdit' )
{
    // if quId is null it means that we create a new question

    $question->setTitle($_REQUEST['title']);
    $question->setDescription($_REQUEST['description']);
    $question->setCategoryId($_REQUEST['categoryId']); 
    
    if( is_null($quId) ) $question->setType($_REQUEST['type']);

    // delete previous file if required
    if( isset($_REQUEST['delAttachment']) && !is_null($quId) )
    {
        $question->deleteAttachment();
    }

    if( $question->validate() )
    {
        // handle uploaded file after validation of other fields
        if( isset($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name']) )
        {
            if( !$question->setAttachment($_FILES['attachment']) )
            {
                // throw error
                $dialogBox->error( get_lang(claro_failure::get_last_failure()  ) );
            }
        }

        $insertedId = $question->save();
        if( $insertedId )
        {
            // if create a new question in exercise context
            if( is_null($quId) && !is_null($exId) )
            {
                $exercise->addQuestion($insertedId);
            }

            // create a new question
            if( is_null($quId) )
            {
                // Go to answer edition
                header('Location: edit_answers.php?exId='.$exId.'&quId='.$insertedId);
                exit();
            }
        }
        else
        {
            // sql error in save() ?
            $cmd = 'rqEdit';
        }
    }
    else
    {
        if( claro_failure::get_last_failure() == 'question_no_title' )
        {
            $dialogBox->error( get_lang('Field \'%name\' is required', array('%name' => get_lang('Title'))) );
        }
        $cmd = 'rqEdit';
    }

}

if( $cmd == 'rqEdit' )
{
    $form['title']                 = $question->getTitle();
    $form['description']         = $question->getDescription();
    $form['attachment']            = $question->getAttachment();
    $form['type']                 = $question->getType();
    $form['categoryId']           = $question->getCategoryId();

    $displayForm = true;
}

/*
 * Output
 */
if( is_null($quId) )
{
    $nameTools = get_lang('New question');
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, './edit_question.php?exId='.$exId . '&amp;cmd=rqEdit' );
}
elseif( $cmd == 'rqEdit' )
{
    $nameTools = get_lang('Edit question');
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Question'), './edit_question.php?exId='.$exId.'&amp;quId='.$quId );
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, './edit_question.php?exId='.$exId.'&amp;quId='.$quId.'&amp;cmd=rqEdit' );
}
else
{
    $nameTools = get_lang('Question');
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, './edit_question.php?exId='.$exId.'&amp;quId='.$quId );
}

if( !is_null($exId) )
{
    ClaroBreadCrumbs::getInstance()->prepend( $exercise->getTitle(), Url::Contextualize( './edit_exercise.php?exId=' . $exId ) );
    //ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercise'), './edit_exercise.php?exId='.$exId );
}
else
{
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Question pool'), './question_pool.php' );
}
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercises'), get_module_url('CLQWZ').'/exercise.php' );


$out = '';

$out .= claro_html_tool_title($nameTools);

// dialog box if required
$out .= $dialogBox->render();


$localizedQuestionType = get_localized_question_type();

if( $displayForm )
{
    $out .= '<form method="post" action="./edit_question.php?quId='.$quId.'&amp;exId='.$exId.'" enctype="multipart/form-data">' . "\n\n"
    .     '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
    .     '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' . "\n";

    $out .= '<table border="0" cellpadding="5">' . "\n";

    if( $askDuplicate )
    {
        $out .= '<tr>' . "\n"
        .     '<td>&nbsp;</td>' . "\n"
        .    '<td valign="top">'
        .    html_ask_duplicate()
        .    '</td>' . "\n"
        .    '</tr>' . "\n\n";
    }
    //--
    // title
    $out .= '<tr>' . "\n"
    .     '<td valign="top"><label for="title">'.get_lang('Title').'&nbsp;<span class="required">*</span>&nbsp;:</label></td>' . "\n"
    .     '<td><input type="text" name="title" id="title" size="60" maxlength="200" value="'.htmlspecialchars($form['title']).'" /></td>' . "\n"
    .     '</tr>' . "\n\n";

    // description
    $out .= '<tr>' . "\n"
    .     '<td valign="top"><label for="description">'.get_lang('Description').'&nbsp;:</label></td>' . "\n"
    .     '<td>'.claro_html_textarea_editor('description', $form['description']).'</td>' . "\n"
    .     '</tr>' . "\n\n";

	$questionCategoryList = getQuestionCategoryList();
    // category
    $out .=  '<tr>' . "\n"
    .     '<td valign="top"><label for="category">'.get_lang('Category').'&nbsp;:</label></td>' . "\n"
    .     '<td><select name="categoryId"><option value="0">';
    foreach ($questionCategoryList as $category)
    {
        $out .= '<option value="'.$category['id'].'"' 
            .( $category['id'] == $form['categoryId']?'selected="selected"':' ' )
            .'>'.$category['title'].'</option>';
    }
    $out .= '</option>'
    .'</td>' . "\n"
    .     '</tr>' . "\n\n";

    // attached file
    if( !empty($form['attachment']) )
    {
        $out .= '<tr>' . "\n"
        .     '<td valign="top">'.get_lang('Current file').'&nbsp;:</td>' . "\n"
        .     '<td>'
        .     '<a href="'.$question->getQuestionDirWeb().$form['attachment'].'" target="_blank">'.$form['attachment'].'</a><br />'
        .     '<input type="checkbox" name="delAttachment" id="delAttachment" /><label for="delAttachment"> '.get_lang('Delete attached file').'</label>'
        .     '</td>' . "\n"
        .     '</tr>' . "\n\n";
    }

    $out .= '<tr>' . "\n"
    .     '<td valign="top"><label for="description">'.get_lang('Attached file').'&nbsp;:</label></td>' . "\n"
    .     '<td><input type="file" name="attachment" id="attachment" size="30" /></td>' . "\n"
    .     '</tr>' . "\n\n";

    // answer type, only if new question
    if( is_null($quId) )
    {
        $out .= '<tr>' . "\n"
        .     '<td valign="top">'.get_lang('Answer type').'&nbsp;:</td>' . "\n"
        .     '<td>' . "\n"
        .     '<input type="radio" name="type" id="MCUA" value="MCUA"'
        .     ( $form['type'] == 'MCUA'?' checked="checked"':' ') . ' />'
        .     ' <label for="MCUA">'.get_lang('Multiple choice (Unique answer)').'</label>'
        .     '<br />' . "\n"
        .     '<input type="radio" name="type" id="MCMA" value="MCMA"'
        .     ( $form['type'] == 'MCMA'?' checked="checked"':' ') . ' />'
        .     ' <label for="MCMA">'.get_lang('Multiple choice (Multiple answers)').'</label>'
        .     '<br />' . "\n"
        .     '<input type="radio" name="type" id="TF" value="TF"'
        .     ( $form['type'] == 'TF'?' checked="checked"':' ') . ' />'
        .     ' <label for="TF">'.get_lang('True/False').'</label>'
        .     '<br />' . "\n"
        .     '<input type="radio" name="type" id="FIB" value="FIB"'
        .     ( $form['type'] == 'FIB'?' checked="checked"':' ') . ' />'
        .     ' <label for="FIB">'.get_lang('Fill in blanks').'</label>'
        .     '<br />' . "\n"
        .     '<input type="radio" name="type" id="MATCHING" value="MATCHING"'
        .     ( $form['type'] == 'MATCHING'?' checked="checked"':' ') . ' />'
        .     ' <label for="MATCHING">'.get_lang('Matching').'</label>'
        .     "\n"
        .     '</td>' . "\n"
        .     '</tr>' . "\n\n"
        ;
    }
    else
    {
        $out .= '<tr>' . "\n"
        .     '<td valign="top">'.get_lang('Answer type').'&nbsp;:</td>' . "\n"
        .     '<td>';

        if( isset($localizedQuestionType[$form['type']]) ) $out .= $localizedQuestionType[$form['type']];

        $out .= '</td>' . "\n"
        .     '</tr>' . "\n\n";
    }

    //--
    $out .= '<tr>' . "\n"
    .     '<td>&nbsp;</td>' . "\n"
    .     '<td><small>' . get_lang('<span class="required">*</span> denotes required field') . '</small></td>' . "\n"
    .     '</tr>' . "\n\n";

    //-- buttons
    $out .= '<tr>' . "\n"
    .     '<td>&nbsp;</td>' . "\n"
    .     '<td>'
    .     '<input type="submit" name="" id="" value="'.get_lang('Ok').'" />&nbsp;&nbsp;';
    if( !is_null($exId) )    $out .= claro_html_button('./edit_exercise.php?exId='.$exId, get_lang("Cancel") );
    else                    $out .= claro_html_button('./question_pool.php', get_lang("Cancel") );
    $out .= '</td>' . "\n"
    .     '</tr>' . "\n\n";

    $out .= '</table>' . "\n\n"
    .     '</form>' . "\n\n";
}
else
{
    $cmd_menu = array();
    $cmd_menu[] = '<a class="claroCmd" href="./edit_question.php?exId='.$exId.'&amp;cmd=rqEdit&amp;quId='.$quId.'">'
                . '<img src="' . get_icon_url('edit') . '" alt="" />'
                . get_lang('Edit question')
                . '</a>';
    $cmd_menu[] = '<a class="claroCmd" href="./edit_answers.php?exId='.$exId.'&amp;cmd=rqEdit&amp;quId='.$quId.'">'
                . '<img src="' . get_icon_url('edit') . '" alt="" />'
                . get_lang('Edit answers')
                . '</a>';
                
    $cmd_menu[] = '<a class="claroCmd" href="./edit_question.php?exId='.$exId.'&amp;cmd=rqEdit">'
                . get_lang('New question')
                . '</a>';

    $out .= claro_html_menu_horizontal($cmd_menu);

    $out .= $question->getQuestionAnswerHtml();


}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>