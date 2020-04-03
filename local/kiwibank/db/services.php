<?php
$functions = array(
    'local_kiwibank_get_completiondata' => array(
    
    'classname' => 'local_kiwibank_external',
    'methodname' => 'get_completiondata',
    'classpath' => 'local/kiwibank/externallib.php',
    'type'      => 'read',
    'restrictedusers' => 1,
    'requiredcapability'=>'local/kiwibank:viewcompletions'
    )
);
