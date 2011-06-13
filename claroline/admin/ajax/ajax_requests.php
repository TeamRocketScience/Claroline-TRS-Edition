<?php

/**
 * CLAROLINE
 *
 * Ajax requests for administration panel
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */

// Load Claroline kernel
require_once dirname(__FILE__) . '/../../inc/claro_init_global.inc.php';
require_once dirname(__FILE__) . '/../../inc/lib/courselist.lib.php';

if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;

if ($action == 'getUserCourseList')
{
    $userId = isset($_REQUEST['userId'])?((int) $_REQUEST['userId']):(null);
    $courseList = array();
    
    if (!is_null($userId))
    {
        $courseList = get_user_course_list($userId);
        
        //We only need courses codes
        if (!empty($courseList))
        {
            $coursesCodeList = array();
            foreach($courseList as $course)
            {
                $coursesCodeList[] = $course['officialCode'];
            }
        }
        else
            $coursesCodeList[] = get_lang("No course");
    }
    else
        $coursesCodeList[] = get_lang("No user id");
    
    echo implode(', ', $coursesCodeList);
}
elseif ($action == 'getUserCategoryList')
{
    // Get table name
    $tbl_mdb_names              = claro_sql_get_main_tbl();
    $tbl_course                 = $tbl_mdb_names['course'];
    $tbl_rel_course_user        = $tbl_mdb_names['rel_course_user'];
    $tbl_category               = $tbl_mdb_names['category'];
    $tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];
    
    $userId = isset($_REQUEST['userId'])?((int) $_REQUEST['userId']):(null);
    $categoryList = array();
    
    if (!is_null($userId))
    {
        $sql = "SELECT ca.name FROM `{$tbl_category}` AS ca
                
                LEFT JOIN `{$tbl_rel_course_category}` AS rcc
                ON ca.id = rcc.categoryId
                
                LEFT JOIN `{$tbl_course}` AS co
                ON rcc.courseId = co.cours_id
                
                LEFT JOIN `{$tbl_rel_course_user}` AS rcu
                ON rcu.code_cours = co.code
                
                WHERE rcc.rootCourse = 1
                AND rcu.user_id = {$userId}
                
                GROUP BY ca.id";
        
        $result = Claroline::getDatabase()->query($sql);
        $result->setFetchMode(Database_ResultSet::FETCH_VALUE);
        
        if ($result->numRows() > 0)
        {
            foreach ($result as $res)
            {
                $categoryList[] = $res;
            }
        }
        else
            $categoryList[] = get_lang('No category for this user');
    }
    else
        $categoryList[] = get_lang("No user id");
    
    echo implode(', ', $categoryList);
}