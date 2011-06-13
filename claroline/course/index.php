<?php // $Id: index.php 12974 2011-03-15 10:53:34Z abourguignon $

/**
 * CLAROLINE
 *
 * @version     $Revision: 12974 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *              old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_home/course_home.php
 * @package     CLHOME
 * @author      Claro Team <cvs@claroline.net>
 * @since       1.9
 */


// If user is here, it means he isn't neither in specific group space
// nor a specific course tool now. So it's careful to reset the group
// and tool settings

$gidReset = true;
$tidReset = true;

if ( isset($_REQUEST['cid']) ) $cidReq = $_REQUEST['cid'];
elseif ( isset($_REQUEST['cidReq']) ) $cidReq = $_REQUEST['cidReq'];

$portletCmd     = (isset($_REQUEST['portletCmd']) ? $_REQUEST['portletCmd'] : null);
$portletId      = (isset($_REQUEST['portletId']) ? $_REQUEST['portletId'] : null);
$portletLabel   = (isset($_REQUEST['portletLabel']) ? $_REQUEST['portletLabel'] : null);
$portletClass   = (isset($portletLabel) ? ($portletLabel.'_portlet') : null);

require '../inc/claro_init_global.inc.php';
require_once get_path('incRepositorySys') . '/lib/claroCourse.class.php';
require_once get_path('clarolineRepositorySys') . 'coursehomepage/lib/coursehomepageportlet.class.php';
require_once get_path('clarolineRepositorySys') . 'coursehomepage/lib/coursehomepageportletiterator.class.php';


