<?php // $Id: user_add.php 13026 2011-03-31 16:37:58Z abourguignon $

/**
 * CLAROLINE
 *
 * This tool allow to add a user in a specific course (and in the platform).
 *
 * @version     1.8 $Revision: 13026 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/index.php/CLUSR
 * @author      Claro Team <cvs@claroline.net>
 * @package     CLUSR
 */

/*=====================================================================
 Init Section
 =====================================================================*/

$tlabelReq = 'CLUSR';
$gidReset = true;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

$can_add_single_user = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_enroll_single_user') )
                     || claro_is_platform_admin();
if ( ! $can_add_single_user ) claro_die(get_lang('Not allowed'));

// include configuration file
include claro_get_conf_repository() . 'user_profile.conf.php';

// include libraries
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/course_user.lib.php';
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

// Initialise variables
$nameTools        = get_lang('Add a user');
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Users'), 'user.php' );

$dialogBox = new DialogBox();

$platformRegSucceed = false;
$courseRegSucceed   = false;

/*=====================================================================
                                MAIN SECTION
 =====================================================================*/

// Initialise field variable from subscription form
$userData = user_initialise();

$cmd = isset($_REQUEST['cmd']) ? $cmd = $_REQUEST['cmd'] : null;

if ( (isset($_REQUEST['applySearch'] ) && ( $_REQUEST['applySearch'] != '' )))
{
    $cmd = 'applySearch';
}

$userData['confirmUserCreate'] = isset($_REQUEST['confirmUserCreate']) ? $_REQUEST['confirmUserCreate'] : null;

$userId = isset($_REQUEST['userId']) ? (int) $_REQUEST['userId'] : null;

$displayResultTable = false;
$displayForm        = true;
$errorMsgList       = array();

