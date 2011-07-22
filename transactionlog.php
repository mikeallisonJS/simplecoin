<?php 
//Set page starter variables//
$includeDirectory = "/var/www/includes/";

//Include site functions
include($includeDirectory."requiredFunctions.php");

//Open a bitcoind connection
$transactions = $bitcoinController->query("listtransactions");
echo print_r($transactions);
?>