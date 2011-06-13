<?php // $Id: CLCHT.def.conf.inc.php 12923 2011-03-03 14:23:57Z abourguignon $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This file describe the parameter for CLCHT config file
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Config
 * @see http://www.claroline.net/wiki/index.php/CLCHT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLCHT
 */

$conf_def['config_file']='CLCHT.conf.php';
$conf_def['config_code']='CLCHT';
$conf_def['config_name']='Chat';
$conf_def['config_class']='tool';


$conf_def['section']['display']['label']='Display Settings';
$conf_def['section']['display']['properties'] =
array ( 'refresh_display_rate' , 
        'max_nick_length' , 
        'max_line_to_display'
      );

$conf_def['section']['advanced']['label']='Advanced settings';
$conf_def['section']['advanced']['properties'] =
array ( 'max_line_in_file' );


$conf_def_property_list['refresh_display_rate'] =
array ( 'label'       => 'Refresh time'
      , 'description' => 'Time to automatically refresh the user screen. Each refresh is a request to your server.'."\n"
                       . 'Too low value can be hard for your server. Too high value can be hard for user.'."\n"
      , 'default'     => '10'
      , 'unit'        => 'seconds'
      , 'acceptedValue' => array( 'min' => 4, 'max' => 90)
      , 'type'        => 'integer'
      );

$conf_def_property_list['max_line_to_display'] =
array ( 'label'         => 'Maximum conversation lines'
      , 'description'   => 'Maximum conversation lines displayed to the user. '
      , 'technicalInfo'   => 'Maximum line diplayed to the user screen. As the active chat file is
      regularly shrinked (see max_line_in_file), keeping this parameter smaller
      than  $max_line_in_file allows smooth display (where no big line chunk are
      removed when the excess line from the active chat file are buffered on fly'

      , 'default'       => '20'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 120)
      , 'unit'          => 'lines'
      , 'type'          => 'integer'
      );

$conf_def_property_list['max_line_in_file'] =
array ( 'label'       => 'Maximum conversation lines in chat file'
      , 'description' => 'Maximum lines in the active chat file. '
                        .'For performance, it\'s interesting '
                        .'to not work with too big file.'
      , 'default'     => '200'
      , 'unit'        => 'lines'
      , 'type'        => 'integer'
      );

$conf_def_property_list['max_nick_length'] =
array ( 'label'       => 'Maximum lengh for a nick'
      , 'description' => 'If the name and the firstname are longer than this value, the script reduce it.'."\n"
                       . 'For revelance, it\'s interesting to not work with to little value'
      , 'default'     => '20'
      , 'unit'        => 'characters'
      , 'acceptedValue' => array( 'min' => 5, 'max' => 60)
      , 'type'        => 'integer'
      );

?>
