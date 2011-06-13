<?php // $Id: module.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * Claroline extension modules library
 *
 * This lib make the interface with kernel task and module extention for theses
 * task. It also provide some backward compatibility functions.
 *
 * @version     $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 *              version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.8
 */

defined('CLARO_CONTEXT_PLATFORM')     || define('CLARO_CONTEXT_PLATFORM','platform');
defined('CLARO_CONTEXT_COURSE')       || define('CLARO_CONTEXT_COURSE','course');
defined('CLARO_CONTEXT_GROUP')        || define('CLARO_CONTEXT_GROUP','group');
defined('CLARO_CONTEXT_USER')         || define('CLARO_CONTEXT_USER','user');
defined('CLARO_CONTEXT_TOOLINSTANCE') || define('CLARO_CONTEXT_TOOLINSTANCE','toolInstance');
defined('CLARO_CONTEXT_TOOLLABEL')    || define('CLARO_CONTEXT_TOOLLABEL','toolLabel');

/**
 * This function return the core repository of a package (zipped module).
 *
 * @return string
 * @since 1.9
 */
function get_package_path()
{
    return get_path('rootSys') . 'packages/';
}

/**
 * This function return the core repository of a module.
 *
 * @param string $toolLabel
 * @return string
 */
function get_module_path($toolLabel)
{

    $toolLabel = rtrim($toolLabel,'_'); // keep this line until  all claro_label
    switch ($toolLabel)
    {
        case 'CLANN' : return get_path('clarolineRepositorySys') . 'announcements';
        case 'CLCAL' : return get_path('clarolineRepositorySys') . 'calendar';
        case 'CLFRM' : return get_path('clarolineRepositorySys') . 'phpbb';
        case 'CLCHT' : return get_path('clarolineRepositorySys') . 'chat';
        case 'CLDOC' : return get_path('clarolineRepositorySys') . 'document';
        case 'CLDSC' : return get_path('clarolineRepositorySys') . 'course_description';
        case 'CLUSR' : return get_path('clarolineRepositorySys') . 'user';
        case 'CLLNP' : return get_path('clarolineRepositorySys') . 'learnPath';
        case 'CLQWZ' : return get_path('clarolineRepositorySys') . 'exercise';
        case 'CLWRK' : return get_path('clarolineRepositorySys') . 'work';
        case 'CLWIKI' : return get_path('clarolineRepositorySys') . 'wiki';
        case 'CLLNK' : return get_path('clarolineRepositorySys') . 'linker';
        case 'CLGRP' : return get_path('clarolineRepositorySys') . 'group';
        case 'CLSTAT' : return get_path('clarolineRepositorySys') . 'tracking';
        case 'CLTI' : return get_path('clarolineRepositorySys') . 'tool_intro';
        default: return get_path('rootSys') . 'module/' . rtrim($toolLabel,'_');
    }
    return '';
}

/**
 * This function return the core repository of a module.
 *
 * @param string $toolLabel
 * @return string
 */
function get_module_url($toolLabel)
{
    $toolLabel = rtrim($toolLabel,'_');
    switch ($toolLabel)
    {
        case 'CLANN' : return get_path('clarolineRepositoryWeb') . 'announcements';
        case 'CLCAL' : return get_path('clarolineRepositoryWeb') . 'calendar';
        case 'CLFRM' : return get_path('clarolineRepositoryWeb') . 'phpbb';
        case 'CLCHT' : return get_path('clarolineRepositoryWeb') . 'chat';
        case 'CLDOC' : return get_path('clarolineRepositoryWeb') . 'document';
        case 'CLDSC' : return get_path('clarolineRepositoryWeb') . 'course_description';
        case 'CLUSR' : return get_path('clarolineRepositoryWeb') . 'user';
        case 'CLLNP' : return get_path('clarolineRepositoryWeb') . 'learnPath';
        case 'CLQWZ' : return get_path('clarolineRepositoryWeb') . 'exercise';
        case 'CLWRK' : return get_path('clarolineRepositoryWeb') . 'work';
        case 'CLLNK' : return get_path('clarolineRepositoryWeb') . 'linker';
        case 'CLWIKI' : return get_path('clarolineRepositoryWeb') . 'wiki';
        case 'CLGRP' : return get_path('clarolineRepositoryWeb') . 'group';
        case 'CLTI' : return get_path('clarolineRepositoryWeb') . 'tool_intro';
        default: return get_conf('urlAppend') . '/module/' . $toolLabel;
    }
    return '';

}

