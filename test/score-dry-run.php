<?php
$includeDirectory = "/var/www/includes/";

//Include hashing functions
include($includeDirectory."requiredFunctions.php");
connectToDb();

//try {
//Open a bitcoind connection
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

$overallReward = 0;
$poolEstimate = 0;
//Get difficulty
$difficulty = $bitcoinController->query("getdifficulty");
$sitePercent = 0;
$sitePercentQ = mysql_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = mysql_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;				
$f = $sitePercent / 100;
$c = .001;
$p = 1.0/$difficulty;
$r = log(1.0-$p+$p/$c);
$B = 50;
$los = log(1/(exp($r)-1));


//Cheat-proof scoring:
$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_history s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted=0 ORDER BY s.blockNumber DESC LIMIT 1");
while ($blocks = mysql_fetch_object($blocksQ)) {
	$block = $blocks->blockNumber;
	


		$totalscoreQ = mysql_query("SELECT (sum(exp(s1.score-s2.score))+exp(".$los."-s2.score)) AS score FROM shares_history s1, shares_history s2 WHERE s2.id = s1.id - 1 AND s1.counted = 0 AND s1.blockNumber <= ".$block);
		$totalscoreR = mysql_fetch_object($totalscoreQ);
		$totalscore = $totalscoreR->score; 
		
		
		$userListCountQ = mysql_query("SELECT DISTINCT s1.username, count(s1.id) AS id, sum(exp(s1.score-s2.score)) AS score FROM shares_history s1, shares_history s2 WHERE s2.id = s1.id -1 AND s1.counted = 0 AND s1.blockNumber <= ".$block." GROUP BY username");
		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
			$username = $userListCountR->username;
			$uncountedShares = $userListCountR->id;
			$score = $userListCountR->score;

			//get owner userId and donation percent
			$ownerIdQ = mysql_query("SELECT p.associatedUserId, u.donate_percent FROM pool_worker p, webUsers u WHERE u.id = p.associatedUserId AND p.username = '".$username."' LIMIT 0,1");
			$ownerIdObj = mysql_fetch_object($ownerIdQ);
			$ownerId = $ownerIdObj->associatedUserId;
			$donatePercent = $ownerIdObj->donate_percent;
			
			$predonateAmount = (1-$f)*$B*$score/$totalscore;
			$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");	
	
			if ($predonateAmount > 0.00000001)
			{
				//Take out donation			
				$totalReward = $predonateAmount - ($predonateAmount * ($donatePercent/100));
								
				//Round Down to 8 digits
				$totalReward = $totalReward * 100000000;
				$totalReward = floor($totalReward);
				$totalReward = $totalReward/100000000;
				
				//Get total site reward
				$donateAmount = $predonateAmount - $totalReward;
				
				$overallReward += $totalReward;
				echo $username."-".$totalReward."-".$uncountedShares."<br/>";
				//Update balance
				//mysql_query("UPDATE accountBalance SET balance = balance + ".$totalReward." WHERE userId = ".$ownerId);
				//mysql_query("UPDATE shares_history SET counted = 1 WHERE username='".$username."' AND blockNumber <= ".$block);				
			}				
		}	
		$poolReward = $B -$overallReward;
		//mysql_query("UPDATE settings SET value = value +".$poolReward." WHERE setting='sitebalance'");
//	}
}
echo "overall-".$overallReward."<br/>";
echo "pool est-".$poolEstimate."<br/>";
echo "pool-".(50-$overallReward)."<br/>";
echo "sitepercent-".$sitePercent."<br/>";
echo "roundshares-".$totalRoundShares."<br/>";


?>