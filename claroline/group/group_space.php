<?php // $Id: group_space.php 12981 2011-03-15 14:59:29Z zefredz $
/**
 * CLAROLINE
 *
 * This tool is "groupe_home" + "group_user"
 *
 * @version 1.9 $Revision: 12981 $
 * @copyright 2001-2011 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 * @package CLGRP
 * @author Claro Team <cvs@claroline.net>
 */

$cidNeeded = true;
$gidNeeded = true;
$tlabelReq = 'CLGRP';

require '../inc/claro_init_global.inc.php';
include_once get_path('incRepositorySys') . '/lib/group.lib.inc.php';
require_once dirname(__FILE__) . '/../messaging/lib/permission.lib.php';

$toolNameList= claro_get_tool_name_list();
$toolRepository = get_path('clarolineRepositoryWeb');
$dialogBox = new DialogBox();

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

// block if !claro_is_in_a_group()
// accept  if claro_is_group_allowed()

if ( ! claro_is_allowed_to_edit() )
{
    if ( ! claro_is_in_a_group() )
    {
        claro_redirect('group.php');
        exit();
    }
    elseif ( ! claro_is_group_allowed() && ! ( isset( $_REQUEST['selfReg'] ) || isset($_REQUEST['doReg']) ) )
    {
        claro_redirect('group.php');
        exit();
    }
}

// use viewMode
claro_set_display_mode_available(true);

/********************
* CONNECTION SECTION
*********************/

$is_allowedToManage  = claro_is_allowed_to_edit();
/*
* DB tables definition
*/

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'];
$tbl_user                    = $tbl_mdb_names['user'];
$tbl_bb_forum                = $tbl_cdb_names['bb_forums'];
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'];
$tbl_group_team              = $tbl_cdb_names['group_team'];
/****************************************************************************/

$_groupProperties = claro_get_current_group_properties_data();
// COUNT IN HOW MANY GROUPS CURRENT USER ARE IN
// (needed to give or refuse selfreg right)

$groupMemberCount = group_count_students_in_group(claro_get_current_group_id());

$groupMemberQuotaExceeded = (bool) ( ! is_null(claro_get_current_group_data('maxMember')) && (claro_get_current_group_data('maxMember') <= $groupMemberCount) ); // no limit assign to group per user;

$userGroupRegCount = group_count_group_of_a_user(claro_get_current_user_id());

// The previous request compute the quantity of subscription for the current user.
// the following request compare with the quota of subscription allowed to each student
$userGroupQuotaExceeded = (bool) (  $_groupProperties ['nbGroupPerUser'] <= $userGroupRegCount
&& ! is_null($_groupProperties['nbGroupPerUser']) && ($_groupProperties ['nbGroupPerUser'] != 'ALL' )); // no limit assign to group per user;

$is_allowedToSelfRegInGroup = (bool) ( $_groupProperties ['registrationAllowed']
&& ( ! $groupMemberQuotaExceeded )
&& ( ! $userGroupQuotaExceeded )
&& ( ! claro_is_course_tutor() ||
     ( claro_is_course_tutor()
       &&
       get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
       )));

$is_allowedToSelfRegInGroup  = (bool) $is_allowedToSelfRegInGroup && claro_is_in_a_course() && ( ! claro_is_group_member() ) && claro_is_course_member();
$is_allowedToSelfUnregInGroup  = (bool) $_groupProperties ['unregistrationAllowed'] && claro_is_in_a_course() && claro_is_group_member() && claro_is_course_member();


$is_allowedToDocAccess = (bool) ( claro_is_course_manager() || claro_is_group_member() ||  claro_is_group_tutor());
$is_allowedToChatAccess     = (bool) (     claro_is_course_manager() || claro_is_group_member() ||  claro_is_group_tutor() );

/**
 * SELF-REGISTRATION PROCESS
 */