/**
 * Return the list of context that the tool can use but not manage.
 *
 * @param string $toolId
 * @return array
 */
function get_module_db_dependance($toolId)
{
    // actual place of this info prom module

    $dbconfFile = get_module_path($toolId) . '/connector/db.conf.php';
    if (file_exists($dbconfFile))
    {
        $contextDbSupport =false;
        include($dbconfFile);
        return $contextDbSupport;
    }
    else
    switch ($toolId)
    {
        // read in manifest

        //case 'CLUNFO' : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLANN'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLWIKI' : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLQWZ'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLDOC'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLCAL'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);

        //case 'CLBLOG' : return array (CLARO_CONTEXT_USER,CLARO_CONTEXT_COURSE);
        case 'CLLNK' :  return array(CLARO_CONTEXT_COURSE);
        case 'CLDSC'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLFRM'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLLNP'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLUSR'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLWRK'  : return array(CLARO_CONTEXT_COURSE);

        default :       return array();
    }
}

/**
 * Return the syspath where a tool can store these file for a given context
 *
 * @param mixed $contextData
 * @return string
 */
function claro_get_data_path( $contextData=array() )
{
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_TOOLLABEL,$contextData))
    {
        $contextData[CLARO_CONTEXT_TOOLLABEL] = rtrim($GLOBALS['_courseTool']['label'],'_');
    }
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_COURSE,$contextData))
    {
        $contextData[CLARO_CONTEXT_COURSE] = claro_get_current_course_id();
    }
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_GROUP,$contextData))
    {
        $contextData[CLARO_CONTEXT_GROUP] = claro_get_current_group_id();
    }
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_USER,$contextData))
    {
        $contextData[CLARO_CONTEXT_USER] = claro_get_current_user_id();
    }

    if (isset($contextData[CLARO_CONTEXT_COURSE]))
    {
        if (isset($contextData[CLARO_CONTEXT_GROUP]))
        {
            $path = claro_get_group_data($contextData[CLARO_CONTEXT_GROUP]
                ,$contextData[CLARO_CONTEXT_COURSE]);
        }
        else
        {
            $path = get_conf('coursesRepositorySys')
                . claro_get_course_path($contextData[CLARO_CONTEXT_COURSE])
                ;
        }
    }

    if (isset($contextData[CLARO_CONTEXT_TOOLLABEL]))
    {
        switch ($contextData[CLARO_CONTEXT_TOOLLABEL])
        {
            case 'CLDOC' : $path = $path . '/document/';        break;
            case 'CLCHT' : $path = $path . '/chat/';            break;
            case 'CLWRK' : $path = $path . '/work/';            break;
            case 'CLQWZ' : $path = $path . '/exercise/';        break;
            case 'CLLNP' : $path = $path . '/scormPackages/';    break;
            default : $path = $path . $contextData[CLARO_CONTEXT_TOOLLABEL] . '/';

        }
    }

    return $path;

}

/**
 * Get module entry filename
 * @param string $claroLabel module label
 * @return string
 */
function get_module_entry( $claroLabel )
{
    return get_module_data($claroLabel, 'entry' );

}

/**
 * Get the complete path to the entry of an module.
 *
 * @param string $claroLabel module label
 * @return string
 */
function get_module_entry_url( $claroLabel )
{
    return get_module_url($claroLabel) . '/'
        . ltrim(get_module_entry($claroLabel),'/')
        ;
}

/**
 * Get information about a module
 * @param string $claroLabel module label
 * @param string $dataName
 * @param boolean $ignoreCache
 * @return mixed
 */
function get_module_data( $claroLabel, $dataName = null, $ignoreCache = false )
{
    static $cachedModuleDataList = null;

    if ( is_null ($cachedModuleDataList) )
    {
        $cachedModuleDataList = array();
    }

    if ($ignoreCache || ! array_key_exists($claroLabel,$cachedModuleDataList))
    {
        $tbl = claro_sql_get_tbl(array('module', 'course_tool'));
        $sql = "SELECT M.`label`      AS label,
                   M.`id`             AS id,
                   M.`name`           AS moduleName,
                   M.`activation`     AS activation,
                   M.`type`           AS type,
                   M.`script_url`     AS entry,
                   CT.`icon`          AS icon,
                   CT.`def_rank`      AS rank,
                   CT.`add_in_course` AS add_in_course,
                   CT.`access_manager` AS access_manager

        FROM `" . $tbl['module'] . "` AS M
        LEFT JOIN `" . $tbl['course_tool'] . "` AS CT
            ON CT.`claro_label`= M.label
        WHERE  M.`label` = '" . claro_sql_escape($claroLabel) . "'";

        $cachedModuleDataList[$claroLabel] = claro_sql_query_get_single_row($sql);
    }
    
    if ( !is_null( $dataName ) )
    {
        return $cachedModuleDataList[$claroLabel][$dataName];
    }
    else
    {
        return $cachedModuleDataList[$claroLabel];
    }
}

