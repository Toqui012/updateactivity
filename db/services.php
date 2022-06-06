<?php

    $functions = array(
        'local_ajaxdemo_getcoursesincategorie' => array(
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

?>