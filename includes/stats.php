<?php

class Stats { 	
	function gettotalshares() {		
		$sql = "SELECT count(id) FROM shares";		
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row != NULL)return $row[0];	
	}
	
	function currenthashrate() {		
		$sql = "SELECT (select count(id) FROM shares WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE)) ". 
				"+ (SELECT count(id) FROM shares_history WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE)) FROM dual";		
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row != NULL)return intval($row[0]) * 8;	
	}
	
	function currentworkers() {
		$sql = "SELECT count(a.username) FROM ( ".
               "SELECT distinct username FROM shares WHERE time > DATE_SUB(now(), INTERVAL 60 MINUTE) ".
			   "UNION ".
			   "SELECT distinct username FROM shares_history WHERE time > DATE_SUB(now(), INTERVAL 60 MINUTE)) a";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row != NULL)return intval($row[0]);
	}
	
	function get_server_load($windows = 0) {
        $os = strtolower(PHP_OS);
        $avgLoad = 0;
        if(strpos($os, "win") === false) {
  			if(file_exists("/proc/loadavg")) {
         		$load = file_get_contents("/proc/loadavg");
         		$load = explode(' ', $load);
         		$avgLoad = $load[0];
  			} elseif(function_exists("shell_exec")) {
         		$load = explode(' ', `uptime`);
         		$avgLoad = $load[count($load)-1];
  			}
        } elseif($windows) {
  			if(class_exists("COM")) {
         		$wmi = new COM("WinMgmts:\\\\.");
         		$cpus = $wmi->InstancesOf("Win32_Processor");
         
         		$cpuload = 0;
         		$i = 0;
         		while ($cpu = $cpus->Next()) {
    				$cpuload += $cpu->LoadPercentage;
    				$i++;
         		}
         
         		$cpuload = round($cpuload / $i, 2);
         		$avgLoad = $cpuload;
  			}
        }
        if ($avgLoad > 0.9) {
        	return "critical";
        } else if ($avgLoad > 0.8) {
        	return "high";
        } else if ($avgLoad > 0.33) {
        	return "mid";
        } else if ($avgLoad > 0) {
        	return "low";
        }
        return "n/a";
	}
}

?>