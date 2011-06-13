<?php // $Id: messageList.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * This script  chat simply works with a flat file where lines are appended.
 * Simple user can  just  write lines.
 * Chat manager can reset and store the chat if $chatforgroup is true,
 * the file  is reserved because always formed
 * with the group id of the current user in the current course.
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLCHT
 *
 * @package CLCHT
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 */

// CLAROLINE INIT
$tlabelReq = 'CLCHT'; // required
require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ( ! claro_is_course_allowed() && ! claro_is_user_authenticated() ) )
{
die ('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'."\n"
    .'<html>'."\n"
    .'<head>'."\n"
    .'<title>'.get_lang('Chat').'</title>'."\n"
    .'</head>'."\n"
    .'<body>'."\n"."\n"
    .'<a href="./chat.php" >click</a>' . "\n"
    .'</body>'."\n"."\n"

);


}


/*============================================================================
        CONNECTION BLOC
============================================================================*/

$coursePath  = get_path('coursesRepositorySys') . claro_get_course_path();
$courseId    = claro_get_current_course_id();
$groupId     = claro_get_current_group_id();
$_user       = claro_get_current_user_data()
;
$_course     = claro_get_current_course_data();
$_group      = claro_get_current_group_data();

$is_allowedToManage = claro_is_course_manager();
$is_allowedToStore  = claro_is_course_manager();
$is_allowedToReset  = claro_is_course_manager();


if ( $_user['firstName'] == '' && $_user['lastName'] == '')
{
    $nick = get_lang('Anonymous');
}
else
{
    $nick = $_user['firstName'] . ' ' . $_user['lastName'] ;
    if (strlen($nick) > get_conf('max_nick_length') ) $nick = $_user['firstName'] . ' '. $_user['lastName'][0] . '.' ;
}


// theses  line prevent missing config file
$refresh_display_rate = get_conf('refresh_display_rate',10);

/*============================================================================
        CHAT INIT
============================================================================*/


// THE CHAT NEEDS A TEMP FILE TO RECORD CONVERSATIONS.
// THIS FILE IS STORED IN THE COURSE DIRECTORY

$curChatRep = $coursePath.'/chat/';

// IN CASE OF AN UPGRADE THE DIRECTORY MAY NOT EXIST
// A PREVIOUS CHECK (AND CREATE IF NEEDED) IS THUS NECESSARY

if ( ! is_dir($curChatRep) ) mkdir($curChatRep, CLARO_FILE_PERMISSIONS);

// DETERMINE IF THE CHAT SYSTEM WILL WORK
// EITHER AT THE COURSE LEVEL OR THE GROUP LEVEL

if (claro_is_in_a_group())
{
    if (claro_is_group_allowed())
    {
        $groupContext  = TRUE;
        $courseContext = FALSE;

        $is_allowedToManage = $is_allowedToManage||  claro_is_group_tutor();
        $is_allowedToStore  = $is_allowedToStore ||  claro_is_group_tutor();
        $is_allowedToReset  = $is_allowedToReset ||  claro_is_group_tutor();

        $activeChatFile = $curChatRep.$courseId.'.'.$groupId.'.chat.html';
        $onflySaveFile  = $curChatRep.$courseId.'.'.$groupId.'.tmpChatArchive.html';
        $exportFile     = $coursePath.'/group/'.claro_get_current_group_data('directory').'/';
    }
    else
    {
        die('<center>' . get_lang('You are not a member of this group') . '</center>');
    }
}
else
{
    $groupContext  = FALSE;
    $courseContext = TRUE;

    $activeChatFile = $curChatRep.$courseId.'.chat.html';
    $onflySaveFile  = $curChatRep.$courseId.'.tmpChatArchive.html';
    $exportFile     = $coursePath.'/document/';
}


$dateNow = claro_html_localised_date(get_locale('dateTimeFormatLong'));
$timeNow = claro_html_localised_date('[%d/%m/%y %H:%M]');

if ( ! file_exists($activeChatFile))
{
    // create the file
    $fp = @fopen($activeChatFile, 'w')
    or die ('<center>'.get_lang('Error : Cannot initialize chat').'</center>');
    fclose($fp);

    $dateLastWrite = get_lang('New chat');
}

/*============================================================================
        COMMANDS
============================================================================*/

/*----------------------------------------------------------------------------
        RESET COMMAND
----------------------------------------------------------------------------*/

if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'reset' && $is_allowedToReset)
{
    $fchat = fopen($activeChatFile,'w');
    fwrite($fchat, '<small>'.$timeNow.' -------- '.get_lang('Chat reset by').' '.$nick.' --------</small><br />'."\n");
    fclose($fchat);

    @unlink($onflySaveFile);
}

/*----------------------------------------------------------------------------
        STORE COMMAND
----------------------------------------------------------------------------*/

