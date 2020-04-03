<?php

$feeds = array(
    'feed1'=>array('feedname'          =>   'Kiwibank',
                   'remotesource'      =>   '/home/kbuser/kb-kb/upload/',
                   'localsource'       =>   '/home/www-data/csv/ready/',
                   'feedelements'      =>   array(array('syncelement'=>'org1','filename'=>'Kiwibank Org KWB_Totara_LMS_org.csv','destelement'=>'org','required'=>1),
                                                  array('syncelement'=>'pos1','filename'=>'Kiwibank Pos KWB_Totara_LMS_pos.csv','destelement'=>'pos','required'=>1),
                                                  array('syncelement'=>'user1','filename'=>'Kiwibank User KWB_Totara_LMS_user.csv','destelement'=>'user','required'=>0))),
    
    'feed2' =>     array('feedname'     =>  'Franchise and agnency',
                        'remotesource'  =>  '/home/kbuser/kb-fa/upload/',
                         'localsource'  =>  '/home/www-data/csv/ready/',
                         'feedelements' =>  array(array('syncelement'=>'org2','filename'=>'FAA Org KWB_Totara_LMS_org.csv','destelement'=>'org','required'=>1),
                                            array('syncelement'=>'pos2','filename'=>'FAA Pos KWB_Totara_LMS_pos.csv','destelement'=>'pos','required'=>1),
                                            array('syncelement'=>'user2','filename'=>'FAA User KWB_Totara_LMS_user.csv','destelement'=>'user','required'=>0)))
        );  

$sftpuser='kbuser';
$sftpass='2cg,wkk[L{hR5D&$';
$host='transfer.synapsys.co.nz';

?>