if( isset($_REQUEST['registration']) )
{
    //RECHECK if subscribe is aivailable
    if( claro_is_course_member() &&  ! claro_is_group_member() && $is_allowedToSelfRegInGroup)
    {
        if( isset($_REQUEST['doReg']) )
        {
            //RECHECK if subscribe is aivailable
            if( claro_is_course_member() &&  ! claro_is_group_member() && $is_allowedToSelfRegInGroup)
            {

                $sql = "INSERT INTO `" . $tbl_group_rel_team_user . "`
                SET `user` = " . (int) claro_get_current_user_id() . ",
                    `team` = " . (int) claro_get_current_group_id()
                    ;
                    
                if (claro_sql_query($sql))
                {
                    // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
                    claro_redirect($_SERVER['PHP_SELF'] . '?gidReset=1&gidReq=' . claro_get_current_group_id() . '&regDone=1');
                    exit();

                }
            }
        }
        else // Confirm reg
        {
            $dialogBox->form( get_lang('Confirm your subscription to the group &quot;<b>%group_name</b>&quot;',array('%group_name'=>claro_get_current_group_data('name'))) . "\n"
            .          '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">' . "\n"
            .          claro_form_relay_context()
            .          '<input type="hidden" name="registration" value="1" />' . "\n"
            .          '<input type="hidden" name="doReg" value="1" />' . "\n"
            .          '<br />' . "\n"
            .          '<input type="submit" value="' . get_lang("Ok") . '" />' . "\n"
            .          claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])) , get_lang("Cancel")) . "\n"
            .          '</form>' . "\n"
            );



        }



    }
}


if ( isset($_REQUEST['regDone']) )
{
    $dialogBox->success( get_lang("You are now a member of this group.") );
}

if( isset($_REQUEST['unregistration']) )
{
    //RECHECK if subscribe is aivailable
    if( claro_is_course_member() && claro_is_group_member() && $is_allowedToSelfUnregInGroup)
    {
        if( isset($_REQUEST['doUnreg']) )
        {
            //RECHECK if subscribe is aivailable
            if( claro_is_course_member() && claro_is_group_member() && $is_allowedToSelfUnregInGroup)
            {

                $sql = "DELETE FROM `" . $tbl_group_rel_team_user . "`
                WHERE `user` = " . (int) claro_get_current_user_id() . "
                AND    `team` = " . (int) claro_get_current_group_id()
                    ;

                if (claro_sql_query($sql))
                {
                    // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
                    claro_redirect( dirname($_SERVER['PHP_SELF']) . '/group.php?gidReset=1&unregDone=1');
                    exit();

                }
            }
        }
        else // Confirm reg
        {
            $dialogBox->form( get_lang('Confirm your unsubscription from the group &quot;<b>%group_name</b>&quot;',array('%group_name'=>claro_get_current_group_data('name'))) . "\n"
            .          '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">' . "\n"
            .          claro_form_relay_context()
            .          '<input type="hidden" name="unregistration" value="1" />' . "\n"
            .          '<input type="hidden" name="doUnreg" value="1" />' . "\n"
            .          '<br />' . "\n"
            .          '<input type="submit" value="' . get_lang("Ok") . '" />' . "\n"
            .          claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])) , get_lang("Cancel")) . "\n"
            .          '</form>' . "\n"
            );

        }

    }
}



/********************************
 * GROUP INFORMATIONS RETRIVIAL
 ********************************/


/*----------------------------------------------------------------------------
GET GROUP MEMBER LIST
----------------------------------------------------------------------------*/

$groupMemberList = get_group_user_list(claro_get_current_group_id(),claro_get_current_course_id());


/*----------------------------------------------------------------------------
GET TUTOR(S) DATA
----------------------------------------------------------------------------*/

$sql = "SELECT user_id AS id, nom AS lastName, prenom AS firstName, email
        FROM `".$tbl_user."` user
        WHERE user.user_id='".claro_get_current_group_data('tutorId')."'";

$tutorDataList = claro_sql_query_fetch_all($sql);

/*----------------------------------------------------------------------------
GET FORUM POINTER
----------------------------------------------------------------------------*/
$forumId = claro_get_current_group_data('forumId');

$toolList = get_group_tool_list();

