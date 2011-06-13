<?php  // $Id: help_claroline.php 11779 2009-05-20 15:31:38Z dimitrirambout $
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Claroline help');
$hide_banner = true;
$hide_footer = true;

$out = '';

$tpl = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/help_claroline.tpl.php' );

$out .= $tpl->render();

$claroline->setDisplayType(Claroline::POPUP);
$claroline->display->body->appendContent($out);

echo $claroline->display->render();