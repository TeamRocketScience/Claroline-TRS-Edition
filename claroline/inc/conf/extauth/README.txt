
                    CLAROLINE AUTHENTICATION SYSTEM
                    Claroline 1.9

Preliminary Note : This document doens't treat single-sign on authentication
using CAS, Shibboleth or any other SSO system.

This system allows Claroline to rely on external or local systems concerning 
authentication and user profile management. It is based on a collection of 
authentication drivers stored inside the platform/conf/extauth folder.
directory.

These drivers can be loaded by the Claroline kernel when a user attempt to log 
on the platform.

To use one of these drivers

1. Open the concerned driver into a text editor and adapt the parameters to your 
own context. Do not forget to enable it (see below).

2. Copy the modified driver to the platform/conf/extauth directory.


                                 HOW IT WORKS ?

These drivers can be called by the Claroline authentication system in two 
circumstances.

1. When a user has never logged to the platform beforehand, ant try to log in to 
Claroline for the first time. No record concerning this user are found into the 
Claroline system, so it attempts to look for this user on the external 
authentication systems list specified by its configuration file. When it founds 
it, Claroline duplicates the user profile into its own user table, stating that 
it comes for this specific external authentication system.

2. When a user log to the platform next time. A record concerning this user is 
already stored into the Claroline system. From this record Claroline is able to 
know from where does this user profile comes. And it try to connect to the 
concerned external authentication system to check if this user account is still 
allowed to connect with this password. It also takes the occasion to update from 
the external authentication system the user data stored into the Claroline 
system.


                                DRIVER SETTINGS

Each Claroline driver sets 5 parameters.

1. $driverConfig['driver'] describes the properties of the driver

- 'enabled' : tell the kernel to use this driver or not

    example : 'enabled' => true
    
- 'userRegistrationAllowed' : tell the kernel if the driver is allowed to create
a new user in Claroline

    example : 'userRegistrationAllowed' => true

- 'userUpdateAllowed' : tell the kernel if the driver is allowed to update an
existing user in Claroline

    example : 'userUpdateAllowed' => true

- 'class' : the PHP class used for the driver. If you are only modifying the
parameters of an existing driver, do not edit this one

    example : 'class' => 'PearAuthSource'

- 'authSourceType' set the technical type of the of the external authentication 
  source
  
    example : 'authSourceType' => 'DB';
    
- 'authSourceName' : set the identity of the external authentication source 

    example : 'authSourceName' => 'phpnuke';


2. $driverConfig['extAuthOptionList'] : set the parameters needed to connect to the external 
  authentication source and the field to to retrieve in it.

    example : $driverConfig['extAuthOptionList'] = array(

                    'url'      => 'ldap://server_address',
                    'port'     => '636',
                    'basedn'   => 'ou=personne,o=your organisation unit,c=domaine',
                    'userattr' => 'uid',
                    'useroc'   => 'person',
                    'attributes' => array('sn', 'givenName', 'telephoneNumber','mail'),

                );

3 $driverConfig['extAuthAttribNameList'] : set how the data retrieved from the external 
  authentication source matches the Claroline data structure. The keys are the 
  Claroline attributes and the value are the authentication external attributes.    

    example : $driverConfig['extAuthAttribNameList'] = array (

                    'lastname'     => 'sn',
                    'firstname'    => 'givenName',
                    'email'        => 'mail',
                    'phoneNumber'  => 'telephoneNumber',
                    'authSource'   => 'ldap'

                );

4. $driverConfig['extAuthAttribTreatmentList'] : set any optional preliminary treatment to the 
  data retrieved from the external authentication source before committing it into 
  Claroline. The keys are the concerned Claroline attribute, and the values are 
  the name of the function which make the treatment. You can use standard PHP 
  function or functions defined by your own.

        example : $driverConfig['extAuthAttribTreatmentList'] = array (

                'lastname'     => 'utf8_decode',
                'firstname'    => 'utf8_decode',
                'loginName'    => 'utf8_decode',
                'email'        => 'utf8_decode',
                'officialCode' => 'utf8_decode',
                'phoneNumber'  => 'utf8_decode',
                'status'       => 'treat_status_from_extauth_to_claroline'

            );

5. $driverConfig['extAuthAttribToIgnore'] : set the list of attributes from the
  external authentication system to ignore when updating the data of a user.
  This is mainly   used to prevent an external authentication system to
  overwrite attributes set by the platform administrator.

    example : $driverConfig['extAuthAttribToIgnore'] = array(
                'isCourseCreator'
            );

In addition, each driver can define a specific driver PHP class or one or more
specific functions to process the data retreived from the authentication source.

For more information : http://forum.claroline.net

Originaly created by Hugues Peeters <peeters@ipm.ucl.ac.be>
New system written and maintained by the Claroline Team <info@claroline.net>