if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'store' && $is_allowedToStore)
{
    $chatDate = 'chat.'.date('Y-m-j').'_';

    // TRY DO DETERMINE A FILE NAME THAT DOESN'T ALREADY EXISTS
    // IN THE DIRECTORY WHERE THE CHAT EXPORT WILL BE STORED

    $i = 1;
    while ( file_exists($exportFile.$chatDate.$i.'.html') ) $i++;

    $saveIn = $chatDate.$i.'.html';

    // COMPLETE THE ON FLY BUFFER FILE WITH THE LAST LINES DISPLAYED
    // BEFORE PROCEED TO COMPLETE FILE STORAGE

    buffer( implode('', file($activeChatFile) ).'</body>'."\n\n".'</html>'."\n",
    $onflySaveFile);

    if (copy($onflySaveFile, $exportFile.$saveIn) )
    {
        $chat_filename = '<a href="../document/document.php" target="blank">' . $saveIn . '</a>' ;

        $cmdMsg = "\n"
                . '<blockquote>'
                . get_lang('%chat_filename is now in the document tool. (<em>This file is visible</em>)',array('%chat_filename'=>$chat_filename))
                . '</blockquote>'."\n";

        @unlink($onflySaveFile);
    }
    else
    {
        $cmdMsg = '<blockquote>' . get_lang('Store failed') . '</blockquote>'."\n";
    }
}

/*----------------------------------------------------------------------------
    'ADD NEW LINE' COMMAND
----------------------------------------------------------------------------*/
// don't use empty() because it will prevent to post a line with only "0"
if ( isset($_REQUEST['chatLine']) && trim($_REQUEST['chatLine']) != "" )
{
    $fchat = fopen($activeChatFile,'a');
    $chatLine = htmlspecialchars( $_REQUEST['chatLine'] );
    // replace url with real html link
    $chatLine = preg_replace("/(http://)(([[:punct:]]|[[:alnum:]])*)/","<a href=\"\\0\" target=\"_blank\">\\2</a>",$chatLine);

    fwrite($fchat, '<small>' . $timeNow . ' &lt;<b>' . $nick . '</b>&gt; ' . $chatLine . '</small><br />' . "\n");

    fclose($fchat);
}

/*============================================================================
DISPLAY MESSAGE LIST
============================================================================*/

if ( !isset($dateLastWrite) )
{
    $dateLastWrite = get_lang('Last message was on') . ' : '
    .                strftime( get_locale('dateTimeFormatLong') , filemtime($activeChatFile) );
}

// WE DON'T SHOW THE COMPLETE MESSAGE LIST.
// WE TAIL THE LAST LINES

$activeLineList  = file($activeChatFile);
$activeLineCount = count($activeLineList);

$excessLineCount = $activeLineCount - get_conf('max_line_to_display');
if ($excessLineCount < 0) $excessLineCount = 0;
$excessLineList = array_splice($activeLineList, 0 , $excessLineCount);
$curDisplayLineList = $activeLineList;

// DISPLAY

// CHAT MESSAGE LIST OWN'S HEADER
// add a unique number in the url to make IE believe that the url is different and to force refresh
if( !isset($_REQUEST['x']) || $_REQUEST['x'] == 1 )
{
    $x = 0;
}
else
{
    $x = 1;
}

// set http charset
if (! is_null(get_locale('charset'))) header('Content-Type: text/html; charset='. get_locale('charset'));

// page header with meta to refresh the page
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'."\n"
    .'<html>'."\n"
    .'<head>'."\n"
    .'<title>'.get_lang('Chat').'</title>'
    .'<meta http-equiv="refresh" content="' . $refresh_display_rate . ';url=./messageList.php?x='.$x.'#final">'."\n"
    .'<link rel="stylesheet" type="text/css" href="'.get_path('clarolineRepositoryWeb').'css/' . get_conf('claro_stylesheet') . '" >'."\n"
    .'</head>'."\n"
    .'<body>'."\n"."\n"
    ;

if( isset($cmdMsg) )
{
    echo $cmdMsg;
}

echo implode("\n", $curDisplayLineList) // LAST LINES
    ."\n"
    .'<p align="right"><small>'
    .$dateLastWrite                 // LAST MESSAGE DATE TIME
    .'</small></p>'."\n\n"
    .'<a name="final"></a>'."\n\n"       // ANCHOR ALLOWING TO DIRECTLY POINT LAST LINE
    .'</body>'."\n\n"
    .'</html>'."\n"
    ;

// FOR PERFORMANCE REASON, WE TRY TO KEEP THE ACTIVE CHAT FILE OF REASONNABLE
// SIZE WHEN THE EXCESS LINES BECOME TOO HIGH WE REMOVE THEM FROM THE ACTIVE
// CHAT FILE AND STORE THEM IN A SORT OF 'ON FLY BUFFER' WHILE WAITHING A
// POSSIBLE EXPORT FOR DEFINITIVE STORAGE


if ($activeLineCount > get_conf('max_line_in_file'))
{

    // STORE THE EXCESS LINES INTO THE 'ON FLY BUFFER'

    buffer(implode('',$excessLineList), $onflySaveFile);

    // REFRESH THE ACTIVE CHAT FILE TO KEEP ONLY NON SAVED TAIL

    $fp = fopen($activeChatFile, 'w');
    fwrite($fp, implode("\n", $curDisplayLineList));
}

//////////////////////////////////////////////////////////////////////////////

/**
 * Store $content in a buffer
 * add an html header if it's new buffer
 *
 * @param string $content content to bufferise
 * @param string $tmpFile filename to store the content
 */
function buffer($content, $tmpFile)
{
    if ( ! file_exists($tmpFile) )
    {
        $content = '<html>'."\n"
                 . '<head>'."\n"
                 . '<title>'.get_lang('Chat').' - '.get_lang('archive').'</title>'."\n"
                 . '</head>'."\n\n"
                 . '<body>'."\n"
                 . $content
                 ;
    }

    $fp = fopen($tmpFile, 'a');
    fwrite($fp, $content);
}
?>
