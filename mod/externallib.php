<?php

require_once($CFG->libdir . "/externallib.php");

class local_activitydate_external extends external_api
{
     /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function getcoursesincategory_parameters()
    {
        return new external_function_parameters(
          array("id" => new external_value(PARAM_INT, "id"))
        );
    }

    /**
     * Returns welcome message
     * @return array = array('' => , ); welcome message
    */

    public static function getcoursesincategory($id)
    {
        global $USER;
        global $DB;
        global $CFG;

        $params = self::validate_parameters(
            self::getcoursesincategory_parameters(),
                array('id'=>$id)
        );

        $listCourses = $DB->get_records('course', array('category'=> $id));
        return json_encode(array_values($listCourses));
    }

    public static function getcoursesincategory_returns()
    {
        return new external_value(PARAM_RAW, 'The updated JSON output');
    }
}


?>