<?php // $Id: desktop.cnr.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * User desktop : MyAnnouncements portlet
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     DESKTOP
 * @author      Claroline team <info@claroline.net>
 */

require_once get_module_path( 'CLANN' ) . '/lib/announcement.lib.php';

uses('courselist.lib');

class CLANN_Portlet extends UserDesktopPortlet
{
    public function __construct()
    {
        if (file_exists(claro_get_conf_repository() . 'CLANN.conf.php'))
        {
            include claro_get_conf_repository() . 'CLANN.conf.php';
        }
    }
    
    
    public function renderContent()
    {
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());
        
        $announcementEventList = announcement_get_items_portlet($personnalCourseList);
        
        $output = '';
        
        if($announcementEventList)
        {
            $output .= '<dl id="portletMyAnnouncements">';
            foreach($announcementEventList as $announcementItem)
            {
                // Hide hidden and expired elements
                $isVisible = (bool) ($announcementItem['visibility'] == 'SHOW') ? (1) : (0);
                $isOffDeadline = (bool)
                    (
                        (isset($announcementItem['visibleFrom'])
                            && strtotime($announcementItem['visibleFrom']) > time()
                        )
                        ||
                        (isset($announcementItem['visibleUntil'])
                            && time() >= strtotime($announcementItem['visibleUntil'])
                        )
                    ) ? (1) : (0);
                
                if ( $isVisible && !$isOffDeadline )
                {
                    $output .= '<dt>' . "\n"
                             . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="" />'
                             . ' <a href="' . $announcementItem['url'] . '">'
                             . $announcementItem['title']
                             . '</a>' . "\n"
                             . '</dt>' . "\n";
                    
                    foreach($announcementItem['eventList'] as $announcementEvent)
                    {
                        $output .= '<dd>'
                                 . '<a href="' . $announcementEvent['url'] . '">'
                                 . $announcementItem['courseOfficialCode']
                                 . '</a> : ' . "\n"
                                 . (!empty($announcementEvent['title']) ?
                                    $announcementEvent['title'] :
                                    get_lang('No title')) . "\n"
                                 . ' - '
                                 . $announcementEvent['content'] . "\n"
                                 . '</dd>' . "\n";
                    }
                }
            }
            $output .= '</dl>';
        }
        else
        {
            $output .= "\n"
                     . '<dl>' . "\n"
                     . '<dt>' . "\n"
                     . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="" />'
                     . ' '.get_lang('No event to display') . "\n"
                     . '</dt>' . "\n"
                     . '</dl>' . "\n";
        }
        
        return $output;
    }
    
    
    public function renderTitle()
    {
        return get_lang('Latest announcements');
    }
}