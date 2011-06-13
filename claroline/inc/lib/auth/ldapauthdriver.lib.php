<?php

/**
 * LDAP Authentication Driver
 *
 * @version     2.5
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU AFFERO GENERAL PUBLIC LICENSE version 3
 */

require_once dirname(__FILE__) . '/ldap.lib.php';
require_once dirname(__FILE__) . '/authdrivers.lib.php';

class ClaroLdapAuthDriver extends AbstractAuthDriver
{
    protected $driverConfig;
    
    protected $authSourceName;
    
    protected
        $userRegistrationAllowed,
        $userUpdateAllowed;
        
    protected
        $extAuthOptionList,
        $extAuthAttribNameList,
        $extAuthAttribTreatmentList;
        
    protected $user;
    
    public function __construct( $driverConfig )
    {
        $this->driverConfig = $driverConfig;
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
    
    public function authenticate()
    {
        $auth = new Claro_Ldap(
            $this->extAuthOptionList['url'],
            $this->extAuthOptionList['port'],
            $this->extAuthOptionList['basedn']
        );
        
        try
        {
            $auth->connect();
            
            $userAttr = isset($this->extAuthOptionList['userattr']) ? $this->extAuthOptionList['userattr'] : null;
            $userFilter = isset($this->extAuthOptionList['userfilter']) ? $this->extAuthOptionList['userfilter'] : null;
            
            $user = $auth->getUser($this->username, $userFilter, $userAttr);
            
            if ( $user )
            {
                if( $auth->authenticate( $user->getDn(), $this->password ) )
                {
                    $this->user = $user;
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        catch ( Exception $e )
        {
            $this->setFailureMessage($e->getMessage());
            Console::error($e->getMessage());
            return false;
        }
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
                if ( ! is_null($this->user->$extAuthAttribName) )
                {
                    $userAttrList[$claroAttribName] = $this->user->$extAuthAttribName;
                }
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

        $userAttrList['loginName' ] = $this->user->getUid();
        $userAttrList['authSource'] = $this->authSourceName;
        
        return $userAttrList;
    }
}
