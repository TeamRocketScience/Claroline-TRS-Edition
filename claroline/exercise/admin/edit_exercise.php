<?php // $Id: edit_exercise.php 12923 2011-03-03 14:23:57Z abourguignon $
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
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 */

$tlabelReq = 'CLQWZ';

require '../../inc/claro_init_global.inc.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// courseadmin reserved page
if( !$is_allowedToEdit )
{
    header("Location: ../exercise.php");
    exit();
}

// tool libraries
include_once '../lib/exercise.class.php';

include_once '../lib/exercise.lib.php';

// claroline libraries
include_once $includePath . '/lib/form.lib.php';

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
$exercise = new Exercise();

if( !is_null($exId) && !$exercise->load($exId) )
{
    $cmd = 'rqEdit';
}

$dialogBox = new DialogBox();
$displayForm = false;
$displaySettings = true;

/*
 * Execute commands
 */
if( $cmd == 'rmQu' && !is_null($quId) )
{
    $exercise->removeQuestion($quId);
}

if( $cmd == 'mvUp' && !is_null($quId) )
{
    $exercise->moveQuestionUp($quId);
}

if( $cmd == 'mvDown' && !is_null($quId) )
{
    $exercise->moveQuestionDown($quId);
}

if( $cmd == 'exEdit' )
{
    $exercise->setTitle($_REQUEST['title']);
    $exercise->setDescription($_REQUEST['description']);
    $exercise->setDisplayType($_REQUEST['displayType']);

    if( isset($_REQUEST['randomize']) && $_REQUEST['randomize'] )
    {
        $exercise->setShuffle($_REQUEST['questionDrawn']);
    }
    else
    {
        $exercise->setShuffle(0);
    }
    
    if( isset( $_REQUEST['useSameShuffle'] ) && $_REQUEST['useSameShuffle'] )
    {
        $exercise->setUseSameShuffle( $_REQUEST['useSameShuffle'] );
    }
    else
    {
        $exercise->setUseSameShuffle(0);
    }
    
    $exercise->setShowAnswers($_REQUEST['showAnswers']);

    $exercise->setStartDate( mktime($_REQUEST['startHour'],$_REQUEST['startMinute'],0,$_REQUEST['startMonth'],$_REQUEST['startDay'],$_REQUEST['startYear']) );

    if( isset($_REQUEST['useEndDate']) && $_REQUEST['useEndDate'] )
    {
        $exercise->setEndDate( mktime($_REQUEST['endHour'],$_REQUEST['endMinute'],0,$_REQUEST['endMonth'],$_REQUEST['endDay'],$_REQUEST['endYear']) );
    }
    else
    {
        $exercise->setEndDate(null);
    }

    if( isset($_REQUEST['useTimeLimit']) && $_REQUEST['useTimeLimit'] )
    {
        $exercise->setTimeLimit( $_REQUEST['timeLimitMin']*60 + $_REQUEST['timeLimitSec'] );
    }
    else
    {
        $exercise->setTimeLimit(0);
    }

    $exercise->setAttempts($_REQUEST['attempts']);
    $exercise->setAnonymousAttempts($_REQUEST['anonymousAttempts']);
    
    $exercise->setQuizEndMessage($_REQUEST['quizEndMessage']);

    if( $exercise->validate() )
    {
        if( $insertedId = $exercise->save() )
        {
            if( is_null($exId) )
            {
                $dialogBox->success( get_lang('Exercise added') );
                $eventNotifier->notifyCourseEvent("exercise_added",claro_get_current_course_id(), claro_get_current_tool_id(), $insertedId, claro_get_current_group_id(), "0");
                $exId = $insertedId;
            }
            else
            {
                $dialogBox->success( get_lang('Exercise modified') );
                $eventNotifier->notifyCourseEvent("exercise_updated",claro_get_current_course_id(), claro_get_current_tool_id(), $insertedId, claro_get_current_group_id(), "0");
            }
            $displaySettings = true;
        }
        else
        {
            // sql error in save() ?
            $cmd = 'rqEdit';
        }

    }
    else
    {
        if( claro_failure::get_last_failure() == 'exercise_no_title' )
        {
            $dialogBox->error( get_lang('Field \'%name\' is required', array('%name' => get_lang('Title'))) );
        }
        elseif( claro_failure::get_last_failure() == 'exercise_incorrect_dates')
        {
            $dialogBox->error( get_lang('Start date must be before end date ...') );
        }
        $cmd = 'rqEdit';
    }
}