if (claro_is_in_a_course())
{
    $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    $modified_tools = $claro_notifier->get_notified_tools(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id());
}
else $modified_tools = array();

$toolLinkList = array();

foreach($toolList as $thisTool)
{
    if( !array_key_exists($thisTool['label'],$_groupProperties['tools']) )
    {
        continue;
    }
    // special case when display mode is student and tool invisible doesn't display it
    if ( !claro_is_allowed_to_edit() )
    {
        if(!$_groupProperties['tools'][$thisTool['label']])
        {
            continue;
        }
    }


    if ( ! empty($thisTool['label']))   // standart claroline tool
    {
        $label = $toolNameList[$thisTool['label']] ;
        $toolName = get_lang($label);
        $url      = trim(get_module_url($thisTool['label']) . '/' . $thisTool['url']);
    }
    elseif( ! empty($thisTool['name']) ) // external tool added by course manager
    {
        $toolName = $thisTool['name'];
        $url      = trim($thisTool['url']);
    }
    else
    {
        $toolName = '<i>no name</i>';
        $url      = trim($thisTool['url']);
    }

    if (! empty($thisTool['icon']))
    {
        $icon = get_icon_url( $thisTool['icon'], $thisTool['label']);
    }
    else
    {
        $icon = get_icon_url( 'tool' );
    }

    $style = '';

    // patchy
    if ( claro_is_platform_admin() || claro_is_course_manager() )
    {
        if ( !$_groupProperties['tools'][$thisTool['label']])
        {
            $style = 'invisible ';
        }
    }

    // see if tool name must be displayed 'as containing new items' (a red ball by default)  or not
    $classItem = '';
    if (in_array($thisTool['id'], $modified_tools)) $classItem = " hot";

    if ( ! empty($url) )
    {
        $toolLinkList[] = '<a class="' . trim( $style . ' item' . $classItem ) . '" href="' . htmlspecialchars(Url::Contextualize($url)) . '">'
        .                 '<img src="' . $icon . '" alt="" />&nbsp;'
        .                 $toolName
        .                 '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . trim( $style ) . '>'
        .                 '<img src="' . $icon . '" alt="" />&nbsp;'
        .                 $toolName
        .                 '</span>' . "\n"
        ;
    }
}


/*****************
 * DISPLAY SECTION
 ******************/

$out = '';

$out .= claro_html_tool_title( array('supraTitle'=> get_lang("Groups"),
                                  'mainTitle' => claro_get_current_group_data('name') . ' <img src="' . get_icon_url('group') . '" alt="" />'));

$out .= $dialogBox->render();


if($is_allowedToSelfRegInGroup && !array_key_exists('registration',$_REQUEST))
{
    $out .= '<p>' . "\n"
    .    claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                            $_SERVER['PHP_SELF'] . '?registration=1' ))
                            , '<img src="' . get_icon_url('enroll') . '"'
                            .     ' alt="' . get_lang("Add me to this group") . '" />'
    .                       get_lang("Add me to this group")
                            )
    .    '</p>'
    ;
}

if ( $is_allowedToSelfUnregInGroup && !array_key_exists('unregistration',$_REQUEST) )
{
    $out .= '<p>' . "\n"
    .    claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                            $_SERVER['PHP_SELF'] . '?unregistration=1' ))
                            , '<img src="' . get_icon_url('unenroll') . '"'
                            .     ' alt="' . get_lang("Remove me from this group") . '" />'
    .                       get_lang("Remove me from this group")
                            )
    .    '</p>'
    ;
}

$out .= '<table cellpadding="5" cellspacing="0" border="0">'  . "\n"
.    '<tr>'  . "\n"
.    '<td style="border-right: 1px solid gray;" valign="top" width="220" class="toolList">'  . "\n"

/*
* Vars needed to determine group File Manager and group Forum
* They are unregistered when opening group.php once again.
*
* session_register("secretDirectory");
* session_register("userGroupId");
* session_register("forumId");
*/

.   claro_html_list( $toolLinkList, array( 'class' => 'groupToolList' ) ) . "\n"
. '<br />'
;

