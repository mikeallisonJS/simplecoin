<?php
$includeDirectory = "/var/www/includes/";

//Include hashing functions
include($includeDirectory."requiredFunctions.php");
connectToDb();

//try {
//Open a bitcoind connection
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

//Setup score variables
$sitePercent = 0;
$sitePercentQ = mysql_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = mysql_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;				


$overallReward = 0;
$f = .001;

$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_history s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted=0  ORDER BY s.blockNumber DESC LIMIT 1");
while ($blocks = mysql_fetch_object($blocksQ)) {
	$block = $blocks->blockNumber;
	$totalRoundSharesQ = mysql_query("SELECT count(id) as id FROM shares_history WHERE counted = 0 AND blockNumber <= ".$block);
	if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
		$totalRoundShares = $totalRoundSharesR->id;
		$userListCountQ = mysql_query("SELECT DISTINCT username, count(id) as id FROM shares_history WHERE counted = 0 AND blockNumber <= ".$block." GROUP BY username");
		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
			$username = $userListCountR->username;
			$uncountedShares = $userListCountR->id;
			$shareRatio = $uncountedShares/$totalRoundShares;
			$predonateAmount = (1-$f)*(50*$shareRatio);
			
			//Get site percentage
			$sitePercent = 0;
			$sitePercentQ = mysql_query("SELECT value FROM settings WHERE setting='sitepercent'");
			if ($sitePercentR = mysql_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;				
						
			//get owner userId and donation percent
			$ownerIdQ = mysql_query("SELECT p.associatedUserId, u.donate_percent FROM pool_worker p, webUsers u WHERE u.id = p.associatedUserId AND p.username = '".$username."' LIMIT 0,1");
			$ownerIdObj = mysql_fetch_object($ownerIdQ);
			$ownerId = $ownerIdObj->associatedUserId;
			$donatePercent = $ownerIdObj->donate_percent;
			
			//Take out site percent
			$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");	
			$totalReward = $predonateAmount - ($predonateAmount * ($sitePercent/100));
			
			if ($predonateAmount > 0.00000001)	{
			
				//Take out donation
				$totalReward = $totalReward - ($totalReward * ($donatePercent/100));
				
				//Round Down to 8 digits
				$totalReward = $totalReward * 100000000;
				$totalReward = floor($totalReward);
				$totalReward = $totalReward/100000000;
				
				//Get total site reward
				$donateAmount = $predonateAmount - $totalReward;
						
				$overallReward += $totalReward;	
				//Update balance
				echo $username."-".$totalReward."<br/>";
				//mysql_query("UPDATE accountBalance SET balance = balance + ".$totalReward." WHERE userId = ".$ownerId);
			}
			//mysql_query("UPDATE shares_history SET counted = 1 WHERE username='".$username."' AND blockNumber <= ".$block);
			
				
		}
		$poolReward = $B -$overallReward;
		//mysql_query("UPDATE settings SET value = value +".$poolReward." WHERE setting='sitebalance'");
	}
}
//$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_history s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted=0 ORDER BY s.blockNumber DESC LIMIT 1");
//while ($blocks = mysql_fetch_object($blocksQ)) {
//	$block = $blocks->blockNumber;
//	$totalRoundSharesQ = mysql_query("SELECT count(id) as id FROM shares_history WHERE counted = 0 AND blockNumber <= ".$block);
//	if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
//		$totalRoundShares = $totalRoundSharesR->id;
//		$userListCountQ = mysql_query("SELECT DISTINCT username, count(id) as id FROM shares_history WHERE counted = 0 AND blockNumber <= ".$block." GROUP BY username");
//		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
//			$username = $userListCountR->username;
//			$uncountedShares = $userListCountR->id;
//			$shareRatio = $uncountedShares/$totalRoundShares;
//			$totalReward = (50*$shareRatio) * 100000000;
//			$totalReward = floor($totalReward);
//			$totalReward = $totalReward/100000000;
//			
//			//update the owner of this workers account balance
//			//get owner userId
//			$ownerIdQ = mysql_query("SELECT associatedUserId FROM pool_worker WHERE username = '".$username."' LIMIT 0,1");
//			$ownerIdObj = mysql_fetch_object($ownerIdQ);
//			$ownerId = $ownerIdObj->associatedUserId;
//
//			//Update balance
//			//mysql_query("UPDATE accountBalance SET balance = balance + ".$totalReward." WHERE userId = ".$ownerId);
//			//mysql_query("UPDATE shares_history SET counted = 1 WHERE username='".$username."'");
//			echo $username."-".$totalReward."<br/>";
//			$overallReward += $totalReward;
//			
//		}
//		
//	}
//}
echo "overall-".$overallReward."<br/>";
echo "pool-".(50-$overallReward)."<br/>"


?>