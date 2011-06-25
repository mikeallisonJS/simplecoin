<?php

class Stats { 	
	function gettotalshares() {		
		$sql = "select count(id) from shares";		
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row != NULL)return $row[0];	
	}
	
	function currenthashrate() {		
		$sql = "select (select count(id) from shares where time > DATE_SUB(now(), INTERVAL 10 MINUTE)) ". 
				"+ (select count(id) from shares_history where time > DATE_SUB(now(), INTERVAL 10 MINUTE)) from dual";		
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row != NULL)return intval($row[0]) * 8;	
	}
	
	function currentworkers() {
		$sql = "select count(a.username) from ( ".
               "select distinct username from shares where time > DATE_SUB(now(), INTERVAL 60 MINUTE) ".
			   "UNION ".
			   "select distinct username from shares_history where time > DATE_SUB(now(), INTERVAL 60 MINUTE)) a";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row != NULL)return intval($row[0]);
	}
}

?>