<?php

class CLWIKI_MergeUser implements Module_MergeUser
{
    public function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $moduleCourseTbl = get_module_course_tbl( array('wiki_pages', 'wiki_pages_content'), $courseId );
        
        // Update wiki_pages
        $sql = "UPDATE `{$moduleCourseTbl['wiki_pages']}`
                SET   owner_id = ".(int)$uidToKeep."
                WHERE owner_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot update wiki_pages in {$courseId}");
        }
        
        // Update wiki_pages_content
        $sql = "UPDATE `{$moduleCourseTbl['wiki_pages_content']}`
                SET   editor_id = ".(int)$uidToKeep."
                WHERE editor_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot update wiki_pages_content in {$courseId}");
        }
        
    }
    
    public function mergeUsers( $uidToRemove, $uidToKeep )
    {
        // empty
    }
}
