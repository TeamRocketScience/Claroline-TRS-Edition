<?php // $Id: authmanager.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Authentication Manager
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.auth
 */

// Get required libraries
require_once dirname(__FILE__) . '/../core/claroline.lib.php';
require_once dirname(__FILE__) . '/../database/database.lib.php';
require_once dirname(__FILE__) . '/../kernel/user.lib.php';
require_once dirname(__FILE__) . '/authdrivers.lib.php';
require_once dirname(__FILE__) . '/ldapauthdriver.lib.php';

class AuthManager
{
    protected static $extraMessage = null;
    
    public static function getFailureMessage()
    {
        return self::$extraMessage;
    }
    
    protected static function setFailureMessage( $message )
    {
        self::$extraMessage = $message;
    }
    
    public static function authenticate( $username, $password )
    {
        if ( !empty($username) && $authSource = AuthUserTable::getAuthSource( $username ) )
        {
            Console::debug("Found authentication source {$authSource} for {$username}");
            $driverList = array( AuthDriverManager::getDriver( $authSource ) );
        }
        else
        {
            // avoid issues with session collision when many users connect from
            // the same computer at the same time with the same browser session !
            if ( AuthUserTable::userExists( $username ) ) 
            {   
                self::setFailureMessage( get_lang( "There is already an account with this username." ) );
                return false;
            }
            
            $authSource = null;
            $driverList = AuthDriverManager::getRegisteredDrivers();
        }
        
        foreach ( $driverList as $driver )
        {
            $driver->setAuthenticationParams( $username, $password );
            
            if ( $driver->authenticate() )
            {
                if ( $uid = AuthUserTable::registered( $username, $driver->getAuthSource() ) )
                {
                    if ( $driver->userUpdateAllowed() )
                    {
                        $userAttrList =  $driver->getFilteredUserData();
                        
                        // avoid session collisions !
                        if ( isset( $userAttrList['loginName'] )
                            && $username != $userAttrList['loginName'] )
                        {
                            Console::error( "EXTAUTH ERROR : try to overwrite an existing user {$username} with another one" . var_export($userAttrList, true) );
                        }
                        else
                        {
                            AuthUserTable::updateUser( $uid, $userAttrList );
                            Console::info( "EXTAUTH INFO : update user {$uid} {$username} with " . var_export($userAttrList, true) );
                        }
                    }
                    
                    return Claro_CurrentUser::getInstance( $uid, true );
                }
                elseif ( $driver->userRegistrationAllowed() )
                {
                    // duplicate code here to avoid issue with multiple requests on a busy server !
                    if ( AuthUserTable::userExists( $username ) ) 
                    {   
                        self::setFailureMessage( get_lang( "There is already an account with this username." ) );
                        return false;
                    }
                    
                    $uid = AuthUserTable::createUser( $driver->getUserData() );
                    
                    return Claro_CurrentUser::getInstance( $uid, true );
                }
            }
            elseif ( $authSource )
            {
                self::setFailureMessage( $driver->getFailureMessage() );
            }
        }
        
        // authentication failed
        return false;
    }
}

class AuthUserTable
{
    public static function userExists( $username )
    {
        $tbl = claro_sql_get_main_tbl();

        $sql = "SELECT user_id, authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($username) . "\n"
            ;

        $res = Claroline::getDatabase()->query( $sql );

        if ( $res->numRows() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public static function registered( $username, $authSourceName )
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT user_id\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($username) . "\n"
            . "AND\n"
            . "authSource = " . Claroline::getDatabase()->quote($authSourceName) . "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;
            
        $res = Claroline::getDatabase()->query( $sql );
        
        if ( $res->numRows() )
        {
            return $res->fetch(Database_ResultSet::FETCH_VALUE);
        }
        else
        {
            return false;
        }
    }
    
    public static function getAuthSource( $username )
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? "BINARY " : "" )
            . "username = ". Claroline::getDatabase()->quote($username) . "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;
            
