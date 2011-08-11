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


$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");

//Check that script is run locally
ScriptIsRunLocally();

$txfee = 0;
if ($settings->getsetting("sitetxfee") > 0)
	$txfee = $settings->getsetting("sitetxfee");
	
/////////Pay users who have reached their threshold payout


//if ($settings->getsetting("siterewardtype") != 2) {
//	$poolPPSbtc = $settings->getsetting('poolppsbtc');
//	$poolPPSbtcPaid = $settings->getsetting('poolppsbtcpaid');
//	$availableFunds = $poolPPSbtc - $poolPPSbtcPaid ;
//	$balance = $bitcoinController->query("getbalance");
//	if($availableFunds > $balance)
//	{
//	   die("Available funds $availableFunds, balance $balance");
//	}
//	
//	$sitewallet = mysql_query("SELECT sum(balance) FROM `accountBalance` WHERE `balance` > 0")or sqlerr(__FILE__, __LINE__);
//	$sitewalletq = mysql_fetch_row($sitewallet);
//	$usersbalance = $sitewalletq[0];
//	if(($usersbalance + $availableFunds) > $balance)
//	{
//	   die("Available funds $availableFunds, balance $balance, users balance $usersbalance");
//	}
//	$idealPay = 0;
//	$resultA = mysql_query("select (sum(pps_credit) + sum(donate_amount)) - (sum(paid_credit) + sum(paid_donate)) as idealPay FROM smPPS");
//	if($resultAobj = mysql_fetch_object($resultA))
//	{
//	   $idealPay = $resultAobj->idealPay;
//	}
//	else
//	{
//	   die("no ideal pay!\n");
//	}
//	$payRatio = 0;
//	if($idealPay <= $availableFunds)
//	{
//	   $payRatio = 1;
//	}
//	else
//	{
//	   $payRatio = $availableFunds / $idealPay;
//	}
//} else if ($settings->getsetting("siterewardtype") == 2) {
////MaxPPS
//$totalJustPaid = 0;
//$totalDonated = 0;
//$resultPay = mysql_query("select userId, sum(paid_credit) + sum(paid_donate) as paid, (sum(pps_credit)) - (sum(paid_credit)) as owedUser,(sum(donate_amount)) - ( sum(paid_donate)) as owedDonate, ((sum(pps_credit) + sum(donate_amount)) - (sum(paid_credit) + sum(paid_donate))) * $payRatio as payNow,(sum(pps_credit)  - sum(paid_credit)) * $payRatio as payUser, (sum(donate_amount) -  sum(paid_donate)) * $payRatio as payDonate, sum(shares) as shares FROM smPPS GROUP BY userId");
//
//while($resultPayobj = mysql_fetch_object($resultPay))
//{
//	$userId = $resultPayobj->userId;
//   $payUser = $resultPayobj->payNow;
//   $payDonate = $resultPayobj->payDonate;
//   $payUser = $payUser - $payDonate;
//   if($payUser > 0 || $payDonate > 0)
//   {
//	   $shares = $resultPayobj->shares;
//	   echo json_encode($resultPayobj);
//      try
//      {
//   	   mysql_query("BEGIN");
//   		// Log what happened
//   	   $sql = "INSERT INTO accountHistory (userId, balanceDelta, blockNumber, userShares) VALUES " .
//   			 "($userId, $payUser, 9999999, $shares)";
//   	   if (!mysql_query($sql)) {
//   		  throw new Exception(mysql_error());
//   	   }
//   	   //Update balance
//   	   $updateOk = mysql_query("UPDATE accountBalance SET balance = balance + ".$payUser." WHERE userId = ".$userId);
//   	   if (!$updateOk) {
//   		  $result = mysql_query("INSERT INTO accountBalance (userId, balance) VALUES (".$userId.",'".$payUser."')");
//   		  if (!$result) {
//   			  throw new Exception(mysql_error());
//   		  }
//   	   }
//   	   $result = mysql_query("INSERT INTO smPPS (`userId`,`date`,`paid_credit`, `paid_donate`) VALUES ($userId, CURDATE(),$payUser, $payDonate) ON DUPLICATE KEY UPDATE paid_credit = paid_credit + $payUser, paid_donate = paid_donate + $payDonate");
//         if(!$result)
//         {
//            throw new Exception(mysql_error());
//         }
//   	   $settings->setsetting('poolppsbtcpaid', $settings->getsetting('poolppsbtcpaid') + $payUser + $payDonate);
//         $settings->setsetting('sitebalance', $settings->getsetting('sitebalance') + $payDonate);
//   	   mysql_query("COMMIT");
//       } catch (Exception $e) {
//				echo("Exception: " . $e->getMessage() . "\n");
//				mysql_query("ROLLBACK");
//		 }
//   }
//}
//}

$sitepercent = $settings->getsetting("sitepercent");
$resultQ = mysql_query("SELECT b.userId, b.balance, IFNULL(b.paid, 0) as paid, IFNULL(b.sendAddress,'') as sendAddress, u.email, u.donate_percent FROM accountBalance b, webUsers u WHERE b.userId = u.id AND b.threshold >= 1 AND b.balance >= threshold");
lock("money");
try {
	while ($resultR = mysql_fetch_object($resultQ)) {
		$currentBalance = $resultR->balance;
		$paid = $resultR->paid;
		$paymentAddress = $resultR->sendAddress;
		$userId = $resultR->userId;
		if ($paymentAddress != '')
		{
			$isValidAddress = $bitcoinController->validateaddress($paymentAddress);
			if($isValidAddress){
				//Subtract TX fee, donation and site fees				
				$currentBalance = ($currentBalance*(1-$sitepercent/100)*(1-$donatepercent/100)) - $txfee;
				$paid = $paid + $currentBalance;
				//Send money//
				if($bitcoinController->sendtoaddress($paymentAddress, $currentBalance)) {				
					//Reduce balance amount to zero
					mysql_query("UPDATE accountBalance SET balance = '0', paid = '$paid' WHERE userId = $userId");
					//mysql_query("INSERT INTO payoutHistory (userId, address, amount) VALUES ('".$resultR->userId."', '".$paymentAddress."', '".$currentBalance."')");
					mail($resultR->email, "Simplecoin.us Automatic Payout Notification", "Hello,\n\nYour balance of ".$currentBalance." BTC has exceeded your automatic payment threshold and has been sent to your payment address ".$paymentAddress.".", "From: Simplecoin.us Notifications <server@simplecoin.us>");
				}
			}
		}
	}
} catch (Exception $ex) {
	echo $ex-getMessage();
}
unlock("money");
