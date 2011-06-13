<?php // $Id: announcements.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * The script works with the 'annoucement' tables in the main claroline table
 *
 * DB Table structure:
 * ---
 *
 * id           : announcement id
 * contenu      : announcement content
 * visibleFrom  : date of the publication of the announcement
 * visibleUntil : date of expiration of the announcement
 * temps        : date of the announcement introduction / modification
 * title        : optionnal title for an announcement
 * ordre        : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * Script Structure:
 * ---
 *
 *        commands
 *            move up and down announcement
 *            delete announcement
 *            delete all announcements
 *            modify announcement
 *            submit announcement (new or modified)
 *
 *        display
 *            title
 *          button line
 *          form
 *            announcement list
 *            form to fill new or modified announcement
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLANN
 *
 * @author Claro Team <cvs@claroline.net>
 */

/*
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
*/

define('CONFVAL_LOG_ANNOUNCEMENT_INSERT', FALSE);
define('CONFVAL_LOG_ANNOUNCEMENT_DELETE', FALSE);
define('CONFVAL_LOG_ANNOUNCEMENT_UPDATE', FALSE);


/**
 *  CLAROLINE MAIN SETTINGS
 */

$tlabelReq = 'CLANN';
$gidReset = true;

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
$context = claro_get_current_context(CLARO_CONTEXT_COURSE);

// Local lib
require_once './lib/announcement.lib.php';

// JS for expand/collapse actions
$jsLoader = JavascriptLoader::getInstance();
$jsLoader->load( 'claroline.ui');

// get some shared lib
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';
// require_once get_path('clarolineRepositorySys') . '/linker/linker.inc.php';

FromKernel::uses('core/linker.lib');
ResourceLinker::init();

// Get specific conf file
require claro_get_conf_repository() . 'ical.conf.php';
require claro_get_conf_repository() . 'rss.conf.php';

claro_set_display_mode_available(TRUE);

// Set flag following depending on settings
$is_allowedToEdit = claro_is_allowed_to_edit();
$courseId         = claro_get_current_course_id();
$userLastLogin    = claro_get_current_user_data('lastLogin');

// DB tables definition
$tbl_cdb_names   = claro_sql_get_main_tbl();
$tbl_course_user = $tbl_cdb_names['rel_course_user'];
$tbl_user        = $tbl_cdb_names['user'];

// Default display
$displayForm = FALSE;
$displayList = TRUE;

$subTitle = '';

$dialogBox = new DialogBox();


/**
 * COMMANDS SECTION (COURSE MANAGER ONLY)
 */

$id  = isset($_REQUEST['id'])  ? (int) $_REQUEST['id']   : 0;
$cmd = isset($_REQUEST['cmd']) ? $cmd = $_REQUEST['cmd'] : '';
$cmdList=array();

