<?php // $Id: viewtopic.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * Claroline forum tool
 * 
 * Script handling topics and posts in forum tool (new topics, replies, topic review, etc.)
 * As from Claroline 1.9.6, gathers functionality of deprecated scripts newtopic.php, reply.php and editpost.php
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      FUNDP - WebCampus <webcampus@fundp.ac.be>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     CLFRM
 */

$tlabelReq = 'CLFRM';
//load claroline kernel
require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';

//security check
if ( !claro_is_in_a_course() || !claro_is_course_allowed() ) claro_disp_auth_form( true );

//load required libraries
require_once get_path( 'incRepositorySys' ) . '/lib/forum.lib.php';
require_once get_path( 'incRepositorySys' ) . '/lib/user.lib.php';

//init general purpose vars
claro_set_display_mode_available( true );
$is_allowedToEdit = claro_is_allowed_to_edit()
                    || ( claro_is_group_tutor() && !claro_is_course_manager() );
$dialogBox = new DialogBox();

//handle user input and possible associated exceptions
try
{
    $userInput = Claro_UserInput::getInstance();
    if( $is_allowedToEdit )
    {
        $userInput->setValidator( 'cmd', new Claro_Validator_AllowedList( array( 'rqPost', 'exSavePost', 'exDelete', 'show', 'exNotify', 'exdoNotNotify' ) ) );
        $userInput->setValidator( 'mode', new Claro_Validator_AllowedList( array( 'add', 'edit', 'reply', 'quote' ) ) );
        $userInput->setValidator( 'mode', new Claro_Validator_NotEmpty() );
    }
    else
    {
        $userInput->setValidator( 'cmd', new Claro_Validator_AllowedList( array( 'rqPost', 'exSavePost', 'show', 'exNotify', 'exdoNotNotify' ) ) );
        $userInput->setValidator( 'mode', new Claro_Validator_AllowedList( array( 'add', 'reply', 'quote' ) ) );
        $userInput->setValidator( 'mode', new Claro_Validator_NotEmpty() );
    }
    $userInput->setValidator( 'forum', new Claro_Validator_ValueType( 'numeric' ) );
    $userInput->setValidator( 'forum', new Claro_Validator_NotEmpty() );
    $userInput->setValidator( 'topic', new Claro_Validator_ValueType( 'numeric' ) );
    $userInput->setValidator( 'topic', new Claro_Validator_NotEmpty() );
    $userInput->setValidator( 'post', new Claro_Validator_ValueType( 'numeric' ) );
    $userInput->setValidator( 'post', new Claro_Validator_NotEmpty() );
    $userInput->setValidator( 'anonymous_post', new Claro_Validator_ValueType( 'numeric' ) );
    $userInput->setValidator( 'subject', new Claro_Validator_ValueType( 'string' ) );
    $userInput->setValidator( 'subject', new Claro_Validator_NotEmpty() );
    $userInput->setValidator( 'message', new Claro_Validator_ValueType( 'string' ) );
    $userInput->setValidator( 'start', new Claro_Validator_ValueType( 'numeric' ) );
    $userInput->setValidator( 'viewall', new Claro_Validator_ValueType( 'numeric' ) );
    
    //gather user input values
    $cmd = $userInput->get( 'cmd', 'show' );
    $forumId = $topicId = $postId = 0;
    $start = $userInput->get( 'start', 0 );
    $viewall = $userInput->get( 'viewall', 0 );
    switch( $cmd )
    {
        case 'rqPost' :
            $editMode = $userInput->getMandatory( 'mode' );
            if( 'add' == $editMode ) $forumId = $userInput->getMandatory( 'forum' );
            elseif( 'reply' == $editMode ) $topicId = $userInput->getMandatory( 'topic' );
            else $postId = $userInput->getMandatory( 'post' ); 
            break;
        case 'exSavePost' :
            $editMode = $userInput->getMandatory( 'mode' );
            if( 'add' == $editMode ) $forumId = $userInput->getMandatory( 'forum' );
            elseif( 'reply' == $editMode ) $topicId = $userInput->getMandatory( 'topic' );
            else $postId = $userInput->getMandatory( 'post' ); 
            $message = $userInput->getMandatory( 'message' );
            $message = preg_replace( '/<script[^\>]*>|<\/script>|(onabort|onblur|onchange|onclick|ondbclick|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onresize|onselect|onsubmit|onunload)\s*=\s*"[^"]+"/i', '', $message );
            if( 'add' == $editMode || 'edit' == $editMode ) $subject = trim( $userInput->getMandatory( 'subject' ) );
            $is_post_anonymous = $userInput->get( 'anonymous_post', 0 );
            break;
        case 'exDelete' :
            $postId = $userInput->getMandatory( 'post' );
            break;
        case 'exNotify' :
            $topicId = $userInput->getMandatory( 'topic' );
            break;
        case 'exdoNotNotify' :
            $topicId = $userInput->getMandatory( 'topic' );
            break;
        case 'show' :
            $topicId = $userInput->getMandatory( 'topic' );
            break;
    }
}
catch( Exception $ex )
{
    if( claro_debug_mode() )
    {
        $dialogBox->error( '<pre>' . $ex->__toString() . '</pre>' );
        claro_die( '<pre>' . $ex->__toString() . '</pre>' );
    }
    if( $ex instanceof Claro_Validator_Exception )
    {
        switch( $cmd )
        {
            case 'rqPost' :
                $dialogBox->error( get_lang( 'Unknown post or edition mode' ) );
                $cmd = 'dialog_only';
                break;
            case 'exSavePost' :
                $dialogBox->error( get_lang( 'Missing information' ) );
                $cmd = 'dialog_only';
                break;
            case 'exDelete' :
                $dialogBox->error( get_lang( 'Unknown post' ) );
                break;
            case 'exNotify' :
                $dialogBox->error( get_lang( 'Unknown topic' ) );
                break;
            case 'exdoNotNotify' :
                $dialogBox->error( get_lang( 'Unknown topic' ) );
                break;
            case 'show' :
                $dialogBox->error( get_lang( 'Unknown topic' ) );
                break;
            default : 
                claro_die( get_lang( 'Not allowed' ) );
        }
    }
    elseif( $ex instanceof Claro_Input_Exception )
    {
        $dialogBox->error( get_lang( 'Unset input variable' ) );
    }
    else
    {
        $dialogBox->error( get_lang( 'Unexpected error' ) );
    }
}
  
    //TODO handle these with ajax calls
    $userInput->setValidator( 'notification', new Claro_Validator_ValueType( 'string' ) );
    $notify = $userInput->get( 'notification', '' );

                        
