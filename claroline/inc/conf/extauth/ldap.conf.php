<?php // $Id: ldap.conf.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * LDAP authentication driver
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     CLAUTH
 */

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

if ( ! function_exists( 'ldap_to_claroline' ) )
{
    function ldap_to_claroline($attribute)
    {
        if ( is_array( $attribute ) ) $attribute = implode(', ', $attribute);
        return utf8_decode($attribute);
    }
}

$driverConfig['driver'] = array(
    'enabled' => true, // set to false to disable the driver
    // do not change this section
    'class' => 'PearAuthDriver',
    'authSourceType' => 'LDAP',
    'authSourceName' => 'ldap',
    // end of section
    // allow driver to create a user in Claroline with data from auth source
    'userRegistrationAllowed' => true,
    // allow driver to update a user in Claroline with data from auth source
    'userUpdateAllowed' => false
);

// you can change the driver from this point

$driverConfig['extAuthOptionList'] = array(
    'url' => 'ldap://server_address',
    'port' => 636,
    'basedn' => 'ou=personne,o=your organisation unit,c=domain',
    'userattr' => 'uid',
    'useroc' => 'person',
    'attributes' => array('sn','givenName','telephoneNumber','mail'),
    'attrformat' => 'AUTH_LDAP_ATTR_AUTH_STYLE',
    //'debug' => true
);

$driverConfig['extAuthAttribNameList'] = array(
    'lastname' => 'sn',
    'firstname' => 'givenName',
    'email' => 'mail',
    'phoneNumber' => 'telephoneNumber',
    'authSource' => 'ldap'
);

$driverConfig['extAuthAttribTreatmentList'] = array (
    'lastname'     => 'ldap_to_claroline',
    'firstname'    => 'ldap_to_claroline',
    'loginName'    => 'ldap_to_claroline',
    'email'        => 'ldap_to_claroline',
    'officialCode' => 'ldap_to_claroline',
    'phoneNumber'  => 'ldap_to_claroline',
    'isCourseCreator' => NULL
);

$driverConfig['extAuthAttribToIgnore'] = array(
    // 'isCourseCreator'
);
?>