if($is_allowedToEdit) // check teacher status
{
    if( isset($_REQUEST['cmd'])
          && ($_REQUEST['cmd'] == 'rqCreate' || $_REQUEST['cmd'] == 'rqEdit')  )
    {
        if ( 'rqEdit' == $_REQUEST['cmd'] )
        {
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                    array( 'id' => (int) $_REQUEST['id'] ) );
            
            ResourceLinker::setCurrentLocator( $currentLocator );
        }
    }
    
    $autoExportRefresh = FALSE;
    if ( !empty($cmd) )
    {
        // Move announcements up or down
        if ( 'exMvDown' == $cmd  )
        {
            move_entry($id,'DOWN');
        }
        if ( 'exMvUp' == $cmd )
        {
            move_entry($id,'UP');
        }
        
        // Delete announcement
        if ( 'exDelete' == $cmd )
        {
            if ( announcement_delete_item($id) )
            {
                $dialogBox->success( get_lang('Announcement has been deleted') );
                
                if ( CONFVAL_LOG_ANNOUNCEMENT_DELETE ) $claroline->log('ANNOUNCEMENT',array('DELETE_ENTRY'=>$id));
                $eventNotifier->notifyCourseEvent('anouncement_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                $autoExportRefresh = TRUE;
                
                #linker_delete_resource();
            }
            else
            {
                $dialogBox->error( get_lang('Cannot delete announcement') );
            }
        }
        
        // Delete all announcements
        if ( 'exDeleteAll' == $cmd )
        {
            $announcementList = announcement_get_item_list($context);
            if ( announcement_delete_all_items() )
            {
                $dialogBox->success( get_lang('Announcements list has been cleared up') );
                
                if ( CONFVAL_LOG_ANNOUNCEMENT_DELETE ) $claroline->log('ANNOUNCEMENT',array ('DELETE_ENTRY' => 'ALL'));
                $eventNotifier->notifyCourseEvent('all_anouncement_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $announcementList , claro_get_current_group_id(), '0');
                $autoExportRefresh = TRUE;
                
                #linker_delete_all_tool_resources();
            }
            else
            {
                $dialogBox->error( get_lang('Cannot delete announcement list') );
            }
        }
        
        // Require announcement's edition
        if ( 'rqEdit' == $cmd  )
        {
            $subTitle = get_lang('Modifies this announcement');
            claro_set_display_mode_available(false);
            
            // Get the announcement to modify
            $announcement = announcement_get_item($id);
            $displayForm = TRUE;
            $nextCommand = 'exEdit';
        
        }
        
        // Switch announcement's visibility
        if ( 'mkShow' == $cmd || 'mkHide' == $cmd )
        {
            if ( 'mkShow' == $cmd )
            {
                $eventNotifier->notifyCourseEvent('anouncement_visible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                $visibility = 'SHOW';
            }
            if ( 'mkHide' == $cmd )
            {
                $eventNotifier->notifyCourseEvent('anouncement_invisible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                $visibility = 'HIDE';
            }
            if (announcement_set_item_visibility($id, $visibility))
            {
                $dialogBox->success( get_lang('Visibility modified') );
            }
            $autoExportRefresh = TRUE;
        }
        
        // Require new announcement's creation
        if ( 'rqCreate' == $cmd )
        {
            $subTitle = get_lang('Add announcement');
            claro_set_display_mode_available(false);
            $displayForm = TRUE;
            $nextCommand = 'exCreate';
            $announcement=array();
        }
        
        // Submit announcement
        if ( 'exCreate' == $cmd  || 'exEdit' == $cmd )
        {
            $title       = isset($_REQUEST['title'])      ? trim($_REQUEST['title']) : '';
            $content     = isset($_REQUEST['newContent']) ? trim($_REQUEST['newContent']) : '';
            $emailOption = isset($_REQUEST['emailOption'])? (int) $_REQUEST['emailOption'] : 0;
            $visibility  = (int) $_REQUEST['visibility'];
            
            // Manage the visibility options
            if (isset($_REQUEST['visibility']) && $_REQUEST['visibility'] == 1)
            {
                if (isset($_REQUEST['enable_visible_from']) && (isset($_REQUEST['visible_from_year']) && isset($_REQUEST['visible_from_month']) && isset($_REQUEST['visible_from_day'])))
                {
                    $visible_from = $_REQUEST['visible_from_year'].'-'.$_REQUEST['visible_from_month'].'-'.$_REQUEST['visible_from_day'];
                }
                else
                {
                    $visible_from = null;
                }
                
                if (isset($_REQUEST['enable_visible_until']) && (isset($_REQUEST['visible_until_year']) && isset($_REQUEST['visible_until_month']) && isset($_REQUEST['visible_until_day'])))
                {
                    $visible_until = $_REQUEST['visible_until_year'].'-'.$_REQUEST['visible_until_month'].'-'.$_REQUEST['visible_until_day'];
                }
                else
                {
                    $visible_until = null;
                }
            }
            else
            {
                $visible_from = null;
                $visible_until = null;
            }
            
            // Modification of an announcement
            if ( 'exEdit' == $cmd )
            {
                // One of the two visible date fields is null OR the "from" field is <= the "until" field
                if ((is_null($visible_from) || is_null($visible_until)) || ($visible_from <= $visible_until))
                {
                    if ( announcement_update_item((int) $_REQUEST['id'], $title, $content, $visible_from, $visible_until, $visibility) )
                    {
                        $dialogBox->success( get_lang('Announcement has been modified') );
                        
                        $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                            array( 'id' => (int) $_REQUEST['id'] ) );
                        
                        $resourceList =  isset($_REQUEST['resourceList'])
                            ? $_REQUEST['resourceList']
                            : array()
                            ;
                            
                        ResourceLinker::updateLinkList( $currentLocator, $resourceList );
                        
                        $eventNotifier->notifyCourseEvent('anouncement_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0');
                        if (CONFVAL_LOG_ANNOUNCEMENT_UPDATE) $claroling->log('ANNOUNCEMENT', array ('UPDATE_ENTRY'=>$_REQUEST['id']));
                        $autoExportRefresh = TRUE;
                    }
                }
                else
                {
                    $dialogBox->error( get_lang('The "visible from" date can\'t exceed the "visible until" date') );
                }
            }
            
            // Create a new announcement
            elseif ( 'exCreate' == $cmd )
            {
                // One of the two visible date fields is null OR the "from" field is <= the "until" field
                if ((is_null($visible_from) || is_null($visible_until)) || ($visible_from <= $visible_until))
                {
                    // Determine the rank of the new announcement
                    $insert_id = announcement_add_item($title, $content, $visible_from, $visible_until, $visibility) ;
                    if ( $insert_id )
                    {
                        $dialogBox->success( get_lang('Announcement has been added') );
                        
                        $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                            array( 'id' => (int) $insert_id ) );
                        
                        $resourceList =  isset($_REQUEST['resourceList'])
                            ? $_REQUEST['resourceList']
                            : array()
                            ;
                            
                        ResourceLinker::updateLinkList( $currentLocator, $resourceList );
                        
                        $eventNotifier->notifyCourseEvent('anouncement_added',claro_get_current_course_id(), claro_get_current_tool_id(), $insert_id, claro_get_current_group_id(), '0');
                        if (CONFVAL_LOG_ANNOUNCEMENT_INSERT) $claroline->log('ANNOUNCEMENT',array ('INSERT_ENTRY'=>$insert_id));
                        $autoExportRefresh = TRUE;
                    }
                }
                else
                {
                    $dialogBox->error( get_lang('The "visible from" date can\'t exceed the "visible until" date') );
                }
            } // end elseif cmd == exCreate
            
            // Email sending (optionnal)
            if ( 1 == $emailOption )
            {
                $courseSender = claro_get_current_user_data('firstName') . ' ' . claro_get_current_user_data('lastName');
                
                $courseOfficialCode = claro_get_current_course_data('officialCode');
                
                $subject = '';
                if ( !empty($title) ) $subject .= $title ;
                else                  $subject .= get_lang('Message from your lecturer');
                
                $msgContent = $content;
                
                // Enclosed resource
                $body = $msgContent . "\n" .
                    "\n" .
                    ResourceLinker::renderLinkList( $currentLocator, true );
                
                require_once dirname(__FILE__) . '/../messaging/lib/message/messagetosend.lib.php';
                require_once dirname(__FILE__) . '/../messaging/lib/recipient/courserecipient.lib.php';
                
                $courseRecipient = new CourseRecipient(claro_get_current_course_id());
                
                $message = new MessageToSend(claro_get_current_user_id(),$subject,$body);
                $message->setCourse(claro_get_current_course_id());
                $message->setTools('CLANN');
                
                $messageId = $courseRecipient->sendMessage($message);
                
                if ( $failure = claro_failure::get_last_failure() )
                {
                    $dialogBox->warning( $failure );
                }
                
            }   // end if $emailOption==1
        }   // end if $submit Announcement
        
        if ($autoExportRefresh)
        {
            /**
             * in future, the 2 following calls would be pas by event manager.
             */
            // rss update
            /*if ( get_conf('enableRssInCourse',1))
            {
                require_once get_path('incRepositorySys') . '/lib/rss.write.lib.php';
                build_rss( array('course' => claro_get_current_course_id()));
            }*/
            
            // iCal update
            if (get_conf('enableICalInCourse', 1)  )
            {
                require_once get_path('incRepositorySys') . '/lib/ical.write.lib.php';
                buildICal( array('course' => claro_get_current_course_id()));
            }
        }
        
    } // end if isset $_REQUEST['cmd']
    
} // end if is_allowedToEdit


// Prepare displays
if ($displayList)
{
    // list
    $announcementList = announcement_get_item_list($context);
    $bottomAnnouncement = $announcementQty = count($announcementList);
}



$displayButtonLine = (bool) $is_allowedToEdit && ( empty($cmd) || $cmd != 'rqEdit' || $cmd != 'rqCreate' ) ;

if ( $displayButtonLine )
{
    $cmdList[] = '<a class="claroCmd" href="'
        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=rqCreate' )) . '">'
        . '<img src="' . get_icon_url('announcement_new') . '" alt="" />'
        . get_lang('Add announcement')
        . '</a>' . "\n"
        ;

    $cmdList[] = '<a class="claroCmd" href="'
        . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'messaging/messagescourse.php?from=clann')) . '">'
        . '<img src="' . get_icon_url('mail_close') . '" alt="" />'
        . get_lang('Messages to selected users')
        . '</a>' . "\n"
        ;
    
    if (($announcementQty > 0 ))
    {
        $cmdList[] = '<a class="claroCmd" href="'
            . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDeleteAll' )) . '" '
            . ' onclick="if (confirm(\'' . clean_str_for_javascript(get_lang('Clear up list of announcements')) . ' ?\')){return true;}else{return false;}">'
            . '<img src="' . get_icon_url('delete') . '" alt="" />'
            . get_lang('Clear up list of announcements')
            . '</a>' . "\n"
            ;
    }
    else
    {
        $cmdList[] = '<span class="claroCmdDisabled" >'
            . '<img src="' . get_icon_url('delete') . '" alt="" />'
            . get_lang('Clear up list of announcements')
            . '</span>' . "\n"
            ;
    }

}


