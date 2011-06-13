<?php // $Id: phptemplate.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * PHP-based templating system
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     display
 */

// load helpers and output buffering libs
FromKernel::uses ( 'core/helpers.lib', 'display/ob.lib' );

/**
 * Simple PHP-based template class
 */
class PhpTemplate implements Display
{
    protected $_templatePath;
    
    /**
     * Constructor
     * @param   string $templatePath path to the php template file
     */
    public function __construct( $templatePath )
    {
        $this->_templatePath = $templatePath;
    }
    
    /**
     * Assign a value to a variable
     * @param   string $name
     * @param   mixed $value
     */
    public function assign( $name, $value )
    {
        $this->$name = $value;
    }
    
    /**
     * Render the template
     * @return  string
     * @throws  Exception if file not found or error/exception in the template
     */
    public function render()
    {
        if ( file_exists( $this->_templatePath ) )
        {
            $claroline = Claroline::getInstance();
            claro_ob_start();
            include $this->_templatePath;
            $render = claro_ob_get_contents();
            claro_ob_end_clean();
            
            return $render;
        }
        else
        {
            throw new Exception("Template file not found {$this->_templatePath}");
        }
    }
    
    /**
     * Show a block in the template given its name 
     * (ie set the variable with the block name to true)
     * @param   string $blockName
     */
    public function showBlock( $blockName )
    {
        $this->$blockName = true;
    }
    
    /**
     * Hide a block in the template given its name 
     * (ie set the variable with the block name to false)
     * @param   string $blockName
     */
    public function hideBlock( $blockName )
    {
        $this->$blockName = false;
    }
}

/**
 * Extended PHP-based template class with preloaded variables for
 *  - current course
 *  - current user
 * Search for template files in platform/templates and in claroline/inc/templates
 * @throws  Exception if template file not found
 */
class CoreTemplate extends PhpTemplate
{
    /**
     * @param   string $template name of the template
     */
    public function __construct( $template )
    {
        $template = secure_file_path( $template );
        
        $customTemplatePath = get_path('rootSys') . '/platform/templates/'.$template;
        $defaultTemplatePath = get_path('includePath') . '/templates/'.$template;
        
        if ( file_exists( $customTemplatePath ) )
        {
            parent::__construct( $customTemplatePath );
        }
        elseif ( file_exists( $defaultTemplatePath ) )
        {
            parent::__construct( $defaultTemplatePath );
        }
        else
        {
            throw new Exception("Template not found {$template} "
                . "at custom location {$customTemplatePath} "
                . "or default location {$defaultTemplatePath} !");
        }
        
        if ( claro_is_in_a_course() )
        {
            $this->course = claro_get_current_course_data();
        }
        
        if ( claro_is_user_authenticated() )
        {
            $this->user = claro_get_current_user_data();
        }
    }
}

/**
 * Extended PHP-based template class for a module
 * Search for template files in platform/templates/MODULELABEL and in module/MODULELABEL/templates
 * @throws  Exception if template file not found
 */
class ModuleTemplate extends PhpTemplate
{
    /**
     * @param   string $moduleLabel label of the module
     * @param   string $template name of the template
     */
    public function __construct( $moduleLabel, $template )
    {
        $template = secure_file_path( $template );
        $moduleLabel = secure_file_path( $moduleLabel );
        
        $customTemplatePath = get_path('rootSys') . 'platform/templates/'.$moduleLabel.'/'.$template;
        $defaultTemplatePath = get_module_path($moduleLabel) . '/templates/'.$template;
        
        if ( file_exists( $customTemplatePath ) )
        {
            parent::__construct( $customTemplatePath );
        }
        elseif ( file_exists( $defaultTemplatePath ) )
        {
            parent::__construct( $defaultTemplatePath );
        }
        else
        {
            throw new Exception("Template not found {$template} "
                . "at custom location {$customTemplatePath} "
                . "or default location {$defaultTemplatePath} !");
        }
    }
}
