<?php // $Id: authdrivers.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Authentication Drivers
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.auth
 */

interface AuthDriver
{
    public function setAuthenticationParams( $username, $password );
    public function authenticate();
    
    public function getUserData();
    public function getFilteredUserData();
    public function getAuthSource();
    
    public function userRegistrationAllowed();
    public function userUpdateAllowed();
    
    public function getFailureMessage();
}

abstract class AbstractAuthDriver implements AuthDriver
{
    protected $userId = null;
    protected $extAuthIgnoreUpdateList = array();
    protected $username = null, $password = null;
    protected $extraMessage = null;
    
    // abstract public function getUserData();
    
    protected function setFailureMessage( $message )
    {
        $this->extraMessage = $message;
    }
    
    public function getFailureMessage()
    {
        return $this->extraMessage;
    }
    
    public function setAuthenticationParams( $username, $password )
    {
        $this->username = $username;
        $this->password = $password;
    }
    
    public function getFilteredUserData()
    {
        $data  = $this->getUserData();
        
        if ( ! is_array($data) )
        {
            return array();
        }
        
        foreach ( $data as $key => $value )
        {
            if ( in_array( $key, $this->extAuthIgnoreUpdateList ) )
            {
                unset( $data[$key] );
            }
        }
        
        return $data;
    }
    
    public function userRegistrationAllowed()
    {
        return false;
    }
    
    public function userUpdateAllowed()
    {
        return false;
    }
}

class UserDisabledAuthDriver extends AbstractAuthDriver
{
    public function getFailureMessage()
    {
        // we use get_lang here to force the language file builder to add this
        // variable, but since this code is executed before the language files are loaded
        // we have to call get_lang a second time when the message is displayed...
        return get_lang('This account has been disabled, please contact the platform administrator');
    }
    
    public function getAuthSource()
    {
        return 'disabled';
    }
    
    public function authenticate()
    {
        return false;
    }
    
    public function getUserData()
    {
        return null;
    }
    
    public function getFilteredUserData()
    {
        return array();
    }
}

class ClarolineLocalAuthDriver extends AbstractAuthDriver
{
    public function getAuthSource()
    {
        return 'claroline';
    }
    
    public function setAuthenticationParams( $username, $password )
    {
        $this->username = $username;
        
        if ( get_conf('userPasswordCrypted',false) )
        {
            $this->password = md5($password);
        }
        else
        {
            $this->password = $password;
        }
    }
    
