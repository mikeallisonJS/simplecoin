<?php
//Set page starter variables//
$includeDirectory = "/var/www/includes/";

//Include site functions
include($includeDirectory."requiredFunctions.php");

//Check that script is run locally
ScriptIsRunLocally();

$bitcoinController->backupwallet("/home/simplecoin/walletbackup/wallet.dat.".date("Ymd"));
?>