<?php

global $CFG;


require ('/var/www/staging-kbme.kiwibank.co.nz/local/kiwibank/classes/totara_upload.php');

$loader= new totara_sync_feedupload();
$loader->upload_feedfiles();


?>
