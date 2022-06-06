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
 * Super bloque udpate activity date
 *
 * @package    activitydate
 * @copyright  2022 Iplacex_AP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/activitydate:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),

    'block/activitydate:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);

$functions = array(
    'local_ajaxdemo_getteachersincourse' => array(
    'classname'   => 'local_ajaxdemo_external',
    'methodname'  => 'getcoursesincategorie',
    'classpath' => 'blocks/activitydate/lib.php',
    'description' => 'Return teachers in a course',
    'type'        => 'read',
    'ajax' => true,
    'capabilities' => '',
    'loginrequired' => true
//                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
     )
);



