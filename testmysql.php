<?php 
$link = mysqli_connect('mydbinstance.crk8jaubwnsf.eu-west-1.rds.amazonaws.com', 'awsuser', 'mypassword', 'mydb', 3306);
if (!$link) { 
	die('Could not connect to MySQL: ' . mysql_error()); 
} 
echo 'Connection OK'; mysqli_close($link); 
?>