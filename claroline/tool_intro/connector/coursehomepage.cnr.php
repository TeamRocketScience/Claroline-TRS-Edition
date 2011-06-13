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

require_once get_module_path( 'CLTI' ) . '/lib/toolintroductioniterator.class.php';

class CLTI_Portlet extends CourseHomePagePortlet
{
    public function renderContent()
    {
        // Init linker
        FromKernel::uses('core/linker.lib');
        ResourceLinker::init();
        
        $output = '';
        $output .= '<dl id="portletAbout">' . "\n";
        
        $toolIntroIterator = new ToolIntroductionIterator($this->courseCode);
        
        if ($toolIntroIterator->count() > 0)
        {
            
            foreach ($toolIntroIterator as $introItem)
            {
                // Display attached resources (if any)
                $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(array('id' => $introItem->getId()));
                $currentLocator->setModuleLabel('CLINTRO');
                $currentLocator->setResourceId($introItem->getId()
                );
                $resources = ResourceLinker::renderLinkList($currentLocator);
                
                // Prepare the render
                $output .= '<dt>' . "\n"
                         . '</dt>' . "\n"
                         . '<dd'.(!$toolIntroIterator->hasNext()?' class="last"':'').'>' . "\n"
                         . $introItem->getContent() . "\n"
                         . $resources
                         . '</dd>' . "\n";
            }
        }
        else
        {
            $output .= '<dt></dt>'
                     . '<dd>' . "\n"
                     . '<img class="iconDefinitionList" src="' . get_icon_url('course_description', 'CLDSC') . '" alt="Description icon" />'
                     . ' ' . get_lang('No description') . "\n"
                     . '</dd>' . "\n";
        }
        
        $output .= '</dl>';
        
        return $output;
    }
    
    public function renderTitle()
    {
        $output = get_lang('Headlines');
        
        if (claro_is_allowed_to_edit())
        {
            $output .= ' <span class="separator">|</span> <a href="'
                     . htmlspecialchars(Url::Contextualize(get_module_url( 'CLTI' ) . '/index.php'))
                     . '">'
                     . '<img src="' . get_icon_url('settings') . '" alt="'.get_lang('Settings').'" /> '
                     . get_lang('Manage').'</a>';
        }
        
        return $output;
    }
}