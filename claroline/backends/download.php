<?php // $Id: download.php 12923 2011-03-03 14:23:57Z abourguignon $

/**
 * Download a file given it's file location within a course or group document
 * directory
 *
 * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2.0
 * @package     KERNEL
 */

require dirname(__FILE__) . '/../inc/claro_init_global.inc.php';

require_once get_path('incRepositorySys') . '/lib/url.lib.php';
require_once get_path('incRepositorySys') . '/lib/file.lib.php';

$nameTools = get_lang('Display file');

$dialogBox = new DialogBox();

$noPHP_SELF=true;

$isDownloadable = true ;

if ( claro_is_in_a_course() && ! claro_is_course_allowed() ) claro_disp_auth_form(true);

$claroline->notification->addListener( 'download', 'trackInCourse' );

if ( isset($_REQUEST['url']) )
{
    $requestUrl = strip_tags($_REQUEST['url']);
}
else
{
    $requestUrl = strip_tags(get_path_info());
}

if ( is_download_url_encoded($requestUrl) )
{
    $requestUrl = download_url_decode( $requestUrl );
}

/*if ( ! claro_is_in_a_course() && file_exists( rtrim( get_path('rootSys'), '/' ) . '/platform/img' . $requestUrl ) )
{
    var_dump($requestUrl);
    exit();
}*/

if ( empty($requestUrl) )
{
    $isDownloadable = false ;
    $dialogBox->error( get_lang('Missing parameters') );
}
else
{
    if ( claro_is_in_a_course() )
    {
        $_course = claro_get_current_course_data();
        $_group  = claro_get_current_group_data();
    
        if (claro_is_in_a_group())
        {
            $groupContext  = true;
            $courseContext = false;
            $is_allowedToEdit = claro_is_group_member() ||  claro_is_group_tutor() || claro_is_course_manager();
        }
        else
        {
            $groupContext  = false;
            $courseContext = true;
            $is_allowedToEdit = claro_is_course_manager();
        }
    
        if ($courseContext)
        {
            $courseTblList = claro_sql_get_course_tbl();
            $tbl_document =  $courseTblList['document'];
            
            if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
            {
                $modifier = '';
            }
            else
            {
                $modifier = 'BINARY ';
            }
    
            $sql = "SELECT visibility
                    FROM `{$tbl_document}`
                    WHERE {$modifier} path = '".claro_sql_escape($requestUrl)."'";
    
            $docVisibilityStatus = claro_sql_query_get_single_value($sql);
    
            if (    ( ! is_null($docVisibilityStatus) ) // hidden document can only be viewed by course manager
                 && $docVisibilityStatus == 'i'
                 && ( ! $is_allowedToEdit ) )
            {
                $isDownloadable = false ;
                $dialogBox->error( get_lang('Not allowed') );
            }
        }
    
        if (claro_is_in_a_group() && claro_is_group_allowed())
        {
            $intermediatePath = get_path('coursesRepositorySys') . claro_get_course_path(). '/group/'.claro_get_current_group_data('directory');
        }
        else
        {
            $intermediatePath = get_path('coursesRepositorySys') . claro_get_course_path(). '/document';
        }
    }
    else
    {
        $intermediatePath = rtrim( str_replace( '\\', '/', get_path('rootSys') ), '/' ) . '/platform/document';
    }

    if ( get_conf('secureDocumentDownload') && $GLOBALS['is_Apache'] )
    {
        // pretty url
        $pathInfo = realpath( $intermediatePath . '/' . $requestUrl);
    }
    else
    {
        // TODO check if we can remove rawurldecode
        $pathInfo = $intermediatePath
                    . implode ( '/',
                            array_map('rawurldecode', explode('/',$requestUrl)));
    }

    // use slashes instead of backslashes in file path
    if (claro_debug_mode() )
    {
        pushClaroMessage('<p>File path : ' . $pathInfo . '</p>','pathInfo');
    }

    $pathInfo = secure_file_path( $pathInfo );

    // Check if path exists in course folder
    if ( ! file_exists($pathInfo) || is_dir($pathInfo) )
    {
        $isDownloadable = false ;

        $dialogBox->title( get_lang('Not found') );
        $dialogBox->error( get_lang('The requested file <strong>%file</strong> was not found on the platform.',
                                array('%file' => basename($pathInfo) ) ) );
    }
}

// Output section

if ( $isDownloadable )
{
    // end session to avoid lock
    session_write_close();

    $extension = get_file_extension($pathInfo);
    $mimeType = get_mime_on_ext($pathInfo);

    // workaround for HTML files and Links
    if ( $mimeType == 'text/html' && $extension != 'url' )
    {
        $claroline->notifier->event('download', array( 'data' => array('url' => $requestUrl) ) );

        if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
        {
            $rootSys =  str_replace( '//', '/', strtolower( str_replace('\\', '/', $rootSys) ) );
            $pathInfo = strtolower( str_replace('\\', '/', $pathInfo) );
        }

        $document_url = str_replace($rootSys,$urlAppend.'/',$pathInfo);

        // redirect to document
        claro_redirect( str_ireplace( '%2F', '/', urlencode( $document_url ) ) );

        die();
    }
    else
    {
        if( get_conf('useSendfile', true) )
        {
            if ( claro_send_file( $pathInfo )  !== false )
            {
                $claroline->notifier->event('download', array( 'data' => array('url' => $requestUrl) ) );
            }
            else
            {
                header('HTTP/1.1 404 Not Found');
                claro_die( get_lang('File download failed : %failureMSg%',
                    array( '%failureMsg%' => claro_failure::get_last_failure() ) ) );
                die();
            }
        }
        else
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
            {
                $rootSys =  str_replace( '//', '/', strtolower( str_replace('\\', '/', $rootSys) ) );
                $pathInfo = strtolower( str_replace('\\', '/', $pathInfo) );
            }

            $document_url = str_replace($rootSys,$urlAppend.'/',$pathInfo);

            // redirect to document
            claro_redirect($document_url);

            die();
        }
    }
}
else
{
    header('HTTP/1.1 404 Not Found');

    $out = '';

    $out .= $dialogBox->render();

    $claroline->display->body->appendContent($out);

    echo $claroline->display->render();

    exit;
}

die();

?>