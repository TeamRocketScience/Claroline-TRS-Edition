<?php // $Id: translation_index.php 11775 2009-05-20 15:30:11Z dimitrirambout $
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
include 'lang/language.conf.php';

$nameTools = get_lang('Translation Tools');
$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$table_exists = TRUE;

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// table

$tbl_used_lang = '`' . get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . TABLE_USED_LANG_VAR . '`';
$tbl_used_translation =  '`' . get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . TABLE_TRANSLATION . '`';

$sql1 = " select count(*) from " . $tbl_used_lang;
$sql2 = " select count(*) from " . $tbl_used_translation;

mysql_query($sql1);
if ( mysql_errno() == 1146 ) $table_exists = FALSE;

mysql_query($sql2);
if ( mysql_errno() == 1146 ) $table_exists = FALSE;

// DISPLAY

// Deal with interbredcrumps  and title variable

ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$tpl = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/translation_index.tpl.php' );

$tpl->assign('table_exists', $table_exists);

$out = '';

$out .= claro_html_tool_title($nameTools);
$out .= $tpl->render();

$claroline->display->body->appendContent($out);

echo $claroline->display->render();