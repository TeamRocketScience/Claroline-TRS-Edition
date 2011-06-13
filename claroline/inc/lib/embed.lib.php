<?php // $Id: embed.lib.php 12923 2011-03-03 14:23:57Z abourguignon $

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Embed script output into Claroline layout
 *
 * @version     1.10 $Revision: 12923 $
 * @deprecated since Claroline 1.9
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE
 * @package     EMBED
 */

/**
 * Embed script output into Claroline layout
 * @deprecated since Claroline 1.9
 * @param   string  $output output to embed
 * @param   bool    $hide_banner hide Claroline banner (opt)
 * @param   bool    $hide_footer hide Claroline banner (opt)
 * @param   bool    $hide_body hide Claroline banner (opt)
 * @todo    TODO return string instead of echoing it
 * @deprecated  since Claroline 1.9
 */
function claro_embed( $output
    , $inPopup = false
    , $hide_banner = false
    , $hide_footer = false
    , $hide_body = false
    , $no_body = false )
{
    pushClaroMessage( __FUNCTION__ . ' is deprecated please use the new display lib instead' );
    
    // global variables needed by header and footer...
    // FIXME make global objects with all these craps !!!
    global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $urlAppend ,
           $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email;
    global $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
           $is_courseAllowed, $imgRepositoryWeb, $_tid, $is_courseMember, $_gid;
    global $claroBodyOnload, $httpHeadXtra, $htmlHeadXtra, $charset, $interbredcrump,
           $noPHP_SELF, $noQUERY_STRING;
    global $institution_name, $institution_url;
    global $no_body;

    if ( true == $inPopup )
    {
        $output = PopupWindowHelper::popupEmbed( $output );
        $hide_banner = true;
        $hide_footer = true;
    }

    // embed script output here
    require $includePath . '/claro_init_header.inc.php';
    echo $output;
    require $includePath . '/claro_init_footer.inc.php' ;
}

/**
 * Claroline script embed class
 *
 * @access  public
 * @deprecated since Claroline 1.9
 */
class ClarolineScriptEmbed
{
    var $inPopup = false;
    var $inFrame = false;
    var $inFrameset = false;
    var $hide_footer = false;
    var $hide_banner = false;
    var $hide_body = false;
    var $content = '';

    // claroline diplay options
    
    public function ClarolineScriptEmbed()
    {
        pushClaroMessage( __CLASS__ . ' is deprecated please use the new display lib instead' );
    }

    /**
     * Hide Claroline banner in display
     *
     * @access  public
     */
    function hideBanner()
    {
        $this->hide_banner = true;
    }
    
    /**
     * Hide Claroline footer in display
     *
     * @access  public
     */
    function hideFooter()
    {
        $this->hide_footer = true;
    }
    
    /**
     * Hide Claroline claroBody class div in display
     *
     * @access  public
     */
    function hideClaroBody()
    {
        $this->hide_body = true;
    }

    // display mode

    /**
     * Set options to display in a popup window
     *
     * @access  public
     */
    function popupMode()
    {
        $this->hideBanner();
        $this->hideFooter();
        $this->inPopup = true;
    }
    
    /**
     * Set options to display in a frame
     *
     * @access  public
     */
    function frameMode()
    {
        $this->hideBanner();
        $this->hideFooter();
        $this->inFrame = true;
    }

    /*function embedInPage()
    {
        $this->hideBanner();
        $this->hideFooter();
        $this->hideBody();
    }*/

    /**
     * Set page content
     *
     * @access  public
     * @param   string content, page content
     */
    function setContent( $content )
    {
        $this->content = $content;
    }

    // claroline header methods

    /**
     * Add extra HTML header elements
     *
     * @access  public
     * @param   string content, page content
     */
    function addHtmlHeader( $header )
    {
        $GLOBALS['htmlHeadXtra'][] = $header;
    }
    
    /**
     * Add extra HTTP header elements
     *
     * @access  public
     * @param   string content, page content
     */
    function addHttpHeader( $header )
    {
        $GLOBALS['httpHeadXtra'][] = $header;
    }
    
    /**
     * Add extra javascript executed when body is loaded
     *
     * @access  public
     * @param   string content, page content
     */
    function addBodyOnloadFunction( $function )
    {
        $GLOBALS['claroBodyOnload'][] = $function;
    }

    // output methods
    /**
     * Generate and set output to client
     *
     * @access  public
     */
    function output()
    {
        if ( $this->inPopup )
        {
            $this->content = PopupWindowHelper::popupEmbed( $this->content );
        }

        $this->embed( $this->content
            , $this->hide_banner
            , $this->hide_footer
            , $this->hide_body );
    }

    /**
     * Embed given contents in Claroline page layout
     *
     * @access  public
     * @static
     * @param   string output, content to display in page
     * @param   bool hide_banner, set to true hide Claroline banner
     * @param   bool hide_footer, set to true hide Claroline footer
     * @param   bool hide_body, set to true remove Claroline claroBody div
     * @todo    TODO return string instead of echoing it
     */
    function embed( $output
        , $hide_banner = false
        , $hide_footer = false
        , $hide_body = false )
    {
        // global variables needed by header and footer...
        // FIXME make global objects with all these craps !!!
        global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $urlAppend ,
           $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email;
        global $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
               $is_courseAllowed, $imgRepositoryWeb, $_tid, $is_courseMember, $_gid;
        global $claroBodyOnload, $httpHeadXtra, $htmlHeadXtra, $charset, $interbredcrump,
               $noPHP_SELF, $noQUERY_STRING;
        global $institution_name, $institution_url;

        // embed script output here
        require $includePath . '/claro_init_header.inc.php';
        echo $this->content;
        require $includePath . '/claro_init_footer.inc.php' ;
    }
}
