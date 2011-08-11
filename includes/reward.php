<?php
class Reward {
	
	function LastNShares($difficulty, $bonusCoins) {
		global $settings;
		$overallreward = 0;
		$blocksQ = mysql_query("SELECT share_id from winning_shares WHERE rewarded = 'N' AND confirms > 119 ORDER BY blockNumber ASC");
		while ($blocksR = mysql_fetch_object($blocksQ)) {
			echo "Last N shares scoring\n";
			echo "difficulty is $difficulty \n";		
			$shareId = $blocksR->share_id;
			$shareLimit = round($difficulty/2); 
			
			//Make sure there are at least $shareLimit shares
			$limitQ = mysql_query("SELECT count(id) FROM shares WHERE id <= $shareId AND our_result='Y'");
			if ($limitR = mysql_fetch_array($limitQ)) {
				if ($limitR[0] < $shareLimit) $shareLimit = round($limitR[0]);
			}
			echo "share limit is $shareLimit\n";
			
			mysql_query("BEGIN");		
			$sharesQ = mysql_query("SELECT u.id, count(s.id) as shares FROM webUsers u, pool_worker p, (SELECT id, username FROM shares WHERE id <= $shareId AND our_result='Y' ORDER BY id DESC LIMIT $shareLimit) s WHERE u.id = p.associatedUserId AND p.username = s.username  GROUP BY u.id");
			try {
				while ($sharesR = mysql_fetch_object($sharesQ)) {
					$totalReward = $sharesR->shares/$shareLimit*$bonusCoins;
					$userid = $sharesR->id;
					$totalReward = $totalReward * 100000000;
					$totalReward = floor($totalReward);
					$totalReward = $totalReward/100000000;
					$overallreward += $totalReward;
					echo "$userid - $totalReward - $sharesR->shares\n";
					if ($totalReward > 0.00000001)
						echo "UPDATE accountBalance SET balance = balance + $totalReward WHERE userId = $userid\n";
						mysql_query("UPDATE accountBalance SET balance = balance + $totalReward WHERE userId = $userid");
				}
				echo "UPDATE winning_shares SET rewarded = 'Y' WHERE share_id = $shareId\n";
				mysql_query("UPDATE winning_shares SET rewarded = 'Y' WHERE share_id = $shareId");
				mysql_query("COMMIT");
				echo "Total Reward: $overallreward";
			} catch (Exception $e) {
				echo("Exception: " . $e->getMessage() . "\n");
				mysql_query("ROLLBACK");
			}							
			
		}
	}
	
	function ProportionalScoring($bonusCoins) {	
		//Go through all of shares that are uncounted shares; Check if there are enough confirmed blocks to award user their BTC
		$overallReward = 0;
		$lastrewarded = 0;

		//Get last rewarded share id
		$rewardedblocksQ = mysql_query("SELECT share_id from winning_shares WHERE rewarded = 'Y' ORDER BY blockNumber DESC LIMIT 0,1") or die(mysql_error());
		if ($rewardedblocksR = mysql_fetch_row($rewardedblocksQ)) {
			$lastrewarded = $rewardedblocksR[0];
		}

		//Get unrewarded blocks
		$blocksQ = mysql_query("SELECT share_id from winning_shares WHERE rewarded = 'N' AND confirms > 119 ORDER BY blockNumber ASC");		
		while ($blocksR = mysql_fetch_object($blocksQ)) {
			echo "Proportional Scoring \n";
			$shareid = $blocksR->share_id;
			//Get unrewarded shares
			$totalRoundSharesQ = mysql_query("SELECT count(id) as id FROM shares WHERE id <= $shareid AND id > $lastrewarded AND our_result='Y' ");
			if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
				$totalRoundShares = $totalRoundSharesR->id;
				$userListCountQ = mysql_query("SELECT DISTINCT username, count(id) as id FROM shares WHERE id <= $shareid  AND id > $lastrewarded AND our_result='Y' GROUP BY username");
				while ($userListCountR = mysql_fetch_object($userListCountQ)) {
					//mysql_query("BEGIN");
					try {
						$username = $userListCountR->username;
						$uncountedShares = $userListCountR->id;
						$shareRatio = $uncountedShares/$totalRoundShares;
						$predonateAmount = $bonusCoins*$shareRatio;				
									
						//get owner userId and donation percent
						$ownerIdQ = mysql_query("SELECT p.associatedUserId, u.donate_percent FROM pool_worker p, webUsers u WHERE u.id = p.associatedUserId AND p.username = '$username' LIMIT 0,1");
						$ownerIdObj = mysql_fetch_object($ownerIdQ);
						$ownerId = $ownerIdObj->associatedUserId;						
						
						//Force decimal value (remove e values)
						$totalReward = rtrim(sprintf("%f",$predonateAmount ),"0");							
						
						if ($totalReward > 0.00000001)	{											
							//Round Down to 8 digits
							$totalReward = $totalReward * 100000000;
							$totalReward = floor($totalReward);
							$totalReward = $totalReward/100000000;
																							
							//Update balance
							//$updateOk = mysql_query("UPDATE accountBalance SET balance = balance + $totalReward WHERE userId = $ownerId");				
						}						
						//mysql_query("UPDATE winning_shares SET rewarded = 'Y' WHERE share_id = $shareId");
						//mysql_query("COMMIT");
					} catch (Exception $e) {
						echo("Exception: " . $e->getMessage() . "\n");
						//mysql_query("ROLLBACK");
					}								
				}
			}
		}
	}
}
?>