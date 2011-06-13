<?php // $Id: helpers.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Helper functions and classes
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     KERNEL
 */

FromKernel::uses ( 'core/url.lib' );

/**
 * Create an html attribute list from an associative array attribute=>value
 * @param   array $attributes
 * @return  string
 */
function make_attribute_list( $attributes )
{
    $attribList = '';
    
    if ( is_array( $attributes ) && !empty( $attributes ) )
    {
        foreach ( $attributes as $attrib => $value )
        {
            $attribList .= ' ' . $attrib . '="'
                . htmlspecialchars($value) . '"'
                ;
        }
    }
    
    return $attribList;
}
 
/**
 * Create an html link to the given url with the given text and attributes
 * @param   string text
 * @param   string url
 * @param   array attributes (optional)
 * @return  string
 */
function link_to ( $text, $url, $attributes = null )
{
    $url = htmlspecialchars_decode( $url );
    
    $link = '<a href="'
        . htmlspecialchars( $url ) . '"'
        . make_attribute_list( $attributes )
        . '>' . htmlspecialchars( $text ) . '</a>'
        ;
        
    return $link;
}

/**
 * Create an html link to the given url inside claroline with the given
 * text and attributes
 * @param   string text
 * @param   string url inside claroline
 * @param   array context (cid, gid)
 * @param   array attributes (optional)
 * @return  string
 */
function link_to_claro ( $text, $url = null, $context = null, $attributes = null )
{
    if ( empty ( $url ) )
    {
        $url = get_path( 'url' ) . '/index.php';
    }
    
    $urlObj = new Url( $url );
    
    if ( $context )
    {
        $urlObj->relayContext($context);
    }
    else
    {
        $urlObj->relayCurrentContext();
    }
    
    $url = $urlObj->toUrl();
    
    return link_to ( $text, $url, $attributes );
}

/**
 * Create an html link to the given course or course tool
 * text and attributes
 * @param   string text
 * @param   string courseId
 * @param   array attributes (optional)
 * @return  string
 */
function link_to_course ( $text, $courseId, $attributes = null )
{
    $url = get_path( 'url' ) . '/claroline/course/index.php?cid='.$courseId;
    $urlObj = new Url( $url );
    
    $url = $urlObj->toUrl();
    
    return link_to ( $text, $url, $attributes );
}

/**
 * Create an html link to the given course or course tool
 * text and attributes
 * @param   string text
 * @param   string toolLabel
 * @param   array context (cid, gid)
 * @param   array attributes (optional)
 * @return  string
 */
function link_to_tool ( $text, $toolLabel = null, $context = null, $attributes = null )
{
    $url = get_module_entry_url( $toolLabel );
    
    return link_to_claro ( $text, $url, $context, $attributes );
}

/**
 * Include the rendering of the given dock
 * @param string dock name
 * @return string rendering
 */
function include_dock( $dockName )
{
    $dock = new ClaroDock( $dockName );
    echo $dock->render();
}

/**
 * Include a template file
 * @param   string $template name of the template
 */
function include_template( $template )
{
    $template = secure_file_path( $template );
    
    $customTemplatePath = get_path('rootSys') . '/platform/templates/'.$template;
    $defaultTemplatePath = get_path('includePath') . '/templates/'.$template;
    
    if ( file_exists( $customTemplatePath ) )
    {
        include $customTemplatePath;
    }
    elseif ( file_exists( $defaultTemplatePath ) )
    {
        include $defaultTemplatePath;
    }
    else
    {
        throw new Exception("Template not found {$templatePath} "
            . "at custom location {$customTemplatePath} "
            . "or default location {$defaultTemplatePath} !");
    }
}

/**
 * Include a textzone file
 * @param   string $textzone name of the textzone
 * @param   string $defaultContent content displayed if textzone cannot be found or doesn't exist
 */
function include_textzone( $textzone, $defaultContent = null )
{
    $textzone = secure_file_path( $textzone );
    // find correct path where the file is
    // FIXME : move ALL textzones to the same location !
    if( file_exists( get_path('rootSys') . './platform/textzone/'.$textzone) )
    {
        $textzonePath = get_path('rootSys') . './platform/textzone/'.$textzone;
    }
    elseif( file_exists( get_path('rootSys') . './'.$textzone) )
    {
        $textzonePath = get_path('rootSys') . './'.$textzone;
    }
    else
    {
        $textzonePath = null;
    }
    
    // textzone content
    if ( !is_null( $textzonePath ) )
    {
        include $textzonePath;
    }
    else
    {    
        if( !is_null( $defaultContent) )
        {
            echo $defaultContent;
        }
        
        if( claro_is_platform_admin() )
        {
            // help tip for administrator
            echo '<p>'
            .    get_lang('blockTextZoneHelp', array('%textZoneFile' => $textzone))
            .    '</p>';
        }
    }
    
    // edit link
    if( claro_is_platform_admin() )
    {
        echo '<p>' . "\n"
        .    '<a href="http://' . $_SERVER['SERVER_NAME'] . '/claroline/admin/managing/editFile.php?cmd=rqEdit&amp;file='.$textzone.'">' . "\n"
        .    '<img src="'.get_icon_url('edit').'" alt="" />' . get_lang('Edit text zone') . "\n"
        .    '</a>' . "\n"
        .    '</p>' . "\n";
    }
}

/**
 * Include the link to a given css
 * @param name of the css without the complete path
 * @param css media
 * @return string
 */
function link_to_css( $css, $media = 'all' )
{
    if( file_exists(get_path('clarolineRepositorySys') . '../platform/css/' . $css) )
    {
        return '<link rel="stylesheet" type="text/css" href="' 
            . get_path('clarolineRepositoryWeb') . '../platform/css/' . $css
            . '" media="'.$media.'" />'
            ;
    }
    elseif( file_exists(get_path('rootSys') . 'web/css/' . $css) )
    {
        return '<link rel="stylesheet" type="text/css" href="' 
            . get_path( 'url' ) . '/web/css/' . $css
            . '" media="'.$media.'" />'
            ;
    }
    
    return '';
}
