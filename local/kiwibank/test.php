<?php

global $CFG;


require ('/Library/WebServer/Documents/local/kiwibank/classes/totara_upload.php');

$loader= new totara_sync_feedupload();
$loader->upload_feedfiles();


?>