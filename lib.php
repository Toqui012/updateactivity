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

function get_categories_todb()
{   
    global $DB;
    return $DB->get_records('course_categories');
}

// Mayor or less function
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

// Update Course Restrictions
function restrictionsCourseSections($sqlGetDateRestriction, $isMayor, $days, $idCourse)
{   
    global $DB;

    foreach($sqlGetDateRestriction as $data)
    {
        // se valida que no entren fechas null (en este caso json)
        if ($date = $data->availability !=null) {
            
            $result = json_decode($data->availability);
            
            $date = date('d-m-Y', $result->c[0]->t); // Fecha (desde) de restrición
            $enddate = date('d-m-Y', $result->c[1]->t); // Fecha (hasta) de restricción

            // Actualización de (desde) de la restrición
            if ($result->c[0]->t != null) {
                
                if ($isMayor) {
                    $date = strtotime($date."+ $days days");
                    $result->c[0]->t = $date;
                }
                else {
                    $date = strtotime($date."- $days days");
                    $result->c[0]->t = $date;
                }
            }

            // Actualización de (hasta) de la restrición
            if ($result->c[1]->t != null) {
    
                if ($isMayor) {
                    $enddate = strtotime($enddate."+ $days days");
                    $result->c[1]->t = $enddate;
                }
                else{
                    $enddate = strtotime($enddate."- $days days");
                    $result->c[1]->t = $enddate;
                }
            }

            // Se transforma a json para respetar la estrucutra de la base de datos
            $result = json_encode($result);

            $sqlToUpload = "UPDATE mdl_course_sections
            SET availability = '$result' 
            WHERE course = $idCourse";

            $DB->execute($sqlToUpload, $params=null);
        }
    }
    
}

// Update Forum Restrictions
function restrictionForumSections($sqlGetDateForum, $isMayor, $days, $idCourse)
{
    global $DB;

    echo('<br>');
    echo('Función de Foros');

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

// Update Assign Restrictions
function restrictionAssignSections ($sqlGetDateAssign, $isMayor, $days, $idCourse)
{
    global $DB;

    echo('<br>');
    echo('Función de Tareas (Assign)');

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

// Update Course Date
function changeDateCourse($days, $newStartDate, $courseData, $isMayor, $idCourse) // newStardate = date selected
{
    global $DB;

    /** ------------ Fixear Codigo ----------- */
    // Validar si es null

    echo('<br>');
    echo('Función de Cursos');
    
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

// Update Course Restriction
function updateCourseRestriction($sqlGetCourseModules, $isMayor, $days, $idCourse){
    
    global $DB;
    
    foreach ($sqlGetCourseModules as $data) {
    
        $cont = 0;
        $endcont = 1;

        // se valida que no entren fechas null
        if ($date = $data->availability != null) {

            $result = json_decode($data->availability);
            
            $date = date('d-m-Y', $result->c[0]->t); // Fecha (desde) de restrición
            $enddate = date('d-m-Y', $result->c[1]->t); // Fecha (hasta) de restricción

            // Actualización de (desde) de la restrición
            if ($result->c[0]->t != null) {
                
                if ($isMayor) {
                    $date = strtotime($date."+ $days days");
                    $result->c[0]->t = $date;
                }
                else {
                    $date = strtotime($date."- $days days");
                    $result->c[0]->t = $date;
                }
            }

            // Actualización de (hasta) de la restrición
            if ($result->c[1]->t != null) {
    
                if ($isMayor) {
                    $enddate = strtotime($enddate."+ $days days");
                    $result->c[1]->t = $enddate;
                }
                else{
                    $enddate = strtotime($enddate."- $days days");
                    $result->c[1]->t = $enddate;
                }
            }

            // Se transforma a json para respetar la estrucutra de la base de datos
            $result = json_encode($result);

            $sqlToUpload = "UPDATE mdl_course_modules
            SET availability = '$result' 
            WHERE course = $idCourse";

            $DB->execute($sqlToUpload, $params=null);
        }

        
    }
}

