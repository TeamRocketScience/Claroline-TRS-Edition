<?php // $Id: eventlistener.cnr.php 12847 2011-02-07 07:49:31Z stephane-klein $

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    $claroline->notification->addListener( 'exercise_visible',      'modificationDefault' );
    $claroline->notification->addListener( 'exercise_invisible',    'modificationDelete' );
    $claroline->notification->addListener( 'exercise_deleted',      'modificationDelete' );

    $claroline->notification->addListener( 'exercise_added',        'calendarAddEvent' );
    $claroline->notification->addListener( 'exercise_deleted',      'calendarDeleteEvent' );
    $claroline->notification->addListener( 'exercise_updated',      'calendarUpdateEvent' );

?>