<?php 
//    Copyright (C) 2011  Mike Allison <dj.mikeallison@gmail.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

// 	  BTC Donations: 163Pv9cUDJTNUbadV4HMRQSSj3ipwLURRc

//Check that script is run locally
$ip = $_SERVER['REMOTE_ADDR'];
if ($ip != "127.0.0.1") {
	echo "cronjobs can only be run locally.";
	exit;
}

$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");
	
////Update share counts

//Update past shares
try {
	$pastSharesQ = mysql_query("SELECT DISTINCT userId, sum(count) AS valid, sum(invalid) AS invalid, id FROM shares_counted GROUP BY userId");
	while ($pastSharesR = mysql_fetch_object($pastSharesQ)) {
		mysql_query("UPDATE webUsers SET share_count=".$pastSharesR->valid.", stale_share_count=".$pastSharesR->invalid." WHERE id=".$pastSharesR->userId);
	} 
} catch (Exception $ex)  {}

//Update current round shares
try {
	$sql ="SELECT sum(id) AS id, a.associatedUserId FROM ".
		  "(SELECT count(s.id) AS id, p.associatedUserId FROM shares s, pool_worker p WHERE p.username=s.username AND s.our_result='Y' GROUP BY p.associatedUserId  ".
		  "UNION ".
		  "SELECT count(s.id) AS id, p.associatedUserId FROM shares_history s, pool_worker p WHERE p.username=s.username AND s.our_result='Y' AND s.counted='0' GROUP BY p.associatedUserId) a GROUP BY associatedUserId ";
	$result = mysql_query($sql);
	$totalsharesthisround = 0;
	while ($row = mysql_fetch_array($result)) {
		mysql_query("UPDATE webUsers SET shares_this_round=".$row["id"]." WHERE id=".$row["associatedUserId"]);
		$totalsharesthisround += $row["id"];
	}
	mysql_query("UPDATE settings SET value='".$totalsharesthisround."' WHERE setting='currentroundshares'");
} catch (Exception $ex)  {}

?>