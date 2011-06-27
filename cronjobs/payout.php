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

$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");
	
/////////Pay users who have reached their threshold payout
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
$resultQ = mysql_query("SELECT userId, balance, IFNULL(paid, 0) as paid, IFNULL(sendAddress,'') as sendAddress FROM accountBalance WHERE threshold >= 1 AND balance > threshold");
while ($resultR = mysql_fetch_object($resultQ)) {
	$currentBalance = $resultR->balance;
	$paid = $resultR->paid;
	$paymentAddress = $resultR->sendAddress;
	$userId = $resultR->userId;
	if ($paymentAddress != '')
	{
		$isValidAddress = $bitcoinControll->validateaddress($paymentAddress);
		if($isValidAddress){
			//Subtract TX feee
			$currentBalance = $currentBalance - 0.01;
			//Send money//
			if($bitcoinControll->sendtoaddress($paymentAddress, $currentBalance)) {				
				//Reduce balance amount to zero
				mysql_query("UPDATE accountBalance SET balance = '0', paid = '$paid' WHERE userId = $userId");
			}
		}
	}
}
