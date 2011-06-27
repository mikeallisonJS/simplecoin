<?PHP
$pageTitle = "- Users";
include ("includes/header.php");
	
$goodMessage = "";
$returnError = "";
//Scince this is the Admin panel we'll make sure the user is logged in and "isAdmin" enabled boolean; If this is not a logged in user that is enabled as admin, redirect to a 404 error page

if(!$cookieValid || $isAdmin != 1) {
	header('Location: /');
	exit;
}
	$show = $_GET['show'];
	$searchUsername = $_POST['searchUsername'];

if($show == ""){
?>
					<h2 style="text-decoration:underline;">Search for a user (% = Wildcard)</h2>
						<form action="?show=searchUsers" method="post">
							By username:<input type="text" name="searchUsername" value=""/><br/>
							<input type="submit" value="Search For user">
					</form>
<?
}else if($show == "editUsers"){ ?>
					<h2 style="text-decoration:underline;">Search for a user (% = Wildcard)</h2>
						<form action="?show=searchUsers" method="post">
							By username:<input type="text" name="searchUsername" value=""/><br/>
							<input type="submit" value="Search For user">
					</form>

<?
}
if($show == "searchUsers"){
				?>
					<div class=
						echo $updateOutput;
				?>
					<h2 style="text-decoration:underline;">Search for a user (% = Wildcard)</h2>
						<form action="?show=searchUsers" method="post">
							By username:<input type="text" name="searchUsername" value=""/><br/>
							<input type="submit" value="Search For user">
						</form><br/><br/>
				<?php
							$searchUsername = mysql_real_escape_string($searchUsername);
						//Query for a list of users that match this username
							$searchQ = mysql_query("SELECT `accountLocked`, `email`, `username`, `id`, `loggedIp`, hashrate, share_count, stale_share_count, activeEmail FROM `webUsers` WHERE `username` LIKE '".$searchUsername."'");
				?>
					<form action="?show=updateSearchedUsers&searchUsername=<?php echo $searchUsername;?>" method="post">
					<h2 style="text-decoration:underline;">Results for <i><?php echo $searchUsername; ?></i></h2>
					<input type="submit" value="Execute Changes"><br/>
						<?php
							$userIdArray = "";
							//List output from $searchQ;
							print("<table width=600 border=1 cellspacing=1 cellpadding=5>");
							print("<tr><td align=left><B>id</B></td><td align=left><B>Username</B></td><td align=left>Email</td><td align=left>Hashrate</td><td align=left>share_count</td><td align=left>stale_share_count</td><td align=left>loggedIp</td><td align=left>Disable</td></tr>");
							while($user = mysql_fetch_array($searchQ)){
							print("<tr>");
							print("<td align=left>$user[id]</td>");
							print("<td align=left>$user[username]</td>");
							print("<td align=left>$user[email]</td>");
							print("<td align=left>$user[hashrate]</td>");
							print("<td align=left>$user[share_count]</td>");
							print("<td align=left>$user[stale_share_count]</td>");
							print("<td align=left>$user[loggedIp]</td>");
							print("<td align=left>$user[accountLocked]</td>");


								//Make array of userId's to post
									if($userIdArray != ""){
										$userIdArray .= ",";
									}
									$userIdArray .= $user["id"];
							print("</tr>");
							}
							print("</table>");

						?>
							<input type="hidden" name="userIdArray" value="<?php echo $userIdArray;?>"/>
				<?php
					}
					//Output Footer
					include($footer);
					///////////////
				?>
			</div>
		</div>
	</body>
</html>
