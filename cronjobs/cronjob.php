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
if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
	echo "cronjobs can only be run locally.";
	exit;
}

//Set page starter variables//
$includeDirectory = "/var/www/includes/";

//Include site functions
include($includeDirectory."requiredFunctions.php");

include($includeDirectory.'mtgox.php');

//Update MtGox last price, bypass if failed
try {
	$mtgox = new mtgox("", "");
	$ticker = $mtgox->ticker();
	if (intval($ticker['last']) > 0) $settings->setsetting('mtgoxlast', $ticker['last']);
} catch (Exception $e) { }

//Open a bitcoind connection
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

//Get current block number & difficulty
$currentBlockNumber = $bitcoinController->getblocknumber();
$difficulty = $bitcoinController->query("getdifficulty");

//Get site percentage
$sitePercent = 0;
$sitePercentQ = mysql_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = mysql_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;				

//Setup score variables
$feeVariance = .001;
$fee = 1;
if ($sitePercent > 0)
	$fee = $sitePercent / 100;
else
	$fee = (-$feeVariance)/(1-$feeVariance);
$difficultyRatio = 1.0/$difficulty;
$logRatio = log(1.0-$difficultyRatio+$difficultyRatio/$feeVariance);
$bonusCoins = 50;
$los = log(1/(exp($logRatio)-1));

//Is this block number in the database already
$inDatabaseQ = mysql_query("SELECT id FROM networkBlocks WHERE blockNumber = $currentBlockNumber LIMIT 0,1");
$inDatabase = mysql_num_rows($inDatabaseQ);

if(!$inDatabase){
	//Add this block into the `networkBlocks` log
	$currentTime = time();
	mysql_query("INSERT INTO networkBlocks (blockNumber, timestamp) VALUES ($currentBlockNumber, $currentTime)");
		
	//Don't delete shares until a new block is started
	//Go through every share and add it to the shares_history database		
	
	//Get last Id
	$lastId = 0;
	$lastShareId = 0;
	$lastShareQ = mysql_query("SELECT id FROM shares ORDER BY id DESC LIMIT 1");
	if ($lastShareR = mysql_fetch_object($lastShareQ)) {
		$lastShareId = $lastShareR->id; 
	}
	
	//Save winning share (if there is one)
	$winningShareQ = mysql_query("SELECT DISTINCT username FROM shares where upstream_result = 'Y'");
	while ($winningShareR = mysql_fetch_object($winningShareQ)) {		
		mysql_query("INSERT INTO winning_shares (blockNumber, username) VALUES ($currentBlockNumber,'$winningShareR->username')");
	}	
	
	//Select all shares	 
	$lastScore = 0;
	$shareInputQ = "";
	$getAllShares = mysql_query("SELECT id, username, our_result, time FROM `shares` ORDER BY `id` ASC");	
	while($share = mysql_fetch_object($getAllShares)) {
		if ($i == 0)
			$shareInputQ = "INSERT INTO shares_history (blockNumber, username, our_result, time, score, counted) VALUES ";
		$i++;
		if ($i > 1)
			$shareInputQ .= ",";		
		$score = $lastScore + $logRatio;
		$shareInputQ .="('$currentBlockNumber','$share->username','$share->our_result','$share->time',$score,'0')";
		$lastId = $share->id;
		$lastScore = $score;				
		if ($i > 20) {
			//Add to `shares_history`
			$shareHistoryQ = mysql_query($shareInputQ);

			//Move all old shares from `shares` and move them to `shares_history`
			if($shareHistoryQ){
				//Delete all from shares whoms "id" is less then $lastId to prevent new "hard-earned" shares to be deleted
				mysql_query("DELETE FROM shares WHERE id <= $lastId");
			}	
			$i = 0;
		}
	}
	//Add to `shares_history`
	if ($shareHistoryQ != "")
		$shareHistoryQ = mysql_query($shareInputQ);

	//Move all old shares from `shares` and move them to `shares_history`
	if($shareHistoryQ){
		//Delete all from shares whoms "id" is less then $lastId to prevent new "hard-earned" shares to be deleted
		mysql_query("DELETE FROM shares WHERE id <= $lastId");
	}
}		


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//try {
//Get list of transactions
$transactions = $bitcoinController->query("listtransactions", "*", "200");

//Go through all the transactions check if there is 50BTC inside
$numAccounts = count($transactions);

for($i = 0; $i < $numAccounts; $i++) {
	//Check for 50BTC inside only if they are in the generate category
	if($transactions[$i]["amount"] >= 50 && ($transactions[$i]["category"] == "generate" || $transactions[$i]["category"] == "immature")) {		
		//At this point we may or may not have found a block,
		//Check to see if this account addres is already added to `networkBlocks`
		$accountExistsQ = mysql_query("SELECT id FROM networkBlocks WHERE accountAddress = '".$transactions[$i]["txid"]."' ORDER BY blockNumber DESC LIMIT 0,1")or die(mysql_error());
		$accountExists = mysql_num_rows($accountExistsQ);

	    //Insert txid into latest network block
		if (!$accountExists) {									
			//Get last winning block			
			$lastSuccessfullBlockQ = mysql_query("SELECT n.id FROM networkBlocks n, winning_shares w where n.blockNumber = w.blockNumber ORDER BY w.id DESC LIMIT 1");
			$lastSuccessfullBlockR = mysql_fetch_object($lastSuccessfullBlockQ);
			$lastEmptyBlock = $lastSuccessfullBlockR->id;			

			$insertBlockSuccess = mysql_query("UPDATE networkBlocks SET accountAddress = '".$transactions[$i]["txid"]."' WHERE id = $lastEmptyBlock")or die(mysql_error());
					
			//Update site balance for tx fee
			$poolReward = $transactions[$i]["amount"] - $bonusCoins;
			mysql_query("UPDATE settings SET value = value + $poolReward WHERE setting='sitebalance'");
		}			
	}
}

