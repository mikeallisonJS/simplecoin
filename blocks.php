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
$pageTitle = "- Block Info";
include ("includes/header.php");

echo "<table class=\"stats_table blocks_width bottom_spacing\">";
echo "<tr><th scope=\"col\" colspan=\"7\">Blocks Found</th></tr>";
echo "<tr><th scope=\"col\">Block</th>";
echo "<th scope=\"col\">Confirms</th>";
echo "<th scope=\"col\">Finder</th>";
echo "<th scope=\"col\">Time</th>";
//echo "<th scope=\"col\" class=\"align_right\">Earnings</th>";
//echo "<th scope=\"col\" class=\"align_right\">Shares</th>";
//echo "<th scope=\"col\" class=\"align_right\">Totals Shares</th></tr>";


$result = mysql_query("SELECT w.username, w.blockNumber, n.timestamp, w.confirms FROM networkBlocks n, winning_shares w WHERE n.blockNumber = w.blockNumber AND w.confirms > 1 ORDER BY w.blockNumber DESC");

while($resultrow = mysql_fetch_object($result)) {
	print("<tr>");	

	$resulta = mysql_query("SELECT userid, count FROM shares_counted WHERE blockNumber = $resultrow->blockNumber AND userid = $userId");
	$resdssa = mysql_fetch_object($resulta);

	$blockNo = $resultrow->blockNumber;	
	
	$splitUsername = explode(".", $resultrow->username);
	$realUsername = $splitUsername[0];
	

	$confirms = $resultrow->confirms;

	if ($confirms > 120) {
		$confirms = 'Completed';
	}

if($resdssa == NULL){

	// FIX THIS CODE IF MISSING DATA IS INSERTED ************************************************
	if( $blockNo <= 131574 )
	{
		$est = "Before Upgrade";
		$users = "Before Upgrade";
		$totals = "Before Upgrade";
	}
	else
	{
		$est = "Missing Data";
		$users = "Missing Data";
		$totals = "Missing Data";
	}

} ELSE  {
	//$est = number_format( $resdssa->balanceDelta, 8 );
	$users = number_format( $resdssa->count );
	//$totals = number_format( $resdssa->totalShares );
}

	echo "<td><a href=\"http://blockexplorer.com/b/" . $blockNo . "\">" . number_format( $blockNo ) . "</a></td>";
	echo "<td>" . $confirms . "</td>";
	echo "<td>".$realUsername."</td>";
	echo "<td>".strftime("%B %d %Y %r",$resultrow->timestamp)."</td>";
	//echo "<td class=\"align_right\">" . $est . "</td>";
	//echo "<td class=\"align_right\">" . $users . "</td>";
	//echo "<td class=\"align_right\">" . $totals . "</td>";
}

echo "</table>";
echo "You will not get paid till Confirms have hit 120";
echo "<br /><a class=\"fancy_button top_spacing\" href=\"stats.php\">";
echo "<span style=\"background-color: #070;\">Stats</span></a>";

include("includes/footer.php");