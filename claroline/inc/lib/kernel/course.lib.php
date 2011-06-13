<?php // $Id: course.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Claroline Course objects
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.objects
 */

require_once dirname(__FILE__) . '/object.lib.php';
require_once dirname(__FILE__) . '/../core/claroline.lib.php';
require_once dirname(__FILE__) . '/../database/database.lib.php';

/**
 * Represents a course in the platform
 */
class Claro_Course extends KernelObject
{
    protected $_courseId;

    /**
     * Constructor
     * @todo use course id (int) instead of course code to identify a course.
     * @param string $courseId course code
     */
    public function __construct( $courseId )
    {
        $this->_courseId = $courseId;
    }

    /**
     * Load course properties and group properties from database
     */
    protected function loadFromDatabase()
    {
        $this->_rawData = array();
        $this->loadCourseKernelData();
        $this->loadCourseCategories();
        $this->loadCourseProperties();
        $this->loadGroupProperties();
    }

    /**
     * Load course main properties from database
     */
    protected function loadCourseKernelData()
    {
        // get course data from main
        $tbl =  claro_sql_get_tbl(array('cours','faculte',));
        
        $sqlCourseId = Claroline::getDatabase()->quote($this->_courseId);

        $sql_getCourseData = "
            SELECT
                c.code                  AS courseId,
                c.code                  AS sysCode,
                c.cours_id              AS courseDbId,
                c.intitule              AS name,
                c.administrativeNumber  AS officialCode,
                c.directory             AS path,
                c.dbName                AS dbName,
                c.titulaires            AS titular,
                c.email                 AS email,
                c.language              AS language,
                c.extLinkUrl            AS extLinkUrl,
                c.extLinkName           AS extLinkName,
                c.visibility            AS visibility,
                c.access                AS access,
                c.registration          AS registration,
                c.registrationKey       AS registrationKey,
                c.diskQuota             AS diskQuota
            FROM
                `{$tbl['cours']}`   AS c
            WHERE
                c.code = {$sqlCourseId};
        ";

        $courseDataList = Claroline::getDatabase()
            ->query( $sql_getCourseData )
            ->fetch();
        
        if ( ! $courseDataList )
        {
            throw new Exception("Cannot load course data for {$this->_courseId}");
        }
        
        // set bool values
        $courseDataList['access'] = $courseDataList['access'];
        $courseDataList['visibility'] = ('visible' == $courseDataList['visibility'] );
        $courseDataList['registrationAllowed'] = ('open' == $courseDataList['registration'] );
        
        // set dbNameGlu
        $courseDataList['dbNameGlu'] =
            get_conf('courseTablePrefix')
            . $courseDataList['dbName']
            . get_conf('dbGlu')
            ;
            
        $this->_rawData = $courseDataList;
    }

