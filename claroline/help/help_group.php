<?php // $Id: help_group.php 11782 2009-05-25 13:02:05Z dimitrirambout $
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Groups help');
$hide_banner = true;
$hide_footer = true;
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

$out = '';

$tpl = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/help_group.tpl.php' );

$out .= $tpl->render();

$claroline->setDisplayType(Claroline::POPUP);
$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>