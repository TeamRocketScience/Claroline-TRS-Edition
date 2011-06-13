<?php // $Id: pager.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Database Pager Classes
 *
 * @version     1.10 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.database
 */

// Interfaces

// forced to call it Claro_Pager_Interface to avoid conflict with the old
// claro_pager class
interface Claro_Pager_Interface
{
    public function setPageSize( $pageSize );
    
    public function getPage( $pageNumber );
    
    public function hasNext( $currentPage );
    
    public function hasPrev( $currentPage );
    
    public function isPageNumberValid( $pageNumber );
    
    public function countPages();
    
    public function getAll();
}

interface Claro_Sortable
{
    const ASC = 'ASC';
    const DESC = 'DESC';
    
    public function sortBy( $field, $direction );
}

interface Claro_Pageable extends Countable
{
    public function setLimit( $length, $offset = null );
    
    public function getPage();
    
    public function getAll();
}

// Abstract Sortable and Pageable object to extend

abstract class Mysql_PageableSortable implements Claro_Pageable, Claro_Sortable
{
    protected $sortBy = array();
    protected $offset = null;
    protected $length = null;
    protected $count = null;
    protected $database;
    
    public function __construct( $database )
    {
        $this->database = $database;
    }
    
    abstract protected function getSelectQuery();
    
    public function sortBy( $field, $direction )
    {
        $this->sortBy[$field] = $direction;
    }
    
    public function setLimit( $length, $offset = null )
    {
        $this->offset = $offset;
        $this->length = $length;
    }
    
    public function count()
    {
        $sql = preg_replace( "/SELECT (.+?)FROM/", "SELECT count(*) FROM", $query );
        
        $res = $this->database->query( $sql );
        
        return $res->fetch(Database_ResultSet::FETCH_VALUE);
    }
    
    protected function getLimit()
    {
        if ( !empty( $this->offset ) )
        {
            return "LIMIT {$this->offset}, {$this->length}\n";
        }
        else
        {
            return "LIMIT {$this->length}\n";
        }
    }
    
    protected function getOrder()
    {
        if ( !empty( $this->sortBy ) )
        {
            $ret = "ORDER BY\n";
            $orderArray = array();
            
            foreach ( $this->sortBy as $field => $direction )
            {
                $orderArray[] = "`"
                    . $this->database->escape( $field )."` "
                    . ($direction == Claro_Utils_sortable::ASC
                       ? 'ASC'
                       : 'DESC');
            }
            
            $ret .= implode("\n,", $orderArray);
            
            return $ret;
        }
        else
        {
            return "";
        }
    }
    
    public function getPage()
    {
        return $this->database->query(
            $this->getSelectQuery() . "\n" .
            $this->getOrder() . "\n" .
            $this->getLimit()
        );
    }
    
    public function getAll()
    {
        return $this->database->query(
            $this->getSelectQuery() . "\n" .
            $this->getOrder()
        );
    }
}

// Implements a pager on a Pageable object

class Claro_Pageable_Pager implements Claro_Pager_Interface
{
    protected $pageable;
    protected $pageSize = 25;
    
    public function __construct( $pageable )
    {
        $this->pageable = $pageable;
        $this->numberOfItems = count( $pageable );
    }
    
    public function setPageSize( $pageSize )
    {
        $this->pageSize = $pageSize;
    }
    
    public function getPage( $pageNumber )
    {
        if ( $this->pageSize == 0
            && $this->isPageNumberValid( $pageNumber ) )
        {
            return $this->getAll();
        }
        elseif ( $this->isPageNumberValid( $pageNumber ) )
        {
            $this->pageable->setLimit( $this->pageSize, $pageNumber * $this->pageSize );
            return $this->pageable->getPage();
        }
        else
        {
            throw new OutOfBoundsException("Page number is not valid");
        }
    }
    
    public function hasNext( $currentPage )
    {
        if ( $this->isPageNumberValid( $pageNumber ) )
        {
            return $this->isPageNumberValid( $pageNumber + 1 );
        }
        else
        {
            throw new OutOfBoundsException("Page number is not valid");
        }
    }
    
    public function hasPrev( $currentPage )
    {
        if ( $this->isPageNumberValid( $pageNumber ) )
        {
            return $this->isPageNumberValid( $pageNumber - 1 );
        }
        else
        {
            throw new OutOfBoundsException("Page number is not valid");
        }
    }
    
    public function isPageNumberValid( $pageNumber )
    {
        return ( $page >= 0
            && $page < $this->countPages() );
    }
    
