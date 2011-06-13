<?php // $Id: group.php 12975 2011-03-15 14:12:54Z zefredz $
/**
 * CLAROLINE
 *
 * This is the groups page
 * This page list existing groups in course.
 * If allowed to enter, a link is under the group name
 * user can subscribe to a group if
 *  - user is member of the course
 *  - auto subscribe is aivailable
 *  - user don't hev hit the max group per user
 *  - the group is not full
 * Course Admin have more tools.
 *  - Create groups
 *  - Edit groups
 *  - Fill groups
 *  - empty groups
 *  - remove (all) groups
 * complete listing of  groups member is not aivailable. the  unsorted info is in user tool
 *
 * @version 1.9 $Revision: 12975 $
 * @copyright 2001-2011 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 * @package CLGRP
 * @author Claro Team <cvs@claroline.net>
 *
 */

//**************** INITIALISATION************************

$tlabelReq = 'CLGRP';
DEFINE('DISP_GROUP_LIST', __LINE__);
DEFINE('DISP_GROUP_SELECT_FOR_ACTION', __LINE__);

$gidReq = null;
$gidReset = true;
require '../inc/claro_init_global.inc.php';
if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
require_once get_path('incRepositorySys') . '/lib/group.lib.inc.php' ;
require_once get_path('incRepositorySys') . '/lib/pager.lib.php';

// use viewMode
claro_set_display_mode_available(TRUE);


$display = DISP_GROUP_LIST;
$nameTools = get_lang("Groups");

/**
 * DB TABLE NAMES INIT
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_user              = $tbl_mdb_names['user'               ];
$tbl_CoursUsers        = $tbl_mdb_names['rel_course_user'    ];
$tbl_Groups            = $tbl_cdb_names['group_team'         ];
$tbl_GroupsProperties  = $tbl_cdb_names['group_property'     ];
$tbl_course_properties = $tbl_cdb_names['course_properties'  ];
$tbl_GroupsUsers       = $tbl_cdb_names['group_rel_team_user'];
$tbl_Forums            = $tbl_cdb_names['bb_forums'          ];

/**
 * MAIN SETTINGS INIT
 */

$currentCourseRepository = claro_get_course_path();
$currentCourseId         = claro_get_current_course_id();
$_groupProperties = claro_get_current_group_properties_data();
$is_allowedToManage      = claro_is_allowed_to_edit();

$isGroupRegAllowed       =     claro_get_current_group_properties_data('registrationAllowed')
                           && (  !claro_is_course_tutor()
                               || (  claro_is_course_tutor()
                                   && get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
                         )
                               );
// Warning $groupRegAllowed is not valable before check of groupPerUserQuota

$groupPrivate   = $_groupProperties ['private'];
$nbGroupPerUser = $_groupProperties ['nbGroupPerUser'];

if ( ! $nbGroupPerUser )
{
    $sql = "SELECT COUNT(*)
            FROM `" . $tbl_Groups . "`";
    $nbGroupPerUser = claro_sql_query_get_single_value($sql);
}

/*$tools['forum'   ] = $_groupProperties['tools']['CLFRM' ];
$tools['document'] = $_groupProperties['tools']['CLDOC' ];
$tools['wiki'    ] = $_groupProperties['tools']['CLWIKI'];
$tools['chat'    ] = $_groupProperties['tools']['CLCHT' ];*/

$dialogBox = new DialogBox();
//// **************** ACTIONS ***********************

if ( isset($_REQUEST['regDone']) )
{
    $dialogBox->success( get_lang("You have been removed of the group.") );
}

$display_groupadmin_manager = (bool) $is_allowedToManage;

// ACTIONS

