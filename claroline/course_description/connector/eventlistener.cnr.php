<?php // $Id: eventlistener.cnr.php 9706 2007-12-12 13:30:11Z mlaurent $

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'course_description_added',      'modificationDefault' );
    $claroline->notification->addListener( 'course_description_modified',   'modificationDefault' );
    $claroline->notification->addListener( 'course_description_visible',    'modificationDefault' );
    $claroline->notification->addListener( 'course_description_deleted',    'modificationDelete' );
?>