/**
 *  DISPLAY SECTION
 */

$nameTools = get_lang('Announcements');
$noQUERY_STRING = true;

$output = '';

if ( !empty( $subTitle ) )
{
    $output .= claro_html_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));
}
else
{
    $output .= claro_html_tool_title( $nameTools );
}

$output .= $dialogBox->render();

$output .= '<p>'
         . claro_html_menu_horizontal($cmdList)
         . '</p>';


/**
 * FORM TO FILL OR MODIFY AN ANNOUNCEMENT
 */

if ( $displayForm )
{
    // DISPLAY ADD ANNOUNCEMENT COMMAND
    
    // Ressource linker
    if ( $_REQUEST['cmd'] == 'rqEdit' )
    {
        ResourceLinker::setCurrentLocator(
            ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $_REQUEST['id'] ) ) );
    }
    
    $output .= '<form method="post" action="' . htmlspecialchars( $_SERVER['PHP_SELF'] ) . '">'."\n"
    . claro_form_relay_context()
    . '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
    . '<input type="hidden" name="cmd" value="' . $nextCommand . '" />'
    . (
        (isset( $announcement['id'] )) ?
        ('<input type="hidden" name="id" value="' . $announcement['id'] . '" />' . "\n") :
        ('')
      )
    . '<fieldset>' . "\n"
    . '<dl>' . "\n"
    . '<dt><label for="title">' . get_lang('Title') . '</label></dt>' . "\n"
    . '<dd>'
    . '<input type="text" id="title" name="title" value = "'
    . ( isset($announcement['title']) ? htmlspecialchars($announcement['title']) : '' )
    . '" size="80" />'
    . '</dd>'
    . '<dt><label for="newContent">' . get_lang('Content') . '</label></dt>'
    . '<dd>'
    . claro_html_textarea_editor('newContent', (!empty($announcement) ? $announcement['content'] : ''), 12, 67)
    . '</dd>'
    . '<dt></dt>' . "\n"
    . '<dd>'
    . '<input type="checkbox" value="1" name="emailOption" id="emailOption" />'
    . '<label for="emailOption">' . get_lang('Send this announcement by internal message to registered students') . '</label>'
    . '</dd>'
    . '</dl>'
    . '</fieldset>'
    
    . '<fieldset id="advancedInformation" class="collapsible collapsed">' . "\n"
    . '<legend><a href="#" class="doCollapse">' . get_lang('Visibility options') . '</a></legend>' . "\n"
    . '<div class="collapsible-wrapper">' . "\n"
    . '<dl>' . "\n"
    . '<dt>'
    . '<input name="visibility" id="visible" value="1" type="radio"'
    . ((!isset($announcement['visibility']) || $announcement['visibility'] == 'SHOW') ? ('checked="checked"') : (''))
    . '/> '
    . '<label for="visible"><img src="' . get_icon_url('visible') . '" alt="" /> '
    . get_lang('Visible') . '</label>'
    . '</dt>'
    . '<dt>&nbsp;&nbsp;&nbsp;&nbsp;'
    . '<input name="enable_visible_from" id="enable_visible_from" type="checkbox" '
    . (isset($announcement['visibleFrom']) ? ('checked="checked"') : ('')) . '/>'
    . '<label for="enable_visible_from">'.get_lang('Visible from').' ('.get_lang('included').')</label>'
    . '</dt>'
    . '<dd>'
    . claro_html_date_form('visible_from_day', 'visible_from_month', 'visible_from_year',
    ((isset($announcement['visibleFrom']) ? strtotime($announcement['visibleFrom']) : (strtotime('Now')))), 'long' ).'</dd>'
    . '<dt>&nbsp;&nbsp;&nbsp;&nbsp;'
    . '<input name="enable_visible_until" id="enable_visible_until" type="checkbox" '
    . (isset($announcement['visibleUntil']) ? ('checked="checked"') : ('')) . '/>'
    . '<label for="enable_visible_until">'.get_lang('Visible until').' ('.get_lang('included').')</label>'
    . '</dt>'
    . '<dd>'
    . claro_html_date_form('visible_until_day', 'visible_until_month', 'visible_until_year',
    ((isset($announcement['visibleUntil']) ? strtotime($announcement['visibleUntil']) : (strtotime('Now +1 day')))), 'long' )
    . '</dd>'
    . '<dt>'
    . '<input name="visibility" id="invisible" value="0" type="radio"'
    . ((isset($announcement['visibility']) && $announcement['visibility'] == 'HIDE') ? ('checked="checked"') : (''))
    . '/> '
    . '<label for="invisible"><img src="' . get_icon_url('invisible') . '" alt="" /> '
    . get_lang('Invisible') . '</label>'
    . '</dt>'
    . '</dl>'
    . '</div>'
    . '</fieldset>'
    
    . ResourceLinker::renderLinkerBlock()
    . '<input type="submit" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />'
    . claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])), get_lang('Cancel'))
    
    . '</form>'
    .'<script type="text/javascript">
    $("#visible").click(function(){
        $("#enable_visible_from").attr("disabled", false);
        $("#enable_visible_until").attr("disabled", false);
        $("#visible_from_day").attr("disabled", false);
        $("#visible_from_month").attr("disabled", false);
        $("#visible_from_year").attr("disabled", false);
        $("#visible_until_day").attr("disabled", false);
        $("#visible_until_month").attr("disabled", false);
        $("#visible_until_year").attr("disabled", false);
    });
    
    $("#invisible").click(function(){
        $("#enable_visible_from").attr("disabled", true);
        $("#enable_visible_until").attr("disabled", true);
        $("#visible_from_day").attr("disabled", true);
        $("#visible_from_month").attr("disabled", true);
        $("#visible_from_year").attr("disabled", true);
        $("#visible_until_day").attr("disabled", true);
        $("#visible_until_month").attr("disabled", true);
        $("#visible_until_year").attr("disabled", true);
    });'
    . '</script>';
}


