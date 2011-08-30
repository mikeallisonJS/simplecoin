<?php

class Stats {
	function previousRoundSharesInShares() {
		//don't cache 0/1 or true/false
		global $read_only_db;
		$retval = "new";
		if (!($retval = getCache("previousRoundSharesInShares"))) {
			$sql = "SELECT s.id FROM shares s, winning_shares w WHERE s.id = w.share_id";
			$result = $read_only_db->query($sql);
			if ($row = $result->fetch()) 
				$retval = "old";			
			setCache("previousRoundSharesInShares", $retval, 300);
		}
		//echo "prev shares: $retval";
		if ($retval == "old")
			return true;
		return false;		
	}
	
	function currentshares() {
		global $read_only_db;		
		$currentshares = 0;
		if (!($currentshares = getCache("pool_shares"))) {
			$lastwinningshare = $this->lastWinningShareId();
			$sql = "SELECT count(*) FROM shares";
			if ($this->previousRoundSharesInShares())
				$sql = "SELECT count(*) FROM shares WHERE id > $lastwinningshare";			
			$result = $read_only_db->query($sql);
			if ($row = $result->fetch()) {		
				$currentshares = $row[0];				
				setCache("pool_shares", $currentshares, 2);
			}
		}
		return $currentshares;				 
	}
	
	function currentUnconfirmedShares() {
		global $read_only_db;		
		$currentshares = 0;
		if (!($currentshares = getCache("unconfirmed_pool_shares"))) {
			$lastwinningshare = $this->lastWinningShareId();
			$lastrewardedshare = $this->lastRewardedShareId();
			$sql = "SELECT count(*) FROM shares WHERE id < $lastwinningshare AND id > $lastrewardedshare";
			$result = $read_only_db->query($sql);
			if ($row = $result->fetch()) {		
				$currentshares = $row[0];				
				setCache("unconfirmed_pool_shares", $currentshares, 600);
			}
		}
		return $currentshares;				 
	}
	
	function currentstales() {
		global $read_only_db;		
		$currentshares = 0;
		$lastwinningshare = $this->lastWinningShareId();
		if (!($currentshares = getCache("pool_stales"))) {
			$sql = "SELECT count(*) FROM shares WHERE our_result='N'";
			if ($this->previousRoundSharesInShares())
				$sql = "SELECT count(*) FROM shares WHERE id > $lastwinningshare AND our_result='N'";
			$result = $read_only_db->query($sql);
			if ($row = $result->fetch()) {		
				$currentshares = $row[0];				
				setCache("pool_stales", $currentshares, 300);
			}
		}
		return $currentshares;				 		
	}

	function currenthashrate() {
		global $read_only_db;
		$currenthashrate = 0;
		if (!($currenthashrate = getCache("pool_hashrate"))) {
			$sql =  "SELECT count(*) as id FROM shares WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE) ";
			$result = mysql_query($sql);
			if ($resultrow = mysql_fetch_array($result)) {
				$currenthashrate = $resultrow[0];
				$currenthashrate = round((($currenthashrate*4294967296)/590)/1000000, 0);
				setCache("pool_hashrate", $currenthashrate, 300);
				try {
					$fileName = "/var/www/api/pool/speed";
					$fileHandle = fopen($fileName, 'w');
					fwrite($fileHandle, ($currenthashrate/1000));
					fclose($fileHandle);
				} catch (Exception $e) {
					//echo $e->getMessage();
				}
			}
		}
		return $currenthashrate;
	}

	function poolefficiency() {
		global $totalUserShares;
		global $read_only_db;
		$efficiency = 0.0;
		if (!($efficiency = getCache("pool_efficiency"))) {
			$efficiency = (1 - ($this->currentstales()/$this->currentshares())) * 100;
			if ($efficiency > 0)
				setCache("pool_efficiency", $efficiency, 600);		
		}
		return $efficiency;
	}
	
	function currentworkers() {
		$currentworkers = 0;		
		if (!($currentworkers = getCache("pool_workers"))) {
			$uwa = $this->workerhashrates();
			foreach ($uwa as $key => $value) {
				if ($value > 0)
					$currentworkers += 1;				
			}
			setCache("pool_workers", $currentworkers, 1800);
		}
		return $currentworkers;
	}
	