//collect forum-topic-post settings and init some vars            
if( false !== $postSettingList = get_post_settings( $postId ) )
{
    $forumSettingList = get_forum_settings( $postSettingList['forum_id'] );
    $topicSettingList = get_topic_settings( $postSettingList['topic_id'] );
    $topicId = $topicSettingList['topic_id'];
}
elseif( false !== $topicSettingList = get_topic_settings( $topicId ) )
{
    $forumSettingList = get_forum_settings( $topicSettingList['forum_id'] );
    $forumId = $forumSettingList['forum_id'];
}
else
{
    $forumSettingList = get_forum_settings( $forumId );
}

$incrementViewCount = 'show' == $cmd ? true : false;

//init anonymity status
if( get_conf( 'clfrm_anonymity_enabled' ) == 'TRUE' )
{
    $anonymityStatus = $forumSettingList['anonymity'];
}
else
{
    $anonymityStatus = 'forbidden';
}

//check access rights
$is_postAllowed = ( $forumSettingList['forum_access'] != 0 
                    && ( !$topicId || !$topicSettingList['topic_status'] ) ) 
                    ? true 
                    : false;
$is_viewAllowed = !is_null( $forumSettingList['idGroup'] )
                  && !( ( $forumSettingList['idGroup'] == claro_get_current_group_id() ) 
                        || claro_is_in_a_group() || claro_is_group_allowed() ) 
                  ? false
                  : true;                 
