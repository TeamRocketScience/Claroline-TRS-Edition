<?php // $Id: dock.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     * Dock display lib
     *
     * @version     1.9 $Revision: 12923 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     display
     */
    
    class ClaroBuffer
    {
        private $_buffer;
        
        public function __construct()
        {
            $this->clear();
        }
        
        public function clear()
        {
            $this->_buffer = '';
        }
        
        public function append( $str )
        {
            $this->_buffer .= $str;
        }
        
        public function replace( $str )
        {
            $this->_buffer = $str;
        }

        public function getContent()
        {
            return $this->_buffer;
        }

        public function flush()
        {
            $buffer = $this->_buffer;
            $this->clear();
            return $buffer;
        }
    }
    
    class DockAppletList
    {
        private static $instance = false;
    
        private $_dockAppletList = array();
        
        private function __construct()
        {
            $this->load();
        }
        
        public function load()
        {
            $tblNameList = claro_sql_get_main_tbl();
            
            $sql = "SELECT M.`label` AS `label`,\n"
                . "M.`script_url` AS `entry`,\n"
                . "M.`name` AS `name`,\n"
                . "M.`activation` AS `activation`,\n"
                . "D.`name` AS `dock`\n"
                . "FROM `" . $tblNameList['dock'] . "` AS D\n"
                . "LEFT JOIN `" . $tblNameList['module'] . "` AS M\n"
                . "ON D.`module_id` = M.`id`\n"
                . "ORDER BY D.`rank` "
                ;

            $appletList = claro_sql_query_fetch_all_rows( $sql );
            
            if ( $appletList )
            {
                $dockAppletList = array();
                
                foreach ( $appletList as $key => $applet )
                {
                    if ( ! array_key_exists($applet['dock'], $dockAppletList) )
                    {
                        $dockAppletList[$applet['dock']] = array();
                    }
                    
                    $entryPath = get_module_path($applet['label'])
                        . '/' . $applet['entry']
                        ;

                    if (file_exists( $entryPath ) )
                    {
                        $applet['path'] = $entryPath;
                        // $appletList[$key] = $applet;
                        $dockAppletList[$applet['dock']][] = $applet;
                    }
                }

                $this->_dockAppletList = $dockAppletList;
            }
        }
        
        public function getAppletList( $dockName )
        {
            if ( array_key_exists( $dockName, $this->_dockAppletList ) )
            {
                return $this->_dockAppletList[$dockName];
            }
            else
            {
                return array();
            }
        }
        
        public static function getInstance()
        {
            if ( ! DockAppletList::$instance )
            {
                DockAppletList::$instance = new DockAppletList;
            }
            
            return DockAppletList::$instance;
        }
    }

    class ClaroDock implements Display
    {
        private $name;
        private $appletList;

        public function __construct($name)
        {
            $this->name = $name;
            $this->loadAppletList();
        }
        
        function getName()
        {
            return $this->name;
        }
        
        public function loadAppletList()
        {
            
            $dockAppletList = DockAppletList::getInstance();
            $this->appletList = $dockAppletList->getAppletList( $this->name );
        }

        public function render()
        {
            $claro_buffer = new ClaroBuffer;

            $claro_buffer->append("\n" . '<!-- ' . $this->name.' -->' . "\n");
            
            foreach ( $this->appletList as $applet )
            {
                set_current_module_label( $applet['label'] );
                
                pushClaroMessage('Current module label set to : ' . get_current_module_label(), 'debug');
                
                // install course applet
                if ( claro_is_in_a_course() )
                {
                    install_module_in_course( $applet['label']
                        , claro_get_current_course_id() ) ;
                }
                
                if ( $applet['activation'] == 'activated'
                    && file_exists( $applet['path'] ) )
                {
                    load_module_config();
                    Language::load_module_translation();
                
                    include_once $applet['path'];
                }
                else
                {
                    Console::debug( "Applet not found or not activated : " . $applet['label'] );
                }
                
                clear_current_module_label();
                pushClaroMessage('Current module label set to : ' . get_current_module_label(), 'debug');
            }
            
            $claro_buffer->append("\n".'<!-- End of '.$this->name.' -->'."\n");
            
            return $claro_buffer->getContent();
        }
    }
?>