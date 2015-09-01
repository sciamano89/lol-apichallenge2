<?php
	//--------------------------------------------------------------------------------------------------------------
	//Author: Raimondo Previdi
	//Date: 8-28-2015
	//Contact: raimondo.previdi@gmail.com
	//Sources:	- http://stackoverflow.com/
	//			- http://dev.mysql.com/doc/refman/5.6/en
	//			- php.net
	//Info: This script automizes the creation of 500+ MySQL tables so that users can have a positive experience
	//		when loading the data on the website. I'm using an old laptop for the server :( so querying big tables
	//		would be super-slow and create long loading times.
	//--------------------------------------------------------------------------------------------------------------
	
	//allow loop to last a long time
	set_time_limit(500000);
	
	//pass arguments through php command line and not web browser
	$argument1 = $argv[1];	//key
	
	//connect to mysql DB using credentials
	require_once 'private/app_config.php';
	connect();

	//enums
	$enumQueue = ["ranked", "normal"];
	$enumRegion = ["br", "eune", "euw", "kr", "lan", "las", "na", "oce", "ru", "tr"]; 
	$enumElo = ["unranked", "bronze", "silver", "gold", "platinum", "diamond", "master", "challenger"];
	$enumPatch = [11, 14];
	
	//main
	if($argument1 == EXECUTE_URL_PASSWORD)
	{
		/*
		//create 4 semi-permanent tables, where all the items will draw from. To delete after all user-tables are created.
		foreach ($enumQueue as $queueVal1)
		{
			foreach ($enumPatch as $patchVal1)
			{
				$QUERY1 = "CREATE TABLE items_plus_$patchVal1"."_$queueVal1 (ItemId INT(5) NOT NULL, Winner VARCHAR(3) NOT NULL, Rank VARCHAR(20) NOT NULL, Region VARCHAR(4) NOT NULL);";
				$QUERY2 = "INSERT INTO items_plus_$patchVal1"."_$queueVal1 (ItemId, Region, Winner, Rank) SELECT Item0, Region, Winner, HighestAchievedSeasonTier FROM participants5" .$patchVal1 . $queueVal1. " UNION ALL SELECT Item1, Region, Winner, HighestAchievedSeasonTier FROM participants5" .$patchVal1 . $queueVal1. " UNION ALL SELECT Item2, Region, Winner, HighestAchievedSeasonTier FROM participants5" .$patchVal1 . $queueVal1. " UNION ALL SELECT Item3, Region, Winner, HighestAchievedSeasonTier FROM participants5" .$patchVal1 . $queueVal1. " UNION ALL SELECT Item4, Region, Winner, HighestAchievedSeasonTier FROM participants5" .$patchVal1 . $queueVal1. " UNION ALL SELECT Item5, Region, Winner, HighestAchievedSeasonTier FROM participants5" .$patchVal1 . $queueVal1. ";";
					
				//execute query1
				//echo "-------------------------------- SEMI PERMANENT QUERIES -------------------------------- <br>"; 
				//echo ($QUERY_CREATE_ITEMS_SEMI_PERMANENT . "<br>");
				//echo "<br>";
				$result_query1 = mysql_query($QUERY1) or die ('Q1 Error : ' . mysql_error());			
				$result_query2 = mysql_query($QUERY2) or die ('Q2 Error : ' . mysql_error());			
			}
		}
		*/
		
		//now create tables based on the 4 item_plus tables
		foreach ($enumQueue as $queueVal)
		{
			foreach ($enumRegion as $regionVal)
			{
				foreach ($enumElo as $eloVal)
				{
					$QUERY3 = "CREATE TABLE items_$queueVal"."_$regionVal"."_$eloVal (id INT(3) NOT NULL AUTO_INCREMENT PRIMARY KEY, ItemId INT(5) NOT NULL, ItemName VARCHAR (120) NOT NULL, ImageName VARCHAR(20) NOT NULL, PickRate VARCHAR(50) NOT NULL, WinRate VARCHAR(50) NOT NULL);";
							
					//execute query2
					//echo "-------------------------------- PERMANENT QUERY -------------------------------- <br>"; 
					//echo ($QUERY1 . "<br>");
					//echo "<br>";
					$result_query3 = mysql_query($QUERY3) or die ('Q3 Error : ' . mysql_error());			
							
					foreach ($enumPatch as $patchVal)
					{
						$QUERY4 = "CREATE TABLE items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal (id INT(3) AUTO_INCREMENT PRIMARY KEY, ItemId INT(5) NOT NULL, TotalAmount INT(10) NOT NULL, PickRate DECIMAL(11,1) NOT NULL, WinRate DECIMAL(11,1) NOT NULL);";
						
						$QUERY5 = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal AS (SELECT ItemId, Winner FROM items_plus_$patchVal"."_$queueVal WHERE (Region = '$regionVal' and Rank = '$eloVal'));";
						 
						$QUERY6 = "INSERT INTO items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal (ItemId, TotalAmount) SELECT DISTINCT ItemId, Count(*) FROM temp_temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal GROUP BY ItemId ORDER BY Count(ItemId) DESC;";
						
						$QUERY7 = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_numOfGames AS (SELECT SUM(TotalAmount) AS NumOfGames FROM items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal);";
						
						$QUERY8 = "UPDATE items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal, temp_numOfGames SET items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal.PickRate = ((items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".TotalAmount/(temp_numOfGames.NumOfGames/6)) *100);";
						 						  
						$QUERY9 = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal AS (SELECT ItemId, Count(*) as Losses FROM temp_temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal WHERE winner = 'NO' GROUP BY ItemId);";
						  
						$QUERY10 = "UPDATE items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal, temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal SET items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".WinRate = (((items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".TotalAmount - temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".Losses)/items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".TotalAmount)*100) WHERE items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".ItemId = temp_items_$queueVal"."_$regionVal"."_$eloVal"."_$patchVal".".ItemId;";
							
						//execute query3
						//echo "-------------------------------- PATCHVAL TEMP QUERIES -------------------------------- <br>"; 
						//echo ($QUERY4 . "<br>" . $QUERY5 . "<br>". $QUERY6 . "<br>". $QUERY7 . "<br>");
						$result_query4 = mysql_query($QUERY4) or die ('Q4 Error : ' . mysql_error());			
						$result_query5 = mysql_query($QUERY5) or die ('Q5 Error : ' . mysql_error());			
						$result_query6 = mysql_query($QUERY6) or die ('Q6 Error : ' . mysql_error());			
						$result_query7 = mysql_query($QUERY7) or die ('Q7 Error : ' . mysql_error());			
						$result_query8 = mysql_query($QUERY8) or die ('Q8 Error : ' . mysql_error());			
						$result_query9 = mysql_query($QUERY9) or die ('Q9 Error : ' . mysql_error());	
						$result_query10 = mysql_query($QUERY10) or die ('Q10 Error : ' . mysql_error());	
					}
					
					//merge into QUERY 1 table and delete the temp patchVal tables
					$QUERY11 = "INSERT INTO items_$queueVal"."_$regionVal"."_$eloVal (ItemId) SELECT ItemId FROM items_$queueVal"."_$regionVal"."_$eloVal"."_11;";
					
					$QUERY12 = "UPDATE items_$queueVal"."_$regionVal"."_$eloVal"."_11, items_$queueVal"."_$regionVal"."_$eloVal"."_14, items_$queueVal"."_$regionVal"."_$eloVal SET items_$queueVal"."_$regionVal"."_$eloVal".".PickRate = concat(items_$queueVal"."_$regionVal"."_$eloVal"."_14.PickRate, '% (', items_$queueVal"."_$regionVal"."_$eloVal"."_14.PickRate - items_$queueVal"."_$regionVal"."_$eloVal"."_11.PickRate, '%)'),items_$queueVal"."_$regionVal"."_$eloVal".".WinRate = concat(items_$queueVal"."_$regionVal"."_$eloVal"."_14.WinRate, '% (', items_$queueVal"."_$regionVal"."_$eloVal"."_14.WinRate - items_$queueVal"."_$regionVal"."_$eloVal"."_11.WinRate, '%)') WHERE (items_$queueVal"."_$regionVal"."_$eloVal"."_14.ItemId = items_$queueVal"."_$regionVal"."_$eloVal"."_11.ItemId and items_$queueVal"."_$regionVal"."_$eloVal".".ItemId = items_$queueVal"."_$regionVal"."_$eloVal"."_11.ItemId);";
					
					$QUERY13 = "DROP TABLE items_$queueVal"."_$regionVal"."_$eloVal"."_11;";
					
					$QUERY14 = "DROP TABLE items_$queueVal"."_$regionVal"."_$eloVal"."_14;";
					
					//execute query4
					//echo "-------------------------------- FINALIZE PERMANENT QUERY AND DELETE TEMP QUERIES -------------------------------- <br>"; 
					//echo ($QUERY8 . "<br>");
					//echo "<br>";
					$result_query11 = mysql_query($QUERY11) or die ('Q11 Error : ' . mysql_error());			
					$result_query12 = mysql_query($QUERY12) or die ('Q12 Error : ' . mysql_error());			
					$result_query13 = mysql_query($QUERY13) or die ('Q13 Error : ' . mysql_error());			
					$result_query14 = mysql_query($QUERY14) or die ('Q14 Error : ' . mysql_error());	
				}
			}
		}	
	}
	else
	{
		echo "unauthorized execution";
	}
?>