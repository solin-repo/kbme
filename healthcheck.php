<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// to avoid setup.php doing lots of unneccessary stuff
define('HEALTHCHECK', true);

require_once('config.php');
$CFG->libdir   = "$CFG->dirroot/lib";
require_once("$CFG->libdir/adodb/adodb.inc.php"); // Database access functions

// set up the PERF object to avoid whingeing from datalib
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
// some of these libraries are needed for the error handling
require_once($CFG->dirroot . '/lib/datalib.php');
require_once($CFG->dirroot . '/lib/dmllib.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/lib/weblib.php');

$tempfile = $CFG->dataroot . '/healthcheck_' . $_SERVER["SERVER_NAME"] . '_' . time() . '_' . getmypid();
if (!$fh = fopen($tempfile, 'w')) {
    error_log("COULDN'T WRITE TO DATAROOT - Health check fail");
    exit(1);
}

fclose($fh);
unlink($tempfile);

if (file_exists($CFG->dirroot . '/heathcheck_local.php')) { 
    /** LOAD UP $CFG - tests db connection AND is needed for ldap connect. **/
    // Load up any configuration from the config table 
    try {
        $configs = $DB->get_records('config');
        $CFG = (array)$CFG;
        foreach ($configs as $config) {
            $CFG[$config->name] = $config->value;
        }
        $CFG = (object)$CFG;
        unset($configs);
        unset($config);
    } catch (dml_exception $e) {
        error_log("COULDN'T INITIALISE CFG - Health check fail");
        error_log("Probably not installed yet...");
    }
    // support for eg topnz which wants to test ldap connection
    require_once($CFG->dirroot . '/healthcheck_local.php');
}
echo "alive\n";
exit(0);
?>
