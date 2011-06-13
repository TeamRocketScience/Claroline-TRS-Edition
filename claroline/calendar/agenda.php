<?php // $Id: agenda.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * - For a Student -> View agenda content
 * - For a Prof    ->
 *         - View agenda content
 *         - Update/delete existing entries
 *         - Add entries
 *         - generate an "announce" entries about an entries
 *
 * @version 1.9 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLCAL
 *
 * @author Claro Team <cvs@claroline.net>
 */

$tlabelReq  = 'CLCAL';
$gidReset   = true;
require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
$_user      = claro_get_current_user_data();
$_course    = claro_get_current_course_data();

//**//

if (claro_is_in_a_group()) $currentContext = claro_get_current_context(array('course','group'));
else                       $currentContext = claro_get_current_context('course');

//**/

FromKernel::uses('core/linker.lib');
ResourceLinker::init();

require_once './lib/agenda.lib.php';
require_once get_path('incRepositorySys') . '/lib/form.lib.php';

require claro_get_conf_repository() . 'ical.conf.php';
require claro_get_conf_repository() . 'rss.conf.php';

$context = claro_get_current_context(CLARO_CONTEXT_COURSE);
define('CONFVAL_LOG_CALENDAR_INSERT', FALSE);
define('CONFVAL_LOG_CALENDAR_DELETE', FALSE);
define('CONFVAL_LOG_CALENDAR_UPDATE', FALSE);

if ( ! claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$nameTools = get_lang('Agenda');

claro_set_display_mode_available(TRUE);

$is_allowedToEdit = claro_is_course_manager();

$cmdList[]=  '<a class="claroCmd" href="'
    . htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']. '#today' )) .'">'
    . get_lang('Today')
    . '</a>'
    ;

if ( $is_allowedToEdit )
{
// 'rqAdd' ,'rqEdit', 'exAdd','exEdit', 'exDelete', 'exDeleteAll', 'mkShow', 'mkHide'

    if ( isset($_REQUEST['cmd'])
        && ( 'rqAdd' == $_REQUEST['cmd'] || 'rqEdit' == $_REQUEST['cmd'] )
    )
    {
        if ( 'rqEdit' == $_REQUEST['cmd'] )
        {
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                    array( 'id' => (int) $_REQUEST['id'] ) );
            
            ResourceLinker::setCurrentLocator( $currentLocator );
        }
    }
}

$tbl_c_names = claro_sql_get_course_tbl();
$tbl_calendar_event = $tbl_c_names['calendar_event'];

$cmd = ( isset($_REQUEST['cmd']) ) ?$_REQUEST['cmd']: null;

$dialogBox = new DialogBox();

if     ( 'rqAdd' == $cmd ) $subTitle = get_lang('Add an event');
elseif ( 'rqEdit' == $cmd ) $subTitle = get_lang('Edit Event');
else                       $subTitle = '&nbsp;';

//-- order direction
if( !empty($_REQUEST['order']) )
    $orderDirection = strtoupper($_REQUEST['order']);
elseif( !empty($_SESSION['orderDirection']) )
    $orderDirection = strtoupper($_SESSION['orderDirection']);
else
    $orderDirection = 'ASC';

$acceptedValues = array('DESC','ASC');

if( ! in_array($orderDirection, $acceptedValues) )
{
    $orderDirection = 'ASC';
}

$_SESSION['orderDirection'] = $orderDirection;


$is_allowedToEdit = claro_is_allowed_to_edit();


/**
 * COMMANDS SECTION
 */

$display_form = FALSE;
$display_command = FALSE;