	function lastRewardedShareId() {
		global $read_only_db;
		$shareid = 0;
		if (!($shareid = getCache("last_rewarded_share_id"))) {
			$result = $read_only_db->query("SELECT max(share_id) FROM winning_shares WHERE rewarded='Y'");
			if ($row = $result->fetch()) {
				$shareid = $row[0];				
			}			
			setCache("last_rewarded_share_id", $shareid, 600);
		}
		return $shareid;
	}
	
	function lastWinningShareId() {
		global $read_only_db;
		$shareid = 0;
		if (!($shareid = getCache("last_winning_share_id"))) {
			$result = $read_only_db->query("SELECT max(share_id) FROM winning_shares");
			if ($row = $result->fetch()) {
				$shareid = $row[0];				
			}			
			setCache("last_winning_share_id", $shareid, 600);
		}
		if ($shareid == '')
			return 0;
		return $shareid;
	}
	
	function onionwinners($limit) {
		global $read_only_db;
		$uwa = Array();
		if (!($uwa = getCache("onion_winners_array"))) {
			$result = $read_only_db->query("SELECT username, (stale_share_count / share_count)*100 AS stale_percent FROM webUsers WHERE shares_this_round > 0 ORDER BY stale_percent DESC LIMIT ".$limit);
			while ($row = $result->fetch()) {
				$uwa[$row[0]] = $row[1];
			}
			setCache("onion_winners_array", $uwa, 1800);
		}
		return $uwa;
	}
	
	function lastwinningblocks($limit) {
		global $read_only_db;
		$i = 0;
		$uwa = Array();
		if (!($uwa = getCache("last_winning_blocks"))) {
			$result = $read_only_db->query("SELECT w.username, w.blockNumber, w.confirms, n.timestamp FROM winning_shares w, networkBlocks n WHERE w.blockNumber = n.blockNumber ORDER BY w.blockNumber DESC LIMIT ".$limit);
			while ($row = $result->fetch()) {
				$uwa[$i] = Array();
				$uwa[$i][0] = $row[0];
				$uwa[$i][1] = $row[1];
				$uwa[$i][2] = $row[2];
				$uwa[$i][3] = $row[3];
				$i += 1;
			}
			setCache("last_winning_blocks", $uwa, 600);
		}
		return $uwa;
	}
	
	function unrewardedblocks() {
		global $read_only_db;
		$count = 0;
		if (!($count = getCache("unrewarded_block_count"))) {
			$result = mysql_query("SELECT count(*) FROM winning_shares WHERE rewarded = 'N'") or die(mysql_error());
			if ($row = mysql_fetch_row($result))
				$count = $row[0];
			setCache("unrewarded_block_count", $count, 600);
		}
		return $count;	
	}	
	
