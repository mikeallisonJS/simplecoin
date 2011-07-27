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
$difficulty = $bitcoinDifficulty;
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

RewardShares($difficulty, $sitePercent, $bonusCoins);

//-----------------------------------------------------------------------------------------------------

function RewardShares($difficulty, $sitePercent, $bonusCoins) {
	global $settings;
	lock("money");
	try {
		if ($settings->getsetting("siterewardtype") == 0) {
			//Cheat-proof scoring
			CheatProof($difficulty, $sitePercent, $bonusCoins);
		//} else if ($settings->getsetting("siterewardtype") == 2) {
			////MaxPPS
			//MaxPPS();
		} else {
			//Proportional Scoring
			ProportionalScoring($sitePercent, $bonusCoins);
		}
	} catch (Exception $e) {
		echo $e;
	}
	unlock("money");
}

function CheatProof($difficulty, $sitePercent, $bonusCoins) {	
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
	$overallReward = 0;
	
	$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted IS NULL AND n.confirms > 119 ORDER BY s.blockNumber DESC LIMIT 1");
	while ($blocks = mysql_fetch_object($blocksQ)) {
		$block = $blocks->blockNumber;
		
		//Get Total Score
		$totalscoreQ = mysql_query("SELECT (sum(exp(s1.score-s2.score))+exp($los-s2.score)) AS score FROM shares s1, shares s2 WHERE s2.id = s1.id - 1 AND s1.counted IS NULL AND s1.blockNumber <= $block");
		$totalscoreR = mysql_fetch_object($totalscoreQ);
		$totalscore = $totalscoreR->score; 	
		
		$userListCountQ = mysql_query("SELECT DISTINCT s1.username, count(s1.id) AS id, sum(exp(s1.score-s2.score)) AS score FROM shares s1, shares s2 WHERE s2.id = s1.id -1 AND s1.counted IS NULL AND s1.blockNumber <= $block GROUP BY username");
		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
			mysql_query("BEGIN");
			try {
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
				mysql_query("UPDATE shares SET counted = '1' WHERE username='$username' AND blockNumber <= $block");
				mysql_query("COMMIT");
			} catch (Exception $e) {
				echo("Exception: " . $e->getMessage() . "\n");
				mysql_query("ROLLBACK");
			}						
		}
		$poolReward = $bonusCoins - $overallReward;
		mysql_query("UPDATE settings SET value = value + $poolReward WHERE setting='sitebalance'");	
	}	
}

function ProportionalScoring($sitePercent, $bonusCoins) {	
	//Go through all of shares that are uncounted shares; Check if there are enough confirmed blocks to award user their BTC
	//Get uncounted shares
	$overallReward = 0;
	$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted IS NULL AND n.confirms > 119 ORDER BY s.blockNumber ASC");
	while ($blocks = mysql_fetch_object($blocksQ)) {
		$block = $blocks->blockNumber;
		$totalRoundSharesQ = mysql_query("SELECT count(id) as id FROM shares WHERE counted IS NULL AND blockNumber <= $block");
		if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
			$totalRoundShares = $totalRoundSharesR->id;
			$userListCountQ = mysql_query("SELECT DISTINCT username, count(id) as id FROM shares WHERE counted IS NULL AND blockNumber <= $block GROUP BY username");
			while ($userListCountR = mysql_fetch_object($userListCountQ)) {
				mysql_query("BEGIN");
				try {
					$username = $userListCountR->username;
					$uncountedShares = $userListCountR->id;
					$shareRatio = $uncountedShares/$totalRoundShares;
					$predonateAmount = (1-$fee)*($bonusCoins*$shareRatio);				
								
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
					mysql_query("UPDATE shares SET counted = '1' WHERE username='$username' AND blockNumber <= $block");
					mysql_query("COMMIT");
				} catch (Exception $e) {
					echo("Exception: " . $e->getMessage() . "\n");
					mysql_query("ROLLBACK");
				}								
			}
		}
		$poolReward = $bonusCoins -$overallReward;
		mysql_query("UPDATE settings SET value = value + $poolReward WHERE setting='sitebalance'");
	}
}

