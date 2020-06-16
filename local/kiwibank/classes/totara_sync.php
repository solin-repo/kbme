<?php

/**
 * Kiwibank-specific Totara sync modifications
 *
 * @author Eugene Venter <eugene@catalyst.net.nz>
 */

namespace local_kiwibank;

class totara_sync {
    static function merge_files() {
        global $CFG;

        $elements = array(
            'org'  => array('org1', 'org2', 'org3'),
            'pos'  => array('pos1', 'pos2'),
	    'user' => array('user1', 'user2'),
	    'jobassignment'  => array('jobassignment1','jobassignment2')
        );

        $fs = get_file_storage();
        $systemcontext = \context_system::instance();

        // Merge elements together
        foreach ($elements as $element => $subelements) {
            totara_sync_log($element, "Merging {$element} files", 'info', 'mergefiles');

            // Clean up any remnant
            $fs->delete_area_files($systemcontext->id, 'totara_sync', $element);

            // Merged file content
            $content = array();
            // Number of files merged in
            $count = 0;
            foreach ($subelements as $subelement) {
                $fieldid = get_config('totara_sync', "sync_{$subelement}_itemid");

                // Check that the files exist
                if (!$fieldid || !$fs->file_exists($systemcontext->id, 'totara_sync', $subelement, $fieldid, '/', '')) {
                    if (in_array($element, array('org', 'pos'))) {
                        // For org and pos, all files should be present
                        totara_sync_log($element, "could not merge files - all files do not exist ($subelement)", 'error', 'mergefiles');
                        // Empty merged file content so it's not created
                        $content = array();
                        break;
                    } else {
                        // For user this is not required, as 'source contains all records' is currently set to 'no', which means nothing can get deleted by accident
                        continue;
                    }
                }

                // Get the file content
                $fsfiles = $fs->get_area_files($systemcontext->id, 'totara_sync', $subelement, $fieldid, 'id DESC', false);
                $fsfile = reset($fsfiles);
                $filecontent = explode(PHP_EOL, trim($fsfile->get_content()));
                if (empty($filecontent)) {
                    continue;
                }
                $count++;
                if ($count > 1) {
                    // Remove the heading line for subsequent files
                    unset($filecontent[0]);
                }
                // Append file content to merged file
                $content = array_merge($content, $filecontent);
                unset($filecontent);
            }

            // Create merged file if content not empty
            if (!empty($content)) {
                $now = time();
                $fileinfo = array(
                    'contextid' => $systemcontext->id,
                    'component' => 'totara_sync',
                    'filearea' => $element,
                    'itemid' => $now,
                    'filepath' => '/',
                    'filename' => "{$element}-{$now}"
                );
                $fs->create_file_from_string($fileinfo, implode(PHP_EOL, $content));
                set_config("sync_{$element}_itemid", $now, 'totara_sync');
            }

            unset($content);
        }

    }
    
    static function upload_feedfiles () {
        global $CFG;
        require_once($CFG->dirroot.'/local/kiwibank/config.php');
     

        
        $filedir = rtrim(get_config('totara_sync', 'filesdir'), '/');
        $systemcontext = \context_system::instance();

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
                if (!ssh2_scp_recv($connection, $feed['remotesource'].'/'.$feedelement['filename'], $feed['localsource'].$feedelement['filename'])){ 
                    if($feedelement['required']) {
                        totara_sync_log($feedelement['destelement'], "Required ".$feed['feedname']." file not present", 'error', 'retrievekbfiles');
                    } else {
                        totara_sync_log($feedelement['destelement'], $feed['feedname']." file not present", 'info', 'retrievekbfiles');
                    }
                } else {
                    $cmd='rm '.$feed['remotesource'].$feedelement['filename'];
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

                $fileinfo=new \stdClass();
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
