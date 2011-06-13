<?php // $Id: index.php 12924 2011-03-03 14:45:00Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * User desktop index
 *
 * @version      1.9 $Revision: 12924 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package      DESKTOP
 * @author       Claroline team <info@claroline.net>
 *
 */

// reset course and groupe
$cidReset = true;
$gidReset = true;
$uidRequired = true;

// load Claroline kernel
require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';

if( ! claro_is_user_authenticated() ) claro_disp_auth_form();

// load libraries
uses('user.lib', 'utils/finder.lib');
require_once dirname(__FILE__) . '/lib/portlet.lib.php';

FromKernel::uses('display/userprofilebox.lib');

$dialogBox = new DialogBox();

define( 'KERNEL_PORTLETS_PATH', dirname( __FILE__ ) . '/lib/portlet' );

// Load and register (if needed) portlets
try
{
    $portletList = new PortletList;
    
    $fileFinder = new Claro_FileFinder_Extension( KERNEL_PORTLETS_PATH, '.class.php', false );

    foreach ( $fileFinder as $file )
    {
        // Require portlet file
        require_once $file->getPathname();

        // Compute portlet class name from file name
        $pos = strpos( $file->getFilename(), '.' );
        $className = substr( $file->getFilename(), '0', $pos );

        // Load portlet from database
        $portletInDB = $portletList->loadPortlet( $className );

        if( !$portletInDB )
        {
            if( class_exists($className) )
            {
                $portlet = new $className();
                
                $portletList->addPortlet( $className, $portlet->renderTitle() );
            }
        }
        else
        {
            continue;
        }
    }
    
    $moduleList = get_module_label_list();
    
    foreach ( $moduleList as $moduleId => $moduleLabel )
    {
        $portletPath = get_module_path( $moduleLabel )
            . '/connector/desktop.cnr.php'
            ;
        
        if ( file_exists( $portletPath ) )
        {
            require_once $portletPath;
            
            $className = "{$moduleLabel}_Portlet";
            
            $portletInDB = $portletList->loadPortlet($className);

            // si present en db on passe
            if( !$portletInDB )
            {
                if ( class_exists($className) )
                {
                    $portlet = new $className();
                    $portletList->addPortlet( $className, $portlet->renderTitle() );
                }
            }
            
            load_module_config($moduleLabel);
            Language::load_module_translation($moduleLabel);
        }
    }
}
catch (Exception $e)
{
    $dialogBox->error( get_lang('Cannot load portlets') );
    pushClaroMessage($e->__toString());
}

// Generate Output from Portlet

$outPortlet = '';

$portletList = $portletList->loadAll( true );

if ( is_array( $portletList ) )
{
    foreach ( $portletList as $portlet )
    {
        // load portlet
        if( ! class_exists( $portlet['label'] ) )
        {
            pushClaroMessage("User desktop : class {$portlet['label']} not found !");
            continue;
        }
        
        $portlet = new $portlet['label']();
    
        if( ! $portlet instanceof UserDesktopPortlet )
        {
            pushClaroMessage("{$portlet['label']} is not a valid user desktop portlet !");
            continue;
        }
        
        $outPortlet .= $portlet->render();
    }
}
else
{
    $dialogBox->error(get_lang('Cannot load portlet list'));
}

// Generate Script Output

$jsloader = JavascriptLoader::getInstance();
$jsloader->load('jquery');
$jsloader->load('claroline.ui');

$cssLoader = CssLoader::getInstance();
$cssLoader->load('desktop','all');

$template = new CoreTemplate('user_desktop.tpl.php');

$userProfileBox = new UserProfileBox(false);

$template->assign('dialogBox', $dialogBox);
$template->assign('userProfileBox', $userProfileBox);
$template->assign('outPortlet', $outPortlet);

$claroline->display->body->appendContent($template->render());

echo $claroline->display->render();
