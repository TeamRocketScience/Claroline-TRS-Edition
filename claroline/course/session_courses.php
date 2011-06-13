<?php // $Id: index.php 12441 2010-06-11 14:34:16Z abourguignon $

/**
 * CLAROLINE
 *
 * @version     1.10 $Revision: 12441 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLHOME
 * @author      Claro Team <cvs@claroline.net>
 */


require '../inc/claro_init_global.inc.php';
require_once get_path('incRepositorySys') . '/lib/claroCourse.class.php';
include claro_get_conf_repository() . 'rss.conf.php';

$cid = ( isset($_REQUEST['cid']) ) ? $_REQUEST['cid'] : '';
$nameTools = get_lang('Manage session courses');

if ( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form(true);

$toolRepository = get_path('clarolineRepositoryWeb');
claro_set_display_mode_available(TRUE);

if (!empty($cid))
{
    $course = new ClaroCourse();
    $course->load(ClaroCourse::getCodeFromId($cid));
}

$sessionCourses = $course->getSessionCourses();

// Display header
$template = new CoreTemplate('session_courses.tpl.php');
$template->assign('sessionCourses', $sessionCourses);
$template->assign('courseId', $course->id);

$claroline->display->body->setContent($template->render());


echo $claroline->display->render();
?>