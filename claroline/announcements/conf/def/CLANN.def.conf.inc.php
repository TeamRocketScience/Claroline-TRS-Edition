<?php //$Id: CLANN.def.conf.inc.php 9816 2008-02-19 13:49:35Z gregk84 $
if ( count( get_included_files() ) == 1 ) die( '---' );

// CONFIG HEADER

$conf_def['config_code'] = 'CLANN';
$conf_def['config_file'] = 'CLANN.conf.php';
$conf_def['config_name'] = 'Announcements';
$conf_def['config_class']= 'tool';

// CONFIG SECTIONS
$conf_def['section']['img_viewer']['label']='Portlet';
$conf_def['section']['img_viewer']['description']='Options for announcements portlet';
$conf_def['section']['img_viewer']['properties'] =
array ( 'announcementPortletMaxItems' );

// CONFIG PROPERTIES
$conf_def_property_list['announcementPortletMaxItems']
= array ('label'     => 'Max announcement number in portlet'
        ,'description' => 'Use 0 to display all'
        ,'default'   => '3'
        ,'type'      => 'integer'
        ,'container' => 'VAR'
        ,'acceptedValue' => array('min' => '0')
        );

?>