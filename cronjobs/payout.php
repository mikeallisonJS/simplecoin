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

$resultQ = mysql_query("SELECT userId, balance, IFNULL(paid, 0) as paid, IFNULL(sendAddress,'') as sendAddress FROM accountBalance WHERE threshold >= 1 AND balance >= threshold");
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
				//Subtract TX feee
				$currentBalance = $currentBalance - $txfee;
				//Send money//
				if($bitcoinController->sendtoaddress($paymentAddress, $currentBalance)) {				
					//Reduce balance amount to zero
					mysql_query("UPDATE accountBalance SET balance = '0', paid = '$paid' WHERE userId = $userId");
					//mysql_query("INSERT INTO payoutHistory (userId, address, amount) VALUES ('".$resultR->userId."', '".$paymentAddress."', '".$currentBalance."')");
					//mail($resultR->email, "Simplecoin.us Automatic Payout Notification", "Hello,\n\nYour balance of ".$currentBalance." BTC has exceeded your automatic payment threshold and has been sent to your payment address ".$paymentAddress.".", "From: Simplecoin.us Notifications <server@simplecoin.us>");
				}
			}
		}
	}
} catch (Exception $ex) {
	echo $ex-getMessage();
}
unlock("money");
