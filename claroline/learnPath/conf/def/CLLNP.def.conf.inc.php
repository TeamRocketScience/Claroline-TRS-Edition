<?php
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This file describe the parameter for CLDOC config file
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 *
 * @package CLLNP
 *
 */

// CONFIG HEADER

$conf_def['config_code'] = 'CLLNP';
$conf_def['config_file'] = 'CLLNP.conf.php';
$conf_def['config_name'] = 'Learning path';
$conf_def['config_class']= 'tool';

// CONFIG SECTIONS
$conf_def['section']['quota']['label']='Quota';
$conf_def['section']['quota']['description']='Disk space allowed for import learning path';
$conf_def['section']['quota']['properties'] =
array ( 'maxFilledSpace_for_import'
      );
      
// CONFIG PROPERTIES
$conf_def_property_list['maxFilledSpace_for_import']
= array ('label'     => 'Quota for courses'
        ,'description' => 'Disk space allowed to import scorm package'
        ,'default'   => '100000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1024')
        );
?>