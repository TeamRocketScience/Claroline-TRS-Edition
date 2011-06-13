<?php // $Id: coursehomepageportletiterator.class.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * CLAROLINE
 *
 * @version     $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */


class CourseHomePagePortletIterator implements Iterator, Countable
{
    private     $courseId;
    private     $portlets = array();
    protected   $n = 0;
    
    public function __construct($courseId)
    {
        $this->courseId = $courseId;
        $courseCode     = ClaroCourse::getCodeFromId($this->courseId);
        
        $tbl_mdb_names          = claro_sql_get_main_tbl();
        $tbl_rel_course_portlet = $tbl_mdb_names['rel_course_portlet'];
        
        $sql = "SELECT id, courseId, rank, label, visible
                FROM `{$tbl_rel_course_portlet}`
                WHERE `courseId` = {$this->courseId}
                ORDER BY `rank` ASC";
        
        $result = Claroline::getDatabase()->query($sql);
        
        foreach($result as $portletInfos)
        {
            // Require the proper portlet class
            $portletPath = get_module_path( $portletInfos['label'] )
            . '/connector/coursehomepage.cnr.php';
            
            $portletName = $portletInfos['label'] . '_Portlet';
            
            if ( file_exists($portletPath) )
            {
                require_once $portletPath;
            }
            else
            {
                echo "Le fichier {$portletPath} est introuvable<br/>";
            }
            
            if (class_exists($portletName))
            {
                $portlet = new $portletName($portletInfos['id'], $courseCode,
                $portletInfos['courseId'], $portletInfos['rank'],
                $portletInfos['label'], $portletInfos['visible']);
                
                $this->portlets[] = $portlet;
            }
            
            #TODO debug
            else
            {
                echo "Can't find the class {$portletName}_portlet<br/>";
                return false;
            }
        }
    }
    
    public function rewind()
    {
        $this->n = 0;
    }
    
    public function next()
    {
        $this->n++;
    }
    
    public function key()
    {
        return 'increment '.$this->n+1;
    }
    
    public function current()
    {
        return $this->portlets[$this->n];
    }
    
    public function valid()
    {
        return $this->n < count($this->portlets);
    }
    
    public function count()
    {
        return count($this->portlets);
    }
}