//Go through networkblocks and update confirms as needed
$winningAccountQ = mysql_query("SELECT id, accountAddress FROM networkBlocks WHERE accountAddress <> '' AND confirms < 120");
while ($winningAccountR = mysql_fetch_object($winningAccountQ)) {
	$txInfo = $bitcoinController->gettransaction($winningAccountR->accountAddress);
	if (count($txInfo["confirmations"]) > 0) {
		mysql_query("UPDATE networkBlocks SET confirms = ".$txInfo["confirmations"]." WHERE id = $winningAccountR->id");
	}
}

if ($settings->getsetting("siterewardtype") == 0) {
	//Cheat-proof scoring:
	$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_history s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted='0' AND n.confirms > 119 ORDER BY s.blockNumber DESC LIMIT 1");
	while ($blocks = mysql_fetch_object($blocksQ)) {
		$block = $blocks->blockNumber;
	
		$totalscoreQ = mysql_query("SELECT (sum(exp(s1.score-s2.score))+exp($los-s2.score)) AS score FROM shares_history s1, shares_history s2 WHERE s2.id = s1.id - 1 AND s1.counted = '0' AND s1.blockNumber <= $block");
		$totalscoreR = mysql_fetch_object($totalscoreQ);
		$totalscore = $totalscoreR->score; 	
		
		$userListCountQ = mysql_query("SELECT DISTINCT s1.username, count(s1.id) AS id, sum(exp(s1.score-s2.score)) AS score FROM shares_history s1, shares_history s2 WHERE s2.id = s1.id -1 AND s1.counted = '0' AND s1.blockNumber <= $block GROUP BY username");
		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
			$username = $userListCountR->username;
			$uncountedShares = $userListCountR->id;
			$score = $userListCountR->score;
	
			//get owner userId and donation percent
			$ownerIdQ = mysql_query("SELECT p.associatedUserId, u.donate_percent FROM pool_worker p, webUsers u WHERE u.id = p.associatedUserId AND p.username = '$username' LIMIT 0,1");
			$ownerIdObj = mysql_fetch_object($ownerIdQ);
			$ownerId = $ownerIdObj->associatedUserId;
			$donatePercent = $ownerIdObj->donate_percent;
			
			$predonateAmount = (1-$fee)*$bonusCoins*$score/$totalscore;
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
				//Update balance
				$updateOk = mysql_query("UPDATE accountBalance SET balance = balance + $totalReward WHERE userId = $ownerId");				
				if (!$updateOk)
					mysql_query("INSERT INTO accountBalance (userId, balance) VALUES ($ownerId,'$totalReward')");									
			}	
			mysql_query("UPDATE shares_history SET counted = '1' WHERE username='$username' AND blockNumber <= $block");			
		}	
		$poolReward = $bonusCoins -$overallReward;
		mysql_query("UPDATE settings SET value = value + $poolReward WHERE setting='sitebalance'");
	}
} else {
	//Proportional Scoring
	//Go through all of `shares_history` that are uncounted shares; Check if there are enough confirmed blocks to award user their BTC
	//Get uncounted shares
	$overallReward = 0;
	$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_history s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted='0' AND n.confirms > 119 ORDER BY s.blockNumber ASC");
	while ($blocks = mysql_fetch_object($blocksQ)) {
		$block = $blocks->blockNumber;
		$totalRoundSharesQ = mysql_query("SELECT count(id) as id FROM shares_history WHERE counted = '0' AND blockNumber <= $block");
		if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
			$totalRoundShares = $totalRoundSharesR->id;
			$userListCountQ = mysql_query("SELECT DISTINCT username, count(id) as id FROM shares_history WHERE counted = '0' AND blockNumber <= $block GROUP BY username");
			while ($userListCountR = mysql_fetch_object($userListCountQ)) {
				$username = $userListCountR->username;
				$uncountedShares = $userListCountR->id;
				$shareRatio = $uncountedShares/$totalRoundShares;
				$predonateAmount = (1-$fee)*(50*$shareRatio);				
							
				//get owner userId and donation percent
				$ownerIdQ = mysql_query("SELECT p.associatedUserId, u.donate_percent FROM pool_worker p, webUsers u WHERE u.id = p.associatedUserId AND p.username = '$username' LIMIT 0,1");
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
					$updateOk = mysql_query("UPDATE accountBalance SET balance = balance + $totalReward WHERE userId = $ownerId");				
					if (!$updateOk)
						mysql_query("INSERT INTO accountBalance (userId, balance) VALUES ($ownerId,'$totalReward')");
				}
				mysql_query("UPDATE shares_history SET counted = '1' WHERE username='$username' AND blockNumber <= $block");							
			}
		}
		$poolReward = $bonusCoins -$overallReward;
		mysql_query("UPDATE settings SET value = value + $poolReward WHERE setting='sitebalance'");
	}
}

?>