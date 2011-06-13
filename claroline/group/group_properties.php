<?php // $Id: group_properties.php 12979 2011-03-15 14:31:50Z zefredz $
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision: 12979 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 *
 * @package CLGRP
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
$tlabelReq = 'CLGRP';
require '../inc/claro_init_global.inc.php';

// $_groupProperties = claro_get_main_group_properties(claro_get_current_course_id());

include_once get_path('incRepositorySys') . '/lib/group.lib.inc.php';

// display login form
if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

// check user right
if ( ! claro_is_allowed_to_edit() )
{
    claro_die(get_lang("Not allowed"));
}

$nameTools = get_lang("Groups settings");
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Groups'), 'group.php' );

$_groupProperties = claro_get_main_group_properties(claro_get_current_course_id());


// session_register('_groupProperties');
$_SESSION['_groupProperties'] =& $_groupProperties;

$claroline->display->body->appendContent( claro_html_tool_title(
    array(
        'supraTitle' => get_lang("Groups"),
        'mainTitle' => $nameTools
    )
) );

$tpl = new CoreTemplate('group_properties.tpl.php');

$tpl->assign( 'groupToolList', get_group_tool_list() );
$tpl->assign( 'nbGroupPerUser', $_groupProperties['nbGroupPerUser'] );
$tpl->assign( 'registrationAllowedInGroup', $_groupProperties ['registrationAllowed'] );
$tpl->assign( 'unregistrationAllowedInGroup',
    isset( $_groupProperties ['unregistrationAllowed'] )
    ? $_groupProperties ['unregistrationAllowed']
    : false
);
$tpl->assign( 'groupPrivate', $_groupProperties ['private'] );
$tpl->assign( 'tools', $_groupProperties ['tools'] );

$claroline->display->body->appendContent($tpl->render());

echo $claroline->display->render();

?>