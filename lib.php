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

// Update Course Sections (Listo)
function restrictionsCourseSections($sqlGetDateRestriction, $isMayor, $days, $idCourse)
{   
    global $DB;

    foreach($sqlGetDateRestriction as $data)
    {
        // se valida que no entren fechas null (en este caso json)
        if ($date = $data->availability !=null) {
            
            $result = json_decode($data->availability);

            // Actualización de (desde) de la restrición
            if ($result->c[0]->t != null) {
                
                $date = date('d-m-Y', $result->c[0]->t); // Fecha (desde) de restrición
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
                $enddate = date('d-m-Y', $result->c[1]->t); // Fecha (hasta) de restricción
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
            $sqlToUpload = "UPDATE mdl_course_sections SET availability = '$result' WHERE course = $idCourse AND id = $data->id";
            $DB->execute($sqlToUpload, $params=null);
        }
    }
    
}

// Update Forum Availability (Listo)
function restrictionForumSections($sqlGetDateForum, $isMayor, $days, $idCourse)
{
    global $DB;

    foreach ($sqlGetDateForum as $data) {

            
        // Asignación de Fechas
        $duedate = $data->duedate;
        $cutoffdate = $data->cutoffdate;
        
        $duedateResult = date('d-m-Y', $duedate);
        $cutoffdateResult = date('d-m-Y', $cutoffdate);
        $sqlToUpload;
        $cont = 0;

        // Duedate
        if ($duedate != 0) {
            
            $cont++;
            if ($isMayor) 
            {   
                $duedate = strtotime($duedateResult. "+ $days days");
            }
            else 
            {
                $duedate = strtotime($duedateResult. "- $days days");
            }

            $sqlToUpload = "UPDATE mdl_forum SET duedate = $duedate WHERE course = $idCourse";
        }
        
        // Cutoffdate
        if ($cutoffdate != 0) {
            
            $cont++;
            if ($isMayor) 
            {
                $cutoffdate = strtotime($cutoffdateResult. "+ $days days");
            }
            else
            {      
                $cutoffdate = strtotime($cutoffdateResult. "- $days days");
            }

            $sqlToUpload = "UPDATE mdl_forum SET cutoffdate = $cutoffdate WHERE course = $idCourse";
        }
        
        // En el caso que existan ambos datos
        if ($cont == 2) {
            $sqlToUpload = "UPDATE mdl_forum 
                            SET duedate = $duedate, cutoffdate = $cutoffdate
                            WHERE course = $idCourse AND id = $data->id";
            $DB->execute($sqlToUpload, $params=null);
        }
        else if($cont == 1)
        {
            $DB->execute($sqlToUpload, $params=null);
        }
    }
}

// Update Assign Restrictions (Listo)
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
        
        // Fecha de Entrega
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
        else
        {
            $duedate = $data->duedate;
        }

        // Fecha de Corte
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
        else
        {
            $cutoffdate = $data->cutoffdate;
        }

        // Fecha de Permitir Envios
        if ($allowsubmission != 0) {
            
            if ($isMayor)
            {
                $allowsubmission = strtotime($allowsubmissionResult. "+ $days days");
            }
            else
            {
                $allowsubmission = strtotime($allowsubmissionResult. "- $days days");
            }
            
        }
        else
        {
            $allowsubmission = $data->allowsubmissionsfromdate;
        }

        // Fecha Recordar Calificar
        if ($gradingduedate != 0) {
            
            if ($isMayor) 
            {
                $gradingduedate = strtotime($gradingduedateResult. "+ $days days");    
            }
            else {
                $gradingduedate = strtotime($gradingduedateResult. "- $days days");
            }
        }
        else
        {
            $gradingduedate = $data->gradingduedate;
        }

        $sqlToUpload = "UPDATE mdl_assign
                        SET duedate = $duedate, 
                            cutoffdate = $cutoffdate,
                            allowsubmissionsfromdate = $allowsubmission,
                            gradingduedate = $gradingduedate
                        WHERE course = $idCourse AND id = $data->id";
        $DB->execute($sqlToUpload, $params = null);
    }   
}

// Update Course Date (Listo)
function changeDateCourse($days, $newStartDate, $courseData, $isMayor, $idCourse) // newStardate = date selected
{
    global $DB;

    // En el caso de que el curso tenga fecha de termino, se cambian ambas fechas
    if ($courseData[$idCourse]->enddate != 0) {
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

        $sqlToUpload = "UPDATE mdl_course SET startdate = $newStartDate, enddate = $endDate WHERE id = $idCourse";
        $DB->execute($sqlToUpload, $params=null);
    } 
    else {

        // En el caso de que no tenga fecha de temrino. solo se actualiza la fecha de inicio

        $sqlToUpload = "UPDATE mdl_course SET startdate = $newStartDate WHERE id = $idCourse";
        $DB->execute($sqlToUpload, $params=null);
    }
}

// Update Course Restriction (Listo)
function updateCourseRestriction($sqlGetCourseModules, $isMayor, $days, $idCourse){
    
    global $DB;
    
    foreach ($sqlGetCourseModules as $data) {
    
        // se valida que no entren fechas null
        if ($data->availability != null) {

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
            WHERE course = $idCourse AND id = $data->id";

            $DB->execute($sqlToUpload, $params=null);
        }
    }
}