if ( $is_allowedToEdit )
{
    $id         = ( isset($_REQUEST['id']) ) ? ((int) $_REQUEST['id']) : (0);
    $title      = ( isset($_REQUEST['title']) ) ? (trim($_REQUEST['title'])) : ('');
    $content    = ( isset($_REQUEST['content']) ) ? (trim($_REQUEST['content'])) : ('');
    $lasting    = ( isset($_REQUEST['lasting']) ) ? (trim($_REQUEST['lasting'])) : ('');
    $speakers     = ( isset($_REQUEST['speakers']) ) ? (trim($_REQUEST['speakers'])) : ('');
    $location   = ( isset($_REQUEST['location']) ) ? (trim($_REQUEST['location'])) : ('');
    
    $autoExportRefresh = false;
    
    if ( 'exAdd' == $cmd )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        $entryId = agenda_add_item($title, $content, $date_selection, $hour, $lasting, $speakers, $location) ;
        
        if ( $entryId != false )
        {
            $dialogBox->success( get_lang('Event added to the agenda') );
            
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $entryId ) );
            
            $resourceList =  isset($_REQUEST['resourceList'])
                ? $_REQUEST['resourceList']
                : array()
                ;
                
            ResourceLinker::updateLinkList( $currentLocator, $resourceList );

            if ( CONFVAL_LOG_CALENDAR_INSERT )
            {
                $claroline->log('CALENDAR', array ('ADD_ENTRY' => $entryId));
            }

            // notify that a new agenda event has been posted

            $eventNotifier->notifyCourseEvent('agenda_event_added', claro_get_current_course_id(), claro_get_current_tool_id(), $entryId, claro_get_current_group_id(), '0');
            $autoExportRefresh = TRUE;

        }
        else
        {
            $dialogBox->error( get_lang('Unable to add the event to the agenda') );
        }
    }

    /*------------------------------------------------------------------------
    EDIT EVENT COMMAND
    --------------------------------------------------------------------------*/


    if ( 'exEdit' == $cmd )
    {
        $date_selection = $_REQUEST['fyear'] . '-' . $_REQUEST['fmonth'] . '-' . $_REQUEST['fday'];
        $hour           = $_REQUEST['fhour'] . ':' . $_REQUEST['fminute'] . ':00';

        if ( !empty($id) )
        {
            if ( agenda_update_item($id,$title,$content,$date_selection,$hour,$lasting,$speakers,$location) )
            {
                $dialogBox->success( get_lang('Event updated into the agenda') );
                
                $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                    array( 'id' => (int) $id ) );
                
                $resourceList =  isset($_REQUEST['resourceList'])
                    ? $_REQUEST['resourceList']
                    : array()
                    ;
                    
                ResourceLinker::updateLinkList( $currentLocator, $resourceList );

                $eventNotifier->notifyCourseEvent('agenda_event_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
                $autoExportRefresh = TRUE;
            }
            else
            {
                $dialogBox->error( get_lang('Unable to update the event into the agenda') );
            }
        }
    }

    /*------------------------------------------------------------------------
    DELETE EVENT COMMAND
    --------------------------------------------------------------------------*/

    if ( 'exDelete' == $cmd && !empty($id) )
    {

        if ( agenda_delete_item($id) )
        {
            $dialogBox->success( get_lang('Event deleted from the agenda') );

            $eventNotifier->notifyCourseEvent('agenda_event_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
            $autoExportRefresh = TRUE;
            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                $claroline->log('CALENDAR',array ('DELETE_ENTRY' => $id));
            }
        }
        else
        {
            $dialogBox->error( get_lang('Unable to delete event from the agenda') );
        }

        // linker_delete_resource();
    }

    /*----------------------------------------------------------------------------
    DELETE ALL EVENTS COMMAND
    ----------------------------------------------------------------------------*/

    if ( 'exDeleteAll' == $cmd )
    {
        if ( agenda_delete_all_items())
        {
            $eventNotifier->notifyCourseEvent('agenda_event_list_deleted', claro_get_current_course_id(), claro_get_current_tool_id(), null, claro_get_current_group_id(), '0');

            $dialogBox->success( get_lang('All events deleted from the agenda') );

            if ( CONFVAL_LOG_CALENDAR_DELETE )
            {
                $claroline->log('CALENDAR', array ('DELETE_ENTRY' => 'ALL') );
            }
        }
        else
        {
            $dialogBox->error( get_lang('Unable to delete all events from the agenda') );
        }

        // linker_delete_all_tool_resources();
    }
    /*-------------------------------------------------------------------------
    EDIT EVENT VISIBILITY
    ---------------------------------------------------------------------------*/

    if ( 'mkShow' == $cmd  || 'mkHide' == $cmd )
    {
        if ($cmd == 'mkShow')
        {
            $visibility = 'SHOW';
            $eventNotifier->notifyCourseEvent('agenda_event_visible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
            $autoExportRefresh = TRUE;
        }

        if ($cmd == 'mkHide')
        {
            $visibility = 'HIDE';
            $eventNotifier->notifyCourseEvent('agenda_event_invisible', claro_get_current_course_id(), claro_get_current_tool_id(), $id, claro_get_current_group_id(), '0'); // notify changes to event manager
            $autoExportRefresh = TRUE;
        }

        agenda_set_item_visibility($id, $visibility);
    }

    /*------------------------------------------------------------------------
    EVENT EDIT
    --------------------------------------------------------------------------*/

    if ( 'rqEdit' == $cmd  || 'rqAdd' == $cmd  )
    {
        claro_set_display_mode_available(false);

        if ( 'rqEdit' == $cmd  && !empty($id) )
        {
            $editedEvent = agenda_get_item($id) ;
            // get date as unixtimestamp for claro_dis_date_form and claro_html_time_form
            $editedEvent['date'] = strtotime($editedEvent['dayAncient'].' '.$editedEvent['hourAncient']);
            $nextCommand = 'exEdit';
        }
        else
        {
            $editedEvent['id'            ] = '';
            $editedEvent['title'         ] = '';
            $editedEvent['content'       ] = '';
            $editedEvent['date'] = time();
            $editedEvent['lastingAncient'] = FALSE;
            $editedEvent['location'      ] = '';

            $nextCommand = 'exAdd';
        }
        $display_form =TRUE;
    } // end if cmd == 'rqEdit' && cmd == 'rqAdd'

    if ('rqEdit' != $cmd  && 'rqAdd' != $cmd ) // display main commands only if we're not in the event form
    {
        $display_command = TRUE;
    } // end if diplayMainCommands

    if ( $autoExportRefresh)
    {
        // rss update
        /*if ( get_conf('enableRssInCourse',1))
        {

            require_once get_path('incRepositorySys') . '/lib/rss.write.lib.php';
            build_rss( array(CLARO_CONTEXT_COURSE => claro_get_current_course_id()));
        }*/

        // ical update
        if (get_conf('enableICalInCourse',1) )
        {
            require_once get_path('incRepositorySys') . '/lib/ical.write.lib.php';
            buildICal( array(CLARO_CONTEXT_COURSE => claro_get_current_course_id()));
        }
    }

} // end id is_allowed to edit

