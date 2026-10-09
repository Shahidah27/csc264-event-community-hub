<?php
	$servername = "localhost"; 
	$username = "root"; 
	$password = ""; 
	$dbname = "smartville_db"; 
	
	$conn = mysqli_connect($servername,$username,$password,$dbname);
	
	if(!$conn) {
		die("Connection failed: ".mysqli_connect_error()); 
	}
	$conn->query("SET GLOBAL max_allowed_packet=67108864");
?>