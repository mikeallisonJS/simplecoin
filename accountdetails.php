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

include ("includes/header.php");

/*
 Copyright (C)  41a240b48fb7c10c68ae4820ac54c0f32a214056bfcfe1c2e7ab4d3fb53187a0 Name Year (sha256)

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

 Note From Author: Keep the original donate address in the source files when transferring or redistrubuting this code.
 Please donate at the following address: 1Fc2ScswXAHPUgj3qzmbRmwWJSLL2yv8Q
 */

if(!$cookieValid) {
	header('Location: /');
	exit;
}
//Execute the following based on what $_POST["act"] is set to
$returnError = "";
$goodMessage = "";
if (isset($_POST["act"])) {
	$act = $_POST["act"];
	$inputAuthPin = hash("sha256", $_POST["authPin"].$salt);
		

	//Check if authorization pin has been inputted correctly
	if($inputAuthPin == $authPin && $act){
		if($act == "cashOut"){
				
			//Get user's balance and send it to set address;
			//Does user have any money in their balance
			if($currentBalance > 0.01){
				$bitcoinControll = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

				//Send $currentBalance to $paymentAddress
				//Validate that a $paymentAddress has been set & is valid before sending
				$isValidAddress = $bitcoinControll->validateaddress($paymentAddress);
				if($isValidAddress){
					//Subtract TX feee
					$currentBalance = $currentBalance - 0.01;
					//Send money//
					if($bitcoinControll->sendtoaddress($paymentAddress, $currentBalance)) {
						$paid = 0;
						$result = mysql_query("SELECT IFNULL(paid,'0') FROM accountBalance WHERE userId=".$userId);
						if ($resultrow = mysql_fetch_object($result)) $paid = $resultrow->paid + $currentBalance;
						
						//Reduce balance amount to zero
						mysql_query("UPDATE `accountBalance` SET balance = '0', paid = '".$paid."' WHERE `userId` = '".$userId."'");

						$goodMessage = "You have successfully sent ".$currentBalance." to the following address:".$paymentAddress;
						//Set new variables so it appears on the page flawlessly
						$currentBalance = 0;						
					}else{
						$returnError = "Commodity failed to send.";
					}
				}else{
					$returnError = "That isn't a valid Bitcoin address";
				}
			}else{
				$returnError = "You have no money in your account!";
			}
		}


		if($act == "updateDetails"){
			//Update user's details
			$newSendAddress = mysql_real_escape_string($_POST["paymentAddress"]);
			$newDonatePercent = mysql_real_escape_string($_POST["donatePercent"]);
			$updateSuccess1 = mysql_query("UPDATE accountBalance SET sendAddress = '".$newSendAddress."' WHERE userId = ".$userId);
			if (!is_nan($newDonatePercent))
				$updateSuccess2 = mysql_query("UPDATE webUsers SET donate_percent='".$newDonatePercent."' WHERE id = ".$userId);
			else
				$returnError = "Donation % must be numeric.";
				
			if($updateSuccess1 && $updateSuccess2){
				$goodMessage = "Account details are now updated.";
				$paymentAddress = $newSendAddress;
				$donatePercent = $newDonatePercent;
			}
		}

		if($act == "updatePassword"){
			//Update password
			$oldPass = hash("sha256", mysql_real_escape_string($_POST["currentPassword"]));
			$newPass = mysql_real_escape_string($_POST["newPassword"]);
			$newPassConfirm = mysql_real_escape_string($_POST["newPassword2"]);

			//If hash $oldPass is the same as the DB already hashed password continue you with the password change
			if($oldPass == $hashedPass){
				//Check if new password is valid
				if($newPass != "" && strlen($newPass) > 6){
					//Change the password only if $newPass == $newPassConfirm
					if($newPass == $newPassConfirm){
						//Update hashed password
						$newHashedPass = hash("sha256", $newPass.$salt);
						$passchangeSuccess = mysql_query("UPDATE `webUsers` SET `pass` = '".$newHashedPass."' WHERE `id` = '".$userId."'");
						if($passchangeSuccess){
							$goodMessage = "Password successfully changed.";
						}else{
							$returnError = "Database Failure - Unable to change password";
						}
					}else if($newPass != $newPassConfirm){
						$returnError = "The \"New Password\" and \"New Password Repeat\" fields must match";
					}
				}else{
					$returnError = "Your new password is not valid, Must be longer then 6 characters";
				}

			}else if($oldPass != $hashedPass){
				//Typed in password dosent match DB password
				$returnError = "You must type in the correct current password before you can set a new password.";
			}
		}


	}else if($inputAuthPin != $authPin && $act){
		$returnError = "Authorization Pin is Invalid!";
	}
	
	if($act == "addWorker"){
		//Add worker
		$prefixUsername = $userInfo->username;
		$inputUser = $prefixUsername.".".mysql_real_escape_string($_POST["username"]);
		$inputPass = mysql_real_escape_string($_POST["pass"]);

		//Check if username already exists
		$usernameExistsQ = mysql_query("SELECT `id` FROM `pool_worker` WHERE `associatedUserId` = ".$userId." AND `username` = '".$inputUser."'");
		$usernameExists = mysql_num_rows($usernameExistsQ);

		if($usernameExists == 0){
			$addWorkerQ = mysql_query("INSERT INTO `pool_worker` (`associatedUserId`, `username`, `password`) VALUES('".$userId."', '".$inputUser."', '".$inputPass."')");
			if($addWorkerQ){
				$goodMessage = "Worker successfully added!";
			}else if(!$addWorkerQ){
				$returnError = "Database Error - Worker was not added :(";
			}
		}else if($usernameExists == 1){
			$returnError = "Try using a different Worker Username";
		}
	}
}

//Display Error and Good Messages(If Any)
echo "<span class=\"goodMessage\">".$goodMessage."</span><br/>";
echo "<span class=\"returnMessage\">".$returnError."</span>";
?>
<h2>Account Details</h2>
<form action="/accountdetails.php" method="post"><input type="hidden" name="act" value="updateDetails">
<table>
	<tr><td>Username: </td><td><?php echo $userInfo->username;?></td></tr>
	<tr><td><a href="api.php?api_key=<?php echo $userApiKey ?>" style="color: blue" target="_blank">API</a> Key: </td><td><?php echo $userApiKey; ?></td></tr>
	<tr><td></td></tr>
	<tr><td>Payment Address: </td><td><input type="text" name="paymentAddress" value="<?php echo $paymentAddress?>" width="300"></td></tr>
	<tr><td>Donation %: </td><td><input type="text" name="donatePercent" value="<?php echo $donatePercent;?>"></td></tr>
	<tr><td>Authorize Pin: </td><td><input type="password" name="authPin" size="4" maxlength="4"></td></tr>
</table>
<input type="submit" value="Update Account Settings"></form>
<br />
<br />
<h2>Cash Out</h2>
<i>(Please note: a 0.01 btc transaction fee is required by the bitcoin client for processing)</i><br/>
<form action="/accountdetails.php" method="post">
<input type="hidden" name="act" value="cashOut">
<table>
	<tr><td>Account Balance: </td><td><?php echo $currentBalance; ?></td></tr>
	<tr><td>Payout to: </td><td><?php echo $paymentAddress; ?></td></tr>
	<tr><td>Authorize Pin: </td><td><input type="password" name="authPin" size="4" maxlength="4"></td></tr>
</table>
<input type="submit" value="Cash Out"></form>
<br />
<br />

<h2>Change Password</h2>
<form action="/accountdetails.php" method="post"><input type="hidden" name="act" value="updatePassword">
<table>
	<tr><td>Current Password: </td><td><input type="password" name="currentPassword"></td></tr>
	<tr><td>New Password: </td><td><input type="password" name="newPassword"></td></tr>
	<tr><td>New Password Repeat: </td><td><input type="password" name="newPassword2"></td></tr>
	<tr><td>Authorize Pin: </td><td><input type="password" name="authPin" size="4"	maxlength="4"></td></tr>
</table>
<span style="text-decoration: underline;">(You will be redirected to the login screen upon success)</span> <br />
<input type="submit" value="Update Password Settings"></form>
<br />
<br />

<h2>Workers</h2>
<form action="/accountdetails.php" method="post">
<table border="1" cellpadding="1" cellspacing="1">
<tr><td><u>Worker Name </u></td><td><u>Worker Password</u></td><td><u>Active</u></td><td><u>Hashrate (Mhash/s)</u></td></tr>
<?php	
//Get list of workers from the associatedUserId
$getWorkers = mysql_query("SELECT `id`, `username`, `password`, active, hashrate FROM `pool_worker` WHERE `associatedUserId` = '".$userId."'");
while($worker = mysql_fetch_array($getWorkers)){
	//Display worker information and the forms to edit or update them
	$splitUsername = explode(".", $worker["username"]);
	$realUsername = $splitUsername[1];
	?>	
	<?php 
    //<form action="/accountdetails.php" method="post"><?php echo $userInfo->username;/>.<input
	//type="text" name="user" size="10" maxlength="20"
	//value="<?php echo $realUsername/>"> &middot; Pass: <input type="text"
	//name="pass" size="10" maxlength="20"
	//value="<?php echo $worker["password"]/>"> <input type="submit"
	//value="Update"></form>
	?>
	<tr><td <?php if ($worker["active"] == 0) { ?>style="color: red"<?php } ?>><?php echo $userInfo->username; ?>.<?php echo $realUsername; ?></td>
	    <td><?php echo $worker["password"]?></td>
	    <td><?php if ($worker["active"] == 1) echo "Y"; else echo "N"; ?>
	    <td><?php echo $worker["hashrate"]?></td></tr></tr>
	<?php
}
?>
</table>
</form>
<form action="/accountdetails.php" method="post"><input type="hidden"
	name="act" value="addWorker"><!--  AuthPin:<input type="password"
	name="authPin" size="4" maxlength="4"><br /> -->
<?php echo $userInfo->username;?>.<input type="text" name="username"
	value="user" size="10" maxlength="20"> &middot; <input type="text"
	name="pass" value="pass" size="10" maxlength="20"> <input type="submit"
	value="Add worker"></form>

<br />
<br />

<?php include ("includes/footer.php");?>
