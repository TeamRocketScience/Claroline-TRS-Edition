<?php // $Id: assignment.class.php 12923 2011-03-03 14:23:57Z abourguignon $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * The script works with the 'assignment' tables in the main claroline table
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLWRK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

class Assignment
{
    /**
     * @var $id id of assignment, -1 if assignment doesn't exist already
     */
    var $id;

    /**
     * @var $title name of the assignment
     */
    var $title;

    /**
     * @var $description statement of the assignment
     */
    var $description;

    /**
     * @var $visibility visibility of the assignment
     */
    var $visibility;

    /**
     * @var $defaultSubmissionVisibility default visibility of new submissions in this assignement
     */
    var $defaultSubmissionVisibility;

    /**
     * @var $assignmentType is the assignment for groups or for individuals
     */
    var $assignmentType;

    /**
     * @var $submissionType expected submission type (text, text and file, file)
     */
    var $submissionType;

    /**
     * @var $allowLateUpload is upload allowed after assignment end date
     */
    var $allowLateUpload;

    /**
     * @var $startDate submissions are not possible before this date
     */
    var $startDate;

    /**
     * @var $endDate submissions are not possible after this date (except if $allowLateUpload is true)
     */
    var $endDate;

    /**
     * @var $autoFeedbackText text of automatic feedback
     */
    var $autoFeedbackText;

    /**
     * @var $autoFeedbackFilename file of automatic feedback
     */
    var $autoFeedbackFilename;

    /**
     * @var $autoFeedbackSubmitMethod automatic feedback submit method
     */
    var $autoFeedbackSubmitMethod;

    /**
     * @var $submissionList
     */
    var $submissionList;

    /**
     * @var $assigDirSys sys path to assignment dir
     */
    var $assigDirSys;

    /**
     * @var $assigDirWeb web path to assignment dir
     */
    var $assigDirWeb;

    /**
     * @var $tblAssignment assignment table
     */
    var $tblAssignment;

    /**
     * @var $tblSubmission submission table
     */
    var $tblSubmission;

    /**
     * constructor
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     */
    function Assignment($course_id = null)
    {
        $this->id = (int) -1;
        $this->title = '';
        $this->description = '';
        $this->visibility = 'VISIBLE';
        $this->defaultSubmissionVisibility = 'VISIBLE';
        $this->assignmentType = 'INDIVIDUAL';
        $this->submissionType = 'FILE';
        $this->allowLateUpload = 'YES';
        $this->startDate = time(); // now as unix timestamp
        $this->endDate = strtotime("+1 year"); // one year later
        $this->autoFeedbackText = '';
        $this->autoFeedbackFilename = '';
        $this->autoFeedbackSubmitMethod = 'ENDDATE';

        $this->submissionList = array();

        $this->assigDirSys = '';
        $this->assigDirWeb = '';

        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        $this->tblAssignment = $tbl_cdb_names['wrk_assignment'];
        $this->tblSubmission = $tbl_cdb_names['wrk_submission'];
    }

