<?php //$Id: CLDOC.def.conf.inc.php 12923 2011-03-03 14:23:57Z abourguignon $
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
 * @package CLDOC
 *
 * @author Claro Team <cvs@claroline.net>
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 */

// CONFIG HEADER

$conf_def['config_code'] = 'CLDOC';
$conf_def['config_file'] = 'CLDOC.conf.php';
$conf_def['config_name'] = 'Documents and Links';
$conf_def['config_class']= 'tool';

// CONFIG SECTIONS
$conf_def['section']['main']['label']='Main';
$conf_def['section']['main']['description']='';
$conf_def['section']['main']['properties'] =
array ( 'openNewWindowForDoc'
       , 'cldoc_allowAnonymousToDownloadFolder');


// CONFIG SECTIONS
$conf_def['section']['quota']['label']='Quota';
$conf_def['section']['quota']['description']='Disk space allowed for documents';
$conf_def['section']['quota']['properties'] =
array ( 'maxFilledSpace_for_course'
      , 'maxFilledSpace_for_groups'
      );

// CONFIG PROPERTIES
$conf_def_property_list['maxFilledSpace_for_course']
= array ('label'     => 'Quota for courses'
        ,'description' => 'Disk space allowed to each course'
        ,'default'   => '100000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1024')
        );

$conf_def_property_list['maxFilledSpace_for_groups']
= array ('label'     => 'Quota for groups'
        ,'description' => 'Disk space allowed to each group'
        ,'default'   => '1000000'
        ,'unit'      => 'bytes'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1024')
        );

// IMAGE VIEWER

$conf_def['section']['img_viewer']['label']='Image Viewer';
$conf_def['section']['img_viewer']['description']='Display options for Image Viewer';
$conf_def['section']['img_viewer']['properties'] =
array ( 'thumbnailWidth'
      , 'numberOfRows'
      , 'numberOfCols'
      );

// CONFIG PROPERTIES
$conf_def_property_list['thumbnailWidth']
= array ('label'     => 'Thumbnail width'
    // ,'description' => ''
        ,'default'   => '75'
        ,'unit'      => 'pixels'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '5')
        );

$conf_def_property_list['numberOfRows']
= array ('label'     => 'Number of rows'
        ,'description' => 'Number of rows displayed per page'
        ,'default'   => '3'
        ,'unit'      => 'rows'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '1')
        );

$conf_def_property_list['numberOfCols']
= array ('label'     => 'Number of columns'
        ,'description' => 'Number of columns displayed per page'
        ,'default'   => '4'
        ,'unit'      => 'columns'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '3')
        );

$conf_def_property_list['openNewWindowForDoc'] =
array ( 'description' => 'When users click on a document, it opens a new window'
      , 'label'       => 'New window for documents'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                               ,'FALSE'=>'No'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['cldoc_allowAnonymousToDownloadFolder'] =
array ( 'description' => 'This option can be used to prevent web crawlers to download an archive of the folder'
      , 'label'       => 'Allow download of folder by anonymous users'
      , 'default'     => TRUE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                               ,'FALSE'=>'No'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );
