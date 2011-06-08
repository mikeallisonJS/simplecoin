<?php
/*
Copyright (C) Copyright (C) 41a240b48fb7c10c68ae4820ac54c0f32a214056bfcfe1c2e7ab4d3fb53187a0 Name Year (sha256)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
Website Reference:http://www.gnu.org/licenses/gpl-2.0.html

*/

//Check if the cookie is set, if so check if the cookie is valid
if(isSet($_COOKIE[$cookieName])){
	$cookieValid = false;
	$ip = $_SERVER['REMOTE_ADDR']; //Get Ip address for cookie validation
	$validateCookie	= new checkLogin();
	$cookieValid = $validateCookie->checkCookie(mysql_real_escape_string($_COOKIE[$cookieName]), $ip);
	$userId	= $validateCookie->returnUserId($_COOKIE[$cookieName]);		
		
	//Get user information
	$userInfoQ = mysql_query("SELECT id, username, pin, pass, admin, share_count, stale_share_count, shares_this_round, hashrate, api_key, IFNULL(donate_percent, '0') as donate_percent, IFNULL(round_estimate, '0') as round_estimate FROM webUsers WHERE id = '".$userId."' LIMIT 0,1"); //
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
	$currentBalanceQ = mysql_query("SELECT balance, IFNULL(sendAddress,'') as sendAddress FROM accountBalance WHERE userId = '".$userId."' LIMIT 0,1");
	if ($currentBalanceObj = mysql_fetch_object($currentBalanceQ)) {
		$currentBalance = $currentBalanceObj->balance;
		//Get payment address that is associated wit this user
		$paymentAddress = $currentBalanceObj->sendAddress;			
	} else {
		$currentBalance = 0;
		$paymentAddress = "";
	}
}
?>