    public function authenticate()
    {
        if ( empty( $this->username ) || empty( $this->password ) )
        {
            return false;
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT user_id, username, password, authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($this->username) . "\n"
            . "AND authSource = 'claroline'" . "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;
            
        $userDataList = Claroline::getDatabase()->query( $sql );
        
        if ( $userDataList->numRows() > 0 )
        {
            foreach ( $userDataList as $userData )
            {
                if ( $this->password === $userData['password'] )
                {
                    $this->userId = $userData['user_id'];
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
    
    public function getUserData()
    {
        return null;
    }
    
    public function getFilteredUserData()
    {
        return array();
    }
}

class TemporaryAccountAuthDriver extends AbstractAuthDriver
{
    protected $failureMsg = null;
    
    public function getAuthSource()
    {
        return 'temp';
    }
    
    public function getFilteredUserData()
    {
        return array();
    }
    
    public function setAuthenticationParams( $username, $password )
    {
        $this->username = $username;
        
        if ( get_conf('userPasswordCrypted',false) )
        {
            $this->password = md5($password);
        }
        else
        {
            $this->password = $password;
        }
    }
    
    public function authenticate()
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT user_id, username, password, authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($this->username) . "\n"
            . "AND authSource = '{$this->getAuthSource()}'". "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;

        // error_log( $sql );
            
        $userDataList = Claroline::getDatabase()->query( $sql );
        
        if ( $userDataList->numRows() > 0 )
        {
            foreach ( $userDataList as $userData )
            {
                // error_log( var_export( $userData, true ) );
                if ( $this->password === $userData['password'] )
                {
                    $sql = "SELECT propertyValue\n"
                        . "FROM `{$tbl['user_property']}`\n"
                        . "WHERE "
                        . "userId = ". Claroline::getDatabase()->quote($userData['user_id']) . "\n"
                        . "AND propertyId = 'accountExpirationDate'"
                        ;
                    
                    $res = Claroline::getDatabase()->query( $sql );
                    
                    if ( $res->numRows() )
                    {
                        $date = $res->fetch(Database_ResultSet::FETCH_VALUE);
                        
                        if ( strtotime($date) <= time() )
                        {
                            $this->failureMsg = get_lang("Your account has expired, please contact the platform adminitrator.");
                                
                            return false;
                        }
                        else
                        {
                            return true;
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
    
    public function getUserData()
    {
        return null;
    }
    
    public function getFailureMessage()
    {
        return $this->failureMsg;
    }
}

class PearAuthDriver extends AbstractAuthDriver
{
    protected $driverConfig;
    protected $authType;
    protected $authSourceName;
    protected $userRegistrationAllowed;
    protected $userUpdateAllowed;
    protected $extAuthOptionList;
    protected $extAuthAttribNameList;
    protected $extAuthAttribTreatmentList;
    
    protected $auth;
    
    public function __construct( $driverConfig )
    {
        $this->driverConfig = $driverConfig;
        $this->authType = $driverConfig['driver']['authSourceType'];
        $this->authSourceName = $driverConfig['driver']['authSourceName'];
        
        $this->userRegistrationAllowed = isset( $driverConfig['driver']['userRegistrationAllowed'] )
            ? $driverConfig['driver']['userRegistrationAllowed']
            : false
            ;
        $this->userUpdateAllowed = isset( $driverConfig['driver']['userUpdateAllowed'] )
            ? $driverConfig['driver']['userUpdateAllowed']
            : false
            ;
            
        $this->extAuthOptionList = $driverConfig['extAuthOptionList'];
        $this->extAuthAttribNameList = $driverConfig['extAuthAttribNameList'];
        $this->extAuthAttribTreatmentList = $driverConfig['extAuthAttribTreatmentList'];
        $this->extAuthIgnoreUpdateList = $driverConfig['extAuthAttribToIgnore'];
    }
    
    public function userRegistrationAllowed()
    {
        return $this->userRegistrationAllowed;
    }
    
    public function userUpdateAllowed()
    {
        return $this->userUpdateAllowed;
    }
    
    public function getAuthSource()
    {
        return $this->authSourceName;
    }
    
    public function authenticate()
    {
        if ( empty( $this->username ) || empty( $this->password ) )
        {
            return false;
        }
        
        $_POST['username'] = $this->username;
        $_POST['password'] = $this->password;
        
        if ( $this->authType === 'LDAP')
        {
            // CASUAL PATCH (Nov 21 2005) : due to a sort of bug in the
            // PEAR AUTH LDAP container, we add a specific option wich forces
            // to return attributes to a format compatible with the attribute
            // format of the other AUTH containers

            $this->extAuthOptionList ['attrformat'] = 'AUTH';
        }
        
        require_once 'Auth/Auth.php';

        $this->auth = new Auth( $this->authType, $this->extAuthOptionList, '', false);

        $this->auth->start();
        
        return $this->auth->getAuth();
    }
    
    public function getUserData()
    {
        $userAttrList = array('lastname'     => NULL,
                          'firstname'    => NULL,
                          'loginName'    => NULL,
                          'email'        => NULL,
                          'officialCode' => NULL,
                          'phoneNumber'  => NULL,
                          'isCourseCreator' => NULL,
                          'authSource'   => NULL);

        foreach($this->extAuthAttribNameList as $claroAttribName => $extAuthAttribName)
        {
            if ( ! is_null($extAuthAttribName) )
            {
                $userAttrList[$claroAttribName] = $this->auth->getAuthData($extAuthAttribName);
            }
        }
        
        foreach($userAttrList as $claroAttribName => $claroAttribValue)
        {
            if ( array_key_exists($claroAttribName, $this->extAuthAttribTreatmentList ) )
            {
                $treatmentCallback = $this->extAuthAttribTreatmentList[$claroAttribName];

                if ( is_callable( $treatmentCallback ) )
                {
                    $claroAttribValue = $treatmentCallback($claroAttribValue);
                }
                else
                {
                    $claroAttribValue = $treatmentCallback;
                }
            }

            $userAttrList[$claroAttribName] = $claroAttribValue;
        } // end foreach

        /* Two fields retrieving info from another source ... */

        $userAttrList['loginName' ] = $this->auth->getUsername();
        $userAttrList['authSource'] = $this->authSourceName;
        
        if ( isset($userAttrList['status']) )
        {
            $userAttrList['isCourseCreator'] = ($userAttrList['status'] == 1) ? 1 : 0;
        }
        
        return $userAttrList;
    }
}
