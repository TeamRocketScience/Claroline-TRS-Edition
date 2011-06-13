<?php // $Id: courseReport.php 9858 2008-03-11 07:49:45Z gregk84 $
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision: 9858 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux <seb@claroline.net>
 *
 * @package CLTRACK
 */

/*
 * Kernel
 */
require_once dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';



/*
 * Permissions
 */
if( ! get_conf('is_trackingEnabled') ) claro_die(get_lang('Tracking has been disabled by system administrator.')); 
if( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
if( ! claro_is_course_manager() ) claro_die(get_lang('Not allowed'));

/*
 * Libraries
 */
require_once dirname( __FILE__ ) . '/lib/trackingRenderer.class.php';
require_once dirname( __FILE__ ) . '/lib/trackingRendererRegistry.class.php';

/*
 * Init some other vars
 */


/*
 * Output
 */
$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'tracking', 'screen');

// initialize output
$claroline->setDisplayType( CL_PAGE );

$nameTools = get_lang('Statistics');

$html = '';

$html .= claro_html_tool_title(
                array(
                    'mainTitle' => $nameTools,
                    'subTitle'  => get_lang('Statistics of course : %courseCode', array('%courseCode' => claro_get_current_course_data('officialCode')))
                )
            );

// display link to delete all course stats
$links[] = '<a class="claroCmd"  href="delete_course_stats.php">'
            .    '<img src="' . get_icon_url('delete') . '" alt="" />'
            .    get_lang('Delete all course statistics')
            .    '</a>'."\n"
            ;

$html .= '<p>' . claro_html_menu_horizontal($links) . '</p>' . "\n\n" ;
            
/*
 * Prepare rendering : 
 * Load and loop through available tracking renderers
 * Order of renderers blocks is arranged using "first found, first display" in the registry
 * Modify the registry to change the load order if required
 */
// get all renderers by using registry
$trackingRendererRegistry = TrackingRendererRegistry::getInstance(claro_get_current_course_id());

// here we need course tracking renderers
$courseTrackingRendererList = $trackingRendererRegistry->getCourseRendererList();

foreach( $courseTrackingRendererList as $ctr )
{
    $renderer = new $ctr( claro_get_current_course_id() );
    $html .= $renderer->render();
}


/*
 * Output rendering
 */
$claroline->display->body->setContent($html);

echo $claroline->display->render();

?>