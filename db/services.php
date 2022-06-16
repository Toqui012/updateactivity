<?php

$functions = array(
    'local_activitydate_getcoursesincategory' => array(
        'classname'   => 'local_activitydate_external',
        'methodname'  => 'getcoursesincategory',
        'classpath'   => 'blocks/activitydate/mod/externallib.php',
        'description' => 'Return courses in a category',
        'type'        => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true
    )
);