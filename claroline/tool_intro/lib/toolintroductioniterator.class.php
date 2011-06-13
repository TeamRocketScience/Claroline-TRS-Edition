<?php // $Id: toolintroductioniterator.class.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * CLAROLINE
 *
 * @version     $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLTI
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @author      Claro Team <cvs@claroline.net>
 * @since       1.10
 */


require get_module_path('CLTI').'/lib/toolintroduction.class.php';

class ToolIntroductionIterator implements Iterator, Countable
{
    private     $courseCode;
    private     $toolIntroductions = array();
    protected   $n = 0;
    
    public function __construct($courseCode)
    {
        $this->courseCode = $courseCode;
        
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->courseCode));
        $tblToolIntro = $tbl_cdb_names['tool_intro'];
        
        $sql = "SELECT id, tool_id, title, display_date,
                content, rank, visibility
                FROM `{$tblToolIntro}`
                ORDER BY rank ASC";
        
        $result = Claroline::getDatabase()->query($sql);
        
        foreach($result as $toolIntro)
        {
            $toolIntro = new ToolIntro(
                $toolIntro['id'],
                $this->courseCode,
                $toolIntro['tool_id'],
                $toolIntro['title'],
                $toolIntro['content'],
                $toolIntro['rank'],
                $toolIntro['display_date'],
                $toolIntro['visibility']
            );
            $this->toolIntroductions[] = $toolIntro;
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
        return $this->toolIntroductions[$this->n];
    }
    
    public function valid()
    {
        return ($this->n < count($this->toolIntroductions));
    }
    
    public function count()
    {
        return count($this->toolIntroductions);
    }
    
    public function hasNext()
    {
        return ($this->n < count($this->toolIntroductions)-1);
    }
}