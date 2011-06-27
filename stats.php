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

$pageTitle = "- Register";
include ("includes/header.php");

?>
<h1>Server Stats</h1><br/>
<?php
$bitcoincon = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword);
echo "Current Block: ".antiXss($bitcoincon->getblocknumber())."<br/>";
echo "Current Difficulty: ".round($bitcoincon->getdifficulty(), 2)."<br/>";

$result = mysql_query("SELECT n.blockNumber, n.confirms, n.timestamp FROM winning_shares w, networkBlocks n WHERE w.blockNumber = n.blockNumber ORDER BY w.blockNumber DESC LIMIT 1");
if ($resultrow = mysql_fetch_object($result)) {
	echo "Last Block Found: ".$resultrow->blockNumber."<br/>";
	echo "Confirmations: ".$resultrow->confirms."<br/>";
	echo "Time: ".strftime("%B %d %Y %r",$resultrow->timestamp)."<br/>";
}
?><br/>

<h1>Member Stats</h1><br/>
<table>
<tr><td><b>Top 20 Hashrates</b></td><td><b>Top 20 Lifetime Shares</b></td></tr>
<tr><td><table>
	<tr><td>User Id</td><td>Hashrate</td></tr>
	<?php
		$result = mysql_query("SELECT id, hashrate FROM webUsers ORDER BY hashrate DESC LIMIT 20");
		while ($resultrow = mysql_fetch_object($result)) {
			echo "<tr><td>".$resultrow->id."</td><td>".$resultrow->hashrate."</td></tr>";
		}
	?>
</table></td>
<td><table>
	<tr><td>User Id</td><td>Shares</td></tr>
	<?php
		$result = mysql_query("SELECT id, share_count, stale_share_count FROM webUsers ORDER BY share_count DESC LIMIT 20");
		while ($resultrow = mysql_fetch_object($result)) {
			echo "<tr><td>".$resultrow->id."</td><td>".($resultrow->share_count - $resultrow->stale_share_count)."</td></tr>";
		}
	?>
</table></td></tr>
</table>

<?php include("includes/footer.php"); ?>