if( $cmd == 'rqEdit' )
{
    $form['title']                 = $exercise->getTitle();
    $form['description']         = $exercise->getDescription();
    $form['displayType']         = $exercise->getDisplayType();
    $form['randomize']             = (boolean) $exercise->getShuffle() > 0;
    $form['questionDrawn']        = $exercise->getShuffle();
    $form['useSameShuffle']      = (boolean) $exercise->getUseSameShuffle(); 
    $form['showAnswers']         = $exercise->getShowAnswers();

    $form['startDate']             = $exercise->getStartDate(); // unix

    if( is_null($exercise->getEndDate()) )
    {
        $form['useEndDate']        = false;
        $form['endDate']         = 0;
    }
    else
    {
        $form['useEndDate']        = true;
        $form['endDate']         = $exercise->getEndDate();
    }

    $form['useTimeLimit']         = (boolean) $exercise->getTimeLimit();
    $form['timeLimitSec']       = $exercise->getTimeLimit() % 60 ;
    $form['timeLimitMin']         = ($exercise->getTimeLimit() - $form['timeLimitSec']) / 60;

    $form['attempts']             = $exercise->getAttempts();
    $form['anonymousAttempts']     = $exercise->getAnonymousAttempts();
    
    $form['quizEndMessage'] = $exercise->getQuizEndMessage();

    $displayForm = true;
}



/*
 * Output
 */


if( is_null($exId) )
{
    $nameTools = get_lang('New exercise');
    $toolTitle = $nameTools;
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercises'), get_module_url('CLQWZ').'/exercise.php' );
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, './edit_exercise.php?cmd=rqEdit' );
}
elseif( $cmd == 'rqEdit' )
{
    $nameTools = get_lang('Edit exercise');
    $toolTitle['mainTitle'] = $nameTools;
    $toolTitle['subTitle'] = $exercise->getTitle();
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercise'), './edit_exercise.php?exId='.$exId );
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercises'), get_module_url('CLQWZ').'/exercise.php' );
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, './edit_exercise.php?cmd=rqEdit&amp;exId='.$exId );
}
else
{
    $nameTools = get_lang('Exercise');
    $toolTitle['mainTitle'] = $nameTools;
    $toolTitle['subTitle'] = $exercise->getTitle();
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Exercises'), get_module_url('CLQWZ').'/exercise.php' );
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, './edit_exercise.php?exId='.$exId );
}

$jsLoader = JavascriptLoader::getInstance();
$jsLoader->load( 'claroline.ui');

$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'exercise', 'screen');

$out = '';

$out .= claro_html_tool_title($toolTitle);

// dialog box if required
$out .= $dialogBox->render();


