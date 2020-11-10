<?php 
	//SAME CONFIG FILE - make sure to replace App Secret, client ID, Redirect URL, imap email settings, and DB info. !!IMPORTANT - Rename this file to config.php !!
	//CLOVER API 
    $is_production = true;
	if($is_production){
		$secret = 'PRODUCTION_SECRET'; // production app secret from clover developer
		$client_id = 'PRODUCTION_CLIENT_ID'; // production app id from clover developer
		$api_url = 'https://api.clover.com';
		$redirect_uri = 'REDIRECT_URL'; //directory on your own server where this repo lives
	} else {
		$secret = 'SANDBOX_SECRET'; // sandbox app secret from clover developer
		$client_id = 'SANDBOX_CLIENT_ID';// sandbox app id from clover developer
		// https://apisandbox.dev.clover.com
		$api_url = 'https://sandbox.dev.clover.com';
		$redirect_uri = 'REDIRECT_URL'; //directory on your own server where this repo lives
	}

	//DOORDASH ORDER EMAIL (IMAP)
	$email_server = "EMAIL_SERVER"; // your imap email server
	$email_username = "EMAIL_ADDRESS"; //your full email address
	$email_password = "EMAIL_PASSWORD"; // your email password/app paswsword
	
	//DB
	date_default_timezone_set("America/Los_Angeles"); 
	$servername="DB_HOSTNAME"; // 'localhost' or mysql servername (hostname)
	$dbname="DB_NAME"; // db name
	$username="DB_USERNAME"; // db username
	$password="DB_PASSWORD"; // db password

	$con=new mysqli($servername,$username,$password,$dbname);
	if( !$con )
	{
		die("Database Connection Failed" . mysqli_error());
	}
	// 0 to log no errors
	error_reporting(-1);
?>