// NOTE : $forumSettingList['idGroup'] != claro_get_current_group_id() is necessary to prevent any hacking
// attempt like rewriting the request without $cidReq. If we are in group
// forum and the group of the concerned forum isn't the same as the session
// one, something weird is happening, indeed ...
if( ( !isset( $_REQUEST['submit'] ) && !$is_postAllowed && 'show' != $cmd )
    || !$is_viewAllowed )
{
    $dialogBox->error( get_lang( 'Not allowed' ) );
}
else
{
    //handle user commands
    if( 'exDelete' == $cmd )
    {
        if( delete_post( $postId, $topicSettingList['topic_id'], $forumSettingList['forum_id'] ) )
        {
            $dialogBox->success( 'Post successfully deleted' );
        }
        else
        {
            $dialogBox->error( 'Error while deleting post' );
        }
        $cmd = 'show';
    }
    elseif( 'exSavePost' == $cmd )
    {        
        $error = false;
        //this test should be handled by a "html not empty" validator
        if ( trim( strip_tags( $message ) ) == '' )
        {
            $dialogBox->error( get_lang( 'You cannot post an empty message' ) ); 
            $error = true;          
        }
        else
        {
            // USER
            $userLastname  = $is_post_anonymous ? 'anonymous' : claro_get_current_user_data( 'lastName' );
            $userFirstname = $is_post_anonymous ? '' : claro_get_current_user_data( 'firstName' );
            $poster_ip     = $_SERVER['REMOTE_ADDR'];
    
            $time = date( 'Y-m-d H:i' );
            
            // record new topic if required
            if( 'add' == $editMode )
            {
                if( '' == $subject )
                {
                    $dialogBox->error( get_lang( 'Subject cannot be empty' ) );  
                    $error = true;
                }
                if( false !== $topicId = create_new_topic( $subject, $time, $forumId, claro_get_current_user_id(), $userFirstname, $userLastname ) )
                {
                    $eventNotifier->notifyCourseEvent( 'forum_new_topic', claro_get_current_course_id(), claro_get_current_tool_id(), $forumId . '-' . $topicId, claro_get_current_group_id(), 0 );
                    $dialogBox->success( 'Your topic has been recorded' );
                    // send message to user registered for notifications of new topics in this forum
                    trig_forum_notification( $forumId );
                    if( false !== $postId = create_new_post( $topicId, $forumId, claro_get_current_user_id(), $time, $poster_ip, $userLastname, $userFirstname, $message ) )
                    { 
                        $eventNotifier->notifyCourseEvent( 'forum_new_post', claro_get_current_course_id(), claro_get_current_tool_id(), $forumId . '-' . $topicId . '-' . $postId, claro_get_current_group_id(), 0 );
                    }
                    else
                    {
                        $dialogBox->error( 'error' );
                        $error = true;
                    }
                }
                $topicSettingList = get_topic_settings( $topicId );
            }
            elseif( 'edit' == $editMode )
            {
                if( '' == $subject && is_first_post( $topicId, $postId ) )
                {
                    $dialogBox->error( get_lang( 'Subject cannot be empty' ) );  
                    $error = true;
                }
                else
                {
                    update_post( $postId, $topicId, $message, $subject );
                    $dialogBox->success( 'Changes recorded' );
                }
            }
            elseif( 'reply' == $editMode || 'quote' == $editMode )
            {
                if( false !== $postId = create_new_post( $topicId, $forumId, claro_get_current_user_id(), $time, $poster_ip, $userLastname, $userFirstname, $message ) )
                $dialogBox->success( 'Your contribution has been recorded' );
                // send message to user registered for notifications of new posts in this topic
                trig_topic_notification( $topicId );
                $eventNotifier->notifyCourseEvent( 'forum_answer_topic', claro_get_current_course_id(), claro_get_current_tool_id(), $forumId . '-' . $topicId, claro_get_current_group_id(), 0 );
                $eventNotifier->notifyCourseEvent( 'forum_new_post', claro_get_current_course_id(), claro_get_current_tool_id(), $forumId . '-' . $topicId . '-' . $postId, claro_get_current_group_id(), 0 );                              
            }
            else
            {
                $dialogBox->error( 'error' );
                $error = true;
            }
        }
        if( $error ) 
        {
            $cmd = 'rqPost';
        }
        else 
        {
            $cmd = 'show';
        }
    }
    if( 'rqPost' == $cmd )
    {        
        if( 'edit' != $editMode || $is_allowedToEdit )
        {
            if( 'quote' == $editMode && $postSettingList )
            {
                $identity = 'anonymous' == $postSettingList['poster_lastname'] ? get_lang( 'Anonymous contributor wrote :' ) : $postSettingList['poster_firstname'] . '&nbsp;' . $postSettingList['poster_lastname'] . '&nbsp;' . get_lang( 'wrote :' );
                $quotedPost = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $postSettingList['post_text'] );
                $message = '<span style="margin-left:20px;font-weight:bold;">' . $identity . '</span><br/>';
                $message .= '<div style="background-color:#F0F0EE;margin-left:20px;margin-right:20px;padding:5px;border:1px solid;">' . $quotedPost . '</div><br/>';
                $subject = '';
            }
            elseif( 'edit' == $editMode )
            { 
                $message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $postSettingList['post_text'] );
                if( is_first_post( $topicId, $postId ) )
                {
                    $subject = $topicSettingList['topic_title'];
                }
            }
            elseif( 'add' == $editMode || 'reply' == $editMode )
            {
                $subject = '';
                $message = '';
            }
            $form = new ModuleTemplate( 'CLFRM', 'forum_editpost.tpl.php' );
        
            $form->assign( 'nextCommand', 'exSavePost' );
            $form->assign( 'editMode', $editMode );
            $form->assign( 'forumId', $forumSettingList['forum_id'] );
            $form->assign( 'topicId', $topicSettingList['topic_id'] );
            $form->assign( 'postId', $postId );
            $form->assign( 'subject', $subject );
            $form->assign( 'anonymityStatus', $anonymityStatus );
            $form->assign( 'is_allowedToEdit', $is_allowedToEdit );
            $form->assign( 'editor', claro_html_textarea_editor( 'message', $message ) );
        }
        else
        {
            $form = null;
            $dialogBox->error( get_lang( 'Your are not allowed to edit a contribution' ) );
            $cmd = 'show';
        }
    }
    //notification commands should be handled by ajax calls
    if( 'exNotify' == $cmd ) 
    {
        request_topic_notification( $topicId, claro_get_current_user_id() );
        $cmd = 'show';
    }
    elseif( 'exdoNotNotify' == $cmd ) 
    {
        cancel_topic_notification( $topicId, claro_get_current_user_id() );
        $cmd = 'show';
    }
}

