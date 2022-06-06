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
 * Bloque de saludo al mundo: la vista de los campos
 *
 * @package   block_activitydate
 * @copyright 2022 Iplacex_APs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/activitydate/lib.php');




class activitydate_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
        
        $mform =& $this->_form;
        // elementos ocultos
        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_RAW);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_RAW);
    
        /* Title Form */
        $mform->addElement('html','<h3>Formulario del Bloque</h3><br><br>');

        /* Initial date Selector */
        $categoriesArray = array();
        $options = array();
        $options[-1] = 'None';
        $categoriesArray = get_categories_todb();

        foreach ($categoriesArray as $category) {
            $options[$category->id] = $category->name;
        }

        $mform->addElement('select',  'selectcategories',  
                get_string('selectcategories', 'block_activitydate'),$options);
        $mform->addRule('selectcategories', null, 'required');
        $mform->setType('selectcategories', PARAM_INT);                  
        $mform->setDefault('selectcategories',  -1);


        /* Initial date Selector */
        $options=[];
        $selectcourse = $mform->addElement('select', 'selectcourses',
                        get_string('selectcourse', 'block_activitydate'),$options);
        $mform->addRule('selectcourses', null, 'required');
        $mform->setType('selectcourses', PARAM_INT);
        $mform->setDefault('selectcategories',  -1);
        $selectcourse->setMultiple(true);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', -1);

        /* Initial date Selector */
        $mform->addElement('date_selector',  'initialdate',  
                get_string('initialdate', 'block_activitydate'));
        $mform->addRule('initialdate', null, 'required');

        $mform->addElement('hidden', 'showpreview', 1);
        $mform->setType('showpreview', PARAM_INT);

        // POST button
        $this->add_action_buttons();
    }
}