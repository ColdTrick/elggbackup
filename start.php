<?php
	require_once(dirname(__FILE__) . "/vendors/zipwrap.php");
	
	function elggbackup_init() {
		
		// grab from pluginsettings
		$scheduleractive = get_plugin_setting("scheduleractive","elggbackup");
		if($scheduleractive == "yes"){
	        $frequency = get_plugin_setting("frequency","elggbackup");
	        if($frequency){
				register_plugin_hook('cron', $frequency, 'elggbackup_cron');
	        }
		}

		register_page_handler('elggbackup','elggbackup_page_handler');
    }
    
    function elggbackup_page_handler($page){
		global $CONFIG;
		
		// only interested in one page for now
		include($CONFIG->pluginspath . "elggbackup/index.php"); 
	}
    
    function elggbackup_cron($hook, $entity_type, $returnvalue, $params, $download = false){
    	global $CONFIG;
    	
    	$zipfile = $CONFIG->dataroot . "my_site_backup_" . date("d_m_Y") . ".zip";
    	// remove if already exists
		if(file_exists($zipfile)) unlink($zipfile);
    	
    	//creating zip in elgg data location
    	$Z = new ZipWrap('');
		$Z->Create($zipfile);
		
		// adding config files
		$Z->Add($CONFIG->path . ".htaccess");
		$Z->Add($CONFIG->path . "engine/settings.php");
			
		// adding upload folder (no trailing slash)
		$Z->Add(substr($CONFIG->dataroot,0,-1));
		 
		// adding database
		$sqlFile = $CONFIG->dataroot . "database_backup_" . date('Y_m_d').".sql";
		
		$command = "mysqldump -h$CONFIG->dbhost -u$CONFIG->dbuser -p'$CONFIG->dbpass' $CONFIG->dbname > $sqlFile";
		system($command , $return); 
		
		//0 = ok
		if($return == 0){
			$Z->Add($sqlFile);
			unlink($sqlFile);
		}

		if($download){
			// download
			$Z->download();
		} else {
			$Z->Close();
			// do scheduler task 
			$type = get_plugin_setting("backup_type","elggbackup");
			//ftp
			
			if($type == "ftp") backup_to_ftp($zipfile);
			
			// send the mail
			if($type == "email") backup_to_mail("Backup done", $zipfile);
		}
    	// cleanup temp zipfile
		unlink($zipfile);
    }
    
    function backup_to_ftp($zipfile){
    	$ftp_remote_host = get_plugin_setting("ftp_remote_host","elggbackup");
    	$ftp_remote_host_port = get_plugin_setting("ftp_remote_host_port","elggbackup");
    	$ftp_remote_host_timeout = get_plugin_setting("ftp_remote_host_timeout","elggbackup");
    	
    	$ftp_remote_folder = get_plugin_setting("ftp_remote_folder","elggbackup");
    	
    	$ftp_user = get_plugin_setting("ftp_user","elggbackup");
    	$ftp_password = get_plugin_setting("ftp_password","elggbackup");
    	
    	$source_file = $zipfile;
    	$destination_file = basename($source_file);
    	
    	// set up basic connection
		$conn_id = ftp_connect($ftp_remote_host, $ftp_remote_host_port, $ftp_remote_host_timeout); 
		
		// login with username and password
		$login_result = ftp_login($conn_id, $ftp_user, $ftp_password); 
		
		// check connection
		if ((!$conn_id) || (!$login_result)) { 
	        $message .= "FTP connection has failed!" . "\r\n";
	        $message .= "Attempted to connect to $ftp_remote_host for user $ftp_user" . "\r\n"; 
		} else {
			//ftp_pasv($conn_id, true);
	        $message .= "Connected to $ftp_remote_host, for user $ftp_user" . "\r\n";

	        if (!ftp_chdir($conn_id, $ftp_remote_folder)) {
	        	ftp_mkdir($conn_id, $ftp_remote_folder);
	        	ftp_chdir($conn_id, $ftp_remote_folder);
			}
			
			// upload the file
			$upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY); 
				        
			// check upload status
			if (!$upload) { 
		        $message .= "FTP upload has failed!" . $source_file . " to " . $destination_file . "\r\n";
		    } else {
		        $message .= "Uploaded $source_file to $ftp_remote_host as $destination_file" . "\r\n";
		    }
		
			// close the FTP stream 
			ftp_close($conn_id); 
		}
		
		backup_to_mail($message);
    }
    
    function backup_to_mail($message = "", $zipfile){
    	global $CONFIG;
    	
    	$mail_to = get_plugin_setting("emailto","elggbackup");
    	if(!$mail_to){
	    	$mail_to = $CONFIG->siteemail;
    	}
    	
    	if(is_plugin_enabled("phpmailer")){
    		$files = array();
    		if($zipfile){
    			$files[0]["name"] = "backup.zip";
    			$files[0]["path"] = $zipfile;
    		}
    		
			$result = phpmailer_send($CONFIG->siteemail,
				$CONFIG->sitename,
				$mail_to,
				$mail_to,
				"Backup " . $CONFIG->sitename,
				$message,
				NULL,
				false,
				$files
				);
    		system_message(elgg_echo("elggbackup:succes:mailsent"));
    	} else {
    		register_error(elgg_echo("ellgbackup:error:nophpmailer"));
    	}
    }
       
	register_elgg_event_handler('init','system','elggbackup_init');
?>