if ( $is_allowedToManage )
{
    if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
    else                           $cmd = null;

    if ( 'exMkGroup' == $cmd )
    {
        $noQUERY_STRING = true;
        // require the forum library to create the related forums

        $groupNamePrefix = (isset($_REQUEST['groupNamePrefix'])) ? $_REQUEST['groupNamePrefix'] : get_lang("Group");

        // For all Group forums, cat_id=1

        if ( isset($_REQUEST['group_max'])
        && ctype_digit($_REQUEST['group_max'])
        && (trim($_REQUEST['group_max']) != '') )
        {
            $groupMax = (int) $_REQUEST['group_max'];
        }
        else
        {
            $groupMax = NULL;
        }

        $groupQuantity = (int) $_REQUEST['group_quantity'];

        if ( $groupQuantity < 1 ) $groupQuantity = 1;

        $sql = 'SELECT MAX(id)
                FROM `' . $tbl_Groups . '`';

        $startNum = claro_sql_query_get_single_value($sql);

        $groupCreatedList = array();

        for ( $i = 1, $groupNum = $startNum + 1 ; $i <= $groupQuantity; $i++, $groupNum++ )
        {
            $groupId = create_group($groupNamePrefix, $groupMax);
            $groupCreatedList[] = $groupId;
        }

        $dialogBox->success( get_lang("%groupQty group(s) has (have) been added", array('%groupQty' => count($groupCreatedList))) );

        $claroline->log( 'GROUPMANAGING' , array ('CREATE_GROUP' => $groupQuantity) );

    }    // end if $submit

    if ('rqMkGroup' == $cmd )
    {
        $dialogBox->title( get_lang("Create new group(s)") );


        $dialogBox->form( '<form method="post" action="group.php">'                         ."\n"
        .          claro_form_relay_context()
        .          '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' ."\n"
        .          '<input type="hidden" name="cmd" value="exMkGroup" />'

        .          '<table>'                                                         ."\n"

        .          '<tr valign="top">'
        .          '<td>'
        .          '<label for="group_quantity">' . get_lang("Create") . '</label>'
        .          '</td>'
        .          '<td>'
        .          '<input type="text" name="group_quantity" id="group_quantity" size="3" value="1" /> '
        .          '<label for="group_quantity">' . get_lang("new group(s)") . '</label>'
        .          '</td>'                                                           ."\n"
        .          '</tr>'                                                           ."\n"

        .          '<tr valign="top">'                                               ."\n"
        .          '<td>'                                                            ."\n"
        .          '<label for="group_max">' . get_lang("Max.") . '</label>'
        .          '</td>'                                                           ."\n"
        .          '<td>'                                                            ."\n"
        .          '<input type="text" name="group_max" id="group_max" size="3" value="8" /> '
        .          get_lang("seats by groups (optional)")
        .          '</td>'                                                           ."\n"
        .          '</tr>'                                                           ."\n"

        .          '<tr>'                                                            ."\n"
        .          '<td>'                                                            ."\n"
        .          '<label for="creation">'
        .          get_lang("Create")
        .          '</label>'
        .          '</td>'                                                           ."\n"
        .          '<td>'                                                            ."\n"
        .          '<input type="submit" value="'.get_lang("Ok").'" name="creation" id="creation" /> '
        .          claro_html_button($_SERVER['HTTP_REFERER'], get_lang("Cancel"))
        .          '</td>'                                                           ."\n"
        .          '</tr>'                                                           ."\n"

        .          '</table>'                                                        ."\n"
        .          '</form>'                                                         ."\n"
        );
    }

    if ( $cmd == 'exDelGroup')
    {
        /*----------------------
        DELETE ALL GROUPS
        ----------------------*/

        if ($_REQUEST['id'] == 'ALL')
        {
            $nbGroupDeleted = deleteAllGroups();

            if ($nbGroupDeleted > 0) $message = get_lang("All groups have been deleted");
            else                     $message = get_lang("No group deleted");
            $claroline->log('GROUPMANAGING',array ('DELETE_GROUP' => $nbGroupDeleted));

        }
        elseif(0 < (int)$_REQUEST['id'])
        {
            /* ----------------
             * DELETE ONE GROUP
             * ---------------- */

            $nbGroupDeleted = delete_groups( (int) $_REQUEST['id']);

            if     ( $nbGroupDeleted == 1 ) $message = get_lang("Group deleted") ;
            elseif ( $nbGroupDeleted >  1 ) $message = $nbGroupDeleted . ' ' . get_lang("Group deleted");
            else                            $message = get_lang("No group deleted") . ' !';
        }
        $cidReset = TRUE;
        $cidReq   = claro_get_current_course_id();

        include(get_path('incRepositorySys') . '/claro_init_local.inc.php');
        $noQUERY_STRING = true;
    }

    /*-------------------
    EMPTY ALL GROUPS
    -------------------*/

    elseif ( 'exEmptyGroup' == $cmd )
    {

        if (empty_group())
        {
            $claroline->log('GROUPMANAGING',array ('EMPTY_GROUP' => TRUE));
            $dialogBox->success( get_lang("All groups are now empty") );
        }
        else
        {
            echo claro_failure::get_last_failure();
            $dialogBox->error( get_lang("Unable to empty groups") );
        }

    }

    /*-----------------
    FILL ALL GROUPS
    -----------------*/

    elseif ( 'exFillGroup' == $cmd  )
    {
        fill_in_groups($nbGroupPerUser, claro_get_current_course_id());
        $claroline->log('GROUPMANAGING',array ('FILL_GROUP' => TRUE));

        $dialogBox->success( get_lang("Groups have been filled (or completed) by students present in the 'Users' list.") );

    }    // end FILL

    /**
     * GROUP PROPERTIES
     */

    // This is called by the form in group_properties.php
    // set common properties for all groups
    if ( isset($_REQUEST['properties']) )
    {
        if (!array_key_exists('limitNbGroupPerUser',$_REQUEST))$_REQUEST['limitNbGroupPerUser'] = 1;

        if ( 'ALL' == $_REQUEST['limitNbGroupPerUser'] )
        {
            $newPropertyList['nbGroupPerUser'] = null;
        }
        else
        {
            $limitNbGroupPerUser = (int) $_REQUEST['limitNbGroupPerUser'];

            if ( $limitNbGroupPerUser < 1 ) $limitNbGroupPerUser = 1;

            $newPropertyList['nbGroupPerUser'] =  (int) $limitNbGroupPerUser;
            $nbGroupPerUser         = $limitNbGroupPerUser;
        }

        /**
         * In case of the table is empty (it seems to happen)
         * insert the parameters.
         */

        $newPropertyList['self_registration'] = isset($_REQUEST['self_registration'])
                                              ? (int) $_REQUEST['self_registration']
                                              : 0;

        $newPropertyList['self_unregistration'] = isset($_REQUEST['self_unregistration'])
                                              ? (int) $_REQUEST['self_unregistration']
                                              : 0;

        $newPropertyList['private'          ] = isset($_REQUEST['private'] )
                                              ? (int) $_REQUEST['private']
                                              : $private = 0;
                                              
        $groupToolList = get_group_tool_label_list();
        
        foreach ( $groupToolList as $thisGroupTool )
        {
            $thisGroupToolLabel = $thisGroupTool['label'];
            
            $newPropertyList[$thisGroupToolLabel] = isset($_REQUEST[$thisGroupToolLabel])
                ? (int) $_REQUEST[$thisGroupToolLabel]
                : 0
                ;
        }

        foreach ($newPropertyList as $propertyName => $propertyValue)
        {

            if ( is_null($propertyValue))
            {
                $sqlReadyPropertyValue = "NULL";
            }
            elseif ( is_int ($propertyValue))
            {
                $sqlReadyPropertyValue = $propertyValue;
            }
            else
            {
                $sqlReadyPropertyValue = "'" . claro_sql_escape($propertyValue) . "'";
            }

            $sql = "UPDATE `".$tbl_course_properties."`
                    SET `value` = " . $sqlReadyPropertyValue . "
                    WHERE `name` = '" . $propertyName . "'";
                    
            if ( claro_sql_query_affected_rows($sql) > 0 )
            {
                continue;
            }
            else
            {
                $sql = "INSERT INTO `".$tbl_course_properties."`
                       SET value    = " . $sqlReadyPropertyValue . ",
                           name     = '" . $propertyName . "',
                           category = 'GROUP'";

                if ( claro_sql_query($sql) !== false ) continue;
            }
        }

        $dialogBox->success( get_lang("Group settings have been modified") );
        $claroline->log('GROUPMANAGING',array ('CONFIG_GROUP' => TRUE));

        $cidReset = TRUE;
        $cidReq   = claro_get_current_course_id();
        $gidReset = TRUE;
        $gidReq = null;

        include get_path('incRepositorySys') . '/claro_init_local.inc.php';

        $isGroupRegAllowed = $_groupProperties['registrationAllowed']
        && (
        !claro_is_course_tutor()
        || (
        claro_is_course_tutor()
        &&
        get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
        )
        );

        $groupPrivate    = $_groupProperties['private'];

    }    // end if $submit

    // Create new groups
    $groupadmin_manager_menu[] =  claro_html_cmd_link( htmlspecialchars( Url::Contextualize(
                                                        $_SERVER['PHP_SELF']
                                                     . '?cmd=rqMkGroup' ))
                                                     , '<img src="' . get_icon_url('group') . '" alt="" />'
    .                            get_lang("Create new group(s)")
                                                     );
    // Delete all groups
    $groupadmin_manager_menu[] =  claro_html_cmd_link( htmlspecialchars( Url::Contextualize(
                                                    $_SERVER['PHP_SELF']
                                                     . '?cmd=exDelGroup&id=ALL' ))
                                                     , '<img src="' . get_icon_url('delete') . '" alt="" />'
    .                            get_lang("Delete all groups")
                                                     , array('onclick'=>'return confirmationDelete();')
                                                     );
    // Fill groups
    $groupadmin_manager_menu[] = claro_html_cmd_link( htmlspecialchars( Url::Contextualize(
                                                    $_SERVER['PHP_SELF']
                                                    . '?cmd=exFillGroup' ))
                                                    , '<img src="' . get_icon_url('fill') . '" alt="" />'
    .                            get_lang("Fill groups (automatically)")
                                                    , array('onclick'=>'return confirmationFill()')
                                                    );
    // Empty all groups
    $groupadmin_manager_menu[] = claro_html_cmd_link( htmlspecialchars( Url::Contextualize(
                                                     $_SERVER['PHP_SELF']
                                                     . '?cmd=exEmptyGroup' ))
                                                     , '<img src="' . get_icon_url('sweep') . '" alt="" />'
    .                            get_lang("Empty all groups")
                                                     , array('onclick' => 'return confirmationEmpty();')
                                                     );
    // Main group settings
    $groupadmin_manager_menu[] =  claro_html_cmd_link( htmlspecialchars( Url::Contextualize(
                                                     'group_properties.php' ))
                                                      , '<img src="' . get_icon_url('settings') . '" alt="" />'
                                                      . get_lang("Main Group Settings"));


} // end if is_allowedToManage


