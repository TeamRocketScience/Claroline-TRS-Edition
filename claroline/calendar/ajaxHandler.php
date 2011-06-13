<?php // $Id: ajaxHandler.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * MyCalendar portlet ajax backend
 *
 * @version     $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     DESKTOP
 * @author      Claroline team <info@claroline.net>
 * @since       1.9
 *
 */

if ( isset($_REQUEST['location']) )
{
    // Call the right class according to the location
    if ( $_REQUEST['location'] == 'coursehomepage' )
    {
        require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
        
        require_once dirname(__FILE__) . '/lib/coursehomepagecalendar.lib.php';
        
        $cal = new CourseHomePageCalendar(htmlentities($_REQUEST['courseCode']));
    }
    elseif ( $_REQUEST['location'] == 'userdesktop' )
    {
        require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
        
        require_once dirname(__FILE__) . '/lib/desktopcalendar.lib.php';
        
        $cal = new UserDesktopCalendar;
    }
    
    if ( isset($_REQUEST['year']) )
    {
        $cal->setYear( (int) $_REQUEST['year'] );
    }
    
    if ( isset($_REQUEST['month']) )
    {
        $cal->setMonth( (int) $_REQUEST['month'] );
    }
    
    echo claro_utf8_encode( $cal->render(), get_conf('charset') );
}

?>