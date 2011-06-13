<?php // $Id: index.php 11768 2009-05-19 14:30:25Z dimitrirambout $
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$cidReset=true;
$gidReset=true;

require '../../../inc/claro_init_global.inc.php';
require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';

$nameTools = get_lang('SDK');

// SECURITY CHECK

if (!claro_is_platform_admin()) claro_disp_auth_form();

// DISPLAY

// Deal with interbredcrumps  and title variable
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$out = '';

$out .= claro_html_tool_title($nameTools);

$out .= '<p><img src="lang/language.png" style="vertical-align: middle;" alt="" /> <a href="translation_index.php">' . get_lang('Translation Tools') . '</a></p>';

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>