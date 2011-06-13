<?php // $Id: claro_init_footer.inc.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * CLAROLINE
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLKERNEL
 * @author      Claro Team <cvs@claroline.net>
 * @deprecated  since 1.9, use display.lib instead
 */

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

// this file can be called from within a function so we need to add the
// folowwing line !!!
$claroline = Claroline::getInstance();

if (!isset($hide_body) || $hide_body == false)
{
    echo "\n" . '</div>' . "\n"
        . '<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->' . "\n\n\n"
        ;
}

if ( get_conf('claro_brailleViewMode',false))
{
    echo $claroline->display->banner->render();
}

// don't display the footer text if requested, only display minimal html closing tags
if ( isset($hide_footer) && $hide_footer )
{
    $claroline->display->footer->hide();
} // if (!isset($hide_footer) || $hide_footer == false)

echo $claroline->display->footer->render();

if (claro_debug_mode())
{
    echo  claro_disp_debug_banner() .  "\n" ;
}

echo '</body>' . "\n"
    . '</html>' . "\n"
    ;
