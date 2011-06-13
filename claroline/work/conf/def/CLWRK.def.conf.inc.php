<?php // $Id: CLWRK.def.conf.inc.php 12923 2011-03-03 14:23:57Z abourguignon $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This file describe the parameter for assignment tool
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLWRK
 *
 */
// TOOL
$conf_def['config_code'] = 'CLWRK';
$conf_def['config_file'] = 'CLWRK.conf.php';
$conf_def['config_name'] = 'Assignments';
$conf_def['config_class']= 'tool';

$conf_def['section']['main']['label']      = 'Main';
$conf_def['section']['main']['properties'] =
array ( 'confval_def_sub_vis_change_only_new', 'open_submitted_file_in_new_window', 'show_only_author', 'mail_notification', 'automatic_mail_notification', 'allow_download_all_submissions', 'allow_work_event_generation', 'assignmentsPerPage', 'usersPerPage' );

$conf_def['section']['quota']['label']      = 'Quota';
$conf_def['section']['quota']['description']= 'Disk space allowed for submitted files';
$conf_def['section']['quota']['properties'] =
array ( 'max_file_size_per_works' );


//PROPERTIES

$conf_def_property_list['confval_def_sub_vis_change_only_new'] =
array ('label'     => 'Assignment property "Default works visibility" acts'
        ,'description' => 'Sets how the assignment property "default works visibility" acts.  It will change the visibility of all the new submissions or it will change the visibility of all submissions already done in the assignment and the new one. '
        ,'default'   => TRUE
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'only for new works', 'FALSE'=>'for current and new works' )
        );

$conf_def_property_list['open_submitted_file_in_new_window'] =
array ( 'description' => 'When users click on a submitted file, it opens a new window'
      , 'label'       => 'New window for submitted files'
      , 'default'     => FALSE
      , 'type'        => 'boolean'
      , 'acceptedValue' => array ('TRUE'=>'Yes'
                               ,'FALSE'=>'No'
                               )
      , 'display'     => TRUE
      , 'readonly'    => FALSE
      );

$conf_def_property_list['show_only_author'] =
array ('label'     => 'Show only author submissions'
        ,'description' => 'Sets if user can see only his own submissions (or those from his groups) or if he can see every visible submission.'
        ,'default'   => FALSE
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Only his own submissions', 'FALSE'=>'All visible submissions' )
        );

$conf_def_property_list['mail_notification'] =
array ('label'     => 'Mail notification'
        ,'description' => 'If activated, allows to define a notification policy for assignment submissions and feedbacks.'
        ,'default'   => FALSE
        ,'type'      => 'boolean'
        ,'display'       => TRUE
        ,'readonly'      => FALSE
        ,'acceptedValue' => array ( 'TRUE'=> 'Yes', 'FALSE'=>'No' )
        );

$conf_def_property_list['automatic_mail_notification'] =
array ('label' => 'Mail notification mode' 
        ,'description' => 'Are notification emails sent automatically or on demand (configurable in the assignement tool).' 
        ,'default' => FALSE 
        ,'type' => 'boolean' 
        ,'display' => TRUE 
        ,'readonly' => FALSE 
        ,'acceptedValue' => array ( 'TRUE' => 'Automatic', 'FALSE' => 'On demand' ) );        

$conf_def_property_list['assignmentsPerPage'] =
array ('label'         => 'Number of assignment per page'
      ,'description'   => 'For assignments list'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => '20'
      ,'type'          => 'integer'
      ,'unit'          => 'assignments'
      );

$conf_def_property_list['usersPerPage'] =
array ('label'         => 'Number of user per page'
      ,'description'   => 'For submissions list'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => '20'
      ,'type'          => 'integer'
      ,'unit'          => 'users'
      );

$conf_def_property_list['allow_download_all_submissions'] =
array ('label'         => 'Allow teacher to download all submissions'
      ,'description'   => 'Add a "Download all submissions" link in the teacher commands'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => FALSE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ( 'TRUE'=> 'Yes', 'FALSE'=>'No' )
      );

$conf_def_property_list['max_file_size_per_works'] =
array ('label'         => 'Maximum size for an assignment'
      ,'description'   => 'Maximum size of a document that a user can upload'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => '3000000' // 3mo
      ,'type'          => 'integer'
      ,'unit'          => 'bytes'
      );

$conf_def_property_list['allow_work_event_generation'] =
array ('label'         => 'Generate an event in the calendar'
      ,'description'   => 'Automatically insert an event in the calendar at the submission date'
      ,'display'       => TRUE
      ,'readonly'      => FALSE
      ,'default'       => TRUE
      ,'type'          => 'boolean'
      ,'acceptedValue' => array ( 'TRUE'=> 'Yes', 'FALSE'=>'No' )
      );

?>