/**
 * Check if a module is installed and actived.
 *
 * @param string $modLabel module label
 * @return array
 */
function check_module($modLabel)
{
    $tbl_name        = claro_sql_get_main_tbl();
    $tbl_module      = $tbl_name['module'];

    $sql = "SELECT M.`id`              AS `id`,
                   M.`label`           AS `label`,
                   M.`activation`      AS `activation`
            FROM `" . $tbl_module . "` AS M
            WHERE M.`label` = '".$modLabel."'";

    $result = claro_sql_query_get_single_row($sql);

    if (empty($result))
    {
        $message[] = "The ".$modLabel." hasn't been installed!";
        return array(false,$message);
    }
    else
    {
        if ($result['activation'] == 'desactivated')
        {
            $message[] = "The ".$modLabel." hasn't been activated!";
            return array(false,$message);
        }
        else
            return array(true,null);
    }
}

/**
 * Load language file for a module
 * @deprecated since Claroline 1.9, use language::load_module_translation
 * @param   $moduleLabel module label (default null for current module)
 * @param   $language language name (default null for current language)
 * @deprecated since 1.9
 */
function load_module_language ( $moduleLabel = null, $language = null )
{
    language::load_module_translation( $moduleLabel, $language );
}

/**
 * Merge module lang with lang file
 * @deprecated since Claroline 1.9, use language::load_module_translation
 * @param   $moduleLabel module label (default null for current module)
 * @param   $language language name (default null for current language)
 * @deprecated since 1.9
 */
function add_module_lang_array( $moduleLabel = null, $language = null )
{
    language::load_module_translation( $moduleLabel, $language );
}

/**
 * Get the list of all modules on the platform
 * @param   bool $activeModulesOnly selects only active module (default true)
 * @return  array module label list
 * @throws  COULD_NOT_GET_MODULE_LABEL_LIST
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function get_module_label_list( $activeModulesOnly = true )
{
    $tbl_name_list = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name_list['module'];

    $activationSQL = $activeModulesOnly
        ? "WHERE `activation` = 'activated'"
        : ''
        ;

    $sql = "SELECT `label`, `id` \n"
        . "FROM `" . $tbl_module . "`\n"
        . $activationSQL
        ;

    if ( ! ( $result = claro_sql_query_fetch_all( $sql ) ) )
    {
        return claro_failure::set_failure('COULD_NOT_GET_MODULE_LABEL_LIST');
    }
    else
    {
        $moduleLabelList = array();

        foreach( $result as $module )
        {
            $moduleLabelList[$module['id']] = $module['label'];
        }

        return $moduleLabelList;
    }
}

/*
 * Module (un)installation functions
 */

require_once dirname(__FILE__) . '/sqlxtra.lib.php';
require_once dirname(__FILE__) . '/backlog.class.php';

/**
 * Install database for the given module in the given course
 * @param   string moduleLabel
 * @param   string courseId
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function install_module_in_course( $moduleLabel, $courseId )
{
    install_module_database_in_course( $moduleLabel, $courseId );

    install_module_script_in_course( $moduleLabel, $courseId );
}

/**
 * Create and initialize the module database in the given course
 * @param string $moduleLabel
 * @param string $courseId
 * @todo what to return if the script file does not exists ?!?
 * @return boolean
 */
