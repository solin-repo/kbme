<?php
require_once('config.php');
$CFG->libdir = "$CFG->dirroot/lib";
require_once("$CFG->libdir/adodb/adodb.inc.php"); // Database access functions
// Set up the PERF object to avoid whinging from datalib.
global $PERF;
$PERF = new StdClass;
$PERF->dbqueries = 0;
$PERF->logwrites = 0;
if (function_exists('microtime')) {
    $PERF->starttime = microtime();
}
if (function_exists('memory_get_usage')) {
    $PERF->startmemory = memory_get_usage();
}
if (function_exists('posix_times')) {
    $PERF->startposixtimes = posix_times();
}
// Some of these libraries are needed for the error handling.
require_once($CFG->dirroot . '/lib/datalib.php');
require_once($CFG->dirroot . '/lib/dmllib.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/lib/weblib.php');
$hrcheckstarted = false;
try {
    $hoursago26 = time() - 93600;
    $syncstrings = array('usersync', 'orgsync', 'possync');
    foreach ($syncstrings as $syncstring) {
       $check_sync_started = $DB->get_record_sql(
            "SELECT * FROM  {totara_sync_log}
                 WHERE time >  ?  AND
                 logtype = ? AND
                 action = ? AND
                 info = ?", array($hoursago26, 'info', $syncstring, 'HR Import started'));

        if(!empty($check_sync_started)) {
            $hrcheckstarted = true;
            $check = $DB->get_record_sql(
                "SELECT * FROM  {totara_sync_log}
                     WHERE time >  ?  AND
                     logtype = ? AND
                     action = ? AND
                     info = ?", array($hoursago26, 'info', $syncstring, 'HR Import finished'));
            if (empty($check)) {
                print_error("HR sync failed - " . $syncstring . " did not complete in the last 26 hours");
                // This exit code is never reached because of print_error().
                exit(2);
            }
        } else continue;
    }
} catch (dml_exception $e) {
    print_error("COULDN'T INITIALISE - HR check fail");
    // This exit code is never reached because of print_error().
    exit(2);
}
if (!$hrcheckstarted) {
    print_error("HR sync was not run in the last 26 hours");
    // This exit code is never reached because of print_error().
    exit(2);
}
echo "HR sync succeeded\n";
exit(0);
