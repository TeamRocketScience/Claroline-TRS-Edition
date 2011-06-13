<?php // $Id: display_translation.php 11656 2009-03-05 09:29:35Z dimitrirambout $
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/**
 * This stupid script just display all translation for a language.
 * This used to be copy paste into a checkspeller
 */

$cleanInput = array ( ) ;
require '../../../../inc/claro_init_global.inc.php' ;

// Security check
if (! $_uid)
    claro_disp_auth_form () ;
if (! $is_platformAdmin)
    claro_die ( get_lang ( 'Not allowed' ) ) ;
    
/*
 * This script displays all the variables 
 * with the same content and a different name.
 */

// include configuration and library file

include ('language.conf.php') ;
require_once ('language.lib.php') ;

// get start time
$starttime = get_time () ;

$cleanInput [ 'lang' ] = $_REQUEST [ 'lang' ] ;

if (false !== $cleanInput [ 'lang' ])
{
    $cleanInput [ 'translation' ] = load_array_translation ( $cleanInput [ 'lang' ] ) ;
    foreach ( $cleanInput [ 'translation' ] as $translation )
    {
        echo $translation . '<hr />' . "\n" ;
    }

}

?>