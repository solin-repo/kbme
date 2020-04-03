
<?php

require_once("$CFG->libdir/externallib.php");

class local_kiwibank_external extends external_api{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function get_completiondata_parameters(){
    return new external_function_parameters(
         array(
               'from' => new external_value(PARAM_INT, 'Unix timestamp from which time to retreive',VALUE_REQUIRED),
               'to' => new external_value(PARAM_INT, 'Unix timestamp to which time to retreive', VALUE_REQUIRED),
               )
         );
     
    }

     public static function get_completiondata_returns() {
     return new external_multiple_structure(
        new external_single_structure(
            array(
                'completionid' => new external_value(PARAM_INT,'Completion ID number'),
                'userid' => new external_value(PARAM_INT,'Totara User ID number'),
                'courseid' => new external_value(PARAM_INT,'Course ID number'),
                'username' => new external_value(PARAM_TEXT,'User Name'),
                'useridnumber' => new external_value(PARAM_TEXT,'IDnumber for user, can be set and changed by totara administrators. Optional, can be null. '),
                'courseshortname' => new external_value(PARAM_TEXT,'Course shortname, unique per course'),
                'coursefullname' => new external_value(PARAM_TEXT,'Full course name'),
                'timecompleted' => new external_value(PARAM_INT,'Timecompleted')
            )
        )
     );
     }
     /**
     *Get completiondata
     Retreive Completion data over a given time period
     @param int $from Unix timestamp indicating the begining of a time period
     @param int $to Unix timestamp indicatng the end of a time period
     @return array containing the details of each successful completion in the time period
     */

     public static function get_completiondata($from , $to){
     global $CFG, $DB;
     self::validate_parameters(self::get_completiondata_parameters(), array('from' => $from,'to' => $to));
      
     $context = context_system::instance();
     self::validate_context($context);
     require_capability('local/kiwibank:viewcompletions', $context);
        
     $week = 604800;
     if(abs($to - $from) > 4*$week){
         throw new invalid_parameter_exception('Range of dates larger than four weeks');
     }
        
     $sql="SELECT cc.id AS completionid,
                  cc.timecompleted AS timecompleted,
                  c.fullname AS coursefullname,
                  c.shortname AS courseshortname,
                  u.username AS username,
                  u.idnumber AS useridnumber,
                  u.id AS userid,
                  c.id AS courseid
             FROM mdl_course_completions as cc
                  JOIN mdl_course as c ON cc.course = c.id 
                  JOIN mdl_user as u ON u.id = cc.userid
             WHERE cc.status = 50
             AND cc.timecompleted < ?
             AND cc.timecompleted >= ?";
       return  $DB->get_records_sql($sql, array($to,$from));
    }     
}

