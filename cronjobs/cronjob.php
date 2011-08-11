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

lock("shares");
try {
	//Include Block class
	include($includeDirectory."block.php");
	$block = new Block();
	
	//Get current block number
	$currentBlockNumber = $bitcoinController->getblocknumber();
	$lastBlockNumber = $currentBlockNumber - 1;
	
	//Get latest block in database
	$latestDbBlock = $block->getLatestDbBlockNumber();
	
	//Do block work if new block 
	if ($latestDbBlock < $lastBlockNumber) {
		//Update past shares to last block number
		//$block->UpdateSharesBlockNumber($lastBlockNumber);
		
		//Insert last block number into networkBlocks
		include($includeDirectory."stats.php");
		$stats = new Stats();
		$lastwinningid = $stats->lastWinningShareId();
		$block->InsertNetworkBlocks($lastBlockNumber, $lastwinningid);
		
		//Find new generations
		$block->FindNewGenerations($bitcoinController);
		
		//Update confirms on unrewarded winning blocks
		$block->UpdateConfirms($bitcoinController);		
	}
	
	//Check for unrewarded blocks
	if ($block->CheckUnrewardedBlocks()) {			
		lock("money");	
		try {
			//Include Reward class
			include($includeDirectory.'reward.php');
			$reward = new Reward();
	
			//Get Difficulty
			$difficulty = $bitcoinDifficulty;
			if(!$difficulty)
			{
			   echo "no difficulty! exiting\n";
			   exit;
			}
			
			//Reward by selected type;
			if ($settings->getsetting("siterewardtype") == 0) {
				//LastNShares
				$reward->LastNShares($difficulty, $bonusCoins);
			//} else if ($settings->getsetting("siterewardtype") == 2) {
				////MaxPPS
				//MaxPPS();
			} else {
				//Proportional Scoring
				$reward->ProportionalScoring($bonusCoins);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		unlock("money");
	}
} catch (Exception $ex) {
	echo $e->getMessage();
}
unlock("shares");

?>