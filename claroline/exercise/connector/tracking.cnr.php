<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 415 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLQWZ
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

class CLQWZ_CourseTrackingRenderer extends CourseTrackingRenderer
{   
    private $tbl_qwz_exercise;
    private $tbl_qwz_tracking;
    
    public function __construct($courseId)
    {
        $this->courseId = $courseId;
        
        $tbl_cdb_names = get_module_course_tbl( array( 'qwz_exercise', 'qwz_tracking' ), $courseId );
        $this->tbl_qwz_exercise = $tbl_cdb_names['qwz_exercise'];
        $this->tbl_qwz_tracking = $tbl_cdb_names['qwz_tracking'];
    }
    
    protected function renderHeader()
    {
        return claro_get_tool_name('CLQWZ');
    }
    
    protected function renderContent()
    {
        $html = '';
        
        $sql = "SELECT TEX.`exo_id`,
                    COUNT(DISTINCT TEX.`user_id`) AS `nbr_distinct_user_attempts`,
                    COUNT(TEX.`exo_id`) AS `nbr_total_attempts`,
                    EX.`title`
                FROM `".$this->tbl_qwz_tracking."` AS TEX, `".$this->tbl_qwz_exercise."` AS EX
                WHERE TEX.`exo_id` = EX.`id`
                GROUP BY TEX.`exo_id`";

        $results = claro_sql_query_fetch_all($sql);
        
        $html .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center" style="width: 99%;">'."\n"
        .   '<thead><tr class="headerX">'."\n"
        .   '<th>&nbsp;'.get_lang('Exercises').'&nbsp;</th>'."\n"
        .   '<th>&nbsp;'.get_lang('User attempts').'&nbsp;</th>'."\n"
        .   '<th>&nbsp;'.get_lang('Total attempts').'&nbsp;</th>'."\n"
        .   '</tr></thead>'."\n"
        .   '<tbody>'."\n"
        ;

        if( !empty($results) && is_array($results) )
        {
            foreach( $results as $result )
            {
                    $html .= '<tr>'."\n"
                    .   '<td><a href="../exercise/track_exercises.php?exId='.$result['exo_id'].'">'.$result['title'].'</a></td>'."\n"
                    .   '<td align="right">'.$result['nbr_distinct_user_attempts'].'</td>'."\n"
                    .   '<td align="right">'.$result['nbr_total_attempts'].'</td>'."\n"
                    .   '</tr>'."\n\n"
                    ;
            }
        }
        else
        {
            $html .= '<tr>' . "\n"
            .    '<td colspan="3">'
            .    '<div align="center">' . get_lang('No result') . '</div>'
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
        }
        $html .= '</tbody>'."\n"
        .   '</table>'."\n"
        ;
            
        return $html;
    }
    
    protected function renderFooter()
    {
        return '';
    }
}

TrackingRendererRegistry::registerCourse('CLQWZ_CourseTrackingRenderer');



/*
 * 
 */
class CLQWZ_UserTrackingRenderer extends UserTrackingRenderer
{   
    private $tbl_qwz_exercise;
    private $tbl_qwz_tracking;
    
    public function __construct($courseId, $userId)
    {
        $this->courseId = $courseId;
        $this->userId = (int) $userId;
        
        $tbl_cdb_names = get_module_course_tbl( array( 'qwz_exercise', 'qwz_tracking' ), $courseId );
        $this->tbl_qwz_exercise = $tbl_cdb_names['qwz_exercise'];
        $this->tbl_qwz_tracking = $tbl_cdb_names['qwz_tracking'];
        
    }
    
    protected function renderHeader()
    {
        return claro_get_tool_name('CLQWZ');
    }
    
