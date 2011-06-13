<?php // $Id: index.php 12997 2011-03-21 16:55:57Z abourguignon $

/**
 * CLAROLINE
 *
 * This is the index page of sdk tools.
 *
 * @version     $Revision: 12997 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     SDK
 * @author      Claro Team <cvs@claroline.net>
 * @author      Christophe Gesche <moosh@claroline.net>
 */

require '../../inc/claro_init_global.inc.php';
if (file_exists(get_path('rootSys') .'platform/currentVersion.inc.php')) include (get_path('rootSys') . 'platform/currentVersion.inc.php');

if (!claro_is_platform_admin())
    claro_disp_auth_form();

if ( get_conf('DEVEL_MODE',false))
{
    $devtoolsList = array();
    if (file_exists('./fillUser.php'))        $devtoolsList[] = '<a href="fillUser.php">'.get_lang('Create fake users').'</a>';
    if (file_exists('./fillCourses.php'))     $devtoolsList[] = '<a href="fillCourses.php">'.get_lang('Create fake courses').'</a>';
    if (file_exists('./fillTree.php'))        $devtoolsList[] = '<a href="fillTree.php">'.get_lang('Create fake categories').'</a>';
    if (file_exists('./fillToolCourses.php')) $devtoolsList[] = '<a href="fillToolCourses.php">'.get_lang('Create item into courses tools').'</a>';
}

$nameTools = get_lang('Development Tools');

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$out = '';

$out .= claro_html_tool_title($nameTools);

$out .= '<h4>' . get_lang('Translations') . '</h4>'
      . '<ul>'
      . '<li><a href="../xtra/sdk/translation_index.php">' . get_lang('Translations') . '</a></li>'
      . '</ul>';

if (0 < count($devtoolsList))
{
    $out .= claro_html_tool_title(get_lang('Filling'))
          . claro_html_list($devtoolsList);
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();