////**************** OUTPUT ************************

if (DISP_GROUP_LIST == $display )
{

    $sql = "SELECT `g`.`id`              AS id,
                   `g`.`name`            AS name,
                   `g`.`maxStudent`      AS maxStudent,
                   `g`.`secretDirectory` AS secretDirectory,
                   `g`.`tutor`           AS id_tutor,
                   `g`.`description`     AS description,

                   `ug`.`user`        AS is_member
                    ,COUNT(`ug2`.`id`) AS nbMember

          FROM `" . $tbl_Groups . "` `g`

          # retrieve the tutor id
          LEFT JOIN  `" . $tbl_user . "` AS `tutor`
          ON `tutor`.`user_id` = `g`.`tutor`

          # retrieve the user group(s)
          LEFT JOIN `" . $tbl_GroupsUsers . "` AS `ug`
          ON `ug`.`team` = `g`.`id` AND `ug`.`user` = " . (int) claro_get_current_user_id() . "

          # count the registered users in each group
          LEFT JOIN `" . $tbl_GroupsUsers . "` `ug2`
          ON `ug2`.`team` = `g`.`id`
          
          # Limit to regsitered users
          #INNER JOIN `" . $tbl_CoursUsers . "` AS `cu` ON `cu`.user_id = `ug2`.`user`
          #AND `cu`.`code_cours` = '" . $currentCourseId ."'

          #WHERE `cu`.`code_cours` = '" . $currentCourseId ."'

          GROUP BY `g`.`id`";

    $offset       = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0 ;
    $groupPager = new claro_sql_pager($sql, $offset,20);

    $sortKey = isset($_GET['sort']) && in_array($_GET['sort'],array('nbMember','name','maxStudent')) ? $_GET['sort'] : 'name';
    $sortDir = isset($_GET['dir' ]) && $_GET['dir'] == SORT_DESC ? SORT_DESC : SORT_ASC;

    $groupPager->add_sort_key($sortKey, $sortDir);

    $groupList = $groupPager->get_result_list($sql);




    $htmlHeadXtra[] =
    '<script type="text/javascript">

    function confirmationEmpty ()
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang("Are you sure you want to empty all groups ?"))  . '\'))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    function confirmationDelete ()
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang("Are you sure you want to delete all groups ?")) . '\'))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    function confirmationDeleteThisGroup (name)
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang("Are you sure to delete this group ?")) . ' \\n\' + name ))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    function confirmationFill ()
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang("Fill groups (automatically)")) . '\'))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    </script>'."\n";
}