//load required js and css files
JavaScriptLoader::getInstance()->load( 'forum' );
CssLoader::getInstance()->load( 'clfrm', 'screen' );

//javascript control to confirm signed posts in anonymous forums
if( 'default' == $anonymityStatus && !$is_allowedToEdit && get_conf( 'confirm_not_anonymous', 'TRUE' ) == 'TRUE' )
{
    $htmlHeadXtra[] =
    '<script type="text/javascript">
    $(document).ready(function(){
        $(".confirm").click(function(){       
            if( $("#anonymous_cb").length <= 0 || $("#anonymous_cb").is(":checked") ) 
            {
                return true;
            }
            else
            {
                if( confirm("' . clean_str_for_javascript( get_lang( 'Do you really want to sign your contribution ?' ) ) . '"))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        });
    });
    </script>';
}

//javascript control to confirm deletion of post
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

$pagetype = !empty( $editMode ) ? $editMode : 'viewtopic';

$out .= claro_html_tool_title( $nameTools, $is_allowedToEdit ? 'help_forum.php' : false );
if( claro_is_allowed_to_edit() && $topicId )
{
    $out .= '<div style="float: right;">' . "\n"
    .   '<img src=' . get_icon_url( 'html' ) . '" alt="" /> <a href="' . htmlspecialchars( Url::Contextualize( 'export.php?type=HTML&topic=' . $topicId )) . '" target="_blank">' . get_lang( 'Export to HTML' ) . '</a>' . "\n"
    .   '<img src="'. get_icon_url( 'mime/pdf' ) . '" alt="" /> <a href="' . htmlspecialchars( Url::Contextualize( 'export.php?type=PDF&topic=' . $topicId ) ) . '" target="_blank">' . get_lang( 'Export to PDF' ) .'</a>' . "\n"
    .   '</div>'
    ;
}

if( $topicSettingList )
{
    $out .= disp_forum_breadcrumb( $pagetype, $forumSettingList['forum_id'], $forumSettingList['forum_name'], $topicSettingList['topic_id'], $topicSettingList['topic_title'] );
}
else
{
    $out .= disp_forum_breadcrumb( $pagetype, $forumSettingList['forum_id'], $forumSettingList['forum_name'] );
}

if( 'show' != $cmd )
{
    if( 'default' == $anonymityStatus )
    {
        $info = '<tr valign="top">' . "\n"
        .    '<td>&nbsp;</td>'
        .    '<td><strong>' 
        . get_lang( 'Contributions to this forum are anonymous by default!<br/>' ) 
        . get_lang( 'If you want to sign your post all the same, uncheck the checkbox above the "OK" button' )
        . '</strong></td>'
        . '</tr>'
        . '<tr style="height:1px;"><td colspan="2">&nbsp;</td></tr>';
    }
    elseif( 'allowed' == $anonymityStatus )
    {
        $info = '<tr valign="top">' . "\n"
        . '<td>&nbsp;</td>'
        . '<td><strong>' 
        . get_lang( 'This forum allows anonymous contributions!<br/>' ) 
        . get_lang( 'If do not want to sign your post, check the checkbox above the "OK" button' )
        . '</strong></td>'
        . '</tr>'
        . '<tr style="height:1px;"><td colspan="2">&nbsp;</td></tr>';
    }
    if( !empty( $info ) )
    {
        $dialogBox->info( $info );
    }
}

$out .= $dialogBox->render();

//display edit form if any
if( isset( $form ) )
{
    $formBox = new DialogBox();
    $formBox->form( $form->render() );
    $out .= $formBox->render();
    $out .= '<p>&nbsp;</p>';
}

//display topic review if any
if( $topicSettingList )
{
    // get post and use pager
    if( !$viewall )
    {
    $postLister = new postLister( $topicId, $start, get_conf( 'posts_per_page' ) );
    }
    else
    {
        $postLister = new postLister( $topicId, $start, get_total_posts( $topicId, 'topic' ) );
        $incrementViewCount = false;
    }
    // get post and use pager
    $postList   = $postLister->get_post_list();
    $totalPosts = $postLister->sqlPager->get_total_item_count();
    $pagerUrl   = htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF'] . '?topic=' . $topicId ) );

    //increment topic view count if required
    if ( $incrementViewCount ) 
    {
        increase_topic_view_count( $topicId );
        $claro_notifier->is_a_notified_ressource( claro_get_current_course_id(), $claro_notifier->get_notification_date( claro_get_current_user_id() ), claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $forumId . "-" . $topicId );
        //$claroline->notifier->event( 'forum_read_topic', array( 'data' => array( 'topic_id' => $topicId ) ) );
    }
    
    if( $is_postAllowed )
    {
        $toolList = disp_forum_toolbar( 'viewtopic', $forumSettingList['forum_id'], $forumSettingList['cat_id'], $topicId );
        
        if ( count( $postList ) > 2 ) // if less than 2 las message is visible
        { 
            $start_last_message = ( ceil( $totalPosts / get_conf( 'posts_per_page' ) ) -1 ) * get_conf( 'posts_per_page' );

            $lastMsgUrl = Url::Contextualize( $_SERVER['PHP_SELF']
            .             '?forum=' . $forumSettingList['forum_id']
            .             '&amp;topic=' . $topicId
            .             '&amp;start=' . $start_last_message
            .             '#post' . $topicSettingList['topic_last_post_id'] )
            ;
            
            $toolList[] = claro_html_cmd_link( htmlspecialchars( Url::Contextualize( $lastMsgUrl ) ), get_lang( 'Last message' ) );
            
            if( !$viewall )
            {
                $viewallUrl = Url::Contextualize( $_SERVER['PHP_SELF']
                .             '?forum=' . $forumSettingList['forum_id']
                .             '&amp;topic=' . $topicId
                .             '&amp;viewall=1' )
                ;
               
                $toolList[] = claro_html_cmd_link( htmlspecialchars( Url::Contextualize( $viewallUrl ) ), get_lang( 'Full review' ) );
            }
        }
        
        $out .= '<p>' . claro_html_menu_horizontal( $toolList ) . '</p>';
    }

    $out .= $postLister->disp_pager_tool_bar( $pagerUrl );
    try
    {
        $display = new ModuleTemplate( 'CLFRM' , 'forum_viewtopic.tpl.php' ); 
        $display->assign( 'forum_id', $forumId );
        $display->assign( 'topic_id', $topicId );
        $display->assign( 'topic_subject', $topicSettingList['topic_title'] );
        $display->assign( 'postList', $postList );
        $display->assign( 'is_allowedToEdit', $is_allowedToEdit );
        $display->assign( 'anonymity' , $anonymityStatus );
        $display->assign( 'claro_notifier', $claro_notifier );
        $display->assign( 'is_post_allowed', $is_postAllowed );
        
        $out .= $display->render();
    }
    catch( Exception $ex )
    {
        $dialogBox->error( $ex );
    }
    
    if( $is_postAllowed )
    {
        $replyUrl = Url::Contextualize( $_SERVER['PHP_SELF']
            . '?topic=' . $topicId
            . '&amp;cmd=rqPost'
            . '&amp;mode=reply'
        );
            
        $toolBar[] = claro_html_cmd_link( htmlspecialchars( $replyUrl )
                                        , '<img src="' . get_icon_url( 'reply' ) . '" alt="" />'
                                        . ' '
                                        . get_lang( 'Reply' )
                                        );
        $out .= '<p>' . claro_html_menu_horizontal( $toolBar ) . '</p>';
    }


    $out .= $postLister->disp_pager_tool_bar( $pagerUrl );
}

ClaroBreadCrumbs::getInstance()->setCurrent( get_lang( 'Forums' ), 'index.php' );

$claroline->display->body->appendContent( $out );

echo $claroline->display->render();