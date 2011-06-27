<?php
include ("includes/header.php");
	
$goodMessage = "";
$returnError = "";
//Scince this is the Admin panel we'll make sure the user is logged in and "isAdmin" enabled boolean; If this is not a logged in user that is enabled as admin, redirect to a 404 error page

if(!$cookieValid || $isAdmin != 1) {
	header('Location: /');
	exit;
}

$action = $_POST["action"];
if($action == "news") {
$title = $_POST["title"];
$title = sqlesc($title);
$news = $_POST["news"];
$news = sqlesc($news);
$currentTime = time();

mysql_query("UPDATE news SET title = $title, message = $news, timestamp = $currentTime WHERE id=1") or sqlerr(__FILE__, __LINE__);
}

$res = mysql_query("SELECT title, message  FROM news WHERE id = 1");
$row = mysql_fetch_array($res);

echo "<h2>Edit news</h2><br/>";
echo "<form action=news.php method=post>";
echo "<input type=hidden name=action value=news>";
echo "Title<br>";
echo "<textarea name=title rows=1 cols=80>" . htmlspecialchars($row["title"]) . "</textarea><br>";
echo "News<br>";
echo "<textarea name=news rows=10 cols=80>" . htmlspecialchars($row["message"]) . "</textarea>";
echo "<br><input type=submit value=Submit>";
echo "</form>";
?>