function install_module_database_in_course( $moduleLabel, $courseId )
{
    $sqlPath = get_module_path( $moduleLabel ) . '/setup/course_install.sql';

    if ( file_exists( $sqlPath ) )
    {
        if ( ! execute_sql_file_in_course( $sqlPath, $courseId ) )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

/**
 * Execute module initialization script in the given course
 * @param string $moduleLabel
 * @param string $courseId
 */
function install_module_script_in_course( $moduleLabel, $courseId )
{
    $phpPath = get_module_path( $moduleLabel ) . '/setup/course_install.php';

    if ( file_exists( $phpPath ) )
    {
        $courseDirectory = claro_get_current_course_data( 'path' );
        $moduleCourseTblList = $courseTbl = claro_sql_get_course_tbl();
        
        // include the language file with all language variables
        language::load_translation( );
        language::load_locale_settings( );
        language::load_module_translation( $moduleLabel );

        require_once $phpPath;
    }
}

/**
 * Remove database for all modules in the given course
 * @param   string courseId
 * @return  array(
 *  boolean success
 *  Backlog log )
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function delete_all_modules_from_course( $courseId )
{
    $backlog = new Backlog;
    $success = true;

    if ( ! $moduleLabelList = get_module_label_list(false) )
    {
        $success = false;
        $backlog->failure( claro_failure::get_last_failure() );
    }
    else
    {
        foreach ( $moduleLabelList as $moduleLabel )
        {
            if ( ! delete_module_in_course( $moduleLabel, $courseId ) )
            {
                $backlog->failure( get_lang('delete failed for module %module'
                    , array( '%module' => $moduleLabel ) ) );

                $success = false;
            }
            else
            {
                $backlog->success( get_lang('delete succeeded for module %module'
                    , array( '%module' => $moduleLabel ) ) );
            }
        }
    }

    return array( $success, $backlog );
}

/**
 * Remove database for the given module in the given course
 * @param   string moduleLabel
 * @param   string courseId
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function delete_module_in_course( $moduleLabel, $courseId )
{
    $sqlPath = get_module_path( $moduleLabel ) . '/setup/course_uninstall.sql';
    $phpPath = get_module_path( $moduleLabel ) . '/setup/course_uninstall.php';

    if ( file_exists( $phpPath ) )
    {
        require_once $phpPath;
    }

    if ( file_exists( $sqlPath ) )
    {
        if ( ! execute_sql_file_in_course( $sqlPath, $courseId ) )
        {
            return false;
        }
    }

    return true;
}

/**
 * Execute course related SQL files by replacing __CL__COURSE__ place holder
 * with given course code, then executing the file
 * @param   string file path to the sql file
 * @param   string courseId course sys code
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 * @throws  SQL_FILE_NOT_FOUND, SQL_QUERY_FAILED
 */
function execute_sql_file_in_course( $file, $courseId )
{
    if ( file_exists( $file ) )
    {
        $sql = file_get_contents( $file );

        if ( !empty( $courseId ) )
        {
            $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseId );
            $sql = str_replace('__CL_COURSE__', $currentCourseDbNameGlu, $sql );
        }

        if ( ! claro_sql_multi_query($sql) )
        {
            return claro_failure::set_failure( 'SQL_QUERY_FAILED' );
        }
        else
        {
            return true;
        }
    }
    else
    {
        return claro_failure::set_failure( 'SQL_FILE_NOT_FOUND' );
    }
}

// ---- Database table list helpers

/**
 * Get list of module table names 'localized' for the given course
 * @param array $arrTblName of tableName
 * @param string $courseCode course code
 * @return array $tableName => $dbNameGlue . $tableName
 * @throws Exception if no course code given and not in a course or
 *  course not valid
 */
function get_module_course_tbl( $arrTblName, $courseCode = null )
{
    if ( empty ( $courseCode ) )
    {
        if ( ! claro_is_in_a_course() )
        {
            throw new Exception('Not in a course !');
        }
        else
        {
            $courseCode = claro_get_current_course_id();
        }
    }
    
    $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseCode );

    if ( ! $currentCourseDbNameGlu )
    {
        throw new Exception('Invalid course !');
    }

    $arrToReturn = array();

    foreach ( $arrTblName as $name )
    {
        $arrToReturn[$name] = $currentCourseDbNameGlu . $name;
    }

    return $arrToReturn;
}

/**
 * Get list of module table names 'localized' for the main db
 * @param array $arrTblName of tableName
 * @return array $tableName => mainTblPrefix . $tableName
 */
function get_module_main_tbl( $arrTblName )
{
    $mainDbNameGlu = get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix');
    $arrToReturn = array();

    foreach ( $arrTblName as $name )
    {
        $arrToReturn[$name] = $mainDbNameGlu . $name;
    }

    return $arrToReturn;
}

/**
 * Load configuration file for a module
 * @param   $module module label (default null for current module)
 */
function load_module_config ( $moduleLabel = null )
{
    if ( !$moduleLabel )
    {
        $moduleLabel = get_current_module_label();
    }
    
    // load main config file
    $mainConfigFile = claro_get_conf_repository() . $moduleLabel . '.conf.php';
    
    if ( file_exists( $mainConfigFile ) )
    {
        include $mainConfigFile;
    }
    
    // check if config overwritten in course and load config file
    if ( claro_is_in_a_course() )
    {
        $courseConfigFile = get_conf('coursesRepositorySys')
            . claro_get_current_course_data('path')
            . '/conf/' . $moduleLabel . '.conf.php'
            ;
        
        if ( file_exists($courseConfigFile))
        {
            include $courseConfigFile;
        }
    }
}

