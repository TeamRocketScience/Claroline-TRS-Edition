<?php // $Id: coursehomepage.cnr.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Course home page: Announcements portlet
 *
 * @version     $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLCHP
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @author      Claroline team <info@claroline.net>
 * @since       1.10
 */

class CLANN_Portlet extends CourseHomePagePortlet
{
    public function renderContent()
    {
        // Select announcements for this course
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->courseCode));
        $tbl_announcement   = $tbl['announcement'];
        
        $currentCourseData  = claro_get_course_data($this->courseCode);
        $curdate            = claro_mktime();
        $output             = '';
        
        $sql = "SELECT " . Claroline::getDatabase()->quote($currentCourseData['sysCode']) . " AS `courseSysCode`, " . "\n"
                . Claroline::getDatabase()->quote($currentCourseData['officialCode']) . " AS `courseOfficialCode`, " . "\n"
                . "'CLANN'                                              AS `toolLabel`, " . "\n"
                . "CONCAT(`temps`, ' ', '00:00:00')                     AS `date`, " . "\n"
                . "CONCAT(`title`,' - ',`contenu`)                      AS `content`, " . "\n"
                . "`title`, " . "\n"
                . "`visibility`, " . "\n"
                . "`visibleFrom`, " . "\n"
                . "`visibleUntil` " . "\n"
                . "FROM `" . $tbl_announcement . "` " . "\n"
                . "WHERE CONCAT(`title`, `contenu`) != '' " . "\n"
                . "AND visibility = 'SHOW' " . "\n"
                . "            AND (UNIX_TIMESTAMP(`visibleFrom`) < '" . $curdate . "'
                                     OR `visibleFrom` IS NULL OR UNIX_TIMESTAMP(`visibleFrom`) = 0
                                   )
                               AND ('" . $curdate . "' < UNIX_TIMESTAMP(`visibleUntil`) OR `visibleUntil` IS NULL)"
                . "ORDER BY `date` DESC" . "\n"
                ;
        
        $announcementList = Claroline::getDatabase()->query($sql);
        
        // Manage announcement's datas
        if($announcementList)
        {
            $output .= '<dl id="portletAnnouncements">' . "\n";
            
            $i = 0;
            foreach($announcementList as $announcementItem)
            {
                // Generate announcement URL
                $announcementItem['url'] = get_path('url')
                    . '/claroline/announcements/announcements.php?cidReq='
                    . $currentCourseData['sysCode'];
                
                // Generate announcement title and content
                $announcementItem['title'] = trim(strip_tags($announcementItem['title']));
                if ( $announcementItem['title'] == '' )
                {
                    $announcementItem['title'] = substr($announcementItem['title'], 0, 60) . (strlen($announcementItem['title']) > 60 ? ' (...)' : '');
                }
                
                $announcementItem['content'] = trim(strip_tags($announcementItem['content']));
                if ( $announcementItem['content'] == '' )
                {
                    $announcementItem['content'] = substr($announcementItem['content'], 0, 60) . (strlen($announcementItem['content']) > 60 ? ' (...)' : '');
                }
                
                // Don't display hidden and expired elements
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
                
                // Prepare the render
                if ( $isVisible && !$isOffDeadline )
                {
                    $output .= '<dt>' . "\n"
                             . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="" /> '
                             . '<a href="' . $announcementItem['url'] . '">'
                             . $announcementItem['title']
                             . '</a>' . "\n"
                             . '</dt>' . "\n"
                             . '<dd'.($i == count($announcementList)-1?' class="last"':'').'>' . "\n"
                             . $announcementItem['content'] . "\n"
                             . '</dd>' . "\n"
                             ;
                }
                
                $i++;
            }
            
            $output .= '</dl>';
        }
        else
        {
            $output .= "\n"
                     . '<dl>' . "\n"
                     . '<dt></dt>' . "\n"
                     . '<dd class="last">'
                     . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="Announcement icon" />'
                     . ' ' . get_lang('No announcement') . "\n"
                     . '</dd>' . "\n"
                     . '</dl>' . "\n" . "\n"
                     ;
        }
        
        return $output;
    }
    
    public function renderTitle()
    {
        $output = get_lang('Latest announcements');
        
        if (claro_is_allowed_to_edit())
        {
            $output .= ' <span class="separator">|</span> <a href="'
                     . htmlspecialchars(Url::Contextualize(get_module_url( 'CLANN' ) . '/announcements.php'))
                     . '">'
                     . '<img src="' . get_icon_url('settings') . '" alt="'.get_lang('Settings').'" /> '
                     . get_lang('Manage').'</a>';
        }
        
        return $output;
    }
}