//Currently not supported
function MaxPPS($difficulty, $bonusCoins) {
	////Setup MaxPPS variables
	$shareValue = ((1 / $difficulty) * $bonusCoins);
	echo "shareValue is $shareValue \n";
	//Go through all of `shares_history` that are uncounted shares; Check if there are enough confirmed blocks to award user their BTC
	//Get uncounted shares
	$overallReward = 0;

	$blocksQ = mysql_query("SELECT nb.blockNumber FROM networkBlocks nb WHERE nb.confirms > 119 AND (SELECT sh.id FROM shares_history sh WHERE sh.blockNumber = nb.blockNumber AND counted = '0' LIMIT 1) ORDER BY nb.blockNumber ASC");
	while ($blocks = mysql_fetch_object($blocksQ)) {
		$block = $blocks->blockNumber;
      //we now have another 50 BTC!
      if($block > 135006)
      {
         $settings->setsetting('poolppsbtc',$settings->getsetting('poolppsbtc') + $bonusCoins);
      }
		$totalRoundSharesQ = mysql_query("SELECT count(id) as id FROM shares_history WHERE counted = '0' AND our_result = 'Y' AND blockNumber <= ".$block);
		if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
			$totalRoundShares = $totalRoundSharesR->id;
            $sql = "SELECT DISTINCT sh.userId, count(sh.id) as id, wu.donate_percent
                    FROM shares_history sh
                    JOIN webUsers wu ON sh.userId = wu.id
                    WHERE sh.counted = '0' AND our_result = 'Y' AND sh.blockNumber <= $block GROUP BY sh.userId";
			$userListCountQ = mysql_query($sql) or die(mysql_error());
			try {
				mysql_query("BEGIN");
				
				while ($userListCountR = mysql_fetch_object($userListCountQ)) {
					$userId = $userListCountR->userId;
					$uncountedShares = $userListCountR->id;
					$donatePercent = $userListCountR->donate_percent;

					$shareRatio = $uncountedShares/$totalRoundShares;
					$predonateAmount = $bonusCoins * $shareRatio;
					$totalReward = (1-($sitePercent/100)) * $predonateAmount;

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
						if($block > 135006)
                  {
                     echo("SMPPS: ($userId, 0, $block, $uncountedShares, $totalRoundShares, 0, 0)\n");
                     
                     // Log what happened
   						$sql = "INSERT INTO accountHistory (userId, balanceDelta, blockNumber, userShares, totalShares, sitePercent, donatePercent) VALUES " .
   								"($userId, 0, $block, $uncountedShares, $totalRoundShares, 0, 0)";
   						if (!mysql_query($sql)) {
   							throw new Exception(mysql_error());
   						}
                  }
                  else
                  {
   						echo("PAID: ($userId, $totalReward, $block, $uncountedShares, $totalRoundShares, $sitePercent, $donatePercent)\n");

   						// Log what happened
   						$sql = "INSERT INTO accountHistory (userId, balanceDelta, blockNumber, userShares, totalShares, sitePercent, donatePercent) VALUES " .
   								"($userId, $totalReward, $block, $uncountedShares, $totalRoundShares, $sitePercent, $donatePercent)";
   						if (!mysql_query($sql)) {
   							throw new Exception(mysql_error());
   						}

   						//Update balance
   						$updateOk = mysql_query("UPDATE accountBalance SET balance = balance + ".$totalReward." WHERE userId = ".$userId);
   						if (!$updateOk) {
   							$result = mysql_query("INSERT INTO accountBalance (userId, balance) VALUES (".$userId.",'".$totalReward."')");
   							if (!$result) {
   								 throw new Exception(mysql_error());
   							}
   						}
                  }
							
					}
					
					$result = mysql_query("UPDATE shares_history SET counted = '1' WHERE userId='".$userId."' AND blockNumber <= ".$block." AND counted = '0'");
					if (!$result) {
						 throw new Exception(mysql_error());
					}					
				}
				
				mysql_query("COMMIT");
			} catch (Exception $e) {
				echo("Exception: " . $e->getMessage() . "\n");
				mysql_query("ROLLBACK");
			}
		}
		$poolReward = $B -$overallReward;
      if($block > 135006)
      {
      }
      else
      {
         mysql_query("UPDATE settings SET value = value +".$poolReward." WHERE setting='sitebalance'");
      }
		mysql_query("BEGIN");
		mysql_query("INSERT INTO shares_count (`username`, `userId`, `countedShares`, `staleShares`) " .
		"SELECT username, userId, sum(count), sum(stalecount) from " .
		"(SELECT  s.username as username, s.userId as userId, count(id) as count, 0 as stalecount FROM shares_history s " .
		"WHERE s.counted = 1 AND (s.upstream_result IS NULL OR s.upstream_result = 'Y') group by s.username UNION " .
		"SELECT  s.username as username, s.userId as userId, 0 as count, count(id) as stalecount " .
		"FROM shares_history s " .
		"WHERE blockNumber <= $block AND s.counted = 1 AND s.our_result = 'N' group by s.username " .
		") test " .
		"group by username") or die(mysql_error());
		mysql_query("DELETE FROM shares_history WHERE blockNumber <= $block AND counted = '1' AND (upstream_result != 'Y' OR upstream_result is null)") or die(mysql_error());
		mysql_query("COMMIT");
	}
}
?>