/**
 * Get the list of tools in a course
 * @param string $courseIdReq course code
 * @param boolean $platformActive get only modules activated for the platform
 * @param boolean $courseActive get only modules activated in the current course
 * @param string $context context of the module
 * @return array or false
 */
function module_get_course_tool_list( $courseIdReq,
                                    $platformActive = true,
                                    $courseActive = true,
                                    $context = null )
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];
    $tbl_module           = $tbl_mdb_names['module'];
    $tbl_module_contexts  = $tbl_mdb_names['module_contexts'];
    $tbl_cdb_names        = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseIdReq) );
    $tbl_course_tool_list = $tbl_cdb_names['tool'];
    
    if ( ! is_null ($context) )
    {
        $contextJoin = "LEFT JOIN `{$tbl_module_contexts}` AS mc\n"
            . " ON mc.module_id = `m`.id\n"
            ;
        
        $contextCondition = " AND `mc`.`context` = '"
            .claro_sql_escape($context)."'"
            ;
    }
    else
    {
        $contextJoin = "";
        $contextCondition = "";
    }
    
    /*
    * Search all the tool corresponding to this access levels
    */
    
    if ( is_null( $courseActive ) )
    {
        $sql_courseActive = '';
    }
    else
    {
        $sql_courseActive = $courseActive
            ? " AND ctl.activated = 'true' "
            : " AND ctl.activated = 'false' "
            ;
    }
    
    if ( is_null( $platformActive ) )
    {
        $sql_platformActive = '';
    }
    else
    {
        $sql_platformActive = $platformActive
            ? " AND m.activation = 'activated' "
            : " AND m.activation = 'deactivated' "
            ;
    }

    // find module or claroline existing tools
    
    $sql = "SELECT DISTINCT ctl.id            AS id,
                  pct.id                      AS tool_id,
                  pct.claro_label             AS label,
                  ctl.script_name             AS external_name,
                  ctl.visibility              AS visibility,
                  IFNULL(pct.icon,'tool.png') AS icon,
                  ISNULL(ctl.tool_id)         AS external,
                  m.activation ,
                  m.name                      AS name,
                  pct.access_manager            AS access_manager,
                  ctl.activated,
                  IFNULL( ctl.script_url ,
                          pct.script_url )    AS url
            FROM `{$tbl_tool_list}` AS pct
            LEFT JOIN `{$tbl_course_tool_list}` AS ctl
            ON ctl.tool_id = pct.id
            LEFT JOIN `{$tbl_module}` AS m
            ON m.label = pct.claro_label
            {$contextJoin}
            WHERE 1
            {$sql_platformActive}
            {$sql_courseActive}
            {$contextCondition}
            ORDER BY external, pct.def_rank, ctl.rank
        
    ";

    return claro_sql_query_fetch_all($sql);
}

/**
 * Get the list of labels for the modules available in groups
 * @param boolean $activatedOnly get only activated modules
 * @return array or false
 */
function get_group_tool_label_list( $activatedOnly = true )
{
    $tbl = claro_sql_get_main_tbl();
    
    $sql = "SELECT `m`.`label` AS `label`\n"
        . " FROM `{$tbl['module']}` AS `m`\n"
        . "LEFT JOIN `{$tbl['module_contexts']}` AS `mc`\n"
        . " ON `mc`.`module_id` = `m`.`id`\n"
        . "WHERE `mc`.`context` = 'group' "
        . " AND `m`.`type` = 'tool' "
        . ( $activatedOnly ? " AND `m`.`activation` = 'activated' " : '' )
        ;
    
    return claro_sql_query_fetch_all_rows($sql);
}

/**
 * Get the list of modules activated for the groups in the given course
 * @param string $courseId course code
 * @return array or false
 */
function get_activated_group_tool_label_list( $courseId )
{
    return module_get_course_tool_list( $courseId,
                                    true,
                                    true,
                                    'group' );
}

/**
 * Is the given tool activated in the given course ?
 * @param int $toolId tool id
 * @param string $courseIdReq course code
 * @return boolean
 */
