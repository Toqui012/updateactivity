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
 * view.php tiene el control (la lógica) de la página
 *
 * @package   block_activitydate
 * @copyright 2022 Iplacex_AP
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('activitydate_form.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot.'/blocks/activitydate/lib.php');
require_once($CFG->dirroot.'/blocks/activitydate/classes/FileClass.php');
require_once($CFG->dirroot.'/blocks/activitydate/classes/HistoricalRecordFile.php');


global $DB, $OUTPUT, $PAGE;

// Definiciones
$PAGE->requires->js('/blocks/activitydate/js/js_activitydate.js');

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);

// Busca el identificador del bloque 
$blockid= required_param('blockid', PARAM_INT);

// Busca los id de los cursos seleccionados
$coursesselected = optional_param('selectcourses', -1, PARAM_INT);

// Busca si hay más variables
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course',array('id' => $courseid))) {
    print_error('invalidcourse', 'block_activitydate', $courseid);
}

// Variables Formulario
$importid = optional_param('importid', '', PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);

// Autentificación Login
require_login($course);

$PAGE->set_url('/blocks/activitydate/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard'); 
$PAGE->set_heading(get_string('edithtml', 'block_activitydate'));

// Creamos el nodo del bloque en las migas de pan
$settingsnode = $PAGE->settingsnav->add(get_string('activitydatessettings', 'block_activitydate'));

// Creamos la URL del bloque con el id del bloque 
$editurl = new moodle_url('/blocks/activitydate/view.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));

// Añadimos el nodo con la url del bloque
$editnode = $settingsnode->add(get_string('editpage', 'block_activitydate'), $editurl);

// Activamos las migas de pan
$editnode->make_active();

$activitydate = new activitydate_form();
$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$activitydate->set_data($toform);
$returnurl = new moodle_url('/course/view.php');

if($activitydate->is_cancelled()) {
    // Los formularios cancelados redirigen a la página principal del curso.
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);

} else if ($fromform = $activitydate->get_data()) {
    
    // Url de redirección
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    
    // Source
    try {
        if ($fromform->selectcategory !=-1) {

            
            // Se asigna los valores de selectcourses a los atributos del formulario
            $fromform->selectcourses = $coursesselected;  
            
            // Se transforma la fecha de unixtime a string(date)
            $toDate = date('d-m-Y', $fromform->initialdate);
            
            // Se hace una consulta a la tabla cursos para obtener la fecha inicial del curso seleccionado
            $days = -1;

            // Se recorre el array según los ids seleccionados en "selectcourses"
            foreach ($fromform->selectcourses as $value) {
                
                /* Se obtiene la diferencia de fechas */
                $sqlGetDate = $DB->get_records('course', array('id' => $value));
                $toDate2 = date('d-m-Y', $sqlGetDate[$value]->startdate);
                $dateSelected = new DateTime($toDate);
                $dateInitialCourse = new DateTime($toDate2);
                $diff = $dateSelected->diff($dateInitialCourse);
                $days = $diff->days;

                // Verificar si la fecha se adelanta o se atrasa
                $mayorOrLess = ismayor($dateSelected, $dateInitialCourse);

                /* Get Dates */
                $sqlGetDateForum = $DB->get_records('forum', array('course' => $value));
                $sqlGetDateAssign = $DB->get_records('assign', array('course' => $value)); // duedate / cutoffdate
                $sqlGetDateRestriction = $DB->get_records('course_sections', array('course' => $value));
                $sqlGetCourseRestriction = $DB->get_records('course_modules', array('course' => $value));
                $sqlGetQuiz = $DB->get_records('quiz', array('course' => $value));
                $sqlGetPoll = $DB->get_records('feedback', array('course' => $value));
                $sqlGetWorkshop = $DB->get_records('workshop', array('course' => $value));
                /* Upload sections */
                changeDateCourse($days, $fromform->initialdate, $sqlGetDate, $mayorOrLess, $value); // Course
                restrictionsCourseSections($sqlGetDateRestriction, $mayorOrLess, $days, $value); // Sections (Topico)
                restrictionForumSections($sqlGetDateForum, $mayorOrLess, $days, $value); // Foros
                restrictionAssignSections($sqlGetDateAssign, $mayorOrLess, $days, $value); // Actividades
                updateCourseRestriction($sqlGetCourseRestriction, $mayorOrLess, $days, $value); // Restriction
                updateQuizTime($sqlGetQuiz, $mayorOrLess, $days, $value); // Examen
                updatePoll($sqlGetPoll, $mayorOrLess, $days, $value); // Encuestas
                updateWorkshop($sqlGetWorkshop, $mayorOrLess, $days, $value); // Talleres
            }

            redirect($courseurl, "La operación se ha llevado a cabo exitosamente", null, \core\output\notification::NOTIFY_SUCCESS);
        }
        else
        {
            redirect($courseurl, "Formulario incompleto", null, \core\output\notification::NOTIFY_ERROR);
        }
    } catch (\Throwable $th) {
        throw $th;
        // redirect($courseurl, "Ha ocurrido un problema interno al actualizar los cursos", null, \core\output\notification::NOTIFY_ERROR);
    }    

} else {
    // Primera vez o con errores
    $site = get_site();
    // Desplegamos nuestra página
    echo $OUTPUT->header();
    $activitydate->display();
    echo $OUTPUT->footer();
}