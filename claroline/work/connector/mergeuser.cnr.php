<?php

class CLWRK_MergeUser implements Module_MergeUser
{
    public function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $moduleCourseTbl = get_module_course_tbl( array('wrk_submission'), $courseId );
        
        $sql = "UPDATE `{$moduleCourseTbl['wrk_submission']}`
                SET   user_id = ".(int)$uidToKeep."
                WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot update wrk_submission in {$courseId}");
        }
    }
    
    public function mergeUsers( $uidToRemove, $uidToKeep )
    {
        // empty
    }
}
