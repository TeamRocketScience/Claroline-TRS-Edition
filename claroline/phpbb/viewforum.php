<?php // $Id: viewforum.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * Claroline forum tool
 *
 * Displays the list of topics gathered within a forum.
 * As from 1.9.6 adds administrative commands : edit-delete-block
 *
 * @version 1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      FUNDP - WebCampus <webcampus@fundp.ac.be>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     CLFRM
 *
 */

$tlabelReq = 'CLFRM';

//load Claroline kernel
require_once dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';

//security check
if( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form( true );
$currentContext = ( claro_is_in_a_group() ) ? CLARO_CONTEXT_GROUP : CLARO_CONTEXT_COURSE;

/**
 * Temporary fix
 * Try to create table (update script error for forum notifications)*
 * TODO : remove as from 1.10
 */
install_module_database_in_course( 'CLFRM', claro_get_current_course_id() );

//load required libraries
require_once get_path( 'incRepositorySys' ) . '/lib/forum.lib.php';

//init general purpose vars
claro_set_display_mode_available( true );
$is_allowedToEdit = claro_is_allowed_to_edit();
$dialogBox = new DialogBox();

//TODO check usefulness of following vars
$forumAllowed  = true;

//handle user input and possible associated exceptions
try
{
    $userInput = Claro_UserInput::getInstance();
    //set validators for user inputs
    if( $is_allowedToEdit )
    {
        $userInput->setValidator( 'cmd', new Claro_Validator_AllowedList( array( 'show', 'exLock', 'exUnlock', 'exDelTopic', 'exEditTopic', 'rqEditTopic', 'exNotify', 'exdoNotNotify' ) ) );
        $userInput->setValidator( 'topic', new Claro_Validator_ValueType( 'numeric' ) );
        $userInput->setValidator( 'topic', new Claro_Validator_NotEmpty() );
        $userInput->setValidator( 'title', new Claro_Validator_ValueType( 'string' ) );       
        $userInput->setValidator( 'title', new Claro_Validator_NotEmpty() );
    }
    else
    {
        $userInput->setValidator( 'cmd', new Claro_Validator_AllowedList( array( 'show', 'exNotify', 'exdoNotNotify' ) ) );    
    }
    $userInput->setValidator( 'forum', new Claro_Validator_ValueType( 'numeric' ) );
    $userInput->setValidator( 'forum', new Claro_Validator_NotEmpty() );
    $userInput->setValidator( 'start', new Claro_Validator_ValueType( 'numeric' ) );

    //collect user input
    $cmd = $userInput->get( 'cmd', 'show' );
    $forumId = $userInput->getMandatory( 'forum' );
    $start = $userInput->get( 'start', 0 );
    //TODO notification commands should be handled by ajax calls
    if( !in_array( $cmd, array( 'exNotify', 'exdoNotNotify', 'show' ) ) )
    {
        $topicId = $userInput->getMandatory( 'topic' );         
    } 
    if( 'exEditTopic' == $cmd )
    {
        $topicTitle = $userInput->getMandatory( 'title' ); 
    }
}
catch( Exception $ex )
{
    if( claro_debug_mode() )
    {
        $dialogBox->error( '<pre>' . $ex->__toString() . '</pre>' );
    }
    if( $ex instanceof Claro_Validator_Exception )
    {
        switch( $cmd )
        {
            //notification commands should be handled by ajax calls
            case 'exNotify' : 
                $dialogBox->error( get_lang( 'Forum unknown' ) );
                $cmd = 'show'; 
                break;
            case 'exDoNotNotify' :
                $dialogBox->error( get_lang( 'Forum unknown' ) );
                $cmd = 'show'; 
                break;
            case 'show' :
                $dialogBox->error( get_lang( 'Forum unknown' ) );
                $cmd = 'show'; 
                break;
            case 'exEdTopic' :
                $dialogBox->error( get_lang( 'Topic title cannot be empty' ) );
                $cmd = 'rqEdTopic'; 
                break;
            default :
                $dialogBox->error( get_lang( 'Topic unknown' ) );
                $cmd = 'show';
                break;
        }
    }
    elseif( $ex instanceof Claro_Input_Exception )
    {
        $dialogBox->error( get_lang( 'Unset input variable' ) );
        $cmd = 'show';
    }
    else
    {
        $dialogBox->error( get_lang( 'Unexpected error' ) );
        $cmd = 'show';
    }
}

//handle admin commands
if( $is_allowedToEdit )
{
    switch( $cmd )
    {
        case 'exEditTopic' :
            if( update_topic_title( $topicId, $topicTitle ) )
            {
                $dialogBox->success( get_lang( 'Topic title changed successfully' ) );
            }
            else
            {
                $dialogBox->error( get_lang( 'Error while modifying topic title' ) );
            }
            break;            
        case 'rqEditTopic' :
            $topicSettingList = get_topic_settings( $topicId );
            if( $topicSettingList )
            {
                try
                {
                    $form = new ModuleTemplate( 'CLFRM', 'forum_edittopic.tpl.php' );
                    $form->assign( 'topicId', $topicId );
                    $form->assign( 'forumId', $forumId );
                    $form->assign( 'nextCommand', 'exEditTopic' );
                    $form->assign( 'topicTitle', $topicSettingList['topic_title'] );
                    $dialogBox->form( $form->render() );
                }
                catch( Exception $ex )
                {
                    if( claro_debug_mode() )
                    {
                        $dialogBox->error( '<pre>' . $ex->__toString() . '</pre>' );
                    }
                    else
                    {
                        $dialogBox->error( $ex->getMessage() );
                    }
                }  
            }
            else
            {
                $dialogBox->error( get_lang( 'Unknown topic' ) );
            }  
            break;   
        case 'exDelTopic' :
            if( delete_topic( $topicId ) )
            {
                $dialogBox->success( get_lang( 'This topic has been deleted' ) );
            }
            else
            {
                $dialogBox->error( get_lang( 'Error while deleting topic' ) );
            }
            break;
        //TODO : lock and notification commands should be handled by ajax calls
        case 'exLock':
            if( set_topic_lock_status( $topicId, true ) )
            {
                $dialogBox->success( get_lang( 'This topic is now locked' ) );
            }
            else
            {
                $dialogBox->error( get_lang( 'Error while updating topic lock status' ) );
            }
            break;
        case 'exUnlock' :
            if( set_topic_lock_status( $topicId, false ) )
            {
                $dialogBox->success( get_lang( 'This topic is now open to new contributions' ) );
            }
            else
            {
                $dialogBox->error( get_lang( 'Error while updating topic lock status' ) );
            }
            break;
        case 'exNotify' :
            request_forum_notification( $forumId, claro_get_current_user_id() );
            break;
        case 'exdoNotNotify' :
            cancel_forum_notification( $forumId, claro_get_current_user_id() );
            break;
    }
}

//load forum settings and check access rights
if( false === $forumSettingList = get_forum_settings( $forumId ) )
{
    $dialogBox->error( 'Unknown forum' );
    $viewAllowed = false;
}
elseif( !is_null( $forumSettingList['idGroup'] )
         && ( ( $forumSettingList['idGroup'] != claro_get_current_group_id() ) 
                 || !claro_is_in_a_group()
                 || !claro_is_group_allowed() ) )
{
    //this forum is attached to a group which the current user is not member of
    $dialogBox->error( get_lang( 'You are not allowed to access this forum' ) );
    $viewAllowed = false;
}
else
{
    $forum_name = $forumSettingList['forum_name'];
    $forum_cat_id = $forumSettingList['cat_id'];
    $forum_post_allowed = ( $forumSettingList['forum_access'] != 0 ) ? true : false;

    $display_name = $forum_name;
    if( get_conf( 'clfrm_anonymity_enabled', true ) )
    {
        if( 'allowed' == $forumSettingList['anonymity'] ) $display_name .= ' (' . get_lang( 'anonymity allowed' ) . ')';
        elseif( 'default' == $forumSettingList['anonymity'] ) $display_name .= ' (' . get_lang( 'anonymous forum' ) . ')';
    }
    $viewAllowed = true;
}

//add javascript control for "dangerous" commands (delete)   
$htmlHeadXtra[] =
    "<script type=\"text/javascript\">
    function confirm_delete()
    {
       if (confirm('". clean_str_for_javascript( get_lang( 'Are you sure to delete' ) ) . "'))
       {return true;}
       else
       {return false;}
    }
    </script>";

//prepare display
$out = '';

$nameTools = get_lang( 'Forums' );

$pagetype = 'viewforum';

$out .= claro_html_tool_title( get_lang( 'Forums' ), $is_allowedToEdit ? 'help_forum.php' : false );

if( !$viewAllowed )
{
    $out .= $dialogBox->render();
}
else
{
    $colspan = $is_allowedToEdit ? 9 : 6;

    $is_allowedToEdit = claro_is_allowed_to_edit()
                        || (  claro_is_group_tutor() && !claro_is_course_manager());
                        // (  claro_is_group_tutor()
                        //  is added to give admin status to tutor
                        // && !claro_is_course_manager())
                        // is added  to let course admin, tutor of current group, use student mode

    if( claro_is_allowed_to_edit() )
    {
        $out .= '<div style="float: right;">' . "\n"
        .   '<img src="' . get_icon_url('html') . '" alt="" /> <a href="' . htmlspecialchars( Url::Contextualize( 'export.php?type=HTML&forum=' . $forumId )) . '" target="_blank">' . get_lang( 'Export to HTML' ) . '</a>' . "\n"
        .   '<img src="'. get_icon_url('mime/pdf') . '" alt="" /> <a href="' . htmlspecialchars( Url::Contextualize( 'export.php?type=PDF&forum=' . $forumId ) ) . '" target="_blank">' . get_lang( 'Export to PDF' ) .'</a>' . "\n"
        .   '</div>' . "\n"
        ;
    }
    
    $out .= disp_forum_breadcrumb( $pagetype, $forumId, $forum_name );
    
    $out .= $dialogBox->render();

    //display group tools if in group context
    if( $currentContext == CLARO_CONTEXT_GROUP )
    {
        $groupToolList = forum_group_tool_list( claro_get_current_group_id() );
        $out .= '<p>' . claro_html_menu_horizontal( $groupToolList ) .'</p>';
    }

    if( $forum_post_allowed )
    {
        $out .= '<p>' . claro_html_menu_horizontal(disp_forum_toolbar( $pagetype, $forumId, $forum_cat_id, 0 ) ) . '</p>';
    }
    
    $topicLister = new topicLister($forumId, $start, get_conf( 'topics_per_page' ) );
    $topicList   = $topicLister->get_topic_list();
    $pagerUrl = htmlspecialchars( Url::Contextualize( get_module_url( 'CLFRM' ) . '/viewforum.php?forum=' . $forumId ) );

    $out .= $topicLister->disp_pager_tool_bar( $pagerUrl );

    try
    {
        $display = new ModuleTemplate( 'CLFRM' , 'forum_viewforum.tpl.php' ); 
        $display->assign( 'forumId', $forumId );
        $display->assign( 'forumName', $display_name );
        $display->assign( 'forumSettings', $forumSettingList );
        $display->assign( 'topicList', $topicList );
        $display->assign( 'is_allowedToEdit', $is_allowedToEdit );
        $display->assign( 'claro_notifier', $claro_notifier );
        
        $out .= $display->render();
    }
    catch( Exception $ex )
    {
        $dialogBox->error( $ex );
    }

    $out .= $topicLister->disp_pager_tool_bar($pagerUrl);
}

ClaroBreadCrumbs::getInstance()->setCurrent( get_lang( 'Forums' ), 'index.php' );

$claroline->display->body->appendContent( $out );

echo $claroline->display->render();