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
   

//Set page starter variables//
$includeDirectory = "/var/www/includes/";

//Include site functions
include($includeDirectory."requiredFunctions.php");

//Check that script is run locally
ScriptIsRunLocally();

//Get Difficulty
$difficulty = $bitcoinController->query("getdifficulty");
if(!$difficulty)
{
   echo "no difficulty! exiting\n";
   exit;
}
echo "difficulty is $difficulty \n";

//Get site percentage
$sitePercent = 0;
if (is_numeric($settings->getsetting("sitepercent")))
	$sitePercent = $settings->getsetting("sitepercent");
	
//Get current block number
$currentBlockNumber = $bitcoinController->getblocknumber();
$lastBlockNumber = $currentBlockNumber - 1;

if ($lastBlockNumber > 0) {
	//Set blocknumber for existing shares
	UpdateSharesBlockNumber($lastBlockNumber);
	
	//Set Score for new shares
	UpdateShareScores($lastBlockNumber, $difficulty, $sitePercent);
	
	//Update networkBlocks & winning_shares if needed
	InsertNetworkBlocks($lastBlockNumber);
}

//Find new generated blocks
FindNewGenerations($bitcoinController, $bonusCoins);

//Go through networkblocks and update confirms as needed
UpdateConfirms($bitcoinController);



//-----------------------------------------------------------------------------------------------------

function UpdateSharesBlockNumber($lastBlockNumber) {
	try {			
		mysql_query("UPDATE shares SET blockNumber = $lastBlockNumber WHERE blockNumber IS NULL");
		
	
	} catch (Exception $e) { 
		echo("Exception: " . $e->getMessage() . "\n");
	}
}

function UpdateShareScores($lastBlockNumber, $difficulty, $sitePercent) {
		//Setup score variables
		$feeVariance = .001;
		$fee = 1;
		if ($sitePercent > 0)
			$fee = $sitePercent / 100;
		else
			$fee = (-$feeVariance)/(1-$feeVariance);
		$difficultyRatio = 1.0/$difficulty;
		$logRatio = log(1.0-$difficultyRatio+$difficultyRatio/$feeVariance);
		$los = log(1/(exp($logRatio)-1));	
		//Score Round
		mysql_query("SET @lastscore = 0");
		mysql_query("UPDATE shares SET score = (SELECT @lastscore := @lastscore + $logRatio) WHERE blockNumber = $lastBlockNumber ORDER BY id ASC");
	
}

function InsertNetworkBlocks($lastBlockNumber) {
	//Check to see if last block number exists in the db.
	$inDatabaseQ = mysql_query("SELECT id FROM networkBlocks WHERE blockNumber = $lastBlockNumber LIMIT 0,1");
	$inDatabase = mysql_num_rows($inDatabaseQ);
	if(!$inDatabase) {
		//If not, insert it.
		$currentTime = time();
		mysql_query("INSERT INTO networkBlocks (blockNumber, timestamp) VALUES ($lastBlockNumber, $currentTime)");
		
		//Save winning share (if there is one)
		$winningShareQ = mysql_query("SELECT DISTINCT username FROM shares where upstream_result = 'Y' and blockNumber = $lastBlockNumber");
		while ($winningShareR = mysql_fetch_object($winningShareQ)) {		
			mysql_query("INSERT INTO winning_shares (blockNumber, username) VALUES ($lastBlockNumber,'$winningShareR->username')");
		}	
	}
} 

function UpdateConfirms($bitcoinController) {	
	$winningAccountQ = mysql_query("SELECT id, accountAddress FROM networkBlocks WHERE accountAddress <> '' AND confirms < 120");
	while ($winningAccountR = mysql_fetch_object($winningAccountQ)) {
		$txInfo = $bitcoinController->query("gettransaction", $winningAccountR->accountAddress);
		if (count($txInfo["confirmations"]) > 0) {
			mysql_query("UPDATE networkBlocks SET confirms = ".$txInfo["confirmations"]." WHERE id = $winningAccountR->id");
		}
	}
}

function FindNewGenerations($bitcoinController, $bonusCoins) {
	//Get list of last 200 transactions
	$transactions = $bitcoinController->query("listtransactions", "*", "200");
	
	//Go through all the transactions check if there is 50BTC inside
	$numAccounts = count($transactions);
	
	for($i = 0; $i < $numAccounts; $i++) {
		//Check for 50BTC inside only if they are in the generate category
		if($transactions[$i]["amount"] >= $bonusCoins && ($transactions[$i]["category"] == "generate" || $transactions[$i]["category"] == "immature")) {		
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
}
?>