$htmlHeadXtra[] =
'<style type="text/css">
<!--
.comment { margin-left: 30px}
-->
</style>'."\n";

$out = '';

$out .= claro_html_tool_title($nameTools);

/*-------------
  MESSAGE BOX
 -------------*/

$out .= $dialogBox->render();

/*==========================
COURSE ADMIN ONLY
==========================*/
if ( $display_groupadmin_manager ) $out .= '<p>' . claro_html_menu_horizontal($groupadmin_manager_menu) . '</p>';

/**
  VIEW COMMON TO STUDENT & TEACHERS
   - List of existing groups
   - For each, show name, qty of member and qty of place
   - Add link if group is "open" to current user
   - show subscribe button if needed
   - show link to edit and delete if authorised
 */

/*
* If Group self registration is allowed, previously check if the user
* is actually registered to the course...
*/

if ( $isGroupRegAllowed && claro_is_user_authenticated() )
{
    if ( ! claro_is_course_member()) $isGroupRegAllowed = FALSE;
}

/*
* Check in how many groups a user is allowed to register
*/

if ( ! is_null($nbGroupPerUser) ) $nbGroupPerUser = (int) $nbGroupPerUser;

if ( is_integer($nbGroupPerUser) )
{
    $countTeamUser = group_count_group_of_a_user(claro_get_current_user_id());
    if ( $countTeamUser >= $nbGroupPerUser ) $isGroupRegAllowed = FALSE;
}