function is_tool_activated_in_course( $toolId, $courseIdReq )
{
    $tbl_cdb_names        = claro_sql_get_course_tbl(
        claro_get_course_db_name_glued( $courseIdReq ) );
    $tbl_course_tool_list = $tbl_cdb_names['tool'];
    
    $sql = "SELECT count(*) \n"
        . "FROM `{$tbl_course_tool_list}`\n"
        . "WHERE tool_id = " . (int) $toolId ."\n"
        . "AND `activated` = 'true'"
        ;
        
    return ( false != claro_sql_query_fetch_single_value($sql) );
}

/**
 * Is the given module installed in the given course ?
 * @param string $toolLabel module label
 * @param string $courseId course code
 * @return boolean
 */
function is_module_installed_in_course( $toolLabel, $courseId )
{
    $toolId = get_tool_id_from_module_label( $toolLabel );
    
    $tbl_cdb_names        = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseId) );
    $tbl_course_tool_list = $tbl_cdb_names['tool'];
    
    $sql = "SELECT count(*) \n"
        . "FROM `{$tbl_course_tool_list}`\n"
        . "WHERE tool_id = " . (int) $toolId ."\n"
        . "AND `installed` = 'true'"
        ;
        
    return ( false != claro_sql_query_fetch_single_value($sql) );
}

/**
 * Is the given module activated in the groups of the given course ?
 * @param string $courseId course code
 * @param string $toolLabel module label
 * @return boolean
 */
function is_tool_activated_in_groups( $courseId, $toolLabel )
{
    $activatedGroupToolList = get_activated_group_tool_label_list( $courseId );
    
    foreach ( $activatedGroupToolList as $groupTool )
    {
        if ( $groupTool['label'] == $toolLabel )
        {
            return true;
        }
    }
    
    return false;
}

/**
 * Get the tool id corresponding to the given module label
 * @param string $moduleLabel
 * @return int
 */
function get_tool_id_from_module_label( $moduleLabel )
{
    $tbl = claro_sql_get_main_tbl();
    
    $sql = "SELECT id
              FROM `" . $tbl['tool']."`
             WHERE claro_label = '".claro_sql_escape($moduleLabel)."'";
             
    return claro_sql_query_fetch_single_value($sql);
}

/**
 * Get the module label corresponding to the given tool id
 * @param int $toolId
 * @return string
 */
function get_module_label_from_tool_id( $toolId )
{
    $tbl = claro_sql_get_main_tbl();
    
    $sql = "SELECT claro_label
              FROM `" . $tbl['tool']."`
             WHERE id = ".(int)$toolId;
             
    return claro_sql_query_fetch_single_value($sql);
}

/**
 * Activate a module in all the groups of the given course
 * @param Database_Connection $database, use Claroline::getDatabase()
 * @param string $moduleLabel
 * @param string $courseId
 * @return boolean
 */
function activate_module_in_groups( $database, $moduleLabel, $courseId )
{
    return change_module_activation_in_groups ( $database, $moduleLabel, $courseId, true );
}

/**
 * Deactivate a module in all the groups of the given course
 * @param Database_Connection $database, use Claroline::getDatabase()
 * @param string $moduleLabel
 * @param string $courseId
 * @return boolean
 */
function deactivate_module_in_groups( $database, $moduleLabel, $courseId )
{
    return change_module_activation_in_groups ( $database, $moduleLabel, $courseId, false );
}

/**
 * Change a module activation in all the groups of the given course
 * @param Database_Connection $database, use Claroline::getDatabase()
 * @param string $moduleLabel
 * @param string $courseId
 * @param boolean $activated
 * @return boolean
 */
function change_module_activation_in_groups ( $database, $moduleLabel, $courseId, $activated )
{
    $tbl = get_module_course_tbl(array('course_properties'), $courseId);

    $activation = $activated ? 1 : 0;

    if ( $database->query("
        SELECT
            `value`
        FROM
            `{$tbl['course_properties']}`
        WHERE
            `name` = " . $database->quote( $moduleLabel ) . "
        AND
            `category` = 'GROUP'" )->numRows() )
    {
        // update
        return $database->exec( "
            UPDATE
                `{$tbl['course_properties']}`
            SET
                `value` = {$activation}
            WHERE
                `name` = " . $database->quote( $moduleLabel ) . "
            AND
                `category` = 'GROUP'
         " );
    }
    else
    {
        // insert
        return $database->exec( "
            INSERT INTO
                `{$tbl['course_properties']}`
            SET
                `value` = {$activation},
                `name` = " . $database->quote( $moduleLabel ) . ",
                `category` = 'GROUP'
         " );
    }
}
