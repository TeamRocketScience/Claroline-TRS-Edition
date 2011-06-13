<?php // $Id: bottomModule.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE 
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 * @subpackage navigation
 *
 * DESCRIPTION:
 * ************
 * This script creates the bottom frame needed when we browse a module that needs to use frame
 * This appens when the module is SCORM (@link http://www.adlnet.org )or made by the user with his own html pages.
 *
 */
require '../../inc/claro_init_global.inc.php';
// header
$hide_banner = TRUE;
$hide_body = TRUE;

// Turn off session lost
$warnSessionLost = false ;

include get_path('incRepositorySys') . '/claro_init_header.inc.php';
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>
