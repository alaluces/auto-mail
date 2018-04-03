<?php

class classReport {
    
    function __construct($DBH) {
        $this->DBH 		 = $DBH; 
		$this->dateToday = date("Y-m-d");	
		$this->totalCalls = 0;
	
    }
	
    public function getDataManual($aPrefixes) 
    {    
		$prefixes = "'" . implode($aPrefixes,"','") . "'";
        $STH = $this->DBH->prepare("
			SELECT SUBSTRING(channel,5,3) AS 'extension', 
			SUM(billsec BETWEEN 1 AND 59) AS ' less than 1 min',
			SUM(billsec BETWEEN 60 AND 300) AS '1 to 5 min',
			SUM(billsec BETWEEN 301 AND 1800) AS '5 to 30 min',
			SUM(billsec > 1800) AS '30 min and above',
			SUM(billsec > 0) AS 'Total'
			FROM cdr AS c
			INNER JOIN phones AS p
			ON SUBSTRING(channel,5,3) = p.login
			WHERE LEFT(calldate,10) = '$this->dateToday'
			AND LEFT(dst,2) IN ($prefixes)
			GROUP BY p.login
			ORDER BY extension");
        $STH->execute(); 
        return $STH->fetchAll();
	}
	
    public function getDataVici($userGroup) 
    { 	
        $STH = $this->DBH->prepare("
			SELECT u.full_name AS 'Agent', 
			SUM(talk_sec BETWEEN 1 AND 59) AS ' less than 1 min',
			SUM(talk_sec BETWEEN 60 AND 300) AS '1 to 5 min',
			SUM(talk_sec BETWEEN 301 AND 1800) AS '5 to 30 min',
			SUM(talk_sec > 1800) AS '30 min and above',
			SUM(talk_sec > 0) AS 'Total Live',
			COUNT(talk_sec) AS 'Total Calls'
			FROM `vicidial_agent_log` AS l
			INNER JOIN vicidial_users AS u
			ON l.user = u.user
			WHERE LEFT(event_time,10) = '$this->dateToday'
			AND u.user_group = '$userGroup'
			GROUP BY u.user");
        $STH->execute(); 
        return $STH->fetchAll();
	}	


	
	public function generateHtmlTable($mode, $aResult, $accountName) 
	{
		$body = "
		<span style='font-family:verdana;font-size:12px;'>
		$accountName Total Calls Report $this->dateToday:
		</span>
		<br><br>
		<table>
		<tr style='background:black;color:white'>
			<th>Extension</th>
			<th>less than 1 min</th>
			<th>1 to 5 min</th>
			<th>5 to 30 min</th>  
			<th>30 min and above</th>";				
			if ($mode == 'vici') {
				$body .= "
				<th>Live Calls</th>
				<th>Total Calls</th>";
			} else {
				$body .= "<th>Total Calls</th>";
			}		
		$body .= "</tr>";		
		
		foreach ($aResult as $row) {		
			$tot1 = $tot1 + $row[1];        
			$tot2 = $tot2 + $row[2];
			$tot3 = $tot3 + $row[3];
			$tot4 = $tot4 + $row[4];
			$tot5 = $tot5 + $row[5];
			if ($mode == 'vici') {
				$tot6 = $tot6 + $row[6];
			}

			$body .= "<tr style='font-family:verdana;font-size:11px;'>
			<td style='background:#C6DEFF;color:black;'>$row[0]</td>           
			<td style='background:#C6DEFF;color:black;'>$row[1]</td>        
			<td style='background:#C6DEFF;color:black;'>$row[2]</td>
			<td style='background:#C6DEFF;color:black;'>$row[3]</td>
			<td style='background:#C6DEFF;color:black;'>$row[4]</td>
			<td style='background:#C6DEFF;color:black;'>$row[5]</td>";	
			if ($mode == 'vici') {
				$body .= "<td style='background:#C6DEFF;color:black;'>$row[6]</td>";
			}		
			$body .= "</tr>";
		} 
		
		$body .= "
		</table>
		<span style='font-family:verdana;font-size:12px;'>
		Total calls less than 1 minute: $tot1 <br>
		Total calls between 1 and 5 minutes: $tot2 <br>
		Total calls between 5 and 30 minutes: $tot3 <br>
		Total calls 30 minutes and above: $tot4 <br>		
		</span>";
		if ($mode == 'vici') {
			$body .= "<span>
			Total live for the day: $tot5 <br>
			Total calls for the day: $tot6 <br></span>";
		} else {
			$body .= "<span>Total calls for the day: $tot5 <br></span>";		
		}		
		
		$this->totalCalls = $tot5;
		return $body;
		
	}	
	
}