        return  Claroline::getDatabase()->query( $sql )->fetch(Database_ResultSet::FETCH_VALUE);
    }
    
    public static function createUser( $userAttrList )
    {
        return self::registerUser( $userAttrList, null );
    }
    
    public static function updateUser( $uid, $userAttrList )
    {
        return self::registerUser( $userAttrList, $uid );
    }
    
    protected static function registerUser( $userAttrList, $uid = null )
    {
        $preparedList = array();
        
        // Map database fields
        $dbFieldToClaroMap = array(
            'nom' => 'lastname',
            'prenom' => 'firstname',
            'username' => 'loginName',
            'email' => 'email',
            'officialCode' => 'officialCode',
            'phoneNumber' => 'phoneNumber',
            'isCourseCreator' => 'isCourseCreator',
            'authSource' => 'authSource');
        
        // Do not overwrite username and authsource for an existing user !!!
        if ( ! is_null( $uid ) )
        {
            unset( $dbFieldToClaroMap['username'] );
            unset( $dbFieldToClaroMap['authSource'] );
        }

        
        foreach ( $dbFieldToClaroMap as $dbFieldName => $claroAttribName )
        {
            if ( isset($userAttrList[$claroAttribName])
                && ! is_null($userAttrList[$claroAttribName]) )
            {
                $preparedList[] = $dbFieldName
                    . ' = '
                    . Claroline::getDatabase()->quote($userAttrList[$claroAttribName])
                    ;
            }
        }
        
        if ( empty( $preparedList ) )
        {
            return false;
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        $sql = ( $uid ? 'UPDATE' : 'INSERT INTO' ) 
            . " `{$tbl['user']}`\n"
            . "SET " . implode(",\n", $preparedList ) . "\n"
            . ( $uid ? "WHERE  user_id = " . (int) $uid : '' )
            ;
        
        Claroline::getDatabase()->exec($sql);
        
        if ( ! $uid )
        {
            $uid = Claroline::getDatabase()->insertId();
        }
        
        return $uid;
    }
}

class AuthDriverManager
{
    protected static $drivers = false;
    
    public static function getRegisteredDrivers()
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        return  self::$drivers;
    }
    
    public static function getDriver( $authSource )
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        if ( array_key_exists( $authSource, self::$drivers ) )
        {
            return self::$drivers[$authSource];
        }
        else
        {
            throw new Exception("No auth driver found for {$authSource} !");
        }
    }
    
    protected static function initDriverList()
    {
        // todo : get from config
        
        // load static drivers
        self::$drivers = array(
            'claroline' => new ClarolineLocalAuthDriver(),
            'disabled' => new UserDisabledAuthDriver(),
            'temp' => new TemporaryAccountAuthDriver()
        );
        
        // load dynamic drivers
        if ( ! file_exists ( get_path('rootSys') . 'platform/conf/extauth' ) )
        {
            FromKernel::uses('fileManage.lib');
            claro_mkdir(get_path('rootSys') . 'platform/conf/extauth', CLARO_FILE_PERMISSIONS, true );
        }
        
        $it = new DirectoryIterator( get_path('rootSys') . 'platform/conf/extauth' );
        
        $driverConfig = array();
        
        foreach ( $it as $file )
        {
            if ( $file->isFile() )
            {
                include $file->getPathname();
                
                if ( $driverConfig['driver']['enabled'] == true )
                {
                    $driverClass = $driverConfig['driver']['class'];
                        
                    if ( class_exists( $driverClass ) )
                    {
                        self::$drivers[$driverConfig['driver']['authSourceName']] = new $driverClass( $driverConfig );
                    }
                    else
                    {
                        $driverPath = dirname(__FILE__). '/drivers/' . strtolower($driverClass).'.lib.php';
                        
                        if ( file_exists($driverPath) )
                        {
                            require_once $driverPath;
                            
                            if ( class_exists( $driverClass ) )
                            {
                                self::$drivers[$driverConfig['driver']['authSourceName']] = new $driverClass( $driverConfig );
                            }
                            else
                            {
                                if ( claro_debug_mode() )
                                {
                                    throw new Exception("Driver class {$driverClass} not found");
                                }
                                
                                Console::error( "Driver class {$driverClass} not found" );
                            }
                        }
                        else
                        {
                            if ( claro_debug_mode() )
                            {
                                throw new Exception("Driver class {$driverClass} not found");
                            }
                            
                            Console::error( "Driver class {$driverClass} not found" );
                        }
                    }
                }
            }
            
            $driverConfig = array();
        }
    }
}