// Display the auth form if necessary
// Also redirect if no cid specified
if ( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$portletiterator = new CourseHomePagePortletIterator(ClaroCourse::getIdFromCode($cidReq));

include claro_get_conf_repository() . 'rss.conf.php';

// Include the course home page special CSS
$cssLoader = CssLoader::getInstance();
$cssLoader->load('coursehomepage', 'all');

// Include specific CSS if any
if ( claro_is_in_a_course()
    && file_exists( get_conf('coursesRepositorySys')
        . $_course['path'] . '/css/course.css' ) )
{
    $claroline->display->header->addHtmlHeader(
        '<link rel="stylesheet" media="screen" type="text/css" href="'
        . get_path('url') . '/' . get_path('coursesRepositoryAppend')
        . $_course['path'] . '/css/course.css" />');
}

// Instanciate dialog box
$dialogBox = new DialogBox();

if (isset($cidReq))
{
    $thisCourse = new ClaroCourse();
    $thisCourse->load($cidReq);
}

$toolRepository = get_path('clarolineRepositoryWeb');
claro_set_display_mode_available(true);

// Manage portlets
if (claro_is_course_manager())
{
    if ($portletCmd == 'rqAdd')
    {
        $form = CourseHomePagePortlet::renderForm();
        if ($form)
        {
            $dialogBox->form($form);
        }
        else
        {
            $dialogBox->error(get_lang('No more portlet available for this course'));
        }
    }
    if ($portletCmd == 'exAdd')
    {
        $portletPath = get_module_path( $portletLabel )
            . '/connector/coursehomepage.cnr.php';
        if ( file_exists($portletPath) )
        {
            require_once $portletPath;
        }
        else
        {
            $dialogBox->error(get_lang('Cannot find this portlet'));
        }
        
        $portlet = new $portletClass();
        $portlet->handleForm();
        if($portlet->save())
        {
            $dialogBox->success(get_lang('Portlet created'));
        }
    }
    if ($portletCmd == 'delete' && !empty($portletId))
    {
        $portlet = new $portletClass();
        $portlet->load($portletId);
        if($portlet->delete())
        {
            $dialogBox->success(get_lang('Portlet deleted'));
        }
    }
    elseif ($portletCmd == 'swapVisibility' && !empty($portletId))
    {
        $portlet = new $portletClass();
        if ($portlet->load($portletId))
        {
            $portlet->swapVisibility();
            if($portlet->save())
            {
                $dialogBox->success(get_lang('Portlet visibility modified'));
            }
        }
    }
    elseif ($portletCmd == 'moveUp' && !empty($portletId))
    {
        $portlet = new $portletClass();
        $portlet->load($portletId);
        
        if ($portlet->load($portletId))
        {
            if($portlet->moveUp())
            {
                $dialogBox->success(get_lang('Portlet moved up'));
            }
            else
            {
                $dialogBox->error(get_lang('This portlet can\'t be moved up'));
            }
        }
    }
    elseif ($portletCmd == 'moveDown' && !empty($portletId))
    {
        $portlet = new $portletClass();
        if ($portlet->load($portletId))
        {
            if($portlet->moveDown())
            {
                $dialogBox->success(get_lang('Portlet moved down'));
            }
            else
            {
                $dialogBox->error(get_lang('This portlet can\'t be moved down'));
            }
        }
    }
}

// Language initialisation of the tool names
$toolNameList = claro_get_tool_name_list();

// Get tool id where new events have been recorded since last login
if (claro_is_user_authenticated())
{
    $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    $modified_tools = $claro_notifier->get_notified_tools(claro_get_current_course_id(), $date, claro_get_current_user_id());
}
else
{
    $modified_tools = array();
}

/**
 * TOOL LIST
 */

$is_allowedToEdit = claro_is_allowed_to_edit();

$sourceCourseCode = ClaroCourse::getCodeFromId(claro_get_current_course_data('sourceCourseId'));

if (isset($sourceCourseCode))
{
    // Call a session course
    $_SESSION['courseSessionCode'][$sourceCourseCode] = claro_get_current_course_id();
    $courseCode['session'] =  claro_get_current_course_id();
    $courseCode['source'] = $sourceCourseCode;
}
else
{
    if (isset($_SESSION['courseSessionCode'][claro_get_current_course_id()]) )
    {
        // Call a source course
        $courseCode['source'] = claro_get_current_course_id();
        $courseCode['session'] =  $_SESSION['courseSessionCode'][$courseCode['source']];
    }
    else
        $courseCode['standAlone'] = claro_get_current_course_id();
}

$toolLinkList = array(
    'source' => array(),
    'session' => array(),
    'standAlone' => array()
);

// Generate tool lists
foreach ($courseCode as $key => $course)
{
    $toolListSource = claro_get_course_tool_list($course, $_profileId, true);
    $toolLinkListSource = array();
    
    foreach ($toolListSource as $thisTool)
    {
        // Special case when display mode is student and tool invisible doesn't display it
        if ( ( claro_get_tool_view_mode() == 'STUDENT' ) && ! $thisTool['visibility']  )
        {
            continue;
        }
        
        if (isset($thisTool['label'])) // standart claroline tool or module of type tool
        {
            $thisToolName = $thisTool['name'];
            $toolName = get_lang($thisToolName);
            
            // Trick to find how to build URL, must be IMPROVED
            $url = htmlspecialchars( get_module_url($thisTool['label']) . '/' . $thisTool['url'] . '?cidReset=true&cidReq=' .$course);
            $icon = get_module_url($thisTool['label']) .'/'. $thisTool['icon'];
            $htmlId = 'id="' . $thisTool['label'] . '"';
            $removableTool = false;
        }
        else   // External tool added by course manager
        {
            if ( ! empty($thisTool['external_name'])) $toolName = $thisTool['external_name'];
            else $toolName = '<i>no name</i>';
            $url = htmlspecialchars( trim($thisTool['url']) );
            $icon = get_icon_url('link');
            $htmlId = '';
            $removableTool = true;
        }
        
        $style = !$thisTool['visibility']? 'invisible ' : '';
        $classItem = (in_array($thisTool['id'], $modified_tools)) ? ' hot' : '';
        
        if ( ! empty($url) )
        {
            $toolLinkList[$key][] = '<a '.$htmlId.'class="' . $style . 'item' . $classItem . '" href="' . $url . '">'
            .                 '<img class="clItemTool"  src="' . $icon . '" alt="" />&nbsp;'
            .                 $toolName
            .                 '</a>' . "\n"
            ;
        }
        else
        {
            $toolLinkList[$key][] = '<span ' . $style . '>'
            .                 '<img class="clItemTool" src="' . $icon . '" alt="" />&nbsp;'
            .                 $toolName
            .                 '</span>' . "\n"
            ;
        }
    }
}

// Generate tool list for managment of the course
$courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb')  . 'course/tools.php' )) . '">'
.                             '<img src="' . get_icon_url('edit') . '" alt="" /> '
.                             get_lang('Edit Tool list')
.                             '</a>'
;
$courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( $toolRepository . 'course/settings.php' )) . '">'
.                             '<img src="' . get_icon_url('settings') . '" alt="" /> '
.                             get_lang('Course settings')
.                             '</a>'
;
if ( !ClaroCourse::isSessionCourse($thisCourse->id) )
{
    $courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'course/session_courses.php', array('cid'=>$thisCourse->id) )) . '">'
    .                             '<img src="' . get_icon_url('duplicate') . '" alt="" /> '
    .                             get_lang("Manage session courses")
    .                             '</a>' ;
}
else
{
    $courseManageToolLinkList[] = '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'course/index.php', array('cid'=>ClaroCourse::getCodeFromId($thisCourse->sourceCourseId)) )) . '">'
    .                             '<img src="' . get_icon_url('default') . '" alt="" /> '
    .                             get_lang("View source course")
    .                             '</a>' ;
}

