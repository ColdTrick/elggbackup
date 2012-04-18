<?php 
	admin_gatekeeper();
	if(get_input("test") == "yes"){
		elggbackup_cron();
		forward($CONFIG->wwwroot . "pg/admin/plugins");	
	} else {
		elggbackup_cron(null,null,null,null,true);
		
	}
?>