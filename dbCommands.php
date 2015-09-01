<?php
	//------------------------------------------------------------------------------------------------------------------------
	//Author: Raimondo Previdi
	//Date: 8-29-2015
	//Contact: raimondo.previdi@gmail.com
	//Sources:
	//Info: This script is intended to retrieve data from MySQL database and push it as JSON to the website
	//------------------------------------------------------------------------------------------------------------------------
				
	//connect to mysql DB using credentials
	require_once 'private/app_config.php';
	connect();
	
	//get command argument from any URL execution
	$mCommandId = $_GET['commandId'];		//the specific table
	$mCategory = $_GET['sortingCategory'];	//the sorting category (i.e. PickRate vs WinRate)
	$mOrderBy = $_GET['orderBy'];			//the order of the sorting (asc vs desc)
	
	//execute command and wait for response
	$mQuery = "SELECT * FROM $mCommandId WHERE (ItemId != 0 and (PickRate != '' or WinRate != '') and (PickRate != '0.0% (0.0%)' and WinRate != '0.0% (0.0%)')) ORDER BY CAST($mCategory as DECIMAL(11,1)) $mOrderBy ";
	$mQueryResult = mysql_query($mQuery) or die ('invalid query');
	
	//push data to an array
	$mData = array();
	while($row = mysql_fetch_assoc($mQueryResult))
	{
	  $mData[] = $row;
	}
	
	//encode to JSON and print out
	echo json_encode($mData);
		
	//just because: unset variabes
	unset($index);
	unset($data);
	unset($mData);
	unset($mQuery);
	unset($mQueryResult);
?>