    protected function renderContent()
    {
        if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) )   $exId = (int) $_REQUEST['exId'];
        else                                                              $exId = null;
        
        $exerciseResults = $this->prepareContent();
        
        $jsloader = JavascriptLoader::getInstance();
        $jsloader->load('jquery');
        
        $html = '<script language="javascript" type="text/javascript">' . "\n"
        .    ' $(document).ready(function() {'
        .    '  $(\'.exerciseDetails\').hide();'
        .    '  $(\'.exerciseDetailsToggle\').click( function()'
        .    '  {'
        .    '   $(this).next(".exerciseDetails").toggle();'
        .    '   return false;'
        .    '  });'
        .    ' });' 
        .    '</script>'."\n\n";
        
        $html .= '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center" style="width: 99%;">' . "\n"
        .    '<thead>' . "\n"
        .    '<tr class="headerX">' . "\n"
        .    '<th>' . get_lang('Exercises').'</th>' . "\n"
        .    '<th>' . get_lang('Worst score').'</th>' . "\n"
        .    '<th>' . get_lang('Best score').'</th>' . "\n"
        .    '<th>' . get_lang('Average score').'</th>' . "\n"
        .    '<th>' . get_lang('Average Time').'</th>' . "\n"
        .    '<th>' . get_lang('Attempts').'</th>' . "\n"
        .    '<th>' . get_lang('Last attempt').'</th>' . "\n"
        .    '</tr>' . "\n"
        .    '</thead>' . "\n"
        ;
    
        if( !empty($exerciseResults) && is_array($exerciseResults) )
        {
            $html .= '<tbody>' . "\n";
            foreach( $exerciseResults as $result )
            {
                $html .= '<tr class="exerciseDetailsToggle">' . "\n"
                .    '<td><a href="#">'.htmlspecialchars($result['title']).'</td>' . "\n"
                .    '<td>'.(int) $result['minimum'].'</td>' . "\n"
                .    '<td>'.(int) $result['maximum'].'</td>' . "\n"
                .    '<td>'.(round($result['average']*10)/10).'</td>' . "\n"
                .    '<td>'.claro_html_duration(floor($result['avgTime'])).'</td>' . "\n"
                .    '<td>'.(int) $result['attempts'].'</td>' . "\n"
                .    '<td>'. claro_html_localised_date( 
                            get_locale('dateTimeFormatLong'), 
                            strtotime($result['lastAttempt'])
                        ) . "</td> \n"
                .    '</tr>' . "\n";
    
                // details
                $exerciseDetails = $this->getUserExerciceDetails($result['id']);
    
                if( is_array($exerciseDetails) && !empty($exerciseDetails) )
                {
                    $html .= '<tr class="exerciseDetails" >'
                    .    '<td>&nbsp;</td>' . "\n"
                    .    '<td colspan="6" class="noHover">' . "\n"
                    .    '<table class="claroTable emphaseLine" cellspacing="1" cellpadding="2" border="0" width="100%" style="width: 99%;">' . "\n"
                    .    '<thead>' . "\n"
                    .    '<tr class="headerX">' . "\n"
                    .    '<th><small>' . get_lang('Date').'</small></th>' . "\n"
                    .    '<th><small>' . get_lang('Score').'</small></th>' . "\n"
                    .    '<th><small>' . get_lang('Time').'</small></th>' . "\n"
                    .    '</tr>' . "\n"
                    .    '</thead>' . "\n"
                    .    '<tbody>' . "\n";
    
                    foreach ( $exerciseDetails as $details )
                    {
                        $html .= '<tr>' . "\n"
                        .    '<td><small>' . "\n"
                        .        '<a href="'.get_module_url('CLQWZ') . '/track_exercise_details.php?trackedExId='.$details['id'].'">'
                        .           claro_html_localised_date( 
                                        get_locale('dateTimeFormatLong'), 
                                        strtotime($details['date'])
                                    )
                        .       '</a></small></td>' . "\n"
                        .    '<td><small>'.$details['result'].'/'.$details['weighting'].'</small></td>' . "\n"
                        .    '<td><small>'.claro_html_duration($details['time']).'</small></td>' . "\n"
                        .    '</tr>' . "\n";
                    }
                    $html .= '</tbody>' . "\n"
                    .    '</table>' . "\n\n"
                    .    '</td>' . "\n"
                    .    '</tr>' . "\n";
                }
    
            }
            $html .= '</tbody>' . "\n";
        }
        else
        {
            $html .= '<tbody>' . "\n"
            .    '<tr>' . "\n"
            .    '<td colspan="7" align="center">' . get_lang('No result').'</td>' . "\n"
            .    '</tr>' . "\n"
            .    '</tbody>' . "\n"
            ;
        }
        $html .= '</table>' . "\n\n";
        
        return $html;
    }
    
    protected function renderFooter()
    {
        return get_lang('Click on exercise title for more details');
    }
    
    private function prepareContent()
    {
        $sql = "SELECT `E`.`title`,
                       `E`.`id`,
                       MIN(`TEX`.`result`)    AS `minimum`,
                       MAX(`TEX`.`result`)    AS `maximum`,
                       AVG(`TEX`.`result`)    AS `average`,
                       MAX(`TEX`.`weighting`) AS `weighting`,
                       COUNT(`TEX`.`user_id`) AS `attempts`,
                       MAX(`TEX`.`date`)      AS `lastAttempt`,
                       AVG(`TEX`.`time`)      AS `avgTime`
                  FROM `" . $this->tbl_qwz_exercise . "` AS `E`
                     , `" . $this->tbl_qwz_tracking . "` AS `TEX`
            WHERE `TEX`.`user_id` = " . (int) $this->userId . "
                AND `TEX`.`exo_id` = `E`.`id`
            GROUP BY `TEX`.`exo_id`
            ORDER BY `E`.`title` ASC";
    
        $results = claro_sql_query_fetch_all($sql);
    
        return $results;
    }
    
    private function getUserExerciceDetails($exerciseId)
    {
        $sql = "SELECT `id`, `date`, `result`, `weighting`, `time`
                FROM `" . $this->tbl_qwz_tracking . "`
                WHERE `exo_id` = ". (int) $exerciseId."
                AND `user_id` = ". (int) $this->userId."
                ORDER BY `date` ASC";
    
        $results = claro_sql_query_fetch_all($sql);
    
        return $results;
    }
}

TrackingRendererRegistry::registerUser('CLQWZ_UserTrackingRenderer');
