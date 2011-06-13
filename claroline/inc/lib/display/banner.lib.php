<?php // $Id: banner.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Class used to configure and display the page banners
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

FromKernel::uses ( 'display/breadcrumbs.lib', 'display/viewmode.lib' );

class ClaroBanner extends CoreTemplate
{
    protected static $instance = false;
    
    protected $hidden = false;
    public $breadcrumbs;
    public $viewmode;

    public function __construct()
    {
        $this->breadcrumbs = ClaroBreadCrumbs::getInstance();
        $this->viewmode = ClaroViewMode::getInstance();
        parent::__construct('banner.tpl.php');
        
        $this->breadcrumbLine = true;
    }
    
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new ClaroBanner;
        }

        return self::$instance;
    }
    
    /**
     * Hide the banners
     */
    public function hide()
    {
        $this->hidden = true;
    }
    
    /**
     * Show the banners
     */
    public function show()
    {
        $this->hidden = false;
    }
    
    /**
     * Hide breadcrump line
     */
    public function hideBreadcrumbLine()
    {
        $this->breadcrumbLine = false;
    }
    
    /**
     * Render the banners
     * @return  string
     */
    public function render()
    {
        if ( $this->hidden )
        {
            return '<!-- banner hidden -->' . "\n";
        }
        
        $this->_prepareCampusBanner();
        $this->_prepareUserBanner();
        $this->_prepareCourseBanner();
        
        return parent::render();
    }
    
    private function _prepareCourseBanner()
    {
        if ( claro_is_in_a_course() )
        {
            $_courseToolList = claro_get_current_course_tool_list_data();
            
            if (is_array($_courseToolList)
                && claro_is_course_allowed())
            {
                $toolNameList = claro_get_tool_name_list();
                
                foreach($_courseToolList as $_courseToolKey => $_courseToolDatas)
                {

                    if (isset($_courseToolDatas['name'])
                        && !is_null($_courseToolDatas['name'])
                        && isset($_courseToolDatas['label']))
                    {
                        $_courseToolList[ $_courseToolKey ] [ 'name' ] = $toolNameList[ $_courseToolDatas['label'] ];
                    }
                    else
                    {
                        $external_name = $_courseToolList[ $_courseToolKey ] [ 'external_name' ] ;
                        $_courseToolList[ $_courseToolKey ] [ 'name' ] = get_lang($external_name);
                    }
                    
                    // now recheck to be sure the value is really filled before going further
                    if ($_courseToolList[ $_courseToolKey ] [ 'name' ] =='')
                    {
                        $_courseToolList[ $_courseToolKey ] [ 'name' ] = get_lang('No name');
                    }
                }
                
                // default option
                $courseToolSelectorOptions = '<option value="' . get_path('clarolineRepositoryWeb') . 'course/index.php?cid=' . htmlspecialchars(claro_get_current_course_id()) .'" style="background:url(' . get_icon_url('course') . ') no-repeat;">' . get_lang('Course Home') . '</option>' . "\n";
                
                if (is_array($_courseToolList))
                {
                    foreach($_courseToolList as $_courseToolKey => $_courseToolData)
                    {
                        //find correct url to access current tool

                        if (isset($_courseToolData['url']))
                        {
                            if (!empty($_courseToolData['label']))
                            {
                                $_courseToolData['url'] = get_module_url($_courseToolData['label']) . '/' . $_courseToolData['url'];
                                
                                if ( strpos( $_courseToolData['url'], '?' ) )
                                {
                                    $_courseToolData['url'] .= '&' . claro_get_current_course_id();
                                }
                                else
                                {
                                    $_courseToolData['url'] .= '?' . claro_get_current_course_id();
                                }
                            }
                            
                            // reset group to access course tool

                            if (claro_is_in_a_group() && !$_courseToolData['external'])
                            {
                                $_toolDataUrl = strpos($_courseToolData['url'], '?') !== false
                                    ? $_courseToolData['url'] . '&amp;gidReset=1'
                                    : $_courseToolData['url'] . '?gidReset=1'
                                    ;
                            }
                            else
                            {
                                $_toolDataUrl = $_courseToolData['url'];
                            }
                        }
                        
                        //find correct url for icon of the tool
                        // External tool
                        if( empty($_courseToolData['label']) )
                        {
                            $_toolIconUrl = get_icon_url('link');
                        }
                        // Declared icon
                        elseif (isset($_courseToolData['icon']))
                        {
                            $_toolIconUrl = get_module_url($_courseToolData['label']).'/'.$_courseToolData['icon'];
                        }
                        // Default icon
                        else
                        {
                            $_toolIconUrl = get_icon_url('tool');
                        }
                        
                        // select "groups" in group context instead of tool
                        if ( claro_is_in_a_group() )
                        {
                            $toolSelected = $_courseToolData['label'] == 'CLGRP' ? 'selected="selected"' : '';
                        }
                        else
                        {
                            $toolSelected = $_courseToolData['id'] == claro_get_current_tool_id() ? 'selected="selected"' : '';
                        }
                        $_courseToolDataName = $_courseToolData['name'];
                        $courseToolSelectorOptions .= '<option value="' . $_toolDataUrl . '" '
                        .   $toolSelected
                        .   'style="background:url('.$_toolIconUrl.') no-repeat;">'
                        .    get_lang($_courseToolDataName)
                        .    '</option>'."\n"
                        ;
                    }
                } // end if is_array _courseToolList
                
                $courseToolSelector = '<form action="" name="redirector" id="redirector" method="post">' . "\n"
                . '<select name="url" size="1" onchange="top.location=redirector.url.options[selectedIndex].value" >' . "\n\n"
                . $courseToolSelectorOptions . "\n"
                    . '</select>' . "\n"
                    . '<noscript>' . "\n"
                    . '<input type="submit" name="gotool" value="go" />' . "\n"
                    . '</noscript>' . "\n"
                    . '</form>' . "\n\n"
                    ;
                
                $this->assign('courseToolSelector', $courseToolSelector );
            }
            
            $this->showBlock('courseBanner');
        }
        else
        {
            $this->hideBlock('courseBanner');
        }
    }
    
    /**
     * Prepare the user banner
     */
    private function _prepareUserBanner()
    {
        if( claro_is_user_authenticated() )
        {
            $userToolUrlListLeft    = array();
            $userToolUrlListRight   = array();
            
            if (get_conf(get_conf('display_former_homepage')))
            {
                
            }
            
            $userToolUrlListLeft[]  = '<a href="'
                . get_path('clarolineRepositoryWeb')
                . 'desktop/index.php" target="_top">'
                . get_lang('My desktop').'</a>'
                ;
            
            $userToolUrlListLeft[]  = '<a href="'
                . get_path('clarolineRepositoryWeb')
                . 'auth/profile.php" target="_top">'
                . get_lang('My user account').'</a>'
                ;

            $userToolUrlListLeft[]  = '<a href="'
                . get_path('clarolineRepositoryWeb')
                . 'messaging" target="_top">'
                . get_lang('My messages').'</a>'
                ;
            
            if(claro_is_platform_admin())
            {
                $userToolUrlListLeft[] = '<a href="'
                    . get_path('clarolineRepositoryWeb')
                    .'admin/" target="_top">'
                    . get_lang('Platform administration'). '</a>'
                    ;
            }
            
            $userToolUrlListRight[] = '<a href="'.  get_path('url')
                . '/index.php?logout=true" target="_top">'
                . get_lang('Logout').'</a>'
                ;
            
            $this->assign('userToolListRight'
                , claro_html_menu_horizontal($userToolUrlListRight));
            
            $this->assign('userToolListLeft'
                , claro_html_menu_horizontal($userToolUrlListLeft));
            
            $this->showBlock('userBanner');
        }
        else
        {
            $this->hideBlock('userBanner');
        }
    }
    
    /**
     * Prepare the campus banner
     */
    private function _prepareCampusBanner()
    {
        $campus = array();
        
        $campus['siteName'] =  get_conf('siteLogo') != ''
            ? '<img src="' . get_conf('siteLogo') . '" alt="'.get_conf('siteName').'"  />'
            : get_conf('siteName')
            ;

        $institutionNameOutput = '';

        $bannerInstitutionName = (get_conf('institutionLogo') != '')
            ? '<img src="' . get_conf('institutionLogo')
                . '" alt="' . get_conf('institution_name') . '" />'
            : get_conf('institution_name')
            ;

        if( !empty($bannerInstitutionName) )
        {
            if( get_conf('institution_url') != '' )
            {
                $institutionNameOutput .= '<a href="'
                    . get_conf('institution_url').'" target="_top">'
                    . $bannerInstitutionName.'</a>'
                    ;
            }
            else
            {
                $institutionNameOutput .= $bannerInstitutionName;
            }
        }

        /* --- External Link Section --- */
        if( claro_get_current_course_data('extLinkName') != '' )
        {
            $institutionNameOutput .= get_conf('institution_url') != ''
                ? ' / '
                : ' '
                ;

            if( claro_get_current_course_data('extLinkUrl') != '' )
            {
                $institutionNameOutput .= '<a href="'
                    . claro_get_current_course_data('extLinkUrl')
                    . '" target="_top">'
                    . claro_get_current_course_data('extLinkName')
                    . '</a>'
                    ;
            }
            else
            {
                $institutionNameOutput .= claro_get_current_course_data('extLinkName');
            }
        }
        
        $campus['institution'] = $institutionNameOutput;

        $this->assign( 'campus', $campus );
    }
}
