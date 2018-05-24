<?php
error_reporting(E_ALL);

date_default_timezone_set('Africa/Cairo');

start_backup();

function start_backup(){

	// Start
	$log = "Start Backup ". date("r")."\n";
	$folder = date("Y-m-d");
	$old_date = date('Y-m-d', strtotime('-30 days', time()));
	$yesterday_date = date('Y-m-d', strtotime('-1 days', time()));
	$old_date_remote = date('Y-m-d', strtotime('-3 days', time()));
	
	// Global Variables
	$current_ip = str_replace(".", "", "176.9.25.71");
	$ftp_server = "u134630.your-storagebox.de";
	$ftp_username = "u134630";
	$ftp_userpass = "e6aPHnA0YqLJUhAa";
	
	// DB backup conf
	$backup_db_path = "/backup/db/".$folder."/";
	$remote_backup_db_path = "/".$current_ip."/db/".$folder."/";
	$backup_db_path_old = "/backup/db/".$old_date."/";
	$remote_backup_db_path_old = "/".$current_ip."/db/".$old_date_remote."/";
		
	// Dump Database
	$log .= shell_exec('mkdir -m0750 '.$backup_db_path.'; cp -R /var/lib/mysql '.$backup_db_path);

	// WWW backup conf
	$backup_www_path = "/backup/www/".$folder."";
	$remote_backup_www_path = "/".$current_ip."/www/".$folder."/";
	$backup_www_path_old = "/backup/www/".$old_date."/";
	$remote_backup_www_path_old = "/".$current_ip."/www/".$old_date_remote."/";
	$home_directory = "/var/www/html";
	
	// Compress www
	$log .= "Start Backup www for day ".Date("d");
	$log .= shell_exec('mkdir -m0750 '.$backup_www_path.'; cp -R '.$home_directory.' '.$backup_www_path);
	return true;
	
	// Connect FTP
	$log .= "FTP Connect\n";
	$ftp_conn = ftp_connect($ftp_server) or $log .= "Could not connect to $ftp_server";
	$log .= "FTP Login\n";
	$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
	
	// Create DB Folder
	if (ftp_mkdir($ftp_conn, $remote_backup_db_path)){
	 $log .= "Successfully created $remote_backup_db_path \n";
	 } else {
	 $log .= "Error while creating $remote_backup_db_path \n";
	 }

	if($www_backup == "enabled" && $i == "1"){
		// Create WWW Folder
		if (ftp_mkdir($ftp_conn, $remote_backup_www_path)){
		 $log .= "Successfully created WWW $remote_backup_www_path \n";
		 } else {
		 $log .= "Error while creating WWW $remote_backup_www_path \n";
		 }
	
		// Upload WWW file
		if (ftp_put($ftp_conn, $remote_wwwgz_file_name, $wwwgz_file_name, FTP_BINARY)) {
		 $log .= "successfully uploaded WWW $remote_wwwgz_file_name \n";
		} else {
		 $log .= "There was a problem while uploading WWW $remote_wwwgz_file_name \n";
		}
		
		// Delete 90 days ago WWW
		$log .= "Start Delete\n";
		$log .= shell_exec('rm -Rf '.$backup_www_path_old);
		ftp_chdir($ftp_conn, $remote_backup_www_path_old);
		$files = ftp_nlist($ftp_conn, ".");
		foreach ($files as $file)
		{
		    ftp_delete($ftp_conn, $file);
		}    
		if (ftp_rmdir($ftp_conn, $remote_backup_www_path_old)) {
		    $log .= "Successfully deleted $remote_backup_www_path_old\n";
		} else {
		    $log .= "There was a problem while deleting $remote_backup_www_path_old\n";
		}
	}
	
	// Upload DB file
	if (ftp_put($ftp_conn, $remote_sql_file_name, $sql_file_name, FTP_BINARY)) {
	 $log .= "successfully uploaded $remote_sql_file_name \n";
	} else {
	 $log .= "There was a problem while uploading $remote_sql_file_name \n";
	}
	
	// Delete 90 days ago files
	$log .= "Start Delete\n";
	$log .= shell_exec('rm -Rf '.$backup_db_path_old);
	ftp_chdir($ftp_conn, $remote_backup_db_path_old);
	$files = ftp_nlist($ftp_conn, ".");
	foreach ($files as $file)
	{
	    ftp_delete($ftp_conn, $file);
	}    
	if (ftp_rmdir($ftp_conn, $remote_backup_db_path_old)) {
	    $log .= "Successfully deleted $remote_backup_db_path_old\n";
	} else {
	    $log .= "There was a problem while deleting $remote_backup_db_path_old\n";
	}	
	
	// Close Connection
	$log .= "ftp close\n-----------------\n";
	ftp_close($ftp_conn); 
	file_put_contents("/backup/backup.log", $log, FILE_APPEND);
	return $log;
}
