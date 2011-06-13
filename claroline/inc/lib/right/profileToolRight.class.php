<?php // $Id: profileToolRight.class.php 12923 2011-03-03 14:23:57Z abourguignon $

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * Class to manage profile and tool right (none, user, manager)
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLMAIN
 * @author      Claro Team <cvs@claroline.net>
 */

require_once dirname(__FILE__) . '/constants.inc.php';
require_once dirname(__FILE__) . '/profileToolAction.class.php';

class RightProfileToolRight extends RightProfileToolAction
{

    /**
     * Set the tool right (none, user, manager)
     *
     * @param integer $toolId tool identifier
     * @param string $right the right value
     */

    function setToolRight($toolId,$right)
    {
        if ( $right == 'none' )
        {
            $this->setAction($toolId,'read',false);
            $this->setAction($toolId,'edit',false);
        }
        elseif ( $right == 'user' )
        {
            $this->setAction($toolId,'read',true);
            $this->setAction($toolId,'edit',false);
        }
        elseif ( $right == 'manager' )
        {
            $this->setAction($toolId,'read',true);
            $this->setAction($toolId,'edit',true);
        }
    }

    /**
     * Get the tool right (none, user, manager)
     *
     * @param integer $toolId tool identifier
     */

    function getToolRight($toolId)
    {
        $readAction = (bool) $this->getAction($toolId,'read');
        $manageAction = (bool) $this->getAction($toolId,'edit');

        if ( $readAction ==  false && $manageAction == false )
        {
            return 'none';
        }
        elseif ( $readAction == true && $manageAction == false )
        {
            return 'user';
        }
        else
        {
            return 'manager';
        }
    }

    /**
     * Set right of the tool list
     */

    function setToolListRight($toolList,$right)
    {
        foreach ( $toolList as $toolId )
        {
             $this->setToolRight($toolId,$right);
        }
    }
}
