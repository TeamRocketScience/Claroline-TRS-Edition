<?php // $Id: eventlistener.cnr.php 9706 2007-12-12 13:30:11Z mlaurent $

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'wiki_added',            'modificationDefault' );
    $claroline->notification->addListener( 'wiki_modified',         'modificationDefault' );
    $claroline->notification->addListener( 'wiki_deleted',          'modificationDelete' );
    $claroline->notification->addListener( 'wiki_page_modified',    'modificationDefault' );
    $claroline->notification->addListener( 'wiki_page_added',       'modificationDefault' );
?>