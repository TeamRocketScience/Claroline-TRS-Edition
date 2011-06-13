<?php // $Id: class.wikisearchengine.php 12923 2011-03-03 14:23:57Z abourguignon $

    if ( count( get_included_files() ) == 1 ) die( '---' );

    // vim: expandtab sw=4 ts=4 sts=4:

    /**
     * CLAROLINE
     *
     * @version 1.8 $Revision: 12923 $
     *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
     *
     * @license GENERAL PUBLIC LICENSE (GPL)
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */

    !defined ( "CLWIKI_SEARCH_ANY" ) && define ( "CLWIKI_SEARCH_ANY", "CLWIKI_SEARCH_ANY" );
    !defined ( "CLWIKI_SEARCH_ALL" ) && define ( "CLWIKI_SEARCH_ALL", "CLWIKI_SEARCH_ALL" );
    !defined ( "CLWIKI_SEARCH_EXP" ) && define ( "CLWIKI_SEARCH_EXP", "CLWIKI_SEARCH_EXP" );

    /**
     * Search engine for the Wiki
     */
    class WikiSearchEngine
    {
        var $connection = null;

        var $config = array(
            'tbl_wiki_pages' => 'wiki_pages',
            'tbl_wiki_pages_content' => 'wiki_pages_content',
            'tbl_wiki_properties' => 'wiki_properties',
            'tbl_wiki_acls' => 'wiki_acls'
        );

        /**
         * Constructor
         * @param DatabaseConnection connection
         * @param Array config
         */
        function WikiSearchEngine( &$connection, $config = null )
        {
            if ( is_array( $config ) )
            {
                $this->config = array_merge( $this->config, $config );
            }

            $this->connection =& $connection;
        }

        /**
         * Search for a given pattern in Wiki pages in a given Wiki
         * @param int wikiId
         * @param String pattern
         * @param Const mode
         * @return Array of Wiki pages
         */
        function searchInWiki( $pattern, $wikiId, $mode = CLWIKI_SEARCH_ANY )
        {
            if ( ! $this->connection->isConnected() )
            {
                $this->connection->connect();
            }

            $searchStr = WikiSearchEngine::makePageSearchQuery( $pattern, $mode );

            $sql = "SELECT p.`id`, p.`wiki_id`, p.`title`, c.`content` "
                . "FROM `"
                . $this->config['tbl_wiki_properties']."` AS w, `"
                . $this->config['tbl_wiki_pages']."` AS p, `"
                . $this->config['tbl_wiki_pages_content']."` AS c "
                . "WHERE p.`wiki_id` = " . (int) $wikiId
                . " AND " . $searchStr
                ;

            $ret = $this->connection->getAllRowsFromQuery( $sql );

            if ( $this->connection->hasError() )
            {
                return false;
            }
            else
            {
                return $ret;
            }
        }

        /**
         * Search for a given pattern in Wiki pages in a given Wiki, light version
         * @param int wikiId
         * @param String pattern
         * @param Const mode
         * @return Array of Wiki pages ids and titles
         */
        function lightSearchInWiki( $wikiId, $pattern, $mode = CLWIKI_SEARCH_ANY )
        {
            if ( ! $this->connection->isConnected() )
            {
                $this->connection->connect();
            }

            $searchStr = WikiSearchEngine::makePageSearchQuery( $pattern, $mode );

            $sql = "SELECT p.`id`, p.`title` "
                . "FROM `"
                . $this->config['tbl_wiki_properties']."` AS w, `"
                . $this->config['tbl_wiki_pages']."` AS p, `"
                . $this->config['tbl_wiki_pages_content']."` AS c "
                . "WHERE p.`wiki_id` = " . (int) $wikiId
                . " AND " . $searchStr
                ;

            $ret = $this->connection->getAllRowsFromQuery( $sql );

            if ( $this->connection->hasError() )
            {
                return false;
            }
            else
            {
                return $ret;
            }
        }

        /**
         * Search for a given pattern in all Wiki pages
         * @param String pattern
         * @param int groupId (default null) FIXME magic value !
         * @param Const mode
         * @return Array of Wiki properties
         */
        function searchAllWiki( $pattern, $groupId = null, $mode = CLWIKI_SEARCH_ANY, $getPageTitles = false )
        {
            if ( ! $this->connection->isConnected() )
            {
                $this->connection->connect();
            }

            $ret = array();
            
            $wikiList = array();

            $searchPageStr = WikiSearchEngine::makePageSearchQuery( $pattern, $groupId, $mode );

            $groupStr = ( ! is_null( $groupId ) )
                ? "( w.`group_id` = " . (int) $groupId . " ) AND"
                : ""
                ;

            $searchWikiStr = WikiSearchEngine::makeWikiPropertiesSearchQuery( $pattern, $groupId, $mode );

            $sql = "SELECT DISTINCT w.`id`, w.`title`, w.`description` "
                . "FROM `"
                . $this->config['tbl_wiki_properties']."` AS w, `"
                . $this->config['tbl_wiki_pages']."` AS p, `"
                . $this->config['tbl_wiki_pages_content']."` AS c "
                . "WHERE "
                . $searchPageStr . " "
                . " OR " . $searchWikiStr
                ;

            $wikiList = $this->connection->getAllRowsFromQuery( $sql );

            if ( $this->connection->hasError() )
            {
                return false;
            }

            if ( is_array( $wikiList ) )
            {
                # search for Wiki pages
                foreach ( $wikiList as $wiki )
                {
                    if ( true === $getPageTitles )
                    {
                        $pages = $this->lightSearchInWiki( $wiki['id'], $pattern, $mode );
                        
                        if ( false !== $pages && !is_null( $pages) )
                        {
                            $wiki['pages'] = is_null($pages) ? array() : $pages;
                        }
                        else
                        {
                            return false;
                        }
                    }
                    
                    $ret[] = $wiki;
                }

                unset( $wikiList );
            }

            if ( $this->connection->hasError() )
            {
                return false;
            }
            else
            {
                return $ret;
            }
        }

        // utility functions

        /**
         * Split a search pattern for the given search mode
         * @param String pattern
         * @param Const mode
         * @return Array ( keywords, implode_word )
         */
        function splitPattern( $pattern, $mode = CLWIKI_SEARCH_ANY )
        {
            $pattern = claro_sql_escape( $pattern );
            $pattern = str_replace('_', '\_', $pattern);
            $pattern = str_replace('%', '\%', $pattern);
            $pattern = str_replace('?', '_' , $pattern);
            $pattern = str_replace('*', '%' , $pattern);

            switch( $mode )
            {
                case CLWIKI_SEARCH_ALL:
                {
                    $impl = "AND";
                    $keywords = preg_split( '~\s~', $pattern );
                    break;
                }
                case CLWIKI_SEARCH_EXP:
                {
                    $impl = "";
                    $keywords = array( $pattern );
                    break;
                }
                case CLWIKI_SEARCH_ANY:
                default:
                {
                    $impl = "OR";
                    $keywords = preg_split( '~\s~', $pattern );
                    break;
                }
            }
            
            $ret = array( $keywords, $impl );

            return $ret;
        }

        /**
         * Generate search string for a given pattern in wiki pages
         * @param String pattern
         * @param Const mode
         * @return String
         */
        function makePageSearchQuery( $pattern, $groupId = null, $mode = CLWIKI_SEARCH_ANY )
        {
            list( $keywords, $impl ) = WikiSearchEngine::splitPattern( $pattern, $mode );

            $searchTitleArr = array();
            $searchPageArr = array();

            $groupstr = ( ! is_null( $groupId ) )
                ? "( w.`group_id` = " . (int) $groupId . "  AND w.`id` = p.`wiki_id`)"
                : "(w.`id` = p.`wiki_id`)"
                ;

            foreach ( $keywords as $keyword )
            {
                $searchTitleArr[] = " p.`title` LIKE '%".$keyword."%' ";
                $searchPageArr[] = " c.`content` LIKE '%".$keyword."%' ";
            }

            $searchTitle = implode ( $impl, $searchTitleArr );

            if ( count ( $searchTitleArr ) > 1 )
            {
                $searchTitle = " ( " . $searchTitle . ") ";
            }

            $searchPage = implode ( $impl, $searchPageArr );

            if ( count ( $searchPageArr ) > 1 )
            {
                $searchPage = " ( " . $searchPage . ") ";
            }

            $searchStr = "( ".$groupstr." AND c.`id` = p.`last_version` AND " . $searchTitle . " ) OR "
                . "( ".$groupstr." AND c.`id` = p.`last_version` AND " . $searchPage . " )"
                ;

            return "($searchStr)";
        }

        /**
         * Generate search string for a given pattern in wiki properties
         * @param String pattern
         * @param Const mode
         * @return String
         */
        function makeWikiPropertiesSearchQuery( $pattern, $groupId = null, $mode = CLWIKI_SEARCH_ANY )
        {
            list( $keywords, $impl ) = WikiSearchEngine::splitPattern( $pattern, $mode );

            $searchWikiArr = array();

            $groupstr = ( ! is_null( $groupId ) )
                ? "( w.`group_id` = " . (int) $groupId . "  AND w.`id` = p.`wiki_id`)"
                : "(w.`id` = p.`wiki_id`)"
                ;

            foreach ( $keywords as $keyword )
            {
                $searchTitleArr[] = $groupstr." AND (w.`title` LIKE '%".$keyword."%' "
                    . "OR w.`description` LIKE '%".$keyword."%') "
                    ;
            }

            $searchStr = implode ( $impl, $searchTitleArr );

            return "($searchStr)";
        }

        // error handling

        var $error = null;
        
        var $errno = 0;

        function setError( $errmsg = '', $errno = 0 )
        {
            $this->error = ($errmsg != '') ? $errmsg : "Unknown error";
            $this->errno = $errno;
        }

        function getError()
        {
            if ( $this->connection->hasError() )
            {
                return $this->connection->getError();
            }
            else if (! is_null( $this->error ) )
            {

                $errno = $this->errno;
                $error = $this->error;
                $this->error = null;
                $this->errno = 0;

                return $errno.' - '.$error;
            }
            else
            {
                return false;
            }
        }

        function hasError()
        {
            return ( ! is_null( $this->error ) ) || $this->connection->hasError();
        }
    }
?>