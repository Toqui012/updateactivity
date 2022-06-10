<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Archivo de funciones auxiliares lib.php
 *
 * @package   block_activitydate
 * @copyright 2022 AP_Iplacex
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * 
 */

require_once($CFG->libdir . "/externallib.php");


class local_ajaxdemo_external extends external_api
{

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function getteachersincourse_parameters()
    {
        return new external_function_parameters(
          array("id" => new external_value(PARAM_INT, "id"))
        );
    }

    /**
     * Returns welcome message
     * @return array = array('' => , ); welcome message
     */
    public static function getteachersincourse($id)
    {
        global $USER;
        global $DB;
        global $CFG;

        //$context = context_system::instance();
        // $context = context_user::instance($USER->id);
        // self::validate_context($context);

        $params = self::validate_parameters(
            self::getteachersincourse_parameters(),
                array('id'=>$id)
        );

        $teachers = $DB->get_records('course', array('category'=> $id));
        return json_encode(array_values($teachers));
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function getteachersincourse_returns()
    {
        return new external_value(PARAM_RAW, 'The updated JSON output');
//        return new external_value();//new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }

}

function get_categories_todb()
{   
    global $DB;
    return $DB->get_records('course_categories');
}

function ismayor($dateSelected, $dateInitialCourse)
{   
    if ($dateSelected > $dateInitialCourse)
    {       
        return true;
    }
    else
    {
        return false;
    }
}

function restrictionsCourseSections($sqlGetDateRestriction, $isMayor, $days, $idCourse)
{   
    global $DB;

    foreach ($sqlGetDateRestriction as $data) {

        $cont = 0;
        if ($date = $data->availability != null) {
            $result = json_decode($data->availability);
            $date = date('d-m-Y', $result->c[$cont]->t);

            if ($isMayor) {
                
                $date = strtotime($date. "+ $days days");
                $result->c[$cont]->t = $date;
                $result = json_encode($result);
                
                $sqlToUpload = "UPDATE mdl_course_sections
                                SET availability = '$result'
                                WHERE course = $idCourse";
                
                $DB->execute($sqlToUpload, $params=null);
            }
            else
            {
                $date = strtotime($date. "- $days days");
                $result->c[$cont]->t = $date;
                $result = json_encode($result);
                $sqlToUpload = "UPDATE mdl_course_sections
                SET availability = '$result'
                WHERE course = $idCourse";

                $DB->execute($sqlToUpload, $params=null);

            }
        }
        $cont++;
    }
}

function restrictionForumSections($sqlGetDateForum, $isMayor, $days, $idCourse)
{
    global $DB;

    foreach ($sqlGetDateForum as $data) {

        $duedate = $data->duedate;
        $cutoffdate = $data->cutoffdate;
        $duedateResult = date('d-m-Y', $duedate);
        $cutoffdateResult = date('d-m-Y', $cutoffdate);

        if ($duedate != 0) {

            if ($isMayor) 
            {
                $duedate = strtotime($duedateResult. "+ $days days");
            }
            else 
            {
                $duedate = strtotime($duedateResult. "- $days days");
            }
        }

        if ($cutoffdate != 0) {

            if ($isMayor) 
            {
                $cutoffdate = strtotime($cutoffdateResult. "+ $days days");
            }
            else
            {      
                $cutoffdate = strtotime($cutoffdateResult. "- $days days");
            }
        }

        $sqlToUpload = "UPDATE mdl_forum 
                        SET duedate = $duedate, cutoffdate = $cutoffdate
                        WHERE course = $idCourse";
        
        $DB->execute($sqlToUpload, $params=null);
    }
}

function restrictionAssignSections ($sqlGetDateAssign, $isMayor, $days, $idCourse)
{
    global $DB;

    foreach ($sqlGetDateAssign as $data) {
        
        /* GET DATE */
        $duedate = $data->duedate;
        $cutoffdate = $data->cutoffdate;
        $allowsubmission = $data->allowsubmissionsfromdate;
        $gradingduedate = $data->gradingduedate;

        /* CONVERSION */
        $duedateResult = date('d-m-Y', $duedate);
        $cutoffdateResult = date('d-m-Y', $cutoffdate);
        $allowsubmissionResult = date('d-m-Y', $allowsubmission);
        $gradingduedateResult = date('d-m-Y', $gradingduedate);
        

        if ($duedate != 0) {

            if ($isMayor) 
            {
                $duedate = strtotime($duedateResult. "+ $days days");
            }
            else 
            {
                $duedate = strtotime($duedateResult. "- $days days");
            }
        }

        if ($cutoffdate != 0) {

            if ($isMayor) 
            {
                $cutoffdate = strtotime($cutoffdateResult. "+ $days days");
            }
            else
            {      
                $cutoffdate = strtotime($cutoffdateResult. "- $days days");
            }
        }

        if ($allowsubmission != 0 || $gradingduedate != 0) {
            
            if ($isMayor) 
            {
                $allowsubmission = strtotime($allowsubmissionResult. "+ $days days");
                $gradingduedate = strtotime($gradingduedateResult. "+ $days days");    
            }
            else {
                $allowsubmission = strtotime($allowsubmissionResult. "- $days days");
                $gradingduedate = strtotime($allowsubmissionResult. "- $days days");
            }
        }

        $sqlToUpload = "UPDATE mdl_assign
                        SET duedate = $duedate, 
                            cutoffdate = $cutoffdate,
                            allowsubmissionsfromdate = $allowsubmission,
                            gradingduedate = $gradingduedate
                        WHERE course = $idCourse";
        $DB->execute($sqlToUpload, $params = null);
    }   
}

function changeDateCourse($days, $newStartDate, $courseData, $isMayor, $idCourse) // newStardate = date selected
{
    global $DB;
    
    $endDate;
    $endDate = $courseData[$idCourse]->enddate;
    $endDateResult = date('d-m-Y', $endDate);

    if ($isMayor) 
    {
        $endDate = strtotime($endDateResult. "+ $days days"); 
    }
    else
    {
        $endDate = strtotime($endDateResult. "- $days days");
    }

    $sqlToUpload = "UPDATE mdl_course 
                    SET startdate = $newStartDate, enddate = $endDate
                    WHERE id = $idCourse";
    $DB->execute($sqlToUpload, $params=null);

}

