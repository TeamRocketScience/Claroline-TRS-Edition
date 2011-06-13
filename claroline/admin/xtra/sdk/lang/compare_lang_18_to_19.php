<?php // $Id: compare_lang_18_to_19.php 11656 2009-03-05 09:29:35Z dimitrirambout $
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

require '../../../../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

/*
 * This script retrieves all the existing translation of an existing Claroline
 * It scans all the files of the 'lang' directory and stored the get_lang(' variables')
 * content into a mySQL database.
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');

// get start time

$starttime = get_time();

$dialogBox = new DialogBox();


/*
 * Compare
 */
$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';

$pathSDK = get_path('rootSys') . 'claroline/admin/xtra/sdk/';
$path_lang = get_path('rootSys') . 'claroline/lang/';

// copy the last 1.8 english complete in this dir
$pathTo18Complete = $pathSDK . 'lang/complete.lang.php';
// missing for 1.9 should be empty
$pathTo19Complete = $path_lang . 'english/complete.lang.php';
$pathTo19Missing = $path_lang . 'english/missing.lang.php';

if( ! file_exists($pathTo18Complete) )
{
    $dialogBox->error('Claroline 1.8 english complete is missing, should be located at ' . $pathTo18Complete);
}
elseif( ! file_exists($pathTo19Complete) )
{
    $dialogBox->error('Claroline 1.9 english complete is missing, should be located at ' . $pathTo19Complete);
}
elseif( !file_exists($pathTo19Missing) )
{
    $dialogBox->error('Claroline 1.9 english complete is missing, should be located at ' . $pathTo19Missing);
}
else
{
    /*
     * Compare
     */
    include $pathTo18Complete;
    $_lang18 = $_lang;
    
    $_lang = null;
    
    include $pathTo19Complete;
    $_lang19 = $_lang;
    
    $_lang = null;
    
    include $pathTo19Missing;
    
    $_lang19 = array_merge($_lang19, $_lang);
    
    /*
     * New keys
     */
    $diffNewKeys = array();
    
    foreach( $_lang19 as $key => $translation )
    {
        // find keys that did not exist in 1.8 
        // new 1.9 vars or renamed vars
        if( ! array_key_exists($key, $_lang18) )
        {
            $diffNewKeys[$key] = $translation;
        }
    }
    
    /*
     * Disappeared keys
     */
    $diffDisappearedKeys = array();
    
    foreach( $_lang18 as $key => $translation )
    {
        // find key that existed in 1.8 but exists no more in 1.9
        if( ! array_key_exists($key, $_lang19) )
        {
            $diffDisappearedKeys[$key] = $translation;
        }
    }
    
    /*
     * Identical translation and different keys
     */
    $diffSameTransOtherKey = array();
    $_lang18flip = array_flip($_lang18);
    $_lang19flip = array_flip($_lang19);
    foreach( $_lang19flip as $translation => $key )
    {
        // find translation
        if( array_key_exists($translation, $_lang18flip) && $key != $_lang18flip[$translation] )
        {
            // $_lang18flip[$translation] is the 1.8 key
            $diffSameTransOtherKey[$key] = $_lang18flip[$translation];

        }
    }
    
    
}

/*
 * Output
 */
$out = '';

$nameTools = 'Compare variables from 1.8 to 1.9 english complete';

$out .= claro_html_tool_title($nameTools);

$out .= $dialogBox->render();

/*
 * Output $diffNewKeys
 */
$out .= '<h4>In 1.9 but not in 1.8 : '.count($diffNewKeys).'</h4>';
$out .= '<p>'.count($diffNewKeys).'</p>';
foreach ( $diffNewKeys as $key => $translation)
{
    //$out .= $key . ' : ' . htmlspecialchars($translation) . '<br />' . "\n";
    $out .= $key . '<br />' . "\n";;
}

$out .= '<hr />' . "\n";

/*
 * Output diffDisappearedKeys
 */
$out .= '<h4>In 1.8 but not in 1.9 : '.count($diffDisappearedKeys).'</h4>';

foreach ( $diffDisappearedKeys as $key => $translation)
{
    //$out .= $key . ' : ' . htmlspecialchars($translation) . '<br />' . "\n";
    $out .= $key . '<br />' . "\n";;
}

$out .= '<hr />' . "\n";

/*
 * Output get same translation in differents var
 */
$out .= '<h4>Same translation but different keys in 1.9 : '.count($diffSameTransOtherKey).'</h4>';
$out .= '<p>key 1.8 => key 1.9</p>' . "\n";

foreach ( $diffSameTransOtherKey as $key19 => $key18)
{
    $out .= htmlspecialchars($key18) . ' => ' . htmlspecialchars($key19) . '<br />' . "\n";
    //$out .= $key . '<br />' . "\n";;
}

$out .= '<hr />' . "\n";

// get and display end time

$endtime = get_time();
$totaltime = ($endtime - $starttime);

$out .= "<p><em>Execution time: $totaltime</em></p>\n"
.    '<a href="'.$urlTranslation.'">&lt;&lt; Back</a>' . "\n";

/*
 * Display
 */

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$claroline->display->body->appendContent($out);

echo $claroline->display->render();
?>