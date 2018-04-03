#!/usr/bin/php -q
<?php
/* 
CRONTAB BASED REPORTING THRU EMAIL (SMTP)
BY ARIES LALUCES

20140730 - version 0.1 
         - total calls report using PHPMailer
20151022 - OOP + PDO
         - Mail to custom list - Pending		 
       
*/   
require 'db.php';
require 'classes.php';
require 'class.phpmailer.php';
require 'config.php';

$datenow   = date("Y-m-d");

$report = new classReport($DBH);
$mail   = new PHPMailer();

$mail->IsSMTP();                                  // telling the class to use SMTP 
#$mail->SMTPDebug  = 2;                           // enables SMTP debug information (for testing) 
$mail->SMTPAuth   = true;                         // enable SMTP authentication
$mail->SMTPSecure = 'tls';
$mail->Host       = 'smtp.gmail.com';             // sets the SMTP server
$mail->Port       = 587;                          // set the SMTP port for gmail
$mail->Username   = 'XXXXXXXXXX@gmail.com'; // SMTP account username
$mail->Password   = 'XXXXXX';
$mail->SetFrom('XXXXXXXXXXXXXXX@gmail.com', 'IT Report');
$mail->AddReplyTo('XXXXXXXXXXXXXXX@gmail.com','IT Report'); 


for ($i=0; $i < count($campaignsManual); $i++) {
	echo "Generating report for $accountsManual[$i]\n";
	$data = $report->getDataManual($campaignsManual[$i]);
	//var_dump($data);
	$body = $report->generateHtmlTable('manual', $data, $accountsManual[$i]);

	echo "$accountsManual[$i] has $report->totalCalls total calls\n";
	if ($report->totalCalls < 40) { continue; }

	$mail->MsgHTML($body);
	$mail->Subject    = "$accountsManual[$i] - MANUAL DIAL Total Calls Report $datenow";
	$mail->AddAddress("XXXX@gmail.com", "IT Support");    
	$mail->AddCC("CCCC@gmail.com");
	echo "attempting to send...";
	if(!$mail->Send()) {
	  echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
	  echo "Message sent!\n";
	}
}


for ($i=0; $i < count($groupsVici); $i++) {
	echo "Generating report for $accountsVici[$i]\n";
	$data = $report->getDataVici($groupsVici[$i]);
	//var_dump($data);
	$body = $report->generateHtmlTable('vici', $data, $accountsVici[$i]);

	echo "$accountsVici[$i] has $report->totalCalls total calls\n";
	if ($report->totalCalls < 40) { continue; }

	$mail->MsgHTML($body);
	$mail->Subject    = "$accountsVici[$i] - VICIDIAL Total Calls Report $datenow";
	$mail->AddAddress("XXXXXX@gmail.com", "IT Support");    
	$mail->AddCC("XXXXXXX@gmail.com");
	echo "attempting to send...";
	if(!$mail->Send()) {
	  echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
	  echo "Message sent!\n";
	}
}
        


     
?>