/**
 *     DISPLAY SECTION
 *
 */

$noQUERY_STRING = true;

$eventList = agenda_get_item_list($currentContext,$orderDirection);

/**
 * Add event button
 */

$cmdList[]= '<a class="claroCmd" href="'
    . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=rqAdd' )) . '">'
    . '<img src="' . get_icon_url('agenda_new') . '" alt="" />'
    . get_lang('Add an event')
    . '</a>'
    ;

/*
* remove all event button
*/
if ( count($eventList) > 0 )
{
    $cmdList[]=  '<a class= "claroCmd" href="'
        . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDeleteAll' )) . '" '
        . ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Clear up event list ?')) . '\')) return false;">'
        . '<img src="' . get_icon_url('delete') . '" alt="" />'
        . get_lang('Clear up event list')
        . '</a>'
        ;
}
else
{
    $cmdList[]=  '<span class="claroCmdDisabled" >'
        . '<img src="' . get_icon_url('delete') . '" alt="" />'
        . get_lang('Clear up event list')
        . '</span>'
        ;
}

$output = '';

$output .= claro_html_tool_title(array('mainTitle' => $nameTools, 'subTitle' => $subTitle));

$output .= $dialogBox->render();


if ($display_form)
{
    // Ressource linker
    if ( 'rqEdit' == $_REQUEST['cmd'] )
    {
        ResourceLinker::setCurrentLocator(
            ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $_REQUEST['id'] ) ) );
    }
    
    $output .= '<form method="post" action="' . htmlspecialchars( $_SERVER['PHP_SELF'] ) . '">'
    . claro_form_relay_context()
    . '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
    . '<input type="hidden" name="cmd" value="' . $nextCommand . '" />'
    . '<input type="hidden" name="id"  value="' . $editedEvent['id'] . '" />'
    . '<fieldset>' . "\n"
    . '<dl>'
    . '<dt><label for="title">' . get_lang('Title') . '</label></dt>' . "\n"
    . '<dd>'
    . '<input size="80" type="text" name="title" id="title" value="'
    . htmlspecialchars($editedEvent['title']). '" />'
    . '</dd>' . "\n"
    . '<dt>' . get_lang('Date')
    . '</dt>' . "\n"
    . '<dd>'
    . claro_html_date_form('fday', 'fmonth', 'fyear', $editedEvent['date'], 'long' ) . ' '
    . claro_html_time_form('fhour','fminute', $editedEvent['date']) . '&nbsp;'
    . '<small>' . get_lang('(d/m/y hh:mm)') . '</small>'
    . '</dd>' . "\n"
    . '<dt>'
    . '<label for="lasting">' . get_lang('Lasting') . '</label>'
    . '</dt>' . "\n"
    . '<dd>'
    . '<input type="text" name="lasting" id="lasting" size="20" maxlength="20" value="' . htmlspecialchars($editedEvent['lastingAncient']) . '" />'
    . '</dd>' . "\n"
    . '<dt>'
    . '<label for="location">' . get_lang('Location') . '</label>'
    . '</dt>' . "\n"
    . '<dd>'
    . '<input type="text" name="location" id="location" size="20" maxlength="20" value="' . htmlspecialchars($editedEvent['location']) . '" />'
    . '</dd>' . "\n"
    . '<dt>'
    . '<label for="speakers">' . get_lang('Speakers') . '</label>'
    . '</dt>' . "\n"
    . '<dd>'
    . '<input type="text" name="speakers" id="speakers" size="20" maxlength="200" value="'
    . (isset($editedEvent['speakers']) ? (htmlspecialchars($editedEvent['speakers'])) : ('')) . '" /><br/>'
    . '<small>' . get_lang('If more than one, separated by a coma') . '</small>'
    . '</dd>' . "\n"
    . '<dt><label for="content">' . get_lang('Detail') . '</label></dt>'
    . '<dd>' . "\n"
    . claro_html_textarea_editor('content', $editedEvent['content'], 12, 67 ) . "\n"
    . '</dd>' . "\n"
    . '</dl>'
    . '</fieldset>'
    
    . ResourceLinker::renderLinkerBlock()
    . '<input type="submit" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '" />' . "\n"
    . claro_html_button($_SERVER['PHP_SELF'], 'Cancel') . "\n"
    ;
}

