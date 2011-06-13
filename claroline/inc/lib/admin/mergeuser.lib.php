<?php // $Id: mergeuser.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Merge User Library
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.admin.mergeuser
 */

/**
 * Merge User Class
 */
class MergeUser
{
    public static function merge( $uidToRemove, $uidToKeep )
    {
        $mainTbl = claro_sql_get_main_tbl();
        
        // inherit platform admin status ? harmful !
        /*$toKeep_isPlatformAdmin = claro_sql_query_fetch_single_value("
            SELECT isPlatformAdmin FROM `{$mainTbl['user']}` WHERE user_id = " . (int) $uidToKeep . "
        ");

        $toRemove_isPlatformAdmin = claro_sql_query_fetch_single_value("
            SELECT isPlatformAdmin FROM `{$mainTbl['user']}` WHERE user_id = " . (int) $uidToRemove . "
        ");

        if ( $toKeep_isPlatformAdmin && ! $toRemove_isPlatformAdmin )
        {
            claro_sql_query("UPDATE `{$mainTbl['user']}` SET `isPlatformAdmin` = 1 WHERE user_id = ".(int) $uidToKeep );
        }*/

        // inherit course creator status
        $toKeep_isCourseCreator = claro_sql_query_fetch_single_value("
            SELECT isCourseCreator FROM `{$mainTbl['user']}` WHERE user_id = " . (int) $uidToKeep . "
        ");

        $toRemove_isCourseCreator = claro_sql_query_fetch_single_value("
            SELECT isCourseCreator FROM `{$mainTbl['user']}` WHERE user_id = " . (int) $uidToRemove . "
        ");

        if ( $toRemove_isCourseCreator && ! $toKeep_isCourseCreator )
        {
            claro_sql_query("UPDATE `{$mainTbl['user']}` SET `isCourseCreator` = 1 WHERE user_id = ".(int) $uidToKeep );
        }

        // Get course list for the user to remove        
        $sql = "
            SELECT
                c.`code` AS `code`,
                cu.`isCourseManager`,
                cu.`profile_id`
            FROM
                `{$mainTbl['course']}` c,
                `{$mainTbl['rel_course_user']}` cu
            WHERE
                cu.user_id = ".(int)$uidToRemove."
              AND
                c.code = cu.code_cours";

        $courseList = claro_sql_query_fetch_all_rows($sql);
        
        foreach ( $courseList as $thisCourse )
        {
            // Check if the user to keep is registered to the course
            $sql = "
                SELECT
                    `code_cours`,
                    `isCourseManager`,
                    `profile_id`
                FROM
                    `{$mainTbl['rel_course_user']}`
                WHERE
                    code_cours = '".claro_sql_escape($thisCourse['code'])."'
                AND
                    user_id = ".(int)$uidToKeep;

            $userToKeepCourseList = claro_sql_query_fetch_single_row($sql);
            
            if ( !empty( $userToKeepCourseList ) )
            {
                // inherit isCourseManager
                if ( ( $thisCourse['isCourseManager'] == 1 ) && ( $userToKeepCourseList['isCourseManager'] != 1 ) )
                {
                    if ( ! claro_sql_query("
                        UPDATE `{$mainTbl['rel_course_user']}`
                        SET `isCourseManager` = 1
                        WHERE code_cours = '".claro_sql_escape($thisCourse['code'])."'
                        AND user_id = ".(int) $uidToKeep ) )
                    {
                        throw new Exception("Cannot change rel_course_user isCourseManager in {$thisCourse['code']}");
                    }
                }

                // inherit profile
                if ( $thisCourse['profile_id'] > $userToKeepCourseList['profile_id'] )
                {
                    if ( ! claro_sql_query("
                        UPDATE `{$mainTbl['rel_course_user']}`
                        SET `profile_id` = ".(int) $thisCourse['profile_id']."
                        WHERE code_cours = '".claro_sql_escape($thisCourse['code'])."'
                        AND user_id = ".(int) $uidToKeep ) )
                    {
                        throw new Exception("Cannot change rel_course_user profile in {$thisCourse['code']}");
                    }
                }

                // Remove the user to remove from the course
                $sql = "DELETE FROM `{$mainTbl['rel_course_user']}` 
                    WHERE user_id    = ".(int)$uidToRemove."
                      AND code_cours = '".claro_sql_escape($thisCourse['code'])."'";

                if ( ! claro_sql_query($sql) )
                {
                    throw new Exception("Cannot change rel_course_user in {$thisCourse['code']}");
                }
            }
            else
            {
                // Replace the user id of the user to remove
                $sql = "UPDATE `{$mainTbl['rel_course_user']}` 
                    SET   user_id    = ".(int)$uidToKeep."
                    WHERE user_id    = ".(int)$uidToRemove."
                      AND code_cours = '".claro_sql_escape($thisCourse['code'])."'";

                if ( ! claro_sql_query($sql) )
                {
                    throw new Exception("Cannot change rel_course_user in {$thisCourse['code']}");
                }
            }
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change rel_course_user in {$thisCourse['code']}");
            }
            
            $sql = "UPDATE `{$mainTbl['rel_class_user']}` 
                SET   user_id    = ".(int)$uidToKeep."
                WHERE user_id    = ".(int)$uidToRemove;

            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change rel_class_user in {$thisCourse['code']}");
            }
            
            
            // Update course
            
            self::mergeCourseUsers( $uidToRemove, $uidToKeep, $thisCourse['code'] );
            self::mergeCourseModuleUsers( $uidToRemove, $uidToKeep, $thisCourse['code'] );
            
            // update course messaging
            self::mergeCourseMessaging( $uidToRemove, $uidToKeep, $thisCourse['code'] );
        }
        
        // Update modules
        self::mergeModuleUsers( $uidToRemove, $uidToKeep );
        
        // Update main tracking
        self::mergeMainTrackingUsers( $uidToRemove, $uidToKeep );
        
        // updtae main messaging
        self::mergeMainMessaging( $uidToRemove, $uidToKeep );
        
        // Delete old user
        $sql = "DELETE FROM `{$mainTbl['user']}`
            WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot delete old use");
        }
    }
    
    public static function mergeMainMessaging( $uidToRemove, $uidToKeep )
    {
        $tableName = get_module_main_tbl(array('im_message','im_message_status','im_recipient'));
            
        $getUserMessagesInCourse = "SELECT M.message_id AS id"
            . " FROM `" . $tableName['im_message'] . "` as M\n"
            . " LEFT JOIN `" . $tableName['im_recipient'] . "` as R ON M.message_id = R.message_id\n"
            . " WHERE R.user_id = " . (int)$uidToKeep
            . " AND M.course IS NULL";
            
        $userToKeepMsgList = claro_sql_query_fetch_all($getUserMessagesInCourse);
        
        if ( !empty( $userToKeepMsgList ) )
        {
            $messageListToRemoveArr = array();
            
            foreach ( $userToKeepMsgList as $message )
            {
                $messageListToRemoveArr[] = (int)$message['id'];
            }
            
            $messageListToRemove = implode(',', $messageListToRemoveArr);
            
            // Remove the user to remove from the course
            $sql = "DELETE FROM `{$tableName['im_recipient']}`
                WHERE user_id = " . (int)$uidToRemove . "
                AND message_id IN ({$messageListToRemove})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot remove doubles in internal messaging recipients");
            }
            
            $sql = "DELETE FROM `{$tableName['im_message_status']}`
                WHERE user_id = " . (int)$uidToRemove . "
                AND message_id IN ({$messageListToRemove})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot remove doubles in internal messaging status");
            }
        }
        
        $getUserMessagesInCourse = "SELECT M.message_id AS id"
            . " FROM `" . $tableName['im_message'] . "` as M\n"
            . " LEFT JOIN `" . $tableName['im_recipient'] . "` as R ON M.message_id = R.message_id\n"
            . " WHERE R.user_id = " . (int)$uidToRemove
            . " AND M.course IS NULL";
            
        $userToKeepMsgList = claro_sql_query_fetch_all($getUserMessagesInCourse);
        
        if ( !empty( $userToKeepMsgList ) )
        {
            $messageListToUpdateArr = array();
            
            foreach ( $userToKeepMsgList as $message )
            {
                $messageListToUpdateArr[] = (int)$message['id'];
            }
            
            $messageListToUpdate = implode(',', $messageListToUpdateArr);
        
            // Replace the user id of the user to remove
            $sql = "UPDATE `{$tableName['im_recipient']}` 
                SET   user_id    = ".(int)$uidToKeep."
                WHERE user_id    = ".(int)$uidToRemove."
                  AND message_id IN ({$messageListToUpdate})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change internal messaging recipient");
            }
            
            $sql = "UPDATE `{$tableName['im_message_status']}` 
                SET   user_id    = ".(int)$uidToKeep."
                WHERE user_id    = ".(int)$uidToRemove."
                  AND message_id IN ({$messageListToUpdate})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change internal messaging status");
            }
        }
    }
    
    public static function mergeCourseMessaging( $uidToRemove, $uidToKeep, $thisCourseCode )
    {
        // update messaging
        
        $tableName = get_module_main_tbl(array('im_message','im_message_status','im_recipient'));
        
        $getUserMessagesInCourse = "SELECT M.message_id AS id"
            . " FROM `" . $tableName['im_message'] . "` as M\n"
            . " LEFT JOIN `" . $tableName['im_recipient'] . "` as R ON M.message_id = R.message_id\n"
            . " WHERE R.user_id = " . (int)$uidToKeep
            . " AND M.course = '".claro_sql_escape($thisCourseCode)."'";
            
        $userToKeepMsgList = claro_sql_query_fetch_all($getUserMessagesInCourse);
        
        if ( !empty( $userToKeepMsgList ) )
        {
            $messageListToRemoveArr = array();
            
            foreach ( $userToKeepMsgList as $message )
            {
                $messageListToRemoveArr[] = (int)$message['id'];
            }
            
            $messageListToRemove = implode(',', $messageListToRemoveArr);
            
            // Remove the user to remove from the course
            $sql = "DELETE FROM `{$tableName['im_recipient']}`
                WHERE user_id = " . (int)$uidToRemove . "
                AND message_id IN ({$messageListToRemove})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change messaging in {$thisCourseCode}");
            }
            
            $sql = "DELETE FROM `{$tableName['im_message_status']}`
                WHERE user_id = " . (int)$uidToRemove . "
                AND message_id IN ({$messageListToRemove})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change messaging in {$thisCourseCode}");
            }
        }
        
        $getUserMessagesInCourse = "SELECT M.message_id AS id"
            . " FROM `" . $tableName['im_message'] . "` as M\n"
            . " LEFT JOIN `" . $tableName['im_recipient'] . "` as R ON M.message_id = R.message_id\n"
            . " WHERE R.user_id = " . (int)$uidToRemove
            . " AND M.course = '".claro_sql_escape($thisCourseCode)."'";
            
        $userToKeepMsgList = claro_sql_query_fetch_all($getUserMessagesInCourse);
        
        if ( !empty( $userToKeepMsgList ) )
        {
            $messageListToUpdateArr = array();
            
            foreach ( $userToKeepMsgList as $message )
            {
                $messageListToUpdateArr[] = (int)$message['id'];
            }
            
            $messageListToUpdate = implode(',', $messageListToUpdateArr);
        
            // Replace the user id of the user to remove
            $sql = "UPDATE `{$tableName['im_recipient']}` 
                SET   user_id    = ".(int)$uidToKeep."
                WHERE user_id    = ".(int)$uidToRemove."
                  AND message_id IN ({$messageListToUpdate})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change messaging in {$thisCourseCode}");
            }
            
            $sql = "UPDATE `{$tableName['im_message_status']}` 
                SET   user_id    = ".(int)$uidToKeep."
                WHERE user_id    = ".(int)$uidToRemove."
                  AND message_id IN ({$messageListToUpdate})";
            
            if ( ! claro_sql_query($sql) )
            {
                throw new Exception("Cannot change messaging in {$thisCourseCode}");
            }
        }
    }
    
    public static function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $courseTbl = claro_sql_get_course_tbl( claro_get_course_db_name_glued( $courseId ) );
        
        // Get groups for the user to remove
        $sql = "SELECT team
                FROM `{$courseTbl['group_rel_team_user']}`
                WHERE user= ".(int)$uidToRemove;

        $result   = claro_sql_query_fetch_all_cols($sql);
        $teamList = $result['team'];
        
        foreach ( $teamList as $thisTeam )
        {
            $sql = "SELECT user 
                    FROM `{$courseTbl['group_rel_team_user']}`
                    WHERE user = ".(int)$uidToKeep."
                      AND team = ".(int)$thisTeam;

            $result = claro_sql_query_fetch_all($sql);

            if ( !empty($result) )
            {
                $sql = "DELETE FROM `{$courseTbl['group_rel_team_user']}`
                         WHERE user  = ".(int)$uidToRemove."
                           AND team  = ".(int)$thisTeam;
            }
            else
            {
                $sql = "UPDATE `{$courseTbl['group_rel_team_user']}`
                           SET user = ".(int)$uidToKeep."
                         WHERE user = ".(int)$uidToRemove."
                           AND team = ".(int)$thisTeam;
            }
        }
        
        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot change group_rel_team_user in {$courseId}");
        }
        
        // Update tracking
        $sql = "UPDATE `{$courseTbl['tracking_event']}`
                SET   user_id = ".(int)$uidToKeep."
                WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot change tracking_event in {$courseId}");
        }

        
        $qwz_tbl_names = get_module_course_tbl( array( 'qwz_tracking' ), $courseId );
        
        $sql = "UPDATE `{$qwz_tbl_names['qwz_tracking']}`
                SET   user_id  = ".(int)$uidToKeep."
                WHERE user_id  = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot change qwz_tracking in {$courseId}");
        }

        // Update user info in course
        $sql = "DELETE FROM `{$courseTbl['userinfo_content']}`
                WHERE user_id = ".(int)$uidToRemove;
        
        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot remove user info in {$courseId}");
        }
    }
    
    public static function mergeMainTrackingUsers( $uidToRemove, $uidToKeep )
    {
        $mainTbl = claro_sql_get_main_tbl();
        
        $sql = "UPDATE `{$mainTbl['tracking_event']}`
            SET   user_id = ".(int)$uidToKeep."
            WHERE user_id = ".(int)$uidToRemove;

        if ( ! claro_sql_query($sql) )
        {
            throw new Exception("Cannot update tracking_event in main DB");
        }

    }
    
    public static function mergeCourseModuleUsers( $uidToRemove, $uidToKeep, $courseId )
    {
        $courseModuleList = module_get_course_tool_list( $courseId );
        
        foreach ( $courseModuleList as $courseModule )
        {
            $moduleMergeUserPath = get_module_path( $courseModule['label'] ) . '/connector/mergeuser.cnr.php';
            
            if ( file_exists( $moduleMergeUserPath ) )
            {
                require_once $moduleMergeUserPath;
                $moduleMergeClass = $courseModule['label'].'_MergeUser';
                
                if ( class_exists( $moduleMergeClass ) )
                {
                    $moduleMerge = new $moduleMergeClass;
                    
                    if ( method_exists( $moduleMerge, 'mergeCourseUsers' ) )
                    {
                        $moduleMerge->mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId );
                    }
                }
            }
        }
    }
    
    public static function mergeModuleUsers( $uidToRemove, $uidToKeep )
    {
        $courseModuleList = get_module_label_list();
        
        foreach ( $courseModuleList as $courseModule )
        {
            $moduleMergeUserPath = get_module_path( $courseModule['label'] ) . '/connector/mergeuser.cnr.php';
            
            if ( file_exists( $moduleMergeUserPath ) )
            {
                require_once $moduleMergeUserPath;
                $moduleMergeClass = $courseModule['label'].'_MergeUser';
                
                if ( class_exists( $moduleMergeClass ) )
                {
                    $moduleMerge = new $moduleMergeClass;
                    
                    if ( method_exists( $moduleMerge, 'mergeUsers' ) )
                    {
                        $moduleMerge->mergeUsers( $uidToRemove, $uidToKeep );
                    }
                }
            }
        }
    }
}

interface Module_MergeUser
{
    public function mergeUsers( $uidToRemove, $uidToKeep );
    public function mergeCourseUsers( $uidToRemove, $uidToKeep, $courseId );
}
