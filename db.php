<?php

$host   = 'localhost';
$dbname = 'asterisk';
$user   = 'cron';
$pass   = '1234';

$DBH    = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); 
?>
