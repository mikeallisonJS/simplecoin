<?php
/*
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

*/

//Check if the cookie is set, if so check if the cookie is valid
if(isSet($_COOKIE[$cookieName])){
	$cookieValid = false;
	$ip = $_SERVER['REMOTE_ADDR']; //Get Ip address for cookie validation
	$validateCookie	= new checkLogin();
	$cookieValid = $validateCookie->checkCookie(mysql_real_escape_string($_COOKIE[$cookieName]), $ip);
	$userId	= $validateCookie->returnUserId($_COOKIE[$cookieName]);		
	
	//ensure userId is integer to prevent sql injection attack
	if (!is_numeric($userId)) {
		$userId = 0;	
		exit;
	}
		
	//Get user information
	$userInfoQ = mysql_query("SELECT id, username, pin, pass, admin, share_count, stale_share_count, shares_this_round, hashrate, api_key, IFNULL(donate_percent, '0') as donate_percent, IFNULL(round_estimate, '0') as round_estimate FROM webUsers WHERE id = '$userId' LIMIT 0,1"); //
	$userInfo = mysql_fetch_object($userInfoQ);
	$authPin = $userInfo->pin;
	$hashedPass = $userInfo->pass;
	$isAdmin = $userInfo->admin;
	$lifetimeUserShares = $userInfo->share_count - $userInfo->stale_share_count;
	$lifetimeUserInvalidShares = $userInfo->stale_share_count;
	$totalUserShares = $userInfo->shares_this_round;
	$currentUserHashrate = $userInfo->hashrate;
	$userApiKey = $userInfo->api_key;
	$donatePercent = $userInfo->donate_percent;
	$userRoundEstimate = $userInfo->round_estimate;
			
	//Get current round share information, estimated total earnings
	//$currentSharesQ = mysql_query("SELECT username FROM pool_worker WHERE associatedUserId = '".$userId."'");
	$totalSharesQ = mysql_query("SELECT value FROM settings where setting='currentroundshares'");
	while ($totalOverallSharesR = mysql_fetch_array($totalSharesQ))
		$totalOverallShares = intval($totalOverallSharesR[0]);
				
			
	//Prevent divide by zero
	if($totalUserShares > 0 && $totalOverallShares > 0){
		$estimatedTotalEarnings = $totalUserShares/$totalOverallShares;
		$estimatedTotalEarnings *= 50; //The expected BTC to be givin out
		$estimatedTotalEarnings = round($estimatedTotalEarnings, 8);
	}else{
		$estimatedTotalEarnings = 0;
	}
				
				
	//Get Current balance				    
	$currentBalanceQ = mysql_query("SELECT balance, IFNULL(sendAddress,'') as sendAddress, threshold FROM accountBalance WHERE userId = '$userId' LIMIT 0,1");
	if ($currentBalanceObj = mysql_fetch_object($currentBalanceQ)) {
		$currentBalance = $currentBalanceObj->balance;
		//Get payment address that is associated wit this user
		$paymentAddress = $currentBalanceObj->sendAddress;		
		$payoutThreshold = $currentBalanceObj->threshold;	
	} else {
		$currentBalance = 0;
		$paymentAddress = "";
		$payoutThreshold = 0;
	}
}
?>