// Update Time Quiz (Listo)
function updateQuizTime($sqlGetQuiz, $isMayor, $days, $idCourse)
{
    global $DB;
    foreach ($sqlGetQuiz as $data) {
        
        /* GET DATA */
        $timeopen = $data->timeopen;
        $timeclose = $data->timeclose;
        
        /* CONVERSION */
        $timeopenResult = date('d-m-Y', $timeopen);
        $timecloseResult = date('d-m-Y', $timeclose);

        // Validate TimeOpen
        if ($timeopen != 0) {
            if ($isMayor) {

                $timeopen = strtotime($timeopenResult. "+ $days days");
            }
            else{
                $timeopen = strtotime($timeopenResult. "- $days days");
            }
        }

        if ($timeclose != 0) {
            
            if ($isMayor) {
                
                $timeclose = strtotime($timecloseResult. "+ $days days");
            }
            else {
                $timeclose = strtotime($timecloseResult. "- $days days");
            }
        }

        $sqlToUpload = "UPDATE mdl_quiz 
                        SET timeopen = $timeopen, timeclose = $timeclose
                        WHERE course = $idCourse AND id = $data->id";
        $DB->execute($sqlToUpload, $params=null);
    }
}

// Update Poll (Encuesta) (Listo)
function updatePoll($sqlGetPoll, $isMayor, $days, $idCourse)
{
    global $DB;

    foreach ($sqlGetPoll as $data) {
        
        // Get timeopen and timeclose
        $timeopen = $data->timeopen;
        $timeclose = $data->timeclose;

        /* CONVERSION */
        $timeopenResult = date('d-m-Y', $data->timeopen);
        $timecloseResult = date('d-m-Y', $data->timeclose);

        // Validate timeopen
        if ($timeopen != 0) {

            if ($isMayor) {

                $timeopen = strtotime($timeopenResult. "+ $days days");
            }
            else
            {
                $timeopen = strtotime($timeopenResult. "- $days days");
            }
        }

        // Validate timeclose
        if ($timeclose != 0) {
            
            if ($isMayor) {

                $timeclose = strtotime($timecloseResult. "+ $days days");
            }
            else
            {
                $timeclose = strtotime($timecloseResult. "- $days days");
            }
        }

        $sqlToUpload = "UPDATE mdl_feedback
                        SET timeopen = $timeopen, timeclose = $timeclose
                        WHERE course = $idCourse AND id = $data->id";
        $DB->execute($sqlToUpload, $params=null);        
    }
}

// Update Taller (Workshop) (Listo)
function updateWorkshop($sqlGetWorkshop, $isMayor, $days, $idCourse)
{

    // Definición de DB para ejecutar sentencias SQL
    global $DB;

    foreach ($sqlGetWorkshop as $data) {
        
        /* GET DATA */
        $dateSubmissionstart = $data->submissionstart;
        $dateSubmissionend = $data->submissionend;
        $dateAssessmentstart = $data->assessmentstart;
        $dateAssessmentend = $data->assessmentend;


        // Date Submissionstart
        if ($dateSubmissionstart != 0) {

            $dateSubmissionstartResult = date('d-m-y', $dateSubmissionstart); // conversion to d-m-Y

            if ($isMayor) {

                $dateSubmissionstart = strtotime($dateSubmissionstartResult. "+ $days days");
            }
            else
            {
                $dateSubmissionstart = strtotime($dateSubmissionstartResult. "- $days days");
            }
        }

        // Date Submissionend
        if ($dateSubmissionend != 0) {
            
            $dateSubmissionendResult = date('d-m-Y', $dateSubmissionend); // conversion to d-m-Y

            if ($isMayor) {

                $dateSubmissionend = strtotime($dateSubmissionendResult. "+ $days days");
            }
            else
            {
                $dateSubmissionend = strtotime($dateSubmissionendResult. "- $days days");
            }
        }

        // Date Assessmentstart
        if ($dateAssessmentstart != 0) {

            $dateAssessmentstartResult = date('d-m-Y', $dateAssessmentstart);

            if ($isMayor) {

                $dateAssessmentstart = strtotime($dateAssessmentstartResult. "+ $days days");
            }
            else
            {
                $dateAssessmentstart = strtotime($dateAssessmentstartResult. "- $days days");
            }
        }

        // Date Assessmentend
        if ($dateAssessmentend != 0) {
            
            $dateAssessmentendResult = date('d-m-Y', $dateAssessmentend);

            if ($isMayor) {
                
                $dateAssessmentend = strtotime($dateAssessmentendResult. "+ $days days");
            }
            else
            {
                $dateAssessmentend = strtotime($dateAssessmentendResult. "- $days days");
            }
        }

        $sqlToUpload = "UPDATE mdl_workshop 
                        SET submissionstart = $dateSubmissionstart,
                            submissionend = $dateSubmissionend,
                            assessmentstart = $dateAssessmentstart,
                            assessmentend = $dateAssessmentend
                        WHERE course = $idCourse AND id = $data->id";

        $DB->execute($sqlToUpload, $params=null);
    }
}

// Update Assign