if( $displayForm )
{
    $out .= '<form method="post" action="./edit_exercise.php?exId='.$exId.'" >' . "\n\n"
    .    claro_form_relay_context()
    .     '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
    .     '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' . "\n"
    ;
    
    $out .= '<fieldset>' . "\n"
    .   '<legend>' . get_lang('Basic information') . '</legend>' . "\n"
    .   '<dl>' . "\n";
    
    //title
    $out .= '<dt><label for="title">' . get_lang('Title') . '&nbsp;<span class="required">*</span>&nbsp;:' . '</label></dt>' . "\n"
    .   '<dd>'
    .   '<input type="text" name="title" id="title" size="60" maxlength="200" value="'. htmlspecialchars($form['title']) .'" />'
    .   '</dd>' . "\n";
    
    //description
    $out .= '<dt><label for="description">' . get_lang('Description') . '&nbsp;:</label></dt>' . "\n"
    .   '<dd>'
    .   '<div style="width: 700px;">' . claro_html_textarea_editor('description', $form['description']) . '</div>'
    .   '</dd>' . "\n";
    
    // exercise type
    $out .= '<dt>' . get_lang('Exercise type') . '&nbsp;:</dt>' . "\n"
    .   '<dd>' . "\n"
    .   '<input type="radio" name="displayType" id="displayTypeOne" value="ONEPAGE" class="radio" '
    .   ( $form['displayType'] == 'ONEPAGE'?' checked="checked"':' ') . ' />&nbsp;'
    .   '<label for="displayTypeOne">' . get_lang('On an unique page') . '</label>' . "\n"
    .   '<br />' . "\n"    
    .   '<input type="radio" name="displayType" id="displayTypeSeq" value="SEQUENTIAL" class="radio" '
    .   ( $form['displayType'] == 'SEQUENTIAL'?' checked="checked"':' ') . ' />&nbsp;'    
    .   '<label for="displayTypeSeq">' . get_lang('One question per page (sequential)') . '</label>' . "\n"
    .   '</dd>' . "\n";
    
    // random question
    $questionCount = count($exercise->getQuestionList());

    if( !is_null($exId) && $questionCount > 0 )
    {
        // prepare select option list
        for( $i = 1; $i <= $questionCount ; $i++)
        {
            $questionDrawnOptions[$i] = $i;
        }
        
        $out .= '<dt><label for="randomize">' . get_lang('Random questions').'&nbsp;:</label></dt>' . "\n"
        .   '<dd>'
        .   '<div>'
        .   '<input type="checkbox" name="randomize" id="randomize" class="checkbox" '
        .     ( $form['randomize']?' checked="checked"':' ') . '/>&nbsp;'
        .   get_lang('<label1>Yes</label1>, <label2>take</label2> %nb questions among %total',
                    array ( '<label1>' => '<label for="randomize">',
                            '</label1>' => '</label>',
                            '<label2>' => '<label for="questionDrawn">',
                            '</label2>' => '</label>',
                            '%nb' => claro_html_form_select('questionDrawn',
                                                            $questionDrawnOptions,
                                                            $form['questionDrawn'],
                                                            array('id' => 'questionDrawn') ) ,
                            '%total' =>  $questionCount ) )
        .   '</div><div>'
        .   '<input type="checkbox" name="useSameShuffle" value="1" class="checkbox" '
        .   ($form['useSameShuffle'] ? ' checked="checked"' : ' ') . '/>&nbsp;'
        .   get_lang('Reuse the same shuffle')        
        .   '</div>'
        .   '</dd>' . "\n";        
        
    }
    
    $out .= '</dl>' . "\n"
    .   '</fieldset>' . "\n";
    
    // Advanced Information
    
    $out .= '<fieldset id="advancedInformation" class="collapsible collapsed">' . "\n"
    .   '<legend><a href="#" class="doCollapse">' . get_lang('Advanced').' ('.get_lang('Optional').')' . '</a></legend>' . "\n"
    .   '<div class="collapsible-wrapper">' . "\n"
    .   '<dl>' . "\n";
    
    // start date
    $out .= '<dt>' . get_lang('Start date') . '&nbsp;:</dt>' . "\n"
    .   '<dd>'
    .   claro_html_date_form('startDay', 'startMonth', 'startYear', $form['startDate'], 'long')." - ".claro_html_time_form("startHour", "startMinute", $form['startDate'])
    .     '<small>' . get_lang('(d/m/y hh:mm)') . '</small>'
    .   '</dd>' . "\n";
    
    // stop date
    $out .= '<dt><label for="useEndDate">' . get_lang('End date') . '&nbsp;:</label></dt>' . "\n"
    .   '<dd>'
    .   '<input type="checkbox" name="useEndDate" id="useEndDate" '
    .   ( $form['useEndDate']?' checked="checked"':' ') . '/>'
    .   ' <label for="useEndDate">'.get_lang('Yes').'</label>,' . "\n"
    .   claro_html_date_form('endDay', 'endMonth', 'endYear', $form['endDate'], 'long')." - ".claro_html_time_form("endHour", "endMinute", $form['endDate'])
    .   '<small>' . get_lang('(d/m/y hh:mm)') . '</small>'
    .   '</dd>' . "\n";
    
    // time limit
    $out .= '<dt><label for="useTimeLimit">' . get_lang('Time limit') . '&nbsp;:</label></dt>' . "\n"
    .   '<dd>'
    .   '<input type="checkbox" name="useTimeLimit" id="useTimeLimit" '
    .   ( $form['useTimeLimit']?' checked="checked"':' ') . '/>'
    .   ' <label for="useTimeLimit">'.get_lang('Yes').'</label>,' . "\n"
    .   ' <input type="text" name="timeLimitMin" id="timeLimitMin" size="3" maxlength="3"  value="'.$form['timeLimitMin'].'" /> '.get_lang('min.')
    .   ' <input type="text" name="timeLimitSec" id="timeLimitSec" size="2" maxlength="2"  value="'.$form['timeLimitSec'].'" /> '.get_lang('sec.')
    .   '</dd>' . "\n";
    
    // attempts allowed
    $out .= '<dt><label for="attempts">' . get_lang('Attempts allowed') . '&nbsp;:</label></dt>' . "\n"
    .   '<dd>'
    .   '<select name="attempts" id="attempts">' . "\n"
    .   '<option value="0"' . ( $form['attempts'] < 1?' selected="selected"':' ') . '>' . get_lang('unlimited') . '</option>' . "\n"
    .  '<option value="1"' . ( $form['attempts'] == 1?' selected="selected"':' ') . '>1</option>' ."\n"
    .  '<option value="2"' . ( $form['attempts'] == 2?' selected="selected"':' ') . '>2</option>' ."\n"
    .  '<option value="3"' . ( $form['attempts'] == 3?' selected="selected"':' ') . '>3</option>' ."\n"
    .  '<option value="4"' . ( $form['attempts'] == 4?' selected="selected"':' ') . '>4</option>' ."\n"
    .  '<option value="5"' . ( $form['attempts'] >= 5?' selected="selected"':' ') . '>5</option>' ."\n"
    .  '</select>' . "\n"
    .   '</dd>' . "\n";
    
    //anonymous attempts
    $out .= '<dt>' . get_lang('Anonymous attempts') . '&nbsp;:</dt>' . "\n"
    .   '<dd>'
    .   '<input type="radio" name="anonymousAttempts" id="anonymousAttemptsAllowed" value="ALLOWED"'
    .  ( $form['anonymousAttempts'] == 'ALLOWED'?' checked="checked"':' ') . ' />'
    .  ' <label for="anonymousAttemptsAllowed">'.get_lang('Allowed : do not record usernames in tracking, anonymous users can do the exercise.').'</label>'
    .  '<br />'
    .  '<input type="radio" name="anonymousAttempts" id="anonymousAttemptsNotAllowed" value="NOTALLOWED"'
    .  ( $form['anonymousAttempts'] == 'NOTALLOWED'?' checked="checked"':' ') . ' />'
    .  ' <label for="anonymousAttemptsNotAllowed">'.get_lang('Not allowed : record usernames in tracking, anonymous users cannot do the exercise.').'</label>'    
    .   '</dd>';
    
    // show answers
    $out .= '<dt>' . get_lang('Show answers') . '&nbsp;:</dt>' . "\n"
    .   '<dd>'
    .   '<input type="radio" name="showAnswers" id="showAnswerAlways" value="ALWAYS"'
    .   ( $form['showAnswers'] == 'ALWAYS'?' checked="checked"':' ') . ' />'
    .   ' <label for="showAnswerAlways">'.get_lang('Yes').'</label>'
    .   '<br />'
    .   '<input type="radio" name="showAnswers" id="showAnswerLastTry" value="LASTTRY"'
    .   ( $form['showAnswers'] == 'LASTTRY'?' checked="checked"':' ') . ' />'
    .   ' <label for="showAnswerLastTry">'.get_lang('After last allowed attempt').'</label>'
    .   '<br />'
    .   '<input type="radio" name="showAnswers" id="showAnswerNever" value="NEVER"'
    .   ( $form['showAnswers'] == 'NEVER'?' checked="checked"':' ') . ' />'
    .   ' <label for="showAnswerNever">'.get_lang('No').'</label>'
    .   '</dd>';
    
    // end form information
    $out .= '<dt>' . get_lang('Quiz end message') . '&nbsp;:</dt>' . "\n"
    .   '<dd>'
    .   '<div style="width: 700px;">' . claro_html_textarea_editor('quizEndMessage', $form['quizEndMessage']) . '</div>'
    .   '</dd>';
    
    $out .= '</dl>' . "\n"
    .   '</div>' . "\n" // fieldset-wrapper
    .   '</fieldset>' . "\n";
    
    $out .= '<div style="padding-top: 5px;">'
    .   '<small>' . get_lang('<span class="required">*</span> denotes required field') . '</small>'
    .   '</div>'
    //-- buttons
    .   '<div style="text-align: center;">'
    .     '<input type="submit" name="" id="" value="'.get_lang('Ok').'" />&nbsp;&nbsp;'
    .     claro_html_button('../exercise.php', get_lang("Cancel") )
    .   '</div>';   
    
    $out .= '</form>' . "\n\n";
}
else
{
    //-- exercise settings

    $out .= '<blockquote>'.claro_parse_user_text($exercise->getDescription()).'</blockquote>' . "\n"
    .     '<ul style="font-size:small;">' . "\n";

    $out .= '<li>'
    .     get_lang('Exercise type').'&nbsp;: '
    .     ( $exercise->getDisplayType() == 'SEQUENTIAL'?get_lang('One question per page (sequential)'):get_lang('On an unique page') )
    .     '</li>' . "\n";

    $out .= '<li>'
    .     get_lang('Random questions').'&nbsp;: '
    .     ( $exercise->getShuffle() > 0?get_lang('Yes'):get_lang('No') )
    .     '</li>' . "\n";
    
    if( $exercise->getShuffle() > 0)
    {
        $out .= '<li>'
        .   get_lang('Reuse same shuffle').'&nbsp;: '
        .   ( $exercise->getUseSameShuffle() ? get_lang('Yes') : get_lang('No') )
        .   '</li>' . "\n";
    }    
    
    $out .= '</ul>' . "\n";
    
    $out .= '<div class="collapsible collapsed"><a href="#" class="doCollapse">'. get_lang('More information') . '</a>' . "\n"
    . '<div class="collapsible-wrapper">' . "\n"
    . '<ul id="moreInformation" style="font-size:small;">' . "\n";
    
    $out .= '<li>'
      .     get_lang('Start date').'&nbsp;: '
      .     claro_html_localised_date($dateTimeFormatLong, $exercise->getStartDate())
      .     '</li>' . "\n";

    $out .= '<li>'
    .     get_lang('End date').'&nbsp;: ';

      if( !is_null($exercise->getEndDate()) )
      {
        $out .= claro_html_localised_date($dateTimeFormatLong, $exercise->getEndDate());
      }
      else
      {
          $out .= get_lang('No closing date');
      }

      $out .= '</li>' . "\n";

      $out .= '<li>'
      .     get_lang('Time limit').'&nbsp;: '
      .     ( $exercise->getTimeLimit() > 0 ? claro_disp_duration($exercise->getTimeLimit()) : get_lang('No time limitation') )
      .     '</li>' . "\n";

      $out .= '<li>'
      .     get_lang('Attempts allowed') . '&nbsp;: '
      .     ( $exercise->getAttempts() > 0 ? $exercise->getAttempts() : get_lang('unlimited') )
      .     '</li>' . "\n";

    $out .= '<li>'
      .     get_lang('Anonymous attempts') . '&nbsp;: ';
      if ( $exercise->getAnonymousAttempts() == 'ALLOWED')     $out .= get_lang('Allowed : do not record usernames in tracking, anonymous users can do the exercise.');
    else                                                    $out .= get_lang('Not allowed : record usernames in tracking, anonymous users cannot do the exercise.');
      $out .= '</li>' . "\n";

    $out .= '<li>'
    .     get_lang('Show answers')." : ";
    switch($exercise->getShowAnswers())
    {
      case 'ALWAYS' : $out .= get_lang('Yes'); break;
      case 'LASTTRY' : $out .= get_lang('After last allowed attempt'); break;
      case 'NEVER'  : $out .= get_lang('No'); break;
    }
    $out .= '</li>' . "\n";
    
    $out .= '<li>'
    .   get_lang('Quiz end message') . '&nbsp;: '
    .   '<blockquote>'.claro_parse_user_text($exercise->getQuizEndMessage()).'</blockquote>' . "\n"
    .   '</li>' . "\n";
    
    $out .= '</ul>' . "\n\n"
    . '</div>' . "\n" // collapsible-wrapper
    . '</div>' . "\n" // collaspible
    . '<br />' . "\n";

    //-- claroCmd
    $cmd_menu = array();
    $cmd_menu[] = '<a class="claroCmd" href="../exercise.php' . claro_url_relay_context('?') . '">'
                . '&lt;&lt; ' . get_lang('Back to the exercise list')
                . '</a>';
    $cmd_menu[] = '<a class="claroCmd" href="./edit_exercise.php?exId='.$exId.'&amp;cmd=rqEdit'. claro_url_relay_context('&amp;') .'">'
                . '<img src="' . get_icon_url('edit') . '" alt="" />'
                . get_lang('Edit exercise settings')
                . '</a>';
    $cmd_menu[] = '<a class="claroCmd" href="./edit_question.php?exId='.$exId.'&amp;cmd=rqEdit">'.get_lang('New question').'</a>';
    $cmd_menu[] = '<a class="claroCmd" href="./question_pool.php?exId='.$exId.'">'.get_lang('Get a question from another exercise').'</a>';


    $out .= claro_html_menu_horizontal($cmd_menu);

    //-- question list
    $questionList = $exercise->getQuestionList();

    $out .= '<table class="claroTable emphaseLine" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">' . "\n\n"
    .     '<thead>' . "\n"
    .     '<tr class="headerX">' . "\n"
    .     '<th>' . get_lang('Id') . '</th>' . "\n"
    .     '<th>' . get_lang('Question') . '</th>' . "\n"
    .    '<th>' . get_lang('Category') . '</th>' . "\n"
    .     '<th>' . get_lang('Answer type') . '</th>' . "\n"
    .     '<th>' . get_lang('Modify') . '</th>' . "\n"
    .     '<th>' . get_lang('Delete') . '</th>' . "\n"
    .     '<th colspan="2">' . get_lang('Order') . '</th>' . "\n"
    .     '</tr>' . "\n"
    .     '</thead>' . "\n\n"
    .     '<tbody>' . "\n";

    if( !empty($questionList) )
    {
        $localizedQuestionType = get_localized_question_type();

        $questionIterator = 0;

        foreach( $questionList as $question )
        {
            $questionIterator++;

            $out .= '<tr>' . "\n"
            .     '<td align="center">' . $question['id'] . '</td>' . "\n"
            .     '<td>'.$question['title'].'</td>' . "\n";

			$out .= '<td>'. getCategoryTitle( $question['id_category']) .'</td>' . "\n";
            // answer type
            $out .= '<td><small>'.$localizedQuestionType[$question['type']].'</small></td>' . "\n";

            // edit
            $out .= '<td align="center">'
            .     '<a href="edit_question.php?exId='.$exId.'&amp;quId='.$question['id'].'">'
            .     '<img src="' . get_icon_url('edit') . '" alt="'.get_lang('Modify').'" />'
            .     '</a>'
            .     '</td>' . "\n";

            // remove question from exercise
            $confirmString = get_lang('Are you sure you want to remove the question from the exercise ?');

            $out .= '<td align="center">'
            .     '<a href="edit_exercise.php?exId='.$exId.'&amp;cmd=rmQu&amp;quId='.$question['id'].'" onclick="javascript:if(!confirm(\''.clean_str_for_javascript($confirmString).'\')) return false;">'
            .     '<img src="' . get_icon_url('delete') . '" alt="'.get_lang('Delete').'" />'
            .     '</a>'
            .     '</td>' . "\n";

            // order
            // up
            $out .= '<td align="center">';
            if( $questionIterator > 1 )
            {
                $out .= '<a href="edit_exercise.php?exId='.$exId.'&amp;quId='.$question['id'].'&amp;cmd=mvUp">'
                .     '<img src="' . get_icon_url('move_up') . '" alt="'.get_lang('Move up').'" />'
                .     '</a>';
            }
            else
            {
                $out .= '&nbsp;';
            }
            $out .= '</td>' . "\n";
            // down
            $out .= '<td align="center">';
            if( $questionIterator < count($questionList) )
            {
                $out .= '<a href="edit_exercise.php?exId='.$exId.'&amp;quId='.$question['id'].'&amp;cmd=mvDown">'
                .     '<img src="' . get_icon_url('move_down') . '" alt="'.get_lang('Move down').'" />'
                .     '</a>';
            }
            else
            {
                $out .= '&nbsp;';
            }
            $out .= '</td>' . "\n";

            $out .= '</tr>' . "\n\n";;

        }

    }
    else
    {
        $out .= '<tr>' . "\n"
        .     '<td colspan="8">' . get_lang('Empty') . '</td>' . "\n"
        .     '</tr>' . "\n\n";
    }
    $out .= '</tbody>' . "\n\n"
    .     '</table>' . "\n\n";
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>