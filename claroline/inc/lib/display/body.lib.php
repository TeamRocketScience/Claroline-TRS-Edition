<?php // $Id: body.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Class used to configure and display the page body
     *
     * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     display
     */
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    class ClaroBody extends CoreTemplate
    {
        protected $content = '';
        protected $claroBodyHidden = false;
        protected $inPopup = false;
        
        public function __construct()
        {
            parent::__construct('body.tpl.php');
        }
        
        /**
         * Hide the claroBody div in the body
         */
        public function hideClaroBody()
        {
            $this->claroBodyHidden = true;
        }
        
        /**
         * Display the claroBody div in the body
         */
        public function showClaroBody()
        {
            $this->claroBodyHidden = false;
        }
        
        /**
         * Show 'Close window' buttons
         */
        public function popupMode()
        {
            $this->inPopup = true;
        }
        
        /**
         * Set the content of the page
         * @param   string content
         */
        public function setContent( $content)
        {
            $this->content = $content;
        }
        
        /**
         * Append a string to the content of the page
         * @param   string str
         */
        public function appendContent( $str )
        {
            $this->content .= $str;
        }
        
        /**
         * Clear the content of the paget
         */
        public function clearContent()
        {
            $this->setContent('');
        }
        
        /**
         * Return the content of the page
         * @return  string  pagecontent
         */
        public function getContent()
        {
            return $this->content;
        }
        
        /**
         * Render the page body
         * @return  string
         */
        public function render()
        {
            if ( ! $this->claroBodyHidden )
            {
                $this->assign('claroBodyStart', true);
                $this->assign('claroBodyEnd', true);
            }
            else
            {
                $this->assign('claroBodyStart', false);
                $this->assign('claroBodyEnd', false);
            }
            
            // automatic since $this->content already exists
            // $this->assign('content', $this->getContent() );
            
            $output = parent::render();
            
            if ( $this->inPopup )
            {
                $output = PopupWindowHelper::popupEmbed($output);
            }
                
            return $output;
        }
        
        protected static $instance = false;
        
        public static function getInstance()
        {
            if ( ! self::$instance )
            {
                self::$instance = new self;
            }

            return self::$instance;
        }
    }
?>