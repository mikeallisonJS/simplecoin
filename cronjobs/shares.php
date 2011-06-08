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

$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");
	
/////////Update share counts
try {
	$sql ="select sum(id) as id, a.associatedUserId from ".
		  "(select count(s.id) as id, p.associatedUserId from shares s, pool_worker p WHERE p.username=s.username group by p.associatedUserId ".
		  "union ".
		  "select count(s.id) as id, p.associatedUserId from shares_history s, pool_worker p WHERE p.username=s.username group by p.associatedUserId) a group by associatedUserId";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		mysql_query("UPDATE webUsers SET share_count=".$row["id"]." WHERE id=".$row["associatedUserId"]);
	}
} catch (Exception $ex)  {}

try {
	$sql ="select sum(id) as id, a.associatedUserId from ".
		  "(select count(s.id) as id, p.associatedUserId from shares s, pool_worker p WHERE p.username=s.username AND s.our_result='N' group by p.associatedUserId  ".
		  "union ".
		  "select count(s.id) as id, p.associatedUserId from shares_history s, pool_worker p WHERE p.username=s.username AND s.our_result='N' group by p.associatedUserId) a group by associatedUserId ";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		mysql_query("UPDATE webUsers SET stale_share_count=".$row["id"]." WHERE id=".$row["associatedUserId"]);
	}
} catch (Exception $ex)  {}

try {
	$sql ="select sum(id) as id, a.associatedUserId from ".
		  "(select count(s.id) as id, p.associatedUserId from shares s, pool_worker p WHERE p.username=s.username AND s.our_result='Y' group by p.associatedUserId  ".
		  "union ".
		  "select count(s.id) as id, p.associatedUserId from shares_history s, pool_worker p WHERE p.username=s.username AND s.our_result='Y' AND s.counted=0 group by p.associatedUserId) a group by associatedUserId ";
	$result = mysql_query($sql);
	$totalsharesthisround = 0;
	while ($row = mysql_fetch_array($result)) {
		mysql_query("UPDATE webUsers SET shares_this_round=".$row["id"]." WHERE id=".$row["associatedUserId"]);
		$totalsharesthisround += $row["id"];
	}
} catch (Exception $ex)  {}
mysql_query("UPDATE settings SET value='".$totalsharesthisround."' WHERE setting='currentroundshares'");
?>