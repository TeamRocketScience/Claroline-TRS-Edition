<?php // $Id: class.clarodbconnection.php 12923 2011-03-03 14:23:57Z abourguignon $
if ( count( get_included_files() ) == 1 ) die( '---' );

    // vim: expandtab sw=4 ts=4 sts=4:

    /**
     * CLAROLINE
     *
     * @version 1.8 $Revision: 12923 $
     *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
     *
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */

    require_once dirname(__FILE__) . "/class.dbconnection.php";

    class ClarolineDatabaseConnection extends CLWIKI_Database_Connection
    {
      
        function ClarolineDatabaseConnection()
        {
            // use only in claroline tools
        }

        function setError( $errmsg = '', $errno = 0 )
        {
            if ( $errmsg != '' )
            {
                $this->error = $errmsg;
                $this->errno = $errno;
            }
            else
            {
                $this->error = ( claro_sql_error() !== false ) ? claro_sql_error() : 'Unknown error';
                $this->errno = ( claro_sql_errno() !== false ) ? claro_sql_errno() : 0;
            }

            $this->connected = false;
        }

        function connect()
        {

        }

        function close()
        {

        }

        function executeQuery( $sql )
        {
            claro_sql_query( $sql );

            if( claro_sql_errno() != 0 )
            {
                $this->setError();

                return 0;
            }

            return claro_sql_affected_rows( );
        }

        function getAllObjectsFromQuery( $sql )
        {
            $result = claro_sql_query( $sql );

            if ( @mysql_num_rows( $result ) > 0 )
            {
                $ret= array();

                while( ( $item = @mysql_fetch_object( $result ) ) != false )
                {
                    $ret[] = $item;
                }
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );

                return null;
            }

            @mysql_free_result( $result );

            return $ret;
        }

        function getObjectFromQuery( $sql )
        {
            $result = claro_sql_query( $sql );

            if ( ( $item = @mysql_fetch_object( $result ) ) != false )
            {
                @mysql_free_result( $result );

                return $item;
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );
                return null;
            }
        }

        function getAllRowsFromQuery( $sql )
        {
            $result = claro_sql_query( $sql );

            if ( @mysql_num_rows( $result ) > 0 )
            {
                $ret= array();

                while ( ( $item = @mysql_fetch_array( $result ) ) != false )
                {
                    $ret[] = $item;
                }
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );

                return null;
            }

            @mysql_free_result( $result );

            return $ret;
        }

        function getRowFromQuery( $sql )
        {
            $result = claro_sql_query( $sql );

            if ( ( $item = @mysql_fetch_array( $result ) ) != false )
            {
                @mysql_free_result( $result );

                return $item;
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );

                return null;
            }
        }

        function queryReturnsResult( $sql )
        {
            $result = claro_sql_query( $sql );

            if ( claro_sql_errno() == 0 )
            {

                if ( @mysql_num_rows( $result ) > 0 )
                {
                    @mysql_free_result( $result );

                    return true;
                }
                else
                {
                    @mysql_free_result( $result );

                    return false;
                }
            }
            else
            {
                $this->setError();

                return false;
            }
        }

        function getLastInsertID()
        {
            if ( $this->hasError() )
            {
                return 0;
            }
            else
            {
                return claro_sql_insert_id();
            }
        }
    }
?>