/**
 * ANNOUNCEMENTS LIST
 */


if ($displayList)
{
    $iterator = 1;

    if ($announcementQty < 1)
    {
        $output .= '<br /><blockquote>' . get_lang('No announcement') . '</blockquote>' . "\n";
    }

    else
    {
        // Get notification date
        if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

        foreach ( $announcementList as $thisAnnouncement )
        {
            // Hide hidden and out of deadline elements
            $cssInvisible = '';
            $isVisible = (bool) ($thisAnnouncement['visibility'] == 'SHOW') ? (1) : (0);
            $isOffDeadline = (bool)
                (
                    (isset($thisAnnouncement['visibleFrom'])
                        && strtotime($thisAnnouncement['visibleFrom']) > time()
                    )
                    ||
                    (isset($thisAnnouncement['visibleUntil'])
                        && time() > strtotime($thisAnnouncement['visibleUntil'])+86400
                    )
                ) ? (1) : (0);
                
            if (($is_allowedToEdit || ( $isVisible && ! $isOffDeadline)))
            {
                
                //modify style if the event is recently added since last login
                if (claro_is_user_authenticated() && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $thisAnnouncement['id']))
                {
                    $cssItem = 'item hot';
                }
                else
                {
                    $cssItem = 'item';
                }
                
                if ( !$isVisible || $isOffDeadline )
                {
                    $cssInvisible = ' invisible';
                }

                $title = $thisAnnouncement['title'];

                $content = make_clickable(claro_parse_user_text($thisAnnouncement['content']));
                 // Post time format in MySQL date format
                $last_post_date = ((isset($thisAnnouncement['visibleFrom'])) ?
                    ($thisAnnouncement['visibleFrom']) :
                    ($thisAnnouncement['time']));

                $output .= '<div class="claroBlock">' . "\n"
                .   '<h4 id="announcement'.$thisAnnouncement['id'].'" '
                .   'class="claroBlockHeader">'
                .   '<span class="'. $cssItem . $cssInvisible .'">' . "\n"
                .   '<img src="' . get_icon_url('announcement') . '" alt="" /> '
                .   get_lang('Published on')
                .   ' : ' . claro_html_localised_date( get_locale('dateFormatLong'), strtotime($last_post_date))
                .   '</span>' . "\n"
                .   '</h4>' . "\n"
                
                .   '<div class="claroBlockContent">' . "\n"
                .   '<a href="#" name="ann' . $thisAnnouncement['id'] . '"></a>'. "\n"

                .   '<div class="' . $cssInvisible . '">' . "\n"
                .   ($title ? '<p><strong>' . htmlspecialchars($title) . '</strong></p>' . "\n"
                    : ''
                    )
                .   claro_parse_user_text($content) . "\n"
                .   '</div>' . "\n"
                ;
                
                $currentLocator = ResourceLinker::$Navigator->getCurrentLocator( array('id' => $thisAnnouncement['id'] ) );
                $output .= ResourceLinker::renderLinkList( $currentLocator );

                if ($is_allowedToEdit)
                {
                    $output .= '<div class="claroBlockCmd">'
                        // EDIT Request LINK
                        . '<a href="'
                        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                            . '?cmd=rqEdit&amp;id=' . $thisAnnouncement['id'] ))
                        . '">'
                        . '<img src="' . get_icon_url('edit') . '" alt="'
                        . get_lang('Modify') . '" />'
                        . '</a>' . "\n"
                        // DELETE  Request LINK
                        . '<a href="'
                        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                            . '?cmd=exDelete&amp;id=' . $thisAnnouncement['id'] ))
                        . '" '
                        . ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Please confirm your choice')) . '\')) return false;">'
                        . '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" />'
                        . '</a>' . "\n"
                        ;

                    // DISPLAY MOVE UP COMMAND only if it is not the top announcement

                    if( $iterator != 1 )
                    {
                        #$output .=    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvUp&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                        // Anchor doesn't refresh the page
                        $output .= '<a href="'. htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exMvUp&amp;id=' . $thisAnnouncement['id'] )) . '">'
                            . '<img src="' . get_icon_url('move_up') . '" alt="' . get_lang('Move up') . '" />'
                            . '</a>' . "\n"
                            ;
                    }

                    // DISPLAY MOVE DOWN COMMAND only if it is not the bottom announcement

                    if($iterator < $bottomAnnouncement)
                    {
                        #$output .=    "<a href=\"".$_SERVER['PHP_SELF']."?cmd=exMvDown&amp;id=",$thisAnnouncement['id'],"#ann",$thisAnnouncement['id'],"\">",
                        // Anchor doesn't refresh the page
                        $output .= '<a href="'
                            . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exMvDown&amp;id=' . $thisAnnouncement['id'] )) . '">'
                            . '<img src="' . get_icon_url('move_down') . '" alt="' . get_lang('Move down') . '" />'
                            . '</a>' . "\n"
                            ;
                    }

                    //  Visibility
                    if ($thisAnnouncement['visibility']=='SHOW')
                    {
                        $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisAnnouncement['id'] )) . '">'
                        .    '<img src="' . get_icon_url('visible') . '" alt="' . get_lang('Visible').'" />'
                        .    '</a>' . "\n"
                        ;
                    }
                    else
                    {
                        $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisAnnouncement['id'] )) . '">'
                        .    '<img src="' . get_icon_url('invisible') . '" alt="' . get_lang('Invisible') . '" />'
                        .    '</a>' . "\n"
                        ;
                    }
                    
                    $output .= '</div>' . "\n"; // claroBlockCmd
                
                } // end if is_AllowedToEdit
                
                $output .= '</div>' . "\n" // claroBlockContent
                .    '</div>' . "\n\n"; // claroBlock
            }
            
            $iterator ++;
        }    // end foreach ( $announcementList as $thisAnnouncement)
    }

} // end if displayList

Claroline::getDisplay()->body->appendContent( $output );

echo Claroline::getDisplay()->render();