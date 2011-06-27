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
}

?>