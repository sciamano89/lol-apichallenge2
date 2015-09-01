<?php
	//--------------------------------------------------------------------------------------------------------------
	//Author: Raimondo Previdi
	//Date: 8-28-2015
	//Contact: raimondo.previdi@gmail.com
	//Sources:	- http://stackoverflow.com/
	//			- http://dev.mysql.com/doc/refman/5.6/en
	//			- php.net
	//Info: This script is grabs RIOT's itemsId and matches it to the corresponding itemName and iconName, then
	//		inserts them in its own table: used to map other existing MySQL tables. Pass php argument through
	//		web browser.
	//--------------------------------------------------------------------------------------------------------------
	
	//connect to mysql DB using credentials
	require_once 'private/app_config.php';
	connect();	

	//main
	if($_GET['key'] == EXECUTE_URL_PASSWORD)
	{
		//grab and decode JSON
		$items_jsondata = file_get_contents("http://ddragon.leagueoflegends.com/cdn/5.2.1/data/en_US/item.json");
		$items = json_decode($items_jsondata, true);
				
		//insert item data into MySQL's 'items_map' table
		$QUERY_ITEMS = "INSERT INTO items_map (ItemId, ItemName, ImageName)";
		$rows = array();
		foreach($items['data'] as $p_itemId => $rowVal)
		{
			$rows[] = '(' .$p_itemId. ', "' .$rowVal['name']. '", "' .$rowVal['image']['full']. '")';
		}
		$result_participant = mysql_query($QUERY_ITEMS .' VALUES'. implode(',', $rows) .';') or die ('Error : ' . mysql_error());
		echo "COMPLETE";
	}
	else
	{
		echo "unauthorized execution";
	}
?>