if ( $display_command ) $output .= '<p>' . claro_html_menu_horizontal($cmdList) . '</p>';

$monthBar     = '';

if ( count($eventList) < 1 )
{
    $output .= "\n" . '<br /><blockquote>' . get_lang('No event in the agenda') . '</blockquote>' . "\n";
}
else
{
    if ( $orderDirection == 'DESC' )
    {
        $output .= '<br /><a href="'
            . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?order=asc' ))
            .'" >' . get_lang('Oldest first') . '</a>' . "\n"
            ;
    }
    else
    {
        $output .= '<br /><a href="'
            . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?order=desc' ))
            . '" >' . get_lang('Newest first') . '</a>' . "\n"
            ;
    }
}

$nowBarAlreadyShowed = FALSE;

if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

foreach ( $eventList as $thisEvent )
{

    if (('HIDE' == $thisEvent['visibility'] && $is_allowedToEdit)
        || 'SHOW' == $thisEvent['visibility'])
    {
        //modify style if the event is recently added since last login
        if (claro_is_user_authenticated()
            && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $thisEvent['id']))
        {
            $cssItem = 'item hot';
        }
        else
        {
            $cssItem = 'item';
        }

        $cssInvisible = '';
        if ($thisEvent['visibility'] == 'HIDE')
        {
            $cssInvisible = ' invisible';
        }

        // TREAT "NOW" BAR CASE
        if ( ! $nowBarAlreadyShowed )
        if (( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) > time() ) &&  'ASC' == $orderDirection )
        ||
        ( ( strtotime($thisEvent['day'] . ' ' . $thisEvent['hour'] ) < time() ) &&  'DESC' == $orderDirection )
        )
        {
            // add monthbar is now bar is the first (or only one) item for this month
            // current time month monthBar display
            if ($monthBar != date('mY',time()))
            {
                $monthBar = date('mY',time());

                $output .= '<div class="claroBlockSuperHeader">' . "\n"
                .    ucfirst(claro_html_localised_date('%B %Y', time()))
                .    '</div>' . "\n"
                ;
            }


            // 'NOW' Bar
            $output .= '<div class="highlight">'
            .    '<img src="' . get_icon_url('pixel') . '" width="20" alt=" " />'
            .    '<a name="today">'
            .    '<i>'
            .    ucfirst(claro_html_localised_date( get_locale('dateFormatLong'))) . ' '
            .    ucfirst(strftime( get_locale('timeNoSecFormat')))
            .    ' -- '
            .    get_lang('Now')
            .    '</i>'
            .    '</a>'
            .    '</div>' . "\n"
            ;

            $nowBarAlreadyShowed = true;
        }

        /*
         * Display the month bar when the current month
         * is different from the current month bar
         */

        if ( $monthBar != date( 'mY', strtotime($thisEvent['day']) ) )
        {
            $monthBar = date('mY', strtotime($thisEvent['day']));

            $output .= '<div class="claroBlockSuperHeader">'
            .    ucfirst(claro_html_localised_date('%B %Y', strtotime( $thisEvent['day']) ))
            .    '</div>' . "\n"
            ;
        }

        /*
         * Display the event date
         */
        $output .= '<div class="claroBlock">' . "\n"
        .   '<h4 id = "event' . $thisEvent['id'] . '" class="claroBlockHeader">'
        .   '<span class="'. $cssItem . $cssInvisible .'">' . "\n"
        .   '<img src="' . get_icon_url('agenda') . '" alt="" /> '
        .    ucfirst(claro_html_localised_date( get_locale('dateFormatLong'), strtotime($thisEvent['day']))) . ' '
        .    ucfirst( strftime( get_locale('timeNoSecFormat'), strtotime($thisEvent['hour'])))
        .    ( empty($thisEvent['lasting']) ? ('') : (' | '.get_lang('Lasting')) . ' : ' . $thisEvent['lasting'] )
        .    ( empty($thisEvent['location']) ? ('') : (' | '.get_lang('Location')) . ' : ' . $thisEvent['location'] )
        .    ( empty($thisEvent['speakers']) ? ('') : (' | '.get_lang('Speakers')) . ' : ' . $thisEvent['speakers'] )
        .   '</span>' . "\n"
        .   '</h4>' . "\n"
        
        /*
         * Display the event content
         */
        .   '<div class="claroBlockContent">' . "\n"

        .   '<div class="' . $cssInvisible . '">' . "\n"
        .    ( empty($thisEvent['title']  ) ? '' : '<p><strong>' . htmlspecialchars($thisEvent['title']) . '</strong></p>' . "\n" )
        .    ( empty($thisEvent['content']) ? '' :  claro_parse_user_text($thisEvent['content']) )
        .   '</div>' . "\n"
        
        ;
            $output .= '</div>' . "\n" // claroBlockContent
    .    '</div>' . "\n\n"; // claroBlock

        $currentLocator = ResourceLinker::$Navigator->getCurrentLocator( array('id' => $thisEvent['id'] ) );
        $output .= ResourceLinker::renderLinkList( $currentLocator );
    }

    if ($is_allowedToEdit)
    {
        $output .= '<div class="claroBlockCmd">'
        .    '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;id=' . $thisEvent['id'] )) . '">'
        .    '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('Modify') . '" />'
        .    '</a> '
        .    '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisEvent['id'] )) . '" '
        .    ' onclick="javascript:if(!confirm(\'' . clean_str_for_javascript(get_lang('Are you sure to delete "%title" ?', array('%title' => $thisEvent['title']))) . '\')) return false;">'
        .    '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" />'
        .    '</a>'
        ;

        //  Visibility
        if ('SHOW' == $thisEvent['visibility'])
        {
            $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkHide&amp;id=' . $thisEvent['id'] )) . '">'
            .    '<img src="' . get_icon_url('visible') . '" alt="" />'
            .    '</a>' . "\n";
        }
        else
        {
            $output .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=mkShow&amp;id=' . $thisEvent['id'] )) . '">'
            .    '<img src="' . get_icon_url('invisible') . '" alt="" />'
            .    '</a>' . "\n"
            ;
        }
        
        $output .= '</div>' . "\n"; // claroBlockCmd
    }

}   // end while

Claroline::getDisplay()->body->appendContent( $output );

echo Claroline::getDisplay()->render();
