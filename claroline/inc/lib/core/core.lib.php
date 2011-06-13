<?php // $Id: core.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Main core library
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.core
 */

/**
 * Protect file path against arbitrary file inclusion
 * @param   string path, untrusted path
 * @return  string secured path
 */
function protect_against_file_inclusion( $path )
{
    while ( false !== strpos( $path, '://' )
        || false !== strpos( $path, '..' ) )
    {
        // protect against remote file inclusion
        $path = str_replace( '://', '', $path );
        // protect against arbitrary file inclusion
        $path = str_replace( '..', '.', $path );
    }
        
    return $path;
}

/**
 * Imports the PHP libraries given in argument with path relative to
 * includePath or module lib/ directory. .php extension added automaticaly
 * @param   list of libraries
 * @return  array of not found libraries + generate an error in debug mode
 * @deprecated since 1.9.0, use FromKernel::uses() and From::module()->uses() instead
 */
function uses()
{
    $args = func_get_args();
    $notFound = array();
    
    defined('INCLUDES') || define ( 'INCLUDES', dirname(__FILE__) . '/..');
    
    foreach ( $args as $lib )
    {
        if ( basename( $lib ) == '*' )
        {
            uses( 'utils/finder.lib' );
            
            $dir = dirname( $lib );
            
            $kernelPath = INCLUDES . '/' . $dir;
            $localPath = get_module_path(get_current_module_label()) . '/lib/' . $dir;
            
            if ( file_exists( $kernelPath )
                && is_dir( $kernelPath )
                && is_readable( $kernelPath )
                && $dir != '.'  // do not allow loading all files in inc/lib !!!
            )
            {
                $path = $kernelPath;
            }
            elseif ( file_exists( $localPath )
                && is_dir( $localPath )
                && is_readable( $localPath )
            )
            {
                $path = $localPath;
            }
            else
            {
                if ( claro_debug_mode() )
                {
                    throw new Exception( "Cannot load libraries from {$dir}" );
                }
                
                $notFound[] = $lib;
                
                continue;
            }
            
            $finder = new Claro_FileFinder_Extension( $path, '.php', false );
            
            foreach ( $finder as $file )
            {
                require_once $file->getPathname();
            }
        }
        else
        {
            if ( substr($lib, -4) !== '.php' ) $lib .= '.php';
            
            $lib = protect_against_file_inclusion( $lib );
            
            $kernelPath = INCLUDES . '/' . $lib;
            $localPath = get_module_path(Claroline::getInstance()->currentModuleLabel()) . '/lib/' . $lib;
            
            if ( file_exists( $localPath ) )
            {
                require_once $localPath;
            }
            elseif ( file_exists( $kernelPath ) )
            {
                require_once $kernelPath;
            }
            else
            {
                // error not found
                if ( claro_debug_mode() ) 
                {
                    throw new Exception( "Lib not found $lib" );
                }
                
                $notFound[] = $lib;
            }
        }
    }
    
    return $notFound;
}

/**
 * Kernel library loader
 */
class FromKernel
{
    /**
     * Load a list of kernel libraries
     * Usage : FromKernel::uses( list of libraries );
     * @params  list of libraries
     * @throws  Exception if a library is not found
     */
    public static function uses()
    {
        $args = func_get_args();
        
        defined('INCLUDES') || define ( 'INCLUDES', dirname(__FILE__) . '/..');
        
        foreach ( $args as $lib )
        {
            if ( substr($lib, -4) !== '.php' )
            {
                $lib .= '.php';
            }
            
            $lib = protect_against_file_inclusion( $lib );
            
            $kernelPath = INCLUDES . '/' . $lib;
            
            if ( file_exists( $kernelPath ) )
            {
                require_once $kernelPath;
            }
            else
            {
                throw new Exception( "Lib not found $lib" );
            }
        }
    }
}

/**
 * Module library loader
 */
class From
{
    protected $moduleLabel;
    
    protected function __construct( $moduleLabel )
    {
        $this->moduleLabel = $moduleLabel;
    }
    
    /**
     * Load a list of libraries from a given module
     * Usage : From::module(ModuleLable)->uses( list of libraries );
     * @params  list of libraries
     * @return  array of not found libraries
     */
    public function uses()
    {
        $args = func_get_args();
        $notFound = array();
        
        foreach ( $args as $lib )
        {
            if ( basename( $lib ) == '*' )
            {
                uses( 'utils/finder.lib' );
                
                $localPath = get_module_path( $this->moduleLabel ) . '/lib/' . dirname( $lib );
                
                if ( file_exists( $localPath )
                    && is_dir( $localPath )
                    && is_readable( $localPath )
                )
                {
                    $path = $localPath;
                }
                else
                {
                    if ( claro_debug_mode() )
                    {
                        throw new Exception( "Cannot load libraries from {$dir} at {$localPath}" );
                    }
                    
                    $notFound[] = $lib;
                    
                    continue;
                }
                
                $finder = new Claro_FileFinder_Extension( $path, '.php', false );
                
                foreach ( $finder as $file )
                {
                    require_once $file->getPathname();
                }
            }
            else
            {
                if ( substr($lib, -4) !== '.php' ) $lib .= '.php';
                
                $lib = protect_against_file_inclusion( $lib );
                
                $libPath = get_module_path( $this->moduleLabel ) . '/lib/' . $lib;
                
                if ( file_exists( $libPath ) )
                {
                    require_once $libPath;
                }
                else
                {
                    if ( claro_debug_mode() )
                    {
                        throw new Exception( "Cannot load library {$libPath}" );
                    }
                    
                    $notFound[] = $lib;
                    
                    continue;
                }
            }
        }
    }
    
    /**
     * Load a list of connectors from a given module
     * Usage : From::module(ModuleLable)->loadConnectors( list of connectors );
     * @since Claroline 1.9.6
     * @params  list of connectors
     * @return  array of not found connectors
     */
    public function loadConnectors()
    {
        $args = func_get_args();
        $notFound = array();
        
        foreach ( $args as $cnr )
        {
            if ( substr($cnr, -4) !== '.php' && substr( $cnr, -4 ) === '.cnr' )            
            {
                $cnr .= '.php';
            }
            elseif ( substr($cnr, -8) !== '.cnr.php' )
            {
                $cnr .= '.cnr.php';
            }
            
            $cnr = protect_against_file_inclusion( $cnr );
            
            $cnrPath = get_module_path( $this->moduleLabel ) . '/connector/' . $cnr;
            
            if ( file_exists( $cnrPath ) )
            {
                require_once $cnrPath;
            }
            else
            {
                if ( claro_debug_mode() )
                {
                    throw new Exception( "Cannot load connector {$cnrPath}" );
                }
                
                $notFound[] = $cnr;
                
                continue;
            }
            
        }
        
        return $notFound;
    }
    
    private static $cache = array();
    
    /**
     * Get the loader for a given module
     * @param   string $moduleLabel
     * @return  Loader instance
     */
    public static function module( $moduleLabel = null )
    {
        if ( empty($moduleLabel) )
        {
            $moduleLabel = get_current_module_label();
        }
        
        if ( !array_key_exists( $moduleLabel, self::$cache ) )
        {
            self::$cache[$moduleLabel] = new self($moduleLabel);
        }
        
        return self::$cache[$moduleLabel];
    }
}
