<?php // $Id: build_install_lang_file.php 11656 2009-03-05 09:29:35Z dimitrirambout $
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

set_time_limit (0);

/*
 * This script scans and retrieves all the language variables of an existing Claroline
 */

// include configuration and library file

include ('language.conf.php');
require_once ('language.lib.php');

require_once get_path('incRepositorySys') . '/lib/config.lib.inc.php';

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';

// get start time

$starttime = get_time();

// Start content

$nameTools = 'Extract variables from installation script';

$urlSDK = get_path('rootAdminWeb') . 'xtra/sdk/';
$urlTranslation = $urlSDK . 'translation_index.php';

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Translation Tools'), $urlTranslation );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('SDK'), $urlSDK );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

if( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'generate' )
{
    $files = array(
        get_path('rootSys') . 'claroline/install/index.php'
    );
    
    $total_var_count = 0;
    $languageVarList = array();
    foreach( $files as $file )
    { 
        echo "<h4>" . $file . "</h4>\n";
    
        // extract variables
    
        $scannedFileList = array(); // re init the scannedFileList for each new script
    
        $fileVarList = get_lang_vars_from_file($file);
    
        $sourceFile = file_get_contents($file);
        $tokenList  = token_get_all($sourceFile);
    
       // $languageVarList = detect_get_lang($tokenList);
    
        echo 'Found ' . count($fileVarList) . ' Variables<br />' . "\n";
        // display variables
    
        $var_count = 0;
    
        foreach($fileVarList as $varName) ++$var_count;
        $total_var_count += $var_count;
        echo 'Total: ' . $total_var_count;
    
        $languageVarList = array_merge($languageVarList, $fileVarList);
    }
    
    $languageVarList = array_unique($languageVarList);
    
    $installVarList = array();
    foreach( $languageVarList as $var )
    {
        $installVarList[$var] = $var;
    }
    // generate english file
    // - get vars from english install lang file
    // - merge the two
    // - generate new language file
    
    $_lang = array();
    
    $englishLangFile = get_path('rootSys') . 'claroline/lang/english/install.lang.php';
    
    if( file_exists($englishLangFile))
    {
        include($englishLangFile);
    }
    
    $completeInstallVarList = array_merge($installVarList, $_lang);
    
    
    $output = '<?php' . "\n";
    foreach( $completeInstallVarList as $key => $value )
    { 
        $output .= build_translation_line_file($key,$value);
    }
    $output .= '?>';
    
    // write to file
    file_put_contents($englishLangFile, $output);
    
    
    echo "<p>Total variables: " . $total_var_count . "</p>";
    
    // end time
    $endtime = get_time();
    $totaltime = ($endtime - $starttime);
    
    echo "<p><em>Execution time: $totaltime</em></p>\n";
}
else
{
    echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=generate">'
    .     'Generate it, it will overwrite previous /lang/english/install.lang.php file'
    .    '</a>';
}
// display footer

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>