if ($is_allowedToManage)
{
    $out .= claro_html_cmd_link( htmlspecialchars(Url::Contextualize('group_edit.php'))
                            , '<img src="' . get_icon_url('edit') . '"'
                            .     ' alt="' . get_lang("Edit this group") . '" />'
                            .    get_lang("Edit this group")
                            );
}

if (current_user_is_allowed_to_send_message_to_current_group())
{
    $out .= '<br />'.claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                            '../messaging/sendmessage.php?cmd=rqMessageToGroup&amp;' ))
                            , '<img src="' . get_icon_url('mail_send') . '" alt="" />' . get_lang("Send a message to group")
                            );
}

$out .= '</td>' . "\n"
.    '<td width="20">' . "\n"
.    '&nbsp;' . "\n"
.    '</td>' . "\n"
.    '<td valign="top">' . "\n"
.    '<b>' . "\n"
.    get_lang("Description") . "\n"
.    '</b> :' . "\n"
;

/*----------------------------------------------------------------------------
DISPLAY GROUP DESCRIPTION
----------------------------------------------------------------------------*/

if( strlen(claro_get_current_group_data('description')) > 0)
{
    $out .= '<br /><br />' . "\n"
    .    claro_get_current_group_data('description')
    ;
}
else // Show 'none' if no description
{
    $out .= get_lang("(none)");
}

$out .= '<br /><br />'
.    '<b>'
.    get_lang("Group Tutor")
.    '</b> :'
;

/*----------------------------------------------------------------------------
DISPLAY GROUP TUTOR INFORMATION
----------------------------------------------------------------------------*/

if (count($tutorDataList) > 0)
{
    $out .= '<br /><br />' . "\n";
    foreach($tutorDataList as $thisTutor)
    {
        $out .= '<span class="item">'
        .    htmlspecialchars( $thisTutor['lastName'] . ' ' . $thisTutor['firstName'] )
        ;
        
        if(current_user_is_allowed_to_send_message_to_user($thisTutor['id']))
        {
            $out .= ' - <a href="'.htmlspecialchars(Url::Contextualize(
              '../messaging/sendmessage.php?cmd=rqMessageToUser&amp;userId=' . (int)$thisTutor['id'] ))
              . '">'
              // . '<img src="' . get_icon_url('mail_send') . '" alt="" />'
              . get_lang('Send a message')
              . '</a>'
              ;
        }
          
        $out .= '</span>'
        .    '<br />'
        ;
    }
}
else
{
    $out .= get_lang("(none)");
}

$out .= '<br /><br />

<b>' . get_lang("Group members") . '</b> : '
;


/*----------------------------------------------------------------------------
DISPLAY GROUP MEMBER LIST
----------------------------------------------------------------------------*/

$context = Claro_Context::getCurrentContext();
$context[CLARO_CONTEXT_GROUP] = null;
$urlContext = Claro_Context::getUrlContext( $context );

if(count($groupMemberList) > 0)
{
    $out .= '<br /><br />' . "\n";
    foreach($groupMemberList as $thisGroupMember)
    {
        $out .= '<a href="'
        .    htmlspecialchars(Url::Contextualize('../user/userInfo.php?uInfo=' . $thisGroupMember['id'], $urlContext  ))
        .    '" class="item">'
        .    $thisGroupMember['lastName'] . ' ' . $thisGroupMember['firstName']
        .    '</a>';
        
        if(current_user_is_allowed_to_send_message_to_user($thisGroupMember['id']))
        {
            $out .= ' - <a href="'
                . htmlspecialchars(Url::Contextualize(
                    '../messaging/sendmessage.php?cmd=rqMessageToUser&amp;userId=' . (int) $thisGroupMember['id'] ))
                . '">'
                // . '<img src="' . get_icon_url('mail_send') . '" alt="" />'
                . get_lang('Send a message')
                . '</a>'
                ;
        }
        
        $out .= '<br />' . "\n";
    }
}
else
{
    $out .= get_lang('(none)');
}


$out .= '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();
