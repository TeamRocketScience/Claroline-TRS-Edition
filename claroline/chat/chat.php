<?php // $Id: chat.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * Build the frameset for chat.
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
 * @author Christophe Gesche <moosh@claroline.net>
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 */

$tlabelReq = 'CLCHT';

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ( ! claro_is_course_allowed() && ! claro_is_user_authenticated() ) ) claro_disp_auth_form(true);

$_course = claro_get_current_course_data();
$nameTools  = get_lang('Chat');

$titlePage = '';

if(!empty($nameTools))
{
  $titlePage .= $nameTools.' - ';
}

if(!empty($_course['officialCode']))
{
  $titlePage .= $_course['officialCode'].' - ';
}
$titlePage .= get_conf('siteName');

// Redirect previously sent paramaters in the correct subframe (messageList.php)
$paramList = array();

if ( isset($_REQUEST['gidReset']) && $_REQUEST['gidReset'] == TRUE )
{
    $paramList[] = 'gidReset=1';
}

if ( isset($_REQUEST['gidReq']) )
{
    $paramList[] = 'gidReq='.$_REQUEST['gidReq'];
}

if (is_array($paramList))
{
    $paramLine = '?'.implode('&', $paramList);
}


?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head><title><?php echo $titlePage; ?></title></head>

    <frameset rows="215,*,120" marginwidth="0" frameborder="yes">
        <frame src="chat_header.php" name="topBanner" scrolling="no">
        <frame src="messageList.php<?php echo $paramLine ?>#final" name="messageList">
        <frame src="messageEditor.php" name="messageEditor" scrolling="no">
    </frameset>

</html>