    /**
     * load an assignment from DB
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param integer $assignment_id id of assignment
     * @return boolean load successfull ?
     */
    function load($id)
    {
        $sql = "SELECT
                    `id`,
                    `title`,
                    `description`,
                    `visibility`,
                    `def_submission_visibility`,
                    `assignment_type`,
                    `authorized_content`,
                    `allow_late_upload`,
                    UNIX_TIMESTAMP(`start_date`) AS `unix_start_date`,
                    UNIX_TIMESTAMP(`end_date`) AS `unix_end_date`,
                    `prefill_text`,
                    `prefill_doc_path`,
                    `prefill_submit`
            FROM `".$this->tblAssignment."`
            WHERE `id` = ".(int) $id;

        $data = claro_sql_query_get_single_row($sql);

        if( !empty($data) )
        {
            // from query
            $this->id = (int) $data['id'];
            $this->title = $data['title'];
            $this->description = $data['description'];
            $this->visibility = $data['visibility'];
            $this->defaultSubmissionVisibility = $data['def_submission_visibility'];
            $this->assignmentType = $data['assignment_type'];
            $this->submissionType = $data['authorized_content'];
            $this->allowLateUpload = $data['allow_late_upload'];
            $this->startDate = $data['unix_start_date'];
            $this->endDate = $data['unix_end_date'];
            $this->autoFeedbackText = $data['prefill_text'];
            $this->autoFeedbackFilename = $data['prefill_doc_path'];
            $this->autoFeedbackSubmitMethod = $data['prefill_submit'];

            // build
            $this->buildDirPaths();

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * save assignment to DB
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return mixed false or id of the record
     */
    function save()
    {
        // TODO method to validate data
        if( $this->id == -1 )
        {
            // insert
            $sql = "INSERT INTO `".$this->tblAssignment."`
                    SET `title` = '".claro_sql_escape($this->title)."',
                        `description` = '".claro_sql_escape($this->description)."',
                        `visibility` = '".claro_sql_escape($this->visibility)."',
                        `def_submission_visibility` = '".claro_sql_escape($this->defaultSubmissionVisibility)."',
                        `assignment_type` = '".claro_sql_escape($this->assignmentType)."',
                        `authorized_content` = '".claro_sql_escape($this->submissionType)."',
                        `allow_late_upload` = '".claro_sql_escape($this->allowLateUpload)."',
                        `start_date` = FROM_UNIXTIME('".claro_sql_escape($this->startDate)."'),
                        `end_date` = FROM_UNIXTIME('".claro_sql_escape($this->endDate)."'),
                        `prefill_text` = '".claro_sql_escape($this->autoFeedbackText)."',
                        `prefill_doc_path` = '".claro_sql_escape($this->autoFeedbackFilename)."',
                        `prefill_submit` = '".claro_sql_escape($this->autoFeedbackSubmitMethod)."'";

            // execute the creation query and get id of inserted assignment
            $insertedId = claro_sql_query_insert_id($sql);

            if( $insertedId )
            {
                $this->id = (int) $insertedId;

                $this->buildDirPaths();

                // create the assignment directory if query was successfull and dir not already exists
                if( !is_dir( $this->assigDirSys ) ) claro_mkdir( $this->assigDirSys , CLARO_FILE_PERMISSIONS,true);

                return $this->id;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if( !get_conf('confval_def_sub_vis_change_only_new') )
            {
                // get current assignment defaultSubmissionVisibility
                $sqlGetOldData = "SELECT `def_submission_visibility`
                                 FROM `".$this->tblAssignment."`
                                 WHERE `id` = '".$this->id."'";

                $prevDefaultSubmissionVisibility = claro_sql_query_get_single_value($sqlGetOldData);

                // change visibility of all works only if defaultSubmissionVisibility has changed
                if( $this->defaultSubmissionVisibility != $prevDefaultSubmissionVisibility )
                {
                    $this->updateAllSubmissionsVisibility($this->defaultSubmissionVisibility);
                }
            }

            // update, main query
            $sql = "UPDATE `".$this->tblAssignment."`
                    SET `title` = '".claro_sql_escape($this->title)."',
                        `description` = '".claro_sql_escape($this->description)."',
                        `visibility` = '".claro_sql_escape($this->visibility)."',
                        `def_submission_visibility` = '".claro_sql_escape($this->defaultSubmissionVisibility)."',
                        `assignment_type` = '".claro_sql_escape($this->assignmentType)."',
                        `authorized_content` = '".claro_sql_escape($this->submissionType)."',
                        `allow_late_upload` = '".claro_sql_escape($this->allowLateUpload)."',
                        `start_date` = FROM_UNIXTIME('".claro_sql_escape($this->startDate)."'),
                        `end_date` = FROM_UNIXTIME('".claro_sql_escape($this->endDate)."'),
                        `prefill_text` = '".claro_sql_escape($this->autoFeedbackText)."',
                        `prefill_doc_path` = '".claro_sql_escape($this->autoFeedbackFilename)."',
                        `prefill_submit` = '".claro_sql_escape($this->autoFeedbackSubmitMethod)."'
                    WHERE `id` = '".$this->id."'";

            // execute and return main query
            if( claro_sql_query($sql) )
            {
                return $this->id;
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * delete assignment from DB
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean
     */
    function delete()
    {
        $sql = "DELETE FROM `".$this->tblSubmission."`
                WHERE `assignment_id` = '".$this->id."'";

        if( claro_sql_query($sql) )
        {
            $sql = "DELETE FROM `".$this->tblAssignment."`
                    WHERE `id` = '".$this->id."'";

            if( claro_sql_query($sql) )
            {
                claro_delete_file($this->assigDirSys);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }

        $this->id = -1;
        return true;
    }

    /**
     * check if data are valide
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean
     */
    function validate()
    {
        // title is a mandatory element
        $title = trim( strip_tags($this->title) );

        if( empty($title) )
        {
            claro_failure::set_failure('assignment_no_title');
            return false;
        }
        else
        {
            // check if title already exists
            if( $this->id == -1 )
            {
                // insert
                $sql = "SELECT `title`
                        FROM `" . $this->tblAssignment . "`
                        WHERE `title` = '" . claro_sql_escape($this->title) . "'";
            }
            else
            {
                // update
                $sql = "SELECT `title`
                        FROM `".$this->tblAssignment."`
                        WHERE `title` = '" . claro_sql_escape($this->title) . "'
                        AND `id` != " . (int) $this->id;
            }

            $query = claro_sql_query($sql);

            if( mysql_num_rows($query) != 0 )
            {
                claro_failure::set_failure('assignment_title_already_exists');
                return false;
            }
        }

        // dates : check if start date is lower than end date else we will have a paradox
        if( $this->endDate <= $this->startDate )
        {
            claro_failure::set_failure('assignment_incorrect_dates');
            return false;
        }

        return true; // no errors, form is valide
    }

    /**
     * update visibility of all submissions of the assignment
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $visibility
     * @return boolean
     */
    function updateAllSubmissionsVisibility($visibility)
    {
        $acceptedValues = array('VISIBLE', 'INVISIBLE');

        if( in_array($visibility, $acceptedValues) )
        {
            // adapt visibility of all submissions of the assignment
            // according to the default submission visibility
            $sql = "UPDATE `".$this->tblSubmission."`
                    SET `visibility` = '".claro_sql_escape($visibility)."'
                    WHERE `assignment_id` = ".$this->id."
                    AND `visibility` != '".claro_sql_escape($visibility)."'";

            return claro_sql_query ($sql);
        }

        return false;
    }

    /**
     * update visibility of an assignment
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param integer $assignmentId
     * @param string $visibility
     * @return boolean
     */
    function updateAssignmentVisibility($assignmentId, $visibility)
    {
        // this method is not used in object context so we cannot access $this->$tblAssignment
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tblAssignment = $tbl_cdb_names['wrk_assignment'];

        $acceptedValues = array('VISIBLE', 'INVISIBLE');

        if( in_array($visibility, $acceptedValues) )
        {
            $sql = "UPDATE `" . $tblAssignment . "`
                       SET `visibility` = '" . $visibility . "'
                     WHERE `id` = " . (int) $assignmentId . "
                       AND `visibility` != '" . $visibility . "'";

            return  claro_sql_query($sql);
        }

        return false;
    }

    /**
     * get submission list of assignment for a user/group
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return array
     * @TODO get the full list is authId is not specified (submissions and feedback for all authors)
     */
    function getSubmissionList($authId)
    {
        if( $this->assignmentType == 'GROUP' )
            $authCondition = '`group_id` = '.(int) $authId;
        else
            $authCondition = '`user_id` = '.(int) $authId;

        $sql = "SELECT `id`
                     FROM `" . $this->tblSubmission . "`
                    WHERE ".$authCondition."
                      AND `assignment_id` = ". (int) $this->id;

        return claro_sql_query_fetch_all($sql);
    }

    /**
     * builds required paths and sets values in assigDirSys and assigDirWeb
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     */
    function buildDirPaths()
    {
        $this->assigDirSys = get_conf('coursesRepositorySys').claro_get_course_path().'/'.'work/assig_'.$this->id.'/';
        $this->assigDirWeb = get_conf('coursesRepositoryWeb').claro_get_course_path().'/'.'work/assig_'.$this->id.'/';
    }

    /**
     * get a unique filename for the new file to add
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string unique filename with extension
     */
    function createUniqueFilename($filename)
    {
        $dotPosition = strrpos($filename, '.');

        if( $dotPosition !== false &&  $dotPosition != 0 )
        {
            // if a dot was found and not as first letter (case of files like .blah)
            $basename = substr($filename, 0, $dotPosition );
            $extension = substr($filename, $dotPosition);
        }
        else
        {
            // if we have no extension
            $basename = $filename;
            $extension = '';
        }
        $i = 1;
        while( file_exists($this->assigDirSys.$basename.'_'.$i.$extension) ) $i++;

        return $basename.'_'.$i.$extension;
    }

    /**
     * get title
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getTitle()
    {
        return $this->title;
    }

    /**
     * set title
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value
     */
    function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * get description
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getDescription()
    {
        return $this->description;
    }

    function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     * get visibility ('VISIBLE', 'INVISIBLE')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getVisibility()
    {
        return $this->visibility;
    }

    function setVisibility($value)
    {
        $acceptedValues = array('VISIBLE', 'INVISIBLE');

        if( in_array($value, $acceptedValues) )
        {
            $this->visibility = $value;
            return true;
        }
        return false;
    }

    /**
     * get default submission visibility ('VISIBLE', 'INVISIBLE')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getDefaultSubmissionVisibility()
    {
        return $this->defaultSubmissionVisibility;
    }

    function setDefaultSubmissionVisibility($value)
    {
        $acceptedValues = array('VISIBLE', 'INVISIBLE');

        if( in_array($value, $acceptedValues) )
        {
            $this->defaultSubmissionVisibility = $value;
            return true;
        }
        return false;
    }

    /**
     * get assignment type ('INDIVIDUAL', 'GROUP')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAssignmentType()
    {
        return $this->assignmentType;
    }

    function setAssignmentType($value)
    {
        $acceptedValues = array('INDIVIDUAL', 'GROUP');

        if( in_array($value, $acceptedValues) )
        {
            $this->assignmentType = $value;
            return true;
        }
        return false;
    }

    /**
     * get submission type ('TEXT', 'TEXTFILE', 'FILE')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getSubmissionType()
    {
        return $this->submissionType;
    }

    function setSubmissionType($value)
    {
        $acceptedValues = array('TEXT', 'TEXTFILE', 'FILE');

        if( in_array($value, $acceptedValues) )
        {
            $this->submissionType = $value;
            return true;
        }
        return false;
    }

    /**
     * get value of allow late upload ('YES', 'NO')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAllowLateUpload()
    {
        return $this->allowLateUpload;
    }

    function setAllowLateUpload($value)
    {
        $acceptedValues = array('YES', 'NO');

        if( in_array($value, $acceptedValues) )
        {
            $this->allowLateUpload = $value;
            return true;
        }
        return false;
    }

    /**
     * get start date (as unix timestamp)
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return integer a unix time stamp
     */
    function getStartDate()
    {
        return $this->startDate;
    }

    function setStartDate($value)
    {
        $this->startDate = (int) $value;
    }

    /**
     * get end date (as unix timestamp)
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return integer a unix time stamp
     */
    function getEndDate()
    {
        return $this->endDate;
    }

    function setEndDate($value)
    {
        $this->endDate = (int) $value;
    }

    /**
     * get text auto submitted feedback
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAutoFeedbackText()
    {
        return $this->autoFeedbackText;
    }

    function setAutoFeedbackText($value)
    {
        $this->autoFeedbackText = $value;
    }

    /**
     * get filename of a file (if exists) attached to the auto submitted feedback
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAutoFeedbackFilename()
    {
        return $this->autoFeedbackFilename;
    }

    function setAutoFeedbackFilename($value)
    {
        $this->autoFeedbackFilename = $value;
    }

    /**
     * get the method of submission of auto submitted feedbacks ('ENDDATE', 'AFTERPOST')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAutoFeedbackSubmitMethod()
    {
        return $this->autoFeedbackSubmitMethod;
    }

    function setAutoFeedbackSubmitMethod($value)
    {
        $acceptedValues = array('ENDDATE', 'AFTERPOST');

        if( in_array($value, $acceptedValues) )
        {
            $this->autoFeedbackSubmitMethod = $value;
            return true;
        }
        return false;
    }

    /**
     * get the full systeme path of the assignment directory
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAssigDirSys()
    {
        return $this->assigDirSys;
    }

    /**
     * get the full web path of the assignment directory
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string
     */
    function getAssigDirWeb()
    {
        return $this->assigDirWeb;
    }

    /**
     * check if the user can upload a submission at this date
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean
     */
    function isUploadDateOk()
    {
        $now = time();

        $assignmentStarted = (bool) ( $this->startDate <= $now );
        $assignmentNotFinished = (bool) ( $now < $this->endDate );
        $canUploadAfterEnd = (bool) ( $this->allowLateUpload == 'YES' );

        return (bool) $assignmentStarted && ( $assignmentNotFinished || $canUploadAfterEnd );
    }
}
