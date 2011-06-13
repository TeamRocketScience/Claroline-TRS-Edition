<?php // $Id: index.php 13027 2011-03-31 16:40:32Z abourguignon $

/**
 * CLAROLINE
 *
 * Campus Home Page
 *
 * @version     $Revision: 13027 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLINDEX
 * @author      Claro Team <cvs@claroline.net>
 */

unset($includePath); // prevent hacking

// Flag forcing the 'current course' reset, as we're not anymore inside a course
$cidReset = true;
$tidReset = true;
$_SESSION['courseSessionCode'] = null;

// Include Library and configuration files
require './claroline/inc/claro_init_global.inc.php'; // main init
include claro_get_conf_repository() . 'CLHOME.conf.php'; // conf file


if (get_conf('display_former_homepage') || !claro_is_user_authenticated())
{
    require_once get_path('incRepositorySys') . '/lib/courselist.lib.php';
    JavascriptLoader::getInstance()->load('courseList');
    
    // Main template
    $template = new CoreTemplate('platform_index.tpl.php');
    
    // Languages
    $template->assign('languages', get_language_to_display_list());
    $template->assign('currentLanguage', language::current_language());
    
    // Category browser
    $categoryId = ( !empty( $_REQUEST['category']) ) ? ( (int) $_REQUEST['category'] ) : ( 0 );
    $categoryBrowser = new ClaroCategoriesBrowser( $categoryId, claro_get_current_user_id() );
    $templateCategoryBrowser = $categoryBrowser->getTemplate();
    
    $template->assign('templateCategoryBrowser', $templateCategoryBrowser);
    
    // User course (activated and deactivated) lists
    $userCourseList = render_user_course_list();
    $userCourseListDesactivated = render_user_course_list_desactivated();
    
    $templateMyCourses = new CoreTemplate('mycourses.tpl.php');
    $templateMyCourses->assign('userCourseList', $userCourseList);
    $templateMyCourses->assign('userCourseListDesactivated', $userCourseListDesactivated);
    
    $template->assign('templateMyCourses', $templateMyCourses);
    
    // Last user action
    $lastUserAction = ($_SESSION['last_action'] != '1970-01-01 00:00:00') ?
        $_SESSION['last_action'] :
        date('Y-m-d H:i:s');
    
    $template->assign('lastUserAction', $lastUserAction);
    
    
    if (claro_is_user_authenticated())
    {
        $userCommands = array();
        
        // User commands
        $userCommands[] = '<a href="' . $_SERVER['PHP_SELF'] . '" class="userCommandsItem">'
                        . '<img src="' . get_icon_url('mycourses') . '" alt="" /> '
                        . get_lang('My course list')
                        . '</a>' . "\n";
        
        // 'Create Course Site' command. Only available for teacher.
        if (claro_is_allowed_to_create_course())
        {
            $userCommands[] = '<a href="claroline/course/create.php" class="userCommandsItem">'
                            . '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
                            . get_lang('Create a course site')
                            . '</a>' . "\n";
        }
        elseif ( $GLOBALS['currentUser']->isCourseCreator )
        {
            $userCommands[] = '<span class="userCommandsItemDisabled">'
                            . '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
                            . get_lang('Create a course site')
                            . '</span>' . "\n";
        }
        
        if (get_conf('allowToSelfEnroll',true))
        {
            $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqReg&amp;categoryId=0" class="userCommandsItem">'
                            . '<img src="' . get_icon_url('enroll') . '" alt="" /> '
                            . get_lang('Enrol on a new course')
                            . '</a>' . "\n";
            
            $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="userCommandsItem">'
                            . '<img src="' . get_icon_url('unenroll') . '" alt="" /> '
                            . get_lang('Remove course enrolment')
                            . '</a>' . "\n";
        }
        
        $userCommands[] = '<a href="claroline/course/platform_courses.php" class="userCommandsItem">'
                        . '<img src="' . get_icon_url('course') . '" alt="" /> '
                        . get_lang('All platform courses')
                        . '</a>' . "\n";
        
        $userCommands[] = '<a href="'.htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb') . 'notification_date.php')).'" class="userCommandsItem">'
                        . '<img class="iconDefinitionList" src="'.get_icon_url('hot').'" alt="'.get_lang('New items').'" />'
                        . ' '.get_lang('New items').' '
                        . get_lang('to another date')
                        . ((substr($lastUserAction, strlen($lastUserAction) - 8) == '00:00:00' ) ?
                            (' ['.claro_html_localised_date(
                                get_locale('dateFormatNumeric'),
                                strtotime($lastUserAction)).']') :
                            (''))
                        . '</a>' . "\n";
        
        $template->assign('userCommands', $userCommands);
        
        // User profilebox
        FromKernel::uses('display/userprofilebox.lib');
        $userProfileBox = new UserProfileBox(false);
        
        $template->assign('userProfileBox', $userProfileBox);
    }
    
    // Render
    $claroline->display->body->setContent($template->render());
    
    if (!(isset($_REQUEST['logout']) && isset($_SESSION['isVirtualUser'])))
    {
        echo $claroline->display->render();
    }
}
else
{
    require_once get_path('clarolineRepositorySys') . '/desktop/index.php';
}

// Logout request : delete session data
if (isset($_REQUEST['logout']))
{
    if (isset($_SESSION['isVirtualUser']))
    {
        unset($_SESSION['isVirtualUser']);
        claro_redirect(get_conf('rootWeb') . 'claroline/admin/admin_users.php');
        exit();
    }
    
    // notify that a user has just loggued out
    if (isset($logout_uid)) // Set  by local_init
    {
        $eventNotifier->notifyEvent('user_logout', array('uid' => $logout_uid));
    }
    /* needed to be able to :
         - log with claroline when 'magic login' has previously been clicked
         - notify logout event
         (logout from CAS has been commented in casProcess.inc.php)*/
    if( get_conf('claro_CasEnabled', false) && ( get_conf('claro_CasGlobalLogout') && !phpCAS::checkAuthentication() ) )
    {
        phpCAS::logout((isset( $_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on'||$_SERVER['HTTPS']==1) ? 'https://' : 'http://')
                        . $_SERVER['HTTP_HOST'].get_conf('urlAppend').'/index.php');
    }
    session_destroy();
}

// Hide breadcrumbs and view mode on platform home page
// $claroline->display->banner->hideBreadcrumbLine();
?>