$out .= $groupPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

$out .=                                                         "\n"
.    '<table class="claroTable emphaseLine" width="100%">' . "\n"
.    '<thead>'. "\n";

 /*-------------
      HEADINGS
   -------------*/

$sortUrlList = $groupPager->get_sort_url_list($_SERVER['PHP_SELF']);

$out .= '<tr class="headerX" align="center">' . "\n"
.    '<th align="left">'
.    '&nbsp;<a href="'.htmlspecialchars(Url::Contextualize( $sortUrlList['name'] )).'">'.get_lang("Groups") . '</a>'
.    '</th>'                               . "\n"
;

if($isGroupRegAllowed && ! $is_allowedToManage) // If self-registration allowed
{
    $out .= '<th align="left">' . get_lang("Registration") . '</th>' . "\n"  ;
}

$out .= '<th><a href="'.htmlspecialchars(Url::Contextualize($sortUrlList['nbMember'])).'">' . get_lang("Registered") . '</a></th>' . "\n"
.    '<th><a href="'.htmlspecialchars(Url::Contextualize($sortUrlList['maxStudent'])).'">' . get_lang("Max.") . '</a></th>' . "\n"
;

if ( $is_allowedToManage ) // only for course administrator
{
    $out .= '<th>' . get_lang("Edit") . '</th>' . "\n"
    .    '<th>' . get_lang("Delete") . '</th>' . "\n"
    ;
}

$out .= '</tr>' . "\n"
.    '</thead>'
.    '<tbody>' . "\n"
;

//////////////////////////////////////////////////////////////////////////////
$totalRegistered = 0;
// get group id where new events have been recorded since last login of the user

if (claro_is_user_authenticated())
{
    $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    $modified_groups = $claro_notifier->get_notified_groups(claro_get_current_course_id(), $date);
}
else
{
    $modified_groups = array();
}

 /*-------------
      DISPLAY
   -------------*/
