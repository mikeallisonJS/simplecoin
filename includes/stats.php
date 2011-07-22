<?php

class Stats {
	function currentshares() {
		$currentshares = 0;
		if (!($currentshares = getCache("pool_shares"))) {
			$sql = "SELECT count(id) FROM shares WHERE counted IS NULL";
			$result = mysql_query($sql);
			$row = mysql_fetch_row($result);
			if ($row != NULL) {
				$currentshares = $row[0];
				//disable the following for truly live stats
				setCache("pool_shares", $currentshares, 1);
			}
		}
		return $currentshares;				 
	}

	function currenthashrate() {
		$currenthashrate = 0;
		if (!($currenthashrate = getCache("pool_hashrate"))) {
			$sql =  "SELECT count(id) as id FROM shares WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE) ";
			$result = mysql_query($sql);
			if ($resultrow = mysql_fetch_row($result)) {
				$currenthashrate = $resultrow[0];
				$currenthashrate = round((($currenthashrate*4294967296)/600)/1000000, 0);
				setCache("pool_hashrate", $currenthashrate, 300);
				try {
					$fileName = "/var/www/api/pool/speed";
					$fileHandle = fopen($fileName, 'w') or die("can't open file");
					fwrite($fileHandle, ($currenthashrate/1000));
					fclose($fileHandle);
				} catch (Exception $e) {
					//echo $e->getMessage();
				}
			}
		}
		return $currenthashrate;
	}

	function currentworkers() {
		$currentworkers = 0;
		if (!($currentworkers = getCache("pool_workers"))) {
			$result = mysql_query("SELECT count(DISTINCT username) FROM shares WHERE time > DATE_SUB(now(), INTERVAL 60 MINUTE)");
			if ($row = mysql_fetch_row($result)) {
				$currentworkers = $row[0];
				setCache("pool_workers", $currentworkers, 1800);
			}
		}
		return $currentworkers;
	}
	
	function workerhashrates() {
		$uwa = Array();
		if (!($uwa = getCache("worker_hashrates"))) {
			$sql ="SELECT IFNULL(count(s.id),0) AS hashrate, p.username FROM pool_worker p LEFT JOIN ".
				  "shares s ON p.username = s.username ".
				  "WHERE s.time > DATE_SUB(now(), INTERVAL 10 MINUTE) ". 
				  "GROUP BY username ";
			$result = mysql_query($sql);
			while ($resultObj = mysql_fetch_object($result)) {				
				$uwa[$resultObj->username] = round((($resultObj->hashrate*4294967296)/600)/1000000, 0);
			}
			if (count($uwa) > 0) 
				setCache("worker_hashrates", $uwa, 600);					
		}
		return $uwa;
	}
	
	function workerhashrate($workername) {
		$workerhashrate = 0;
		$uwa = $this->workerhashrates();
		if (array_key_exists($workername, $uwa)) {
			$workerhashrate = $uwa[$workername];
		}
		return $workerhashrate;
	}
	
	function userhashrates() {
		$uwa = Array();
		if (!($uwa = getCache("user_hashrates"))) {
			$sql ="SELECT IFNULL(count(s.id),0) AS hashrate, u.username FROM webUsers u ".
				  "INNER JOIN pool_worker p ON p.associatedUserId = u.id ".
				  "LEFT JOIN shares s ON p.username = s.username ".
				  "WHERE s.time > DATE_SUB(now(), INTERVAL 10 MINUTE) ". 
				  "GROUP BY username ".
				  "ORDER BY hashrate DESC";
			$result = mysql_query($sql);
			while ($resultObj = mysql_fetch_object($result)) {				
				$uwa[$resultObj->username] = round((($resultObj->hashrate*4294967296)/600)/1000000, 0);
			}
			if (count($uwa) > 0) 
				setCache("user_hashrates", $uwa, 600);					
		}
		return $uwa;
	}
	
	function userhashrate($username) {
		$userhashrate = 0;
		$uwa = $this->userhashrates();
		if (array_key_exists($username, $uwa)) {
			$userhashrate = $uwa[$username];
		}
		return $userhashrate;
	}
	
	function usersharecount($userId) {
		$totalUserShares = 0;
		$workers = Array();
		if (!($totalUserShares = getCache("user_shares_".$userId))) {
			$workers = $this->workers($userId);		
			$sql = "SELECT count(id) as id FROM shares WHERE username in ('".implode("','",$workers)."')";
			$currentSharesQ = mysql_query($sql);
			if ($currentSharesR = mysql_fetch_row($currentSharesQ)) {
				$totalUserShares = $currentSharesR[0];
				setCache("user_shares_".$userId, $totalUserShares,3);
			}
		}
		return $totalUserShares;
	}
	
	function workers($userId) {
		$workers = Array();
		if (!($workers = getCache("user_workers_".$userId))) {
			$workersQ = mysql_query("SELECT username FROM pool_worker WHERE associatedUserId = $userId");
			while ($workersR = mysql_fetch_object($workersQ)) {
				$workers[] = $workersR->username;
			}
			if (count($workers) > 0)
				setCache("user_workers_".$userId, $workers, 300);
		}
		return $workers;
	}
	
	function mtgoxlast () {
		$last = "n/a";
		if (!($last = getCache("mtgox_last"))) {
			include('includes/mtgox.php');
			try {
				$mtgox = new mtgox("", "");
				$ticker = $mtgox->ticker();
				if (intval($ticker['last']) > 0) 
					$last = round(floatval($ticker['last']),2);
			} catch (Exception $e) { }
			if ($last != "n/a")
				setCache("mtgox_last", $last, 1800);
		}
		return $last;
	}
	
	function get_server_load($windows = 0) {	
		$serverload = "n/a";
		if (!($serverload = getCache("pool_load"))) {
			$avgLoad = 0;			
			$os = strtolower(PHP_OS);		
			if(strpos($os, "win") === false) {
				if(file_exists("/proc/loadavg")) {
					$load = file_get_contents("/proc/loadavg");
					$load = explode(' ', $load);
					$avgLoad = $load[0];
				} elseif(function_exists("shell_exec")) {
					$load = explode(' ', `uptime`);
					$avgLoad = $load[count($load)-1];
				}
				//This may need to be adjusted depending on your system. This is assuming a dual core setup.
				if ($avgLoad > 1.9) {
					$serverload = "critical";
				} else if ($avgLoad > 1.5) {
					$serverload = "high";
				} else if ($avgLoad > .5) {
					$serverload = "mid";
				} else if ($avgLoad > 0) {
					$serverload = "low";
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
				if ($avgLoad > 90) {
					$serverload = "critical";
				} else if ($avgLoad > 66) {
					$serverload = "high";
				} else if ($avgLoad > 33) {
					$serverload = "mid";
				} else if ($avgLoad > 0) {
					$serverload = "low";
				}
			}
			if ($serverload != "n/a");
				setCache("pool_load", $serverload, 60);
		}			
		return $serverload;
	}
}

?>