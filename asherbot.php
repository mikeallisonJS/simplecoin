<?php

//Asher's PHP Stats File

include ("includes/requiredFunctions.php");

/*
Outputs

Total Hashrate
Number of Users Mining
Last Time Block Found
Who Found Last Block
*/

//Total Hashrate
$hashrate = round($settings->getsetting('currenthashrate')/1000,1)or sqlerr(__FILE__,__LINE__);

//Number of Users Mining
$res = mysql_query("SELECT count(webUsers.id) FROM webUsers WHERE hashrate > 0") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_array($res);
$users = $row[0];

//Who Found Last Block + Last Time Block Found + Block Number
$result = mysql_query("SELECT blockNumber, username, time FROM shares_history WHERE upstream_result = 'Y' ORDER BY id DESC LIMIT 1");
if ($resultrow = mysql_fetch_object($result)) {
	$blocknumber = $resultrow->blockNumber;
	$blocktime = $resultrow->time;
	$solvedby = $resultrow->username;
}
$result = mysql_query("SELECT id,confirms FROM networkBlocks WHERE confirms >= 1 ORDER BY blockNumber DESC LIMIT 1");
if ($resultrow = mysql_fetch_object($result)) {
	$confirms = $resultrow->confirms;
}


$timediff = time() - strtotime($blocktime);
$solved = explode(".", $solvedby);
echo $hashrate."g-".$users."-".$blocknumber."-".$timediff."-".$solved[0]."-".$confirms;


?>