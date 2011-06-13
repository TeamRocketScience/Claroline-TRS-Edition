<?php // $Id: index.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * CLAROLINE
 *
 * Manage tools' introductions
 *
 * @version     $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLINTRO
 * @author      Claro Team <cvs@claroline.net>
 * @since       1.9
 */


// Reset session variables
$cidReset = true; // course id
$gidReset = true; // group id
$tidReset = true; // tool id

// Load Claroline kernel
require_once dirname(__FILE__) . '/../inc/claro_init_global.inc.php';

// Build the breadcrumb
$nameTools = get_lang('Headlines');

// Initialisation of variables and used classes and libraries
require_once get_module_path('CLTI').'/lib/toolintroductioniterator.class.php';

$introId            = (!empty($_REQUEST['introId'])?((int) $_REQUEST['introId']):(null));
$introCmd           = (!empty($_REQUEST['introCmd'])?($_REQUEST['introCmd']):(null));
$isAllowedToEdit    = claro_is_allowed_to_edit();
$output             = '';

set_current_module_label('CLINTRO');

// Init linker
FromKernel::uses('core/linker.lib');
ResourceLinker::init();

// Instanciate dialog box
$dialogBox = new DialogBox();



if (isset($introCmd) && $isAllowedToEdit)
{
    // Set linker's params
    if ($introId)
    {
        $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
            array('id' => (int) $introId));
        
        ResourceLinker::setCurrentLocator($currentLocator);
    }
    
    // CRUD
    if ($introCmd == 'rqAdd')
    {
        $toolIntro = new ToolIntro();
        $output .= $toolIntro->renderForm();
    }
    
    if ($introCmd == 'rqEd')
    {
        $toolIntro = new ToolIntro($introId);
        if($toolIntro->load())
        {
            $output .= $toolIntro->renderForm();
        }
    }
    
    if ($introCmd == 'exAdd')
    {
        $toolIntro = new ToolIntro();
        $toolIntro->handleForm();
        
        //TODO inputs validation
        
        // Manage ressources
        if ($toolIntro->save())
        {
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $toolIntro->getId() ) );
            
            $resourceList =  isset($_REQUEST['resourceList'])
                ? $_REQUEST['resourceList']
                : array()
                ;
            
            ResourceLinker::updateLinkList( $currentLocator, $resourceList );
            
            $dialogBox->success( get_lang('Introduction added') );
            
            // Notify that the introsection has been created
            $claroline->notifier->notifyCourseEvent('introsection_created', claro_get_current_course_id(), claro_get_current_tool_id(), $toolIntro->getId(), claro_get_current_group_id(), '0');
        }
    }
    
    if ($introCmd == 'exEd')
    {
        $toolIntro = new ToolIntro($introId);
        $toolIntro->handleForm();
        
        //TODO inputs validation
        
        if ($toolIntro->save())
        {
            $currentLocator = ResourceLinker::$Navigator->getCurrentLocator(
                array( 'id' => (int) $toolIntro->getId() ) );
            
            $resourceList =  isset($_REQUEST['resourceList'])
                ? $_REQUEST['resourceList']
                : array()
                ;
            
            ResourceLinker::updateLinkList( $currentLocator, $resourceList );
            
            $dialogBox->success( get_lang('Introduction modified') );
            
            // Notify that the introsection has been modified
            $claroline->notifier->notifyCourseEvent('introsection_modified', claro_get_current_course_id(), claro_get_current_tool_id(), $toolIntro->getId(), claro_get_current_group_id(), '0');
        }
    }
    
    if ($introCmd == 'exDel')
    {
        $toolIntro = new ToolIntro($introId);
        
        if ($toolIntro->delete())
        {
            $dialogBox->success( get_lang('Introduction deleted') );
            
            //TODO linker_delete_resource('CLINTRO_');
        }
    }
    
    // Modify rank and visibility
    if ($introCmd == 'exMvUp')
    {
        $toolIntro = new ToolIntro($introId);
        if($toolIntro->load())
        {
            if ($toolIntro->moveUp())
            {
                $dialogBox->success( get_lang('Introduction moved up') );
            }
            else
            {
                $dialogBox->error( get_lang('This introduction can\'t be moved up') );
            }
        }
    }
    
    if ($introCmd == 'exMvDown')
    {
        $toolIntro = new ToolIntro($introId);
        $toolIntro->load();
        if($toolIntro->load())
        {
            if ($toolIntro->moveDown())
            {
                $dialogBox->success( get_lang('Introduction moved down') );
            }
            else
            {
                $dialogBox->error( get_lang('This introduction can\'t be moved down') );
            }
        }
    }
    
    if ( $introCmd == 'mkVisible' || $introCmd == 'mkInvisible' )
    {
        $toolIntro = new ToolIntro($introId);
        if($toolIntro->load())
        {
            $toolIntro->swapVisibility();
            if ($toolIntro->save())
            {
                $dialogBox->success( get_lang('Introduction\' visibility modified') );
            }
            else
            {
                $dialogBox->error( get_lang('This introduction\'s visibility can\'t be modified') );
            }
        }
    }
}

// Display

$output .= $dialogBox->render();

$output .= '<p>'
         . '<a href="'
         . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] .'?introCmd=rqAdd')).'">'
         . '<img src="' . get_icon_url('default_new') . '" alt="' . get_lang('New introduction') . '" /> '
         . get_lang('New item').'</a>'
         . '</p>';

$toolIntroIterator = new ToolIntroductionIterator(claro_get_current_course_id());

if (!empty($toolIntroIterator))
{
    foreach ($toolIntroIterator as $toolIntro)
    {
        $output .= $toolIntro->render();
    }
}
else
{
    $output .= '<div class="HelpText">' . "\n"
             . get_block('blockIntroCourse') . "\n"
             . '</div>' . "\n";
}

// Append output
$claroline->display->body->appendContent($output);

// Render output
echo $claroline->display->render();