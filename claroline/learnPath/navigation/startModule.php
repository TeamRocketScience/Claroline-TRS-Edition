<?php // $Id: startModule.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 * @subpackage navigation
 *
  * This script is the main page loaded when user start viewing a module in the browser.
  * We define here the frameset containing the launcher module (SCO if it is a SCORM conformant one)
  * and a top and bottom frame to display the claroline banners.
  * If the module is an exercise of claroline, no frame is created,
  * we redirect to exercise_submit.php page in a path mode
  *
  */

/*======================================
       CLAROLINE MAIN
  ======================================*/

require '../../inc/claro_init_global.inc.php';

// Tables names
/*
 * DB tables definition
 */
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;

// lib of this tool
require_once(get_path('incRepositorySys')."/lib/learnPath.lib.inc.php");

if(isset ($_GET['viewModule_id']) && $_GET['viewModule_id'] != '')
    $_SESSION['module_id'] = $_GET['viewModule_id'];

// SET USER_MODULE_PROGRESS IF NOT SET
if(claro_is_user_authenticated()) // if not anonymous
{
    // check if we have already a record for this user in this module
    $sql = "SELECT COUNT(LPM.`learnPath_module_id`)
            FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP, `".$TABLELEARNPATHMODULE."` AS LPM
           WHERE UMP.`user_id` = '" . (int)claro_get_current_user_id() . "'
             AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
             AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
             AND LPM.`module_id` = ". (int)$_SESSION['module_id'];
    $num = claro_sql_query_get_single_value($sql);

    $sql = "SELECT `learnPath_module_id`
            FROM `".$TABLELEARNPATHMODULE."`
           WHERE `learnPath_id` = ". (int)$_SESSION['path_id']."
             AND `module_id` = ". (int)$_SESSION['module_id'];
    $learnPathModuleId = claro_sql_query_get_single_value($sql);

    // if never intialised : create an empty user_module_progress line
    if( !$num || $num == 0 )
    {
        $sql = "INSERT INTO `".$TABLEUSERMODULEPROGRESS."`
                ( `user_id` , `learnPath_id` , `learnPath_module_id`, `suspend_data` )
                VALUES ( " . (int)claro_get_current_user_id() . " , ". (int)$_SESSION['path_id']." , ". (int)$learnPathModuleId.", '')";
        claro_sql_query($sql);
    }
}  // else anonymous : record nothing !


// Get info about launched module

$sql = "SELECT `contentType`,`startAsset_id`
          FROM `".$TABLEMODULE."`
         WHERE `module_id` = ". (int)$_SESSION['module_id'];

$module = claro_sql_query_get_single_row($sql);

$sql = "SELECT `path`
               FROM `".$TABLEASSET."`
              WHERE `asset_id` = ". (int)$module['startAsset_id'];

$assetPath = claro_sql_query_get_single_value($sql);

// Get path of file of the starting asset to launch

$withFrames = false;

$moduleStartAssetPage = '';

switch ($module['contentType'])
{
    case CTDOCUMENT_ :
        if(claro_is_user_authenticated())
        {
            // if credit was already set this query changes nothing else it update the query made at the beginning of this script
            $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."`
                       SET `credit` = 1,
                           `raw` = 100,
                           `lesson_status` = 'completed',
                           `scoreMin` = 0,
                           `scoreMax` = 100
                     WHERE `user_id` = " . (int)claro_get_current_user_id() . "
                       AND `learnPath_module_id` = ". (int)$learnPathModuleId;

            claro_sql_query($sql);
        } // else anonymous : record nothing

        $startAssetPage = urlencode($assetPath);
        $moduleStartAssetPage = claro_get_file_download_url( $startAssetPage );

          $withFrames = true;
        break;

    case CTEXERCISE_ :
        // clean session vars of exercise
        unset($_SESSION['serializedExercise']);
        unset($_SESSION['serializedQuestionList']);
        unset($_SESSION['exeStartTime'    ]);

        $_SESSION['inPathMode'] = true;
        $startAssetpage = get_module_url('CLQWZ') . '/exercise_submit.php';
        $moduleStartAssetPage = $startAssetpage . '?exId=' . $assetPath;
        break;
    case CTSCORM_ :
        // real scorm content method
        $startAssetPage = $assetPath;
        $modulePath     = 'path_' . $_SESSION['path_id'];
        $moduleStartAssetPage = get_path('coursesRepositoryWeb')
        .   claro_get_course_path()
        .   '/scormPackages/'
        .   $modulePath
        .   $startAssetPage;
        break;
    case CTCLARODOC_ :
        break;
} // end switch

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>

  <head>

<?php
   // add the update frame if this is a SCORM module
   if ( $module['contentType'] == CTSCORM_ )
   {

      include("scormAPI.inc.php");
      echo '<frameset border="0" cols="0,20%,80%" frameborder="no">
            <frame src="updateProgress.php" name="upFrame">';

   }
   else
   {
      echo '<frameset border="0" cols="20%,80%" frameborder="yes">';
   }
?>
    <frame src="tableOfContent.php" name="tocFrame" />
    <frame src="<?php echo $moduleStartAssetPage; ?>" name="scoFrame" />

    </frameset>
  <noframes>
<body>
<?php
  echo get_lang('Your browser cannot see frames.');
?>
</body>
</noframes>
</html>