    /**
     * Load course categories
     */
    protected function loadCourseCategories()
    {
        $tbl = claro_sql_get_course_tbl( $this->_rawData['dbNameGlu'] );

        $categoriesDataList = Claroline::getDatabase()->query("
            SELECT
                cat.id      AS categoryId,
                cat.name    AS categoryName,
                cat.code    AS categoryCode,
                cat.visible AS visibility,
                cat.rank    AS categoryRank
            FROM
                `{$tbl['category']}` AS cat
            LEFT JOIN
                `{$tbl['rel_course_category']}` AS rcc
            ON
                cat.id = rcc.categoryId
            WHERE
                rcc.courseId = {$this->_rawData['id']};
        ");
                
        $this->_rawData['categories'] = array();

        foreach ( $categoriesDataList as $category )
        {
            $category['visibility'] = ($category['visibility'] == 1);
            $this->_rawData['categories'][] = $category;
        }
    }

    /**
     * Load course additionnal properties from database
     */
    protected function loadCourseProperties()
    {
        // get extra course properties
        $tbl = claro_sql_get_course_tbl( $this->_rawData['dbNameGlu'] );

        $courseProperties = Claroline::getDatabase()
            ->query("
                SELECT
                    name,
                    value
                FROM
                    `{$tbl['course_properties']}`
                WHERE
                    category = 'MAIN';
            ")
            ->fetch();
        
        $coursePropertyList = array();

        if ( is_array( $courseProperties ) )
        {
            foreach ( $courseProperties as $currentProperty )
            {
                $coursePropertyList[$currentProperty['name']] = $currentProperty['value'];
            }
        }
        
        $this->_rawData['courseProperties'] = $coursePropertyList;
    }

    /**
     * Load course group properties from database
     */
    protected function loadGroupProperties()
    {
        $tbl = claro_sql_get_course_tbl( $this->_rawData['dbNameGlu'] );

        $db_groupProperties = Claroline::getDatabase()
            ->query("
                SELECT
                    name,
                    value
                FROM
                    `{$tbl['course_properties']}`
                WHERE
                    category = 'GROUP';
            ")
            ->fetch();
        
        if ( ! $db_groupProperties )
        {
            throw new Exception("Cannot load group properties for {$courseId}");
        }
        
        $groupProperties = array();
        
        foreach($db_groupProperties as $currentProperty)
        {
            $groupProperties[$currentProperty['name']] = (int) $currentProperty['value'];
        }
        
        $groupProperties ['registrationAllowed'] =  ($groupProperties['self_registration'] == 1);
        unset ( $groupProperties['self_registration'] );

        $groupProperties ['private'] =  ($groupProperties['private'] == 1);

        $groupProperties['tools'] = array();
        
        $groupToolList = get_group_tool_label_list();
        
        foreach ( $groupToolList as $thisGroupTool )
        {
            $groupTLabel = $thisGroupTool['label'];
            
            if ( array_key_exists( $groupTLabel, $groupProperties ) )
            {
                $groupProperties ['tools'] [$groupTLabel] = ($groupProperties[$groupTLabel] == 1);
                
                unset ( $groupProperties[$groupTLabel] );
            }
            else
            {
                $groupProperties ['tools'] [$groupTLabel] = false;
            }
        }
        
        $this->_rawData['groupProperties'] = $groupProperties;
    }

    /**
     * Get group properties in the course
     * @return array
     */
    public function getGroupProperties()
    {
        return $this->_rawData['groupProperties'];
    }

    /**
     * Get course additional properties
     * @return array
     */
    public function getCourseProperties()
    {
        return $this->_rawData['courseProperties'];
    }

    /**
     * Overwrite KernelObjet::__get to get properties from both main properties
     * and additionnal properties.
     * @param string $nm property name
     * @return mixed property value or null
     */
    public function __get( $nm )
    {
        if ( isset ( $this->_rawData[$nm] ) )
        {
            return $this->_rawData[$nm];
        }
        elseif ( isset ( $this->_rawData['courseProperties'][$nm] ) )
        {
            return $this->_rawData['courseProperties'][$nm];
        }
        else
        {
            return null;
        }
    }
}

/**
 * Represents the current course object. This class is a singleton.
 */
class Claro_CurrentCourse extends Claro_Course
{
    public function __construct( $courseId = null )
    {
        $courseId = empty( $courseId )
            ? claro_get_current_course_id()
            : $courseId
            ;
            
        parent::__construct( $courseId );
    }

    /**
     * Load the course from the session
     */
    public function loadFromSession()
    {
        if ( !empty($_SESSION['_course']) )
        {
            $this->_rawData = $_SESSION['_course'];
            pushClaroMessage( "Course {$this->_courseId} loaded from session", 'debug' );
        }
        else
        {
            throw new Exception("Cannot load course data from session for {$this->_courseId}");
        }
    }

    /**
     * Save the course to the session
     */
    public function saveToSession()
    {
        $_SESSION['_course'] = $this->_rawData;
    }
    
    protected static $instance = false;

    /**
     * Singleton constructor
     * @param int $courseId course code
     * @param boolean $forceReload force relaoding the course
     * @return Claro_CurrentCourse
     */
    public static function getInstance( $courseId = null, $forceReload = false )
    {
        if ( $forceReload || ! self::$instance )
        {
            self::$instance = new self( $courseId );
            
            if ( !$forceReload && claro_is_in_a_course() )
            {
                self::$instance->loadFromSession();
            }
            else
            {
                self::$instance->loadFromDatabase();
            }
        }
        
        return self::$instance;
    }
}