if( $groupList ) 
{
    foreach ($groupList as $thisGroup)
    {
        // COLUMN 1 - NAME OF GROUP + If open LINK.

        $out .= '<tr align="center">' . "\n"
        .    '<td align="left">'
        ;
        /**
             * Note : student are allowed to enter into group only if they are
             * group member.
             * Tutors are allowed to enter in any groups, they
             * are also able to notice whose groups they are responsible
             */
        if( claro_is_user_authenticated() && ( $is_allowedToManage
        ||   $thisGroup['id_tutor'] == claro_get_current_user_id()
        ||   $thisGroup['is_member']
        || ! $_groupProperties['private']) )
        {
            // see if group name must be displayed as "containing new item" or not

            if (in_array($thisGroup['id'], $modified_groups))
            {
                $classItem = '<div class="item hot">';
            }
            else // otherwise just display its name normally
            {
                $classItem = '<div class="item">';
            }

            $out .= $classItem . '<img src="' . get_icon_url('group') . '" alt="" /> '
            .    '<a href="'
            .    htmlspecialchars(Url::Contextualize(
                    'group_space.php?gidReq=' . $thisGroup['id'] ))
            .    '">'
            .    $thisGroup['name']
            .    '</a>'
            .    '</div>'
            ;

            if     (claro_is_user_authenticated() && (claro_get_current_user_id() == $thisGroup['id_tutor'] ))
            {
                $out.= ' (' . get_lang("my supervision") . ')';
            }
            elseif ($thisGroup['is_member'])
            {
                $out .= ' (' . get_lang("my group") . ')';
            }
        }
        else
        {
            $out .= '<img src="' . get_icon_url('group') . '" alt="" /> '
            .    $thisGroup['name']
            ;
        }

        $out .= '</td>' . "\n";

        /*----------------------------
        COLUMN 2 - SELF REGISTRATION
        ----------------------------*/

        if (! $is_allowedToManage)
        {
            if($isGroupRegAllowed)
            {
                $out .= '<td align="center">';

                if( (! claro_is_user_authenticated())
                OR ( $thisGroup['is_member'])
                OR ( claro_get_current_user_id() == $thisGroup['id_tutor'])
                OR (!is_null($thisGroup['maxStudent']) //unlimited
                AND ($thisGroup['nbMember'] >= $thisGroup['maxStudent']) // still free place
                ))
                {
                    $out .= '&nbsp;-';
                }
                else
                {
                    $out .= '&nbsp;'
                    .    '<a href="'
                    .    htmlspecialchars( Url::Contextualize(
                            'group_space.php?registration=1&amp;selfReg=1&amp;gidReq=' . (int) $thisGroup['id'] )) . '">'
                    .    '<img src="' . get_icon_url('enroll') . '" alt="' . get_lang("register") . '" />'
                    .    '</a>'
                    ;
                }
                $out .= '</td>' . "\n";
            }    // end If $isGroupRegAllowed
        }

        /*------------------
        MEMBER NUMBER
        ------------------*/

        $out .=    '<td>' . $thisGroup['nbMember'] . '</td>' . "\n";

        /*------------------
        MAX MEMBER NUMBER
        ------------------*/

        if (is_null($thisGroup['maxStudent'])) $out .= '<td> - </td>' . "\n";
        else                                   $out .= '<td>' . $thisGroup['maxStudent'] . '</td>' . "\n";

        if ($is_allowedToManage)
        {
            $out .= '<td>'
            .    '<a href="'.htmlspecialchars( Url::Contextualize('group_edit.php?gidReq=' . $thisGroup['id'])) . '">'
            .    '<img src="' . get_icon_url('edit') . '" alt="' . get_lang("Edit") . '" />'
            .    '</a>'
            .    '</td>' . "\n"
            .    '<td>'
            .    '<a href="' . htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exDelGroup&amp;id=' . $thisGroup['id'] )) . '" '
            .    ' onclick="return confirmationDeleteThisGroup(\'' . clean_str_for_javascript($thisGroup['name']) . '\');">'
            .    '<img src="' . get_icon_url('delete') . '" alt="' . get_lang("Delete") . '" />'
            .    '</a>'
            .    '</td>' . "\n"
            ;
        }

        $out .= '</tr>' . "\n\n";

        if (   ! is_null($thisGroup['description'])
        && trim($thisGroup['description']) != '' )
        {
            $out .= '<tr>' . "\n"
            .    '<td colspan="5">' . "\n"
            .    '<div class="comment">'
            .    $thisGroup['description']
            .    '</div>'
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
        }


        $totalRegistered = $totalRegistered + $thisGroup['nbMember'];

    }    // while loop
}
else
{
    if ( $is_allowedToManage )
    {
        $out .= "\n"
        . '<tr>'
        . '<td colspan="5" class="centerContent">'
        . get_lang('Empty')
        . '</td>'
        . '</tr>'
        ;
    }
    else
    {
        $colspan = ( $isGroupRegAllowed ? '4' : '3' );
        
        $out .= "\n"
        . '<tr>'
        . '<td colspan="'.$colspan.'" class="centerContent">'
        . get_lang('Empty')
        . '</td>'
        . '</tr>'
        ;
    }
}

$out .= '</tbody>' . "\n"
.     '</table>' . "\n"
;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();