	function workerhashrates() {
		global $read_only_db;
		$uwa = Array();
		if (!($uwa = getCache("worker_hashrates"))) {
			$sql ="SELECT IFNULL(count(s.id),0) AS hashrate, p.username FROM pool_worker p LEFT JOIN ".
				  "shares s ON p.username = s.username ".
				  "WHERE s.time > DATE_SUB(now(), INTERVAL 10 MINUTE) ". 
				  "GROUP BY username ";
			$result = $read_only_db->query($sql);
			while ($resultObj = $result->fetch()) {				
				$uwa[$resultObj[1]] = round((($resultObj[0]*4294967296)/590)/1000000, 0);
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
		global $read_only_db;	
		$uwa = Array();
		if (!($uwa = getCache("user_hashrates"))) {
			$sql ="SELECT IFNULL(count(s.id),0) AS hashrate, u.username FROM webUsers u ".
				  "INNER JOIN pool_worker p ON p.associatedUserId = u.id ".
				  "LEFT JOIN shares s ON p.username = s.username ".
				  "WHERE s.time > DATE_SUB(now(), INTERVAL 10 MINUTE) ". 
				  "GROUP BY username ".
				  "ORDER BY hashrate DESC";
			$result = $read_only_db->query($sql);
			while ($resultObj = $result->fetch()) {				
				$uwa[$resultObj[1]] = round((($resultObj[0]*4294967296)/590)/1000000, 0);
			}
			if (count($uwa) > 0) 
				setCache("user_hashrates", $uwa, 600);					
		}
		return $uwa;
	}
	
	function stalecalc($roundstales, $roundshares) {		
		if ($roundshares > 0)
			return round($roundstales/$roundshares*100,2);
		return 0;
	}
	
	function username_userid_array() {
		$userarray = Array();
		$result = mysql_query("SELECT id, username from webUsers");
		while ($row = mysql_fetch_object($result)) {
			$userarray[$row->username] = $row->id;
		}
		return $userarray;
	}
	
	function userhashratesbyid() {
		$uhr = Array();
		$uwa = $this->userhashrates();
		foreach ($this->username_userid_array() as $username => $userid) {
			if (array_key_exists($username, $uwa))
				$uhr[$userid] = $uwa[$username];
			else 
				$uhr[$userid] = 0;
		}
		return $uhr;
	}
	
	function userhashrate($username) {
		$userhashrate = 0;
		$uwa = $this->userhashrates();
		if (array_key_exists($username, $uwa)) {
			$userhashrate = $uwa[$username];
		}
		return $userhashrate;
	}
	
	function userUnconfirmedEstimate($userid) {
		global $read_only_db;
		$estimate = 0;			
		$unrewardedblocks = $this->unrewardedblocks();
		if ($unrewardedblocks) {
			if (!($estimate = getCache("user_unconfirmed_estimate_".$userid))) {				
				$sql = "SELECT IFNULL(sum(amount),0) FROM unconfirmed_rewards WHERE userId = $userid AND rewarded='N'";
				$result = mysql_query($sql) or die(mysql_error());
				if ($row = mysql_fetch_row($result)) {
					$estimate = $row[0];
				}
				setCache("user_unconfirmed_estimate_".$userid, $estimate, 600);
			}			
		}
		return $estimate;
	}
	
	function userUnconfirmedShares($userid) {
		global $read_only_db;
		$shares = 0;			
		if ($unrewardedblocks) {
			if (!($shares = getCache("user_unconfirmed_shares_".$userid))) {				
				$sql = "SELECT IFNULL(sum(shares),0) FROM unconfirmed_rewards WHERE userId = $userid AND rewarded='N'";
				$result = mysql_query($sql);
				if ($row = mysql_fetch_row($result)) {
					$shares = $row[0];
				}
				setCache("user_unconfirmed_shares_".$userid, $shares, 600);
			}			
		}
		return $shares;
	}
	
	function usersharecount($userId) {
		global $read_only_db;	
		$totalUserShares = 0;
		$workers = Array();
		$lastwinningshare = $this->lastWinningShareId();
		if (!($totalUserShares = getCache("user_shares_".$userId))) {
			$workers = $this->workers($userId);
			$sql = "SELECT sum(s.id) FROM (SELECT 'a' as username, 0 as id ";
			foreach ($workers as $worker) {
				if ($this->previousRoundSharesInShares())
					$sql .= "UNION SELECT username, count(id) as id FROM shares WHERE username = '$worker' AND id > $lastwinningshare ";
				else 
					$sql .= "UNION SELECT username, count(id) as id FROM shares WHERE username = '$worker' ";				
			}
			$sql .= ") s";			
			$currentSharesQ = $read_only_db->query($sql);
			if ($currentSharesR = $currentSharesQ->fetch()) {
				$totalUserShares = $currentSharesR[0];
				setCache("user_shares_".$userId, $totalUserShares,3);
			}
		}
		return $totalUserShares;
	}
	
	function userssharecount($limit) {
		global $read_only_db;
		$uwa = Array();
		if (!($uwa = getCache("users_sharecount"))) {
			$sql = "SELECT username, share_count-stale_share_count+shares_this_round AS shares FROM webUsers ORDER BY shares DESC LIMIT ".$limit;
			$result = $read_only_db->query($sql);
			while ($row = $result->fetch()) {
				$uwa[$row[0]] = $row[1];				
			}
			setCache("users_sharecount", $uwa, 1800);
		}
		return $uwa;
	}

	function userstalecount($userId) {
		global $read_only_db;
		$totalUserShares = 0;
		$workers = Array();
		$lastwinningshare = $this->lastWinningShareId();
		if (!($totalUserShares = getCache("user_stales_".$userId))) {
			$workers = $this->workers($userId);		
			$sql = "SELECT sum(s.id) FROM (SELECT 'a' as username, 0 as id ";
			foreach ($workers as $worker) {
				if ($this->previousRoundSharesInShares())
					$sql .= "UNION SELECT username, count(id) as id FROM shares WHERE username = '$worker' AND id > $lastwinningshare AND our_result='N' ";
				else 
					$sql .= "UNION SELECT username, count(id) as id FROM shares WHERE username = '$worker' AND our_result='N' ";				
			}
			$sql .= ") s";
			$currentSharesQ = $read_only_db->query($sql);							
			if ($currentSharesR = $currentSharesQ->fetch()) {
				$totalUserShares = $currentSharesR[0];
				setCache("user_stales_".$userId, $totalUserShares,1800);
			}
		}
		return $totalUserShares;
	}	
	
	function userrankshares($userid) {
		global $read_only_db;
		$rank_shares = Array();
		if (!($rank_shares = getCache("user_rank_shares_".$userid))) {		
			$query_init = "SET @rownum := 0";
			$query_getrank =   "SELECT rank, shares FROM (
	        	                SELECT @rownum := @rownum + 1 AS rank, share_count-stale_share_count+shares_this_round AS shares, id
	            	            FROM webUsers ORDER BY shares DESC
	                	        ) as result WHERE id=" . $userid;
	
			$read_only_db->query($query_init);
			$result = $read_only_db->query($query_getrank);
			if ($row = $result->fetch()) {
				$rank_shares[0] = $row[0];
				$rank_shares[1] = $row[1];
			}
		}
		return $rank_shares;
	}
	
	function userrankhash($userid) {
		global $read_only_db;
		$rank = 1;		
		$uha = $this->userhashratesbyid();
		if (!($rank = getCache("user_rank_hash_".$userid))) {
			foreach ($uha as $key => $value) {
				if ($key == $userid)
					break;
				else 
					$rank += 1;
			}
			setCache("user_rank_hash_".$userid, $rank, 1800);
		}
		return $rank;		
	}
	
	function workers($userId) {
		global $read_only_db;
		$workers = Array();
		if (!($workers = getCache("user_workers_".$userId))) {
			$sql = "SELECT username FROM pool_worker WHERE associatedUserId = ".$userId;
			$workersQ = $read_only_db->query($sql);
			while ($workersR = $workersQ->fetch()) {
				$workers[] = $workersR[0];
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
			setCache("mtgox_last", $last, 1800);
		}
		return $last;
	}
	
	function get_server_load($windows = 0) {			
		$serverload = "n/a";
		if (!($serverload = getCache("pool_load"))) {
			$numberOfCores = 8;
			$avgLoad = 0;			
			$os = strtolower(PHP_OS);		
			if(strpos($os, "win") === false) {
				if(file_exists("http://pool.simplecoin.us/loadavg.html")) {
					$load = file_get_contents("http://pool.simplecoin.us/loadavg.html");
					$load = explode(' ', $load);
					$avgLoad = $load[0];
				} elseif (function_exists("shell_exec")) {
					$load = explode(' ', `uptime`);
					$avgLoad = $load[count($load)-1];
				}
				//This may need to be adjusted depending on your system. This is assuming a dual core setup.
				if ($avgLoad > 1.9*$numberOfCores) {
					$serverload = "critical";
				} else if ($avgLoad > 1.5*$numberOfCores) {
					$serverload = "high";
				} else if ($avgLoad > .5*$numberOfCores) {
					$serverload = "mid";
				} else if ($avgLoad > 0*$numberOfCores) {
					$serverload = "low";
				}
			} elseif ($windows) {
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