    public function countPages()
    {
        if ( $this->numberOfItems )
        {
            // we want all results on one page
            if ( $this->pageSize == 0 )
            {
                return 1;
            }
            else
            {
                $tmp = floor( $this->numberOfItems / $this->pageSize );
                
                if ( $tmp * $this->pageSize < $this->numberOfItems )
                {
                    return $tmp + 1;
                }
                else
                {
                    return $tmp;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    
    public function getAll()
    {
        return $this->pageable->getAll();
    }
}

// Implements a pager on a SQL query (replacement for the old claro_pager class)

class Mysql_Pager implements Claro_Pager_Interface, Claro_Sortable
{
    protected $query;
    protected $database;
    protected $pageSize = 25;
    protected $numberOfItems;
    protected $sortArray = array();
    
    /**
     * Constructor, can be used directly or through Database_Connection::pager()
     *
     * @param   string $query database query
     * @param   Database_Connection $database connection to the database,
     *  if missing the Claroline default database will be used
     */
    public function __construct( $query, $database = null )
    {
        $this->database = empty($database)
            ? Claroline::getDatabase()
            : $database
            ;
            
        $this->query = $query;
        
        $this->numberOfItems = $this->getCount();
    }
    
    /**
     * Add an sort condition on the given field name in the given sort direction
     *
     * @param   string $field field name
     * @param   string $direction Claro_Sortable::ASC or Claro_Sortable::DESC
     */
    public function sortBy( $field, $direction )
    {
        $this->sortArray[$field] = $direction;
    }
    
    /**
     * Set the number of items by page. If set to 0, all items
     * are going to be returned on one single page by getPage()
     * (same as calling the getAll() method)
     * 
     * @param   int $pageSize
     */
    public function setPageSize( $pageSize )
    {
        $this->pageSize = (int) $pageSize;
    }
    
    /**
     * Get all the results on one page. This method will use
     * the sort by fields.
     * 
     * @return  Database_ResultSet
     * @throws  Claro_Database_Exception
     */
    public function getAll()
    {
        $sql = $this->query . "\n"
            . $this->getOrder() . "\n"
            ;
                
        return $this->database->query( $sql );
    }
    
    /**
     * Get one page of the result set. Pages are numbered from 0
     * to countPages().
     * If pageSize set to 0, calling getPage() is the same as calling
     * getAll(). In this case only 0 is a valid page number.
     * 
     * @param   int $pageNumber number of the page needed
     * @return  Database_ResultSet
     * @throws  Claro_Database_Exception if an error occurs while
     *  executing the query
     * @throws  OutOfBoundsException if the page number is not valid
     */
    public function getPage( $pageNumber )
    {
        if ( $this->pageSize == 0
            && $this->isPageNumberValid( $pageNumber ) )
        {
            return $this->getAll();
        }
        if ( $this->isPageNumberValid( $pageNumber ) )
        {
            $sql = $this->query . "\n"
                . $this->getLimit() ."\n"
                . $this->getOrder() . "\n"
                ;
            
            return $this->database->query( $sql );
        }
        else
        {
            throw new OutOfBoundsException("Page number is not valid");
        }
    }
    
    /**
     * Returns true if the current page as a next page, false else
     *
     * @param   int $pageNumber
     * @return  bool true if there is a next page
     * @throws  OutOfBoundsException if the given page number is not valid
     */
    public function hasNext( $pageNumber )
    {
        if ( $this->isPageNumberValid( $pageNumber ) )
        {
            return $this->isPageNumberValid( $pageNumber + 1 );
        }
        else
        {
            throw new OutOfBoundsException("Page number is not valid");
        }
    }
    
    /**
     * Returns true if the current page as a previous page, false else
     *
     * @param   int $pageNumber
     * @return  bool true if there is a previous page
     * @throws  OutOfBoundsException if the given page number is not valid
     */
    public function hasPrev( $pageNumber )
    {
        if ( $this->isPageNumberValid( $pageNumber ) )
        {
            return $this->isPageNumberValid( $pageNumber - 1 );
        }
        else
        {
            throw new OutOfBoundsException("Page number is not valid");
        }
    }
    
    /**
     * Check if the given page number is valid
     *  i.e. : 0 <= $pageNumber < Claro_Pageable::countPages()
     *
     * @param   int $pageNumber
     * @return  bool
     */
    public function isPageNumberValid( $pageNumber )
    {
        return ( $page >= 0
            && $page < $this->countPages() );
    }
    
    /**
     * Count the number of pages in the database.
     * Return 1 if pageSize is set to 0.
     * Return 0 if no result have been returned by the database
     *
     * @return  int number of pages
     */
    public function countPages()
    {
        if ( $this->numberOfItems )
        {
            // we want all results on one page
            if ( $this->pageSize == 0 )
            {
                return 1;
            }
            else
            {
                $tmp = floor( $this->numberOfItems / $this->pageSize );
                
                if ( $tmp * $this->pageSize < $this->numberOfItems )
                {
                    return $tmp + 1;
                }
                else
                {
                    return $tmp;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    
    // Private and protected methods
    
    protected function getCount()
    {
        $sql = preg_replace( "/SELECT (.+?)FROM/", "SELECT count(*) AS numberOfRows FROM", $query );
        
        $res = $this->database->query( $sql );
        
        return $res->fetch(Database_ResultSet::FETCH_VALUE);
    }
    
    protected function getLimit( $pageNumber )
    {
        $offset = (int)$pageNumber * (int)$this->pageSize;
        $numberOfItems = (int)$this->pageSize;
        
        return "LIMIT {$numberOfItems}". (!empty($offset) ? ", {$offset}" : '' );
    }
    
    protected function getOrder( $pageNumber )
    {
        if ( !empty( $this->sortArray ) )
        {
            $ret = "ORDER BY\n";
            $orderArray = array();
            
            foreach ( $this->sortArray as $field => $direction )
            {
                $orderArray[] = "`"
                    . $this->database->escape( $field )."` "
                    . ($direction == Claro_Utils_sortable::ASC
                       ? 'ASC'
                       : 'DESC');
            }
            
            $ret .= implode("\n,", $orderArray);
            
            return $ret;
        }
        else
        {
            return "";
        }
    }
}