if ( $cmd == 'registration' )
{
    /*
     * Two possible ways to enroll a user to a course :
     * Registration of a completly new user from $userData
     * Registration of an existing user form its $userId
     */

    if ( $userData && ! $userId)
    {
        $errorMsgList = user_validate_form_registration($userData);

        if ( count($errorMsgList) == 0 ) $validUserData = true;
        else                             $validUserData = false;

        if ( in_array(get_lang('This official code is already used by another user.'), $errorMsgList) ) // validation exception ...
        {
            $userList = user_search( array('officialCode' => $userData['officialCode']),
                                     claro_get_current_course_id(), false, true, false);

            $dialogBox->error(get_lang('This official code is already used by another user.')
                           . '<br />' . get_lang('Take one of these options') . ' : '
                           . '<ul>'
                           . '<li>'
                           . '<a href="#resultTable">'
                           . get_lang('Click on the enrollment command beside the concerned user')
                           . '</a>'
                           . '</li>'
                           . '<li>'
                           . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=cancel'. claro_url_relay_context('&amp;') . '">' . get_lang('Cancel the operation') . '</a>'
                           . '</li>'
                           . '</ul>'
                           );

             $displayResultTable = true;
        }
        // User's registration will need a confirmation
        elseif (    ! $userData['confirmUserCreate']
                 && ! ( empty($userData['lastname']) && empty($userData['email']) ) )
        {
            $userList = user_search( array('lastname' => $userData['lastname'    ],
                                           'email'    => $userData['email'       ]),
                                     claro_get_current_course_id(), false, true, false);
            if ( count($userList) > 0 )
            {
                 // PREPARE THE URL command TO CONFIRM THE USER CREATION
                 $confirmUserCreateUrl = array();
                 foreach($userData as $thisDataKey => $thisDataValue)
                 {
                    $confirmUserCreateUrl[] = $thisDataKey .'=' . urlencode($thisDataValue);
                 }

                 $confirmUserCreateUrl = Url::Contextualize( $_SERVER['PHP_SELF']
                                       . '?cmd=registration&amp;'
                                       . implode('&amp;', $confirmUserCreateUrl)
                                       . '&amp;confirmUserCreate=1' );


                 $dialogBox->warning( get_lang('Notice') . '. '
                    . get_lang('Users with similar settings exist on the system yet')
                    . '<br />' . get_lang('Take one of these options') . ' : '
                    . '<ul>'
                    . '<li>'
                    . '<a href="#resultTable" onclick="highlight(\'resultTable\');">'
                    . get_lang('Click on the enrollment command beside the concerned user')
                    . '</a>'
                    . '</li>'
                    . '<li>'
                    . '<a href="'.htmlspecialchars( $confirmUserCreateUrl ).'">'
                    . get_lang('Confirm the creation of a new user')
                    . '</a>'
                    . '<br /><small>'
                    . $userData['lastname'    ] . ' ' . $userData['firstname']
                    . $userData['officialCode'] . ' ' . $userData['email']
                    . '</small>'
                    . '</li>'
                    . '<li>'
                    . '<a href="'.htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=cancel' )) .'">'
                    . get_lang('Cancel the operation') . '</a>'
                    . '</li>'
                    . '</ul>'
                );

                $displayForm        = false;
                $displayResultTable = true;
            }
        }
        else
        {
            $userList = array();
        }
        
        if( !empty($errorMsgList) && count($userList) == 0 )
        {
            foreach( $errorMsgList as $errorMsg )
            {
                $dialogBox->error($errorMsg);
            }
        }
    }

    if ( ! $userId && $validUserData && count($userList) == 0 )
    {
        $userData['language'] = null;
        $userId = user_create($userData);

        if ($userId) user_send_registration_mail($userId, $userData,claro_get_current_course_id());
    }

    if ( $userId )
    {
        $courseRegSucceed = user_add_to_course($userId, claro_get_current_course_id(), $userData['courseAdmin'], $userData['courseTutor'],false);
        Console::log(
            "{$userId} enroled to course "
            .  claro_get_current_course_id()
            . " by " . claro_get_current_user_id(),
                'COURSE_SUBSCRIBE'
        );
    }
    else
    {
        $courseRegSucceed = false;
    }
} // end if $cmd == 'registration'

if ($cmd == 'applySearch')
{
    // search on username, official_code, ...

    $displayResultTable = TRUE;

    if ( ! (   empty($userData['lastname'    ])
            && empty($userData['email'       ])
            && empty($userData['username'    ])
            && empty($userData['officialCode']) ) )
    {

        $userList = user_search( array('lastname'     => $userData['lastname'],
                                       'firstname'      => $userData['firstname'],
                                       'email'        => $userData['email'],
                                       'officialCode' => $userData['officialCode'],
                                       'username'       => $userData['username']),
                                       claro_get_current_course_id(),
                                       true,
                                       false,
                                       !claro_is_platform_admin() );
    }
    else
    {
        $userList = array();
    }
} // if $cmd == 'applySearch'

// Send mail notification
if ( $courseRegSucceed )
{
    $userData = user_get_properties($userId);

    user_send_enroll_to_course_mail($userId, $userData,claro_get_current_course_id() );
    // display message
    $dialogBox->success( get_lang('%firstname %lastname has been registered to your course',
                            array ( '%firstname' => $userData['firstname'],
                                    '%lastname'  => $userData['lastname'])
                           )
                     );
}


/*=====================================================================
 Display Section
 =====================================================================*/

$out = '';

$out .= claro_html_tool_title(array('mainTitle' =>$nameTools, 'supraTitle' => get_lang('Users')),
                'help_user.php');
$out .= $dialogBox->render();

