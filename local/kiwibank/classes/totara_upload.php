<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace local_kiwibank;
require_once('../../config.php');
//require_once($CFG->dirroot.'/local/kiwibank/config.php');
//require_once($CFG->dirroot.'/admin/tool/totara_sync/sources/classes/source.org.class.php');
require_once($CFG->dirroot.'/lib/filestorage/file_storage.php');
require_once($CFG->dirroot.'/admin/tool/totara_sync/lib.php');




class totara_sync_feedupload {
      
    
static function upload_feedfiles () {
global $CFG;
require_once($CFG->dirroot.'/local/kiwibank/config.php');
     
    
    $filedir = rtrim(get_config('totara_sync', 'filesdir'), '/');
    $systemcontext = context_system::instance();
        
    foreach ($feeds as $feed) {
 
        //check feed is complete. 

        if(!$connection = ssh2_connect($host, 22)) {
           throw new moodle_exception('kbsshcantconnect','kiwibank'); 
        } 

        
        if(!ssh2_auth_password($connection, $sftpuser, $sftpass)) {
            throw new moodle_exception('kbssfailedtoauthenticate','kiwibank');
        }
        
      
        foreach ($feed['feedelements'] as $feedelement) {
 

            totara_sync_log($feedelement['destelement'], "Retrieving ".$feed['feedname']." file", 'info', 'retrievekbfiles');
	    if (!ssh2_scp_recv($connection, $feed['remotesource'].$feedelement['filename'], $feed['localsource'].$feedelement['filename'])){ 
                if($feedelement['required']) {
                    totara_sync_log($feedelement['destelement'], "Required ".$feed['feedname']." file not present", 'error', 'retrievekbfiles');
                } else {
                    totara_sync_log($feedelement['destelement'], $feed['feedname']." file not present", 'info', 'retrievekbfiles');
                }
            } else {
                
                $cmd='rm \''.$feed['remotesource'].$feedelement['filename'].'\'';
                $stream = ssh2_exec($connection, $cmd);
            }
  

            //Establish existence of file 
            $filepath=$feed['localsource'].$feedelement['filename'];
            

            if (!file_exists($filepath)) {
                    throw new totara_sync_exception('test', 'populatesynctablecsv', 'nofiletosync', $filepath, null, 'warn');
            }
            // See if file is readable
            if (!$file = is_readable($filepath)) {
                throw new totara_sync_exception($feedelement['syncelement'], 'populatesynctablecsv', 'cannotreadx', $filepath);
            }

            $filemd5 = md5_file($filepath);
            while (true) {
                // Ensure file is not currently being written to
                sleep(2);
                $newmd5 = md5_file($filepath);
                if ($filemd5 != $newmd5) {
                    $filemd5 = $newmd5;
                } else {
                    break;
                }
            }
            $fs = get_file_storage();
            $fs->delete_area_files($systemcontext->id, 'totara_sync', $feedelement['syncelement']);

            $fileinfo=new stdClass();
            $fileinfo->contextid = $systemcontext->id;
            $fileinfo->component = 'totara_sync';
            $fileinfo->filearea  = $feedelement['syncelement'];
            $fileinfo->itemid    = time();
            $fileinfo->filepath  = '/';
            $fileinfo->filename  = $feedelement['syncelement'].'-'. $fileinfo->itemid;

            totara_sync_log($feedelement['destelement'], $feed['feedname']." loading", 'info', 'loadkbfiles');
            $newfile=$fs->create_file_from_pathname($fileinfo,$filepath);
            $fieldid = set_config("sync_".$feedelement['syncelement']."_itemid",$fileinfo->itemid,'totara_sync');
            unlink($filepath);
      
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
}
    
}
    
    

