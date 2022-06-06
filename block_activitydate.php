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
 * Bloque encargado de actualiar las fechas de las actividades de un curso en especifico
 *
 * @package   activitydate
 * @copyright 2022 iplacex_ap
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_activitydate extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_activitydate');
    }

    function has_config ()  { return  true ;}

    public function get_content() {
        global $COURSE;
    
        if($this->config->disabled){
            return null;
        } else if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        if (!empty($this->config->text)){
            $this->content->text = $this->config->text;
        } else {
            $this->content->text = get_string('descriptionblock', 'block_activitydate');
        }
        // $this->content->footer = get_string('footerblock', 'block_activitydate');

        $url = new moodle_url(
            '/blocks/activitydate/view.php',
            array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)
        );
        $this->content->footer = html_writer::link($url, get_string('addpage', 'block_activitydate'));
        return $this->content;
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_activitydate');            
            } else {
                $this->title = $this->config->title;
            }
     
            if (empty($this->config->text)) {
                $this->config->text = get_string('defaulttext', 'block_activitydate');
            }    
        }
    }

    public function instance_allow_multiple(){
        return true;
    }

    public function instance_config_save($data,$nolongerused =false) {
        global $CFG;
        
        if (!empty($CFG->block_activitydate_allowhtml)) {
            // && $CFG->block_holamundo_allowhtml == '1'
            $data->text = strip_tags($data->text);
        } 
    
        // Implementación predeterminada definida en la clase principal
        return parent::instance_config_save($data,$nolongerused);
    }

    /** Restricción de instalación del bloque en otra pagina  */
    public function applicable_formats() {
        return array(
            'site-index' => true,
            'course-view' => true,
            'course-view-social' => false,
            'mod' => true,
            'mod-quiz' => false
        );
    }

}