if ( $courseRegSucceed )
{
    $out .= '<p><a href="' . htmlspecialchars(Url::Contextualize( get_module_entry_url('CLUSR') ) ) . '">&lt;&lt; ' . get_lang('Back to user list') . '</a></p>' . "\n";
}
else
{
    if ($displayResultTable) //display result of search (if any)
    {
        $enrollmentLabel = $userData['courseAdmin'] ? get_lang('Enrol as teacher') : get_lang('Enrol as student');
        $enrollmentLabel .= $userData['courseTutor'] ? '&nbsp;-&nbsp;' . get_lang('tutor') : '';
                
        $regUrlAddParam = '';
        if ( $userData['courseTutor'   ] ) $regUrlAddParam .= '&amp;courseTutor=1';
        if ( $userData['courseAdmin'  ] ) $regUrlAddParam .= '&amp;courseAdmin=1';
        
        $out .= '<a name="resultTable"></a>'
              . '<table id="resultTable" class="claroTable emphaseLine" border="0" cellspacing="2">' . "\n"
              . '<thead>' . "\n"
              . '<tr class="superHeader">'
              . '<th colspan="6">' . get_lang('Search result') . '</th>'
              . '</tr>'
              . '<tr class="headerX" align="center" valign="top">' . "\n"
              . '<th>' . get_lang('Last name')           . '</th>' . "\n"
              . '<th>' . get_lang('First name')          . '</th>' . "\n"
              . '<th>' . get_lang('Administrative code') . '</th>' . "\n"
              . '<th>' . get_lang('Username')               . '</th>' . "\n"
              . '<th>' . get_lang('Email')               . '</th>' . "\n"
              . '<th>' . $enrollmentLabel            . '</th>' . "\n"
              . '</tr>' . "\n"
              . '</thead>' . "\n"
              . '<tbody>' . "\n";
        
        foreach ($userList as $thisUser)
        {
           $out .= '<tr valign="top">' . "\n"
                 . '<td>' . htmlspecialchars($thisUser['lastname'    ]) . '</td>' . "\n"
                 . '<td>' . htmlspecialchars($thisUser['firstname'   ]) . '</td>' . "\n"
                 . '<td>' . htmlspecialchars($thisUser['officialCode']) . '</td>' . "\n"
                 . '<td>' . htmlspecialchars($thisUser['username'    ]) . '</td>' . "\n"
                 . '<td>' . htmlspecialchars($thisUser['email'       ]) . '</td>' . "\n"
                 . '<td align="center">' . "\n";
            
            // deal with already registered users found in result
            if ( empty($thisUser['registered']) )
            {
                $out .= '<a href="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF']
                      . '?cmd=registration'
                      . '&amp;userId=' . $thisUser['uid'] . $regUrlAddParam )) . '">'
                      . '<img src="' . get_icon_url('enroll') . '" alt="' . $enrollmentLabel . '" />'
                      . '</a>';
            }
            else
            {
                $out .= '<span class="highlight">'
                      . get_lang('Already enroled')
                      . '</span>';
            }
            
            $out .= '</td>' . "\n"
                  . '</tr>' . "\n";
        }
        
        if ( sizeof($userList) == 0 )
        {
            $out .= '<td align="center" colspan="5">' . get_lang('No user found') . '</td>';
        }
        
        $out .= '</tbody>'
              . '</table>'
              . '<hr />';
    }
    
    //display form to add a user
    if ($displayForm)
    {
        if( get_conf( 'is_coursemanager_allowed_to_register_single_user' ) || claro_is_platform_admin() )
        {
            $out .= '<p>' . get_lang('Add user manually') . ' :</p>'
                  . '<p>' . get_lang('He or she will receive email confirmation with login and password') . '</p>' . "\n"
                  . user_html_form();
        }
        else
        {
            $out .= '<p>' . get_lang('Search user to add to your course') . ' :</p>'
                  . '<p>' . get_lang('Fill in one or more search criteria, select user profile parameters for your course and press \'Search\'') . '</p>' . "\n"
                  . user_html_search_form($userData);
        }
    }
} // end else of if ( $courseRegSucceed )

$claroline->display->body->appendContent($out);

echo $claroline->display->render();