<?php // $Id: redirector.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * CLAROLINE
 *
 * @version     1.8 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/index.php/CLUSR
 * @package     CLKERNEL
 * @author      Claro Team <cvs@claroline.net>
 */

die('---');

// duplicated from claro_main.lib.php to avoid loading unwanted functions.
function http_response_splitting_workaround( $str )
{
    $pattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';
    return preg_replace( $pattern, '', $str );
}

$url = isset( $_REQUEST['url'] )
    ? http_response_splitting_workaround( $_REQUEST['url'] )
    : '../'
    ;

header( 'Location: ' . $url );

?>