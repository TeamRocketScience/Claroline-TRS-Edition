<?php // $Id: export.lib.php 13028 2011-03-31 17:05:16Z abourguignon $
/**
 *
 * @version 0.1 $Revision: 13028 $
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claroline team <info@claroline.net>
 *
 * @package CLUSR
 *
 */

require_once get_path('incRepositorySys') . '/lib/csv.class.php';
FromKernel::uses( 'user_info.lib' );

class UserInfoList
{
    private $courseId;

    public function __construct( $courseId )
    {
        $this->courseId = $courseId;
    }

    public function getUserInfoLabels()
    {
        $labels = claro_user_info_claro_user_info_get_cat_def_list( $this->courseId );

        if ( $labels )
        {
            $ret = array();

            foreach ( $labels as $label )
            {
                $ret[$label['catId']] = $label['title'];
            }

            return $ret;
        }
        else
        {
            return array();
        }
    }

    public function getUserInfo( $catId )
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->courseId));

        return Claroline::getDatabase()->query("
            SELECT
                content.user_id     AS userId,
                cat.id              AS catId,
                cat.title           AS title,
                content.content     AS content
            FROM
                `" . $tbl['userinfo_def'] . "`     AS cat
            LEFT JOIN
                `" . $tbl['userinfo_content'] . "` AS content
            ON
                cat.id = content.def_id
            WHERE
                cat.id = " . (int) $catId . "
            ORDER BY `cat`.`id`
        ");
    }
}


class csvUserList extends csv
{
    private $course_id;
    private $exId;
    
    public function __construct( $course_id )
    {
        parent::csv(); // call constructor of parent class
        
        $this->course_id = $course_id;
    }
    
    function buildRecords( $exportUserInfo = true )
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();

        $tbl_user = $tbl_mdb_names['user'];
        $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
        
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->course_id));

        $tbl_team = $tbl_cdb_names['group_team'];
        $tbl_rel_team_user = $tbl_cdb_names['group_rel_team_user'];
        
        $username = ( claro_is_platform_admin() && get_conf( 'export_sensitive_data_for_admin', false ) )
                || get_conf('export_user_username', false)
            ? "`U`.`username`     AS `username`,"
            : ""
            ;
                 
        if ( ( claro_is_platform_admin() && get_conf( 'export_sensitive_data_for_admin', false ) )
            || get_conf('export_user_password', false) )
        {
            if ( ( claro_is_platform_admin() && get_conf( 'export_sensitive_data_for_admin', false ) )
                || get_conf('export_user_password_encrypted', true ) )
            {
                $password = "MD5(`U`.`password`)     AS `password`,";
            }
            else
            {
                $password = "`U`.`password`     AS `password`,";
            }
        }
        else
        {
            $password = '';
        }

        // get user list
        $sql = "SELECT `U`.`user_id`      AS `userId`,
                       `U`.`nom`          AS `lastname`,
                       `U`.`prenom`       AS `firstname`,
                       {$username}
                       {$password}
                       `U`.`email`        AS `email`,
                       `U`.`officialCode`     AS `officialCode`,
                       GROUP_CONCAT(`G`.`id`) AS `groupId`,
                       GROUP_CONCAT(`G`.`name`) AS `groupName`
               FROM
                    (
                    `" . $tbl_user . "`           AS `U`,
                    `" . $tbl_rel_course_user . "` AS `CU`
                    )
               LEFT JOIN `" . $tbl_rel_team_user . "` AS `GU`
                ON `U`.`user_id` = `GU`.`user`
               LEFT JOIN `" . $tbl_team . "` AS `G`
                ON `GU`.`team` = `G`.`id`
               WHERE `U`.`user_id` = `CU`.`user_id`
               AND   `CU`.`code_cours`= '" . claro_sql_escape($this->course_id) . "'
               GROUP BY U.`user_id`
               ORDER BY U.`user_id`";

        $userList = claro_sql_query_fetch_all($sql);

        // build recordlist with good values for answers
        if( is_array($userList) && !empty($userList) )
        {
            // add titles at row 0, for that get the keys of the first row of array
            $this->recordList[0] = array_keys($userList[0]);

            $i = 1;

            $userIdList = array();

            foreach( $userList as  $user )
            {
                $userIdList[$user['userId']] = $i;

                if ( !( ( claro_is_platform_admin() && get_conf( 'export_sensitive_data_for_admin', false ) )
                    || get_conf('export_user_id', false) ) )
                {
                    $user['userId'] = $i;
                }
                
                // $this->recordList is defined in parent class csv
                $this->recordList[$i] = $user;

                $i++;
            }

            if ( $exportUserInfo )
            {
                $userInfoList = new UserInfoList($this->course_id);

                $userInfoLabelList = $userInfoList->getUserInfoLabels();

                foreach ( $userInfoLabelList as $catId => $catTitle )
                {
                    $this->recordList[0][] = $catTitle;

                    $userCatInfo = $userInfoList->getUserInfo($catId);

                    foreach ( $userCatInfo as $userCatInfo )
                    {
                        $this->recordList[$userIdList[$userCatInfo['userId']]][] = $userCatInfo['content'];
                    }
                }
            }
        }
        
        if( is_array($this->recordList) && !empty($this->recordList) )
        {
            return true;
        }
        else
        {
        return false;
    }
}
}

function export_user_list( $course_id )
{
    $csvUserList = new csvUserList( $course_id );
    
    $csvUserList->buildRecords();
    $csvContent = $csvUserList->export();
    
    return $csvContent;
}

