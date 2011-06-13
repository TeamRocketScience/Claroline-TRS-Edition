<?php // $Id: platform_courses.php 12935 2011-03-09 10:13:30Z abourguignon $

/**
 * PHP version 5
 *
 * @version     $Revision: 12935 $
 * @license     http://www.gnu.org/licenses/agpl-3.0-standalone.html AGPL Affero General Public License
 * @copyright   Copyright 2010 Claroline Consortium
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 *
 */

require '../inc/claro_init_global.inc.php';
require '../inc/lib/courselist.lib.php';

// Build the breadcrumb
$nameTools = get_lang('Platform courses');

$categoryId = ( !empty( $_REQUEST['category']) ) ? ( (int) $_REQUEST['category'] ) : ( 0 );

$categoryBrowser    = new ClaroCategoriesBrowser( $categoryId, claro_get_current_user_id() );

if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'search' )
{
    $categoriesList = array();
    $coursesList = search_course( $_REQUEST['keyword'] );
}

// Display
$template = $categoryBrowser->getTemplate();

$claroline->display->body->setContent($template->render());

echo $claroline->display->render();