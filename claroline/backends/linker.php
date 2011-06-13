<?php // $Id: linker.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Claroline Resource Linker ajax backend
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     core.linker
 */

try
{
    require_once dirname(__FILE__) . '/../inc/claro_init_global.inc.php';
    
    FromKernel::uses( 'core/linker.lib', 'utils/ajax.lib', 'utils/input.lib' );
    
    ResourceLinker::init();
    
    $userInput = Claro_UserInput::getInstance();

    $userInput->setValidator( 'cmd', new Claro_Validator_AllowedList(array(
        'getLinkList', 'getResourceList', 'resolveLocator'
    )) );
    
    $cmd = $userInput->get('cmd', 'getResourceList');
    
    $locator = isset( $_REQUEST['crl'] ) && ! empty( $_REQUEST['crl'] )
            ? ClarolineResourceLocator::parse($_REQUEST['crl'])
            : ResourceLinker::$Navigator->getCurrentLocator( array() );
            ;
    
    if ( 'getLinkList' == $cmd )
    {
        $linkListIt = ResourceLinker::getLinkList( $locator );
        
        // FIXME : use getResourceName instead of the title recorded in database !
        if ( empty( $linkListIt ) )
        {
            $linkList = array();
        }
        else
        {
            $linkList = array();
            
            // $linkList = iterator_to_array( $linkListIt );
            foreach ( $linkListIt as $link )
            {
                $linkList[] = array(
                    'crl' => $link['crl'],
                    'name' => ResourceLinker::$Resolver->getResourceName( ClarolineResourceLocator::parse( $link['crl'] ) )
                );
            }
        }
        
        $response = new Json_Response( $linkList );
    }
    elseif( 'resolveLocator' == $cmd )
    {
        $resourceLinkerResolver = new ResourceLinkerResolver;
        $url = $resourceLinkerResolver->resolve( $locator );
        $url = get_conf( 'rootWeb' ) . '..' . $url;
        $response = new Json_Response( array( 'url' => $url ) );
    }
    else
    {
        
        if ( !ResourceLinker::$Navigator->isNavigable( $locator ) )
        {
            throw new Exception('Resource not navigable');
        }
        
        $resourceList = ResourceLinker::$Navigator->getResourceList( $locator );
        
        $elementList = $resourceList->toArray();
        
        $resourceArr = array();
        $resourceArr['name'] = ResourceLinker::$Resolver->getResourceName( $locator );
        $resourceArr['crl'] = $locator->__toString();
        
        $parent = ResourceLinker::$Navigator->getParent( $locator );
        
        $resourceArr['parent'] = (empty($parent) ? false : $parent->__toString());
        $resourceArr['resources'] = $elementList;
        
        $response = new Json_Response( $resourceArr );
    }
}
catch (Exception $e )
{
    $response = new Json_Exception( $e );
}

echo $response->toJson();
exit;