if( get_conf('is_trackingEnabled') )
{
    $courseManageToolLinkList[] =  '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( $toolRepository . 'tracking/courseReport.php' )) . '">'
    .                             '<img src="' . get_icon_url('statistics') . '" alt="" /> '
    .                             get_lang('Statistics')
    .                             '</a>'
    ;
}


// Fetch the portlets
$portletiterator = new CourseHomePagePortletIterator(ClaroCourse::getIdFromCode($cidReq));

// Notices for course managers
if (claro_is_allowed_to_edit())
{
    if ( $thisCourse->status == 'pending' )
    {
        $dialogBox->warning(
            get_lang('This course is deactivated: you can reactive it from your course list'));
    }
    elseif  ( $thisCourse->status == 'date' )
    {
        if (!empty($thisCourse->publicationDate) && $thisCourse->publicationDate > claro_mktime())
        {
            $dialogBox->warning(
                get_lang('This course will be enabled on the %date',
                array('%date' => claro_date('d/m/Y', $thisCourse->publicationDate))));
        }
        if (!empty($thisCourse->expirationDate) && $thisCourse->expirationDate > claro_mktime())
        {
            $dialogBox->warning(
                get_lang('This course will be disable on the %date',
                array('%date' => claro_date('d/m/Y', $thisCourse->expirationDate))));
        }
    }
    
    if ($thisCourse->userLimit > 0)
    {
        $dialogBox->warning(
            get_lang('This course is limited to %userLimit users',
            array('%userLimit' => $thisCourse->userLimit)));
    }
    
    if ($thisCourse->registration == 'validation')
    {
        $usersPanelUrl = htmlspecialchars(Url::Contextualize( $toolRepository . 'user/user.php' ));
        $dialogBox->warning(
            get_lang('You have to validate users to give them access to this course through the <a href="%url">course user list</a>', array('%url' => $usersPanelUrl))
        );
    }
}


// Display header
$template = new CoreTemplate('course_index.tpl.php');
$template->assign('dialogBox', $dialogBox);
$template->assign('toolLinkListSource', $toolLinkList['source']);
$template->assign('toolLinkListSession', $toolLinkList['session']);
$template->assign('toolLinkListStandAlone', $toolLinkList['standAlone']);
$template->assign('courseManageToolLinkList', $courseManageToolLinkList);
$template->assign('portletIterator', $portletiterator);

$claroline->display->body->setContent($template->render());


echo $claroline->display->render();