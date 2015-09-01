//------------------------------------------------------------------------------------------------------------------------
//Author: Raimondo Previdi
//Date: 8-29-2015
//Contact: raimondo.previdi@gmail.com
//Sources:	- http://www.w3schools.com/js
//			- http://www.w3schools.com/jquery
//			- http://www.w3schools.com/html
//			- http://www.w3schools.com/css
//Info: This script has all the functions that make this website work. It handles requests to mysql and back.
//		It also contains the behavior of buttons, and the behavior of the BGM.
//------------------------------------------------------------------------------------------------------------------------

//constants
var SIDEBAR_HEIGHT_MIN = 560;
var SIDEBAR_HEIGHT_RATIO = 54;
var MUTED_BUTTON = "<button class=\"muteUnmuteButton\" id=\"muteUnmuteButton\" onclick=\"MuteUnmute();\" style=\"background-image:url(media/muted.png);\"></button>";
var UNMUTED_BUTTON = "<button class=\"muteUnmuteButton\" id=\"muteUnmuteButton\" onclick=\"MuteUnmute();\" style=\"background-image:url(media/unmuted.png);\"></button>";
var ITEM_IMAGE_URL = "http://ddragon.leagueoflegends.com/cdn/5.7.2/img/item/";

//variables
var descTriangle = " ▼";
var ascTriangle = " ▲";

//enums
var enumCategories = ["items", "champions", "roles"];
var enumQueue = ["ranked", "normal"];
var enumRegion = ["regionAll", "br", "eune", "euw", "kr", "lan", "las", "na", "oce", "ru", "tr"]; 
var enumElo = ["eloAll", "unranked", "bronze", "silver", "gold", "platinum", "diamond", "master", "challenger"];
//sortingCategory and orderBy
var enumSortingCategory = ["PickRate", "WinRate"];
var enumOrderBy = ["DESC", "ASC"];

//currently selected variables
//enums
var selectedCategory = "items";
var selectedQueue = "normal";
var selectedRegion = "euw";
var selectedElo = "bronze";
//sortingCategory and orderBy
var selectedSortingCategory = "PickRate";
var selectedOrderBy = "DESC";
var previousSelectedSortingCategory = "";

//categories
var topButton = "topButton";
var pushTopButtonDown = "pushTopButtonDown";
var hoverTopButton = "hoverTopButton";
//tiers
var topButtonImage = "topButtonImage";
var pushTopButtonDownImage = "pushTopButtonDownImage";
var hoverTopButtonImage = "hoverTopButtonImage";
//regions
var regionTopButton = "regionTopButton";
var regionPushTopButtonDown = "regionPushTopButtonDown";
var regionHoverTopButton = "regionHoverTopButton";

//------------------------------------------------------------------------------------------------------------------------
//	JS -> PHP -> MYSQL -> PHP -> JS
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//	SendQuery(pQuery, tableType)
//		Info:	gets the data from MySQL through a .php script in JSON format, then decodes it
//------------------------------------------------------------------------------------
function SendQuery(pQuery, pSortingCategory, pOrderBy, tableType)
{
	var pJsonData;
	
	$.get("http://league.rprevidi.com/dbCommands.php?commandId=" + pQuery + "&sortingCategory=" + pSortingCategory + "&orderBy=" + pOrderBy, function(data){
		 var jsonData = JSON.parse(data);
		 WriteOnTable(jsonData, tableType, pSortingCategory, pOrderBy);
	});
}

//------------------------------------------------------------------------------------
//	WriteOnTable(table, tableType)
//		Info:	grabs decoded json data and writes it in proper format inside the main table of the webpage
//------------------------------------------------------------------------------------
function WriteOnTable(table, tableType, pSortingCategory2, pOrderBy2)
{	
	var str = "<thead>\n<tr>\n";
	var columnSize = 0;
	var rowIsLight = true;
	var id = 1;
	var pSortingAndOrder1 = "Pick Rate";
	var pSortingAndOrder2 = "Win Rate";

	//changes the text to show which category and order is selected
	if (selectedSortingCategory == enumSortingCategory[0])
	{
		if (selectedOrderBy == enumOrderBy[0])
			pSortingAndOrder1 += descTriangle;
		else
			pSortingAndOrder1 += ascTriangle;
	}
	else
	{
		if (selectedOrderBy == enumOrderBy[0])
			pSortingAndOrder2 += descTriangle;
		else
			pSortingAndOrder2 += ascTriangle;
	}


	switch (tableType)
	{
		case "items":
			str += "<th class = \"danger\">#</th>\n<th class = \"danger\">Item</th>\n<th class = \"danger\">Name</th>\n<th class = \"danger\"><button class=\"orderBy\" id=\"PickRate\" onclick=\"PushButtonDown(this);\">" + pSortingAndOrder1 + "</button></th>\n<th class = \"danger\"><button class=\"orderBy\" id=\"WinRate\" onclick=\"PushButtonDown(this);\">" + pSortingAndOrder2 + "</button></th>\n";
			columnSize = 5;
			break;
		case "champions":	
			str += "<th class = \"danger\">#</th>\n<th class = \"danger\">Item</th>\n<th class = \"danger\">Name</th>\n<th class = \"danger\"><button class=\"orderBy\" id=\"PickRate\" onclick=\"PushButtonDown(this);\">" + pSortingAndOrder1 + "</button></th>\n<th class = \"danger\"><button class=\"orderBy\" id=\"WinRate\" onclick=\"PushButtonDown(this);\">" + pSortingAndOrder2 + "</button></th>\n";
			columnSize = 5;
			break;	
	}
	
	str += "</tr>\n</thead>\n<tbody>\n";

	for (row in table)
	{		
		//alternates the colors in the table
		if (rowIsLight)
		 	str += "<tr class = \"info\">\n";
		else
			str += "<tr class = \"success\">\n";
			
		rowIsLight = !rowIsLight;
		var counter = 0;
		
		for (key in table[row])
		{	
			str += "<td>";
			if (counter == 0)
				str += id;
			else if (counter == 1)
				str += "<img src=\"" + ITEM_IMAGE_URL + table[row][key] + ".png\" class=\"icon\">";
			else if (counter == 3 || counter == 4)
			{
				var tempRate1 = table[row][key].replace("(", "").replace(")", "");
				var tempRate2 = tempRate1.split("%");
				var numberColor = "neutralNumber";
				if (Number(tempRate2[1]) < 0)
   					numberColor = "negativeNumber";
				else if(Number(tempRate2[1]) > 0)
   					{
						numberColor = "positiveNumber";
						var tempRate3 = tempRate2[1].replace(" ", " +");
						tempRate2[1] = tempRate3;	
					}
				str += "<a class=\"\">" + tempRate2[0] + "%</a><a class=\"" + numberColor + "\">" + tempRate2[1] + "%</a>";
			}
			else 
				str += table[row][key];
			str += "</td>\n";
			counter++;
		}
		str += "</tr>\n";
		id++;
	 }
	 str += "</tbody>\n";

	//send content to table in HTML
	document.getElementById("mainTable").innerHTML = str;
	
	//extend sidebars to reach the end of the table (based on number of rows in table)
	var tempHeight = SIDEBAR_HEIGHT_MIN + (SIDEBAR_HEIGHT_RATIO * id);
	document.getElementById("leftSidebar").outerHTML = "<div class=\"sidebar\" id=\"leftSidebar\" style=\"margin-left: -30px; float: left; border-top-left-radius: 10px 10px; border-bottom-left-radius: 10px 10px; height:" + tempHeight + "px;\"></div>";
	document.getElementById("rightSidebar").outerHTML = "<div class=\"sidebar\" id=\"rightSidebar\"  style=\"float: right; margin-right: -30px; border-top-right-radius: 10px 10px; border-bottom-right-radius: 10px 10px; height:" + tempHeight + "px;\"></div>";
}


//------------------------------------------------------------------------------------------------------------------------
//	BUTTONS
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//	PushButtonDown(field)
//		Info:	called when a button is pushed down
//------------------------------------------------------------------------------------
function PushButtonDown(field)
{
	if ($.inArray (field.id, enumElo) > -1)
	{
		if (field.className != pushTopButtonDownImage && field.className != pushTopButtonDown)
		{
			if (field.id == enumElo[0])
				field.className = pushTopButtonDown;
			else
				field.className = pushTopButtonDownImage;
		}
	}
	else if	($.inArray (field.id, enumRegion) > -1)
	{ 
		if (field.className != regionPushTopButtonDown && field.className != pushTopButtonDown)
		{
			if (field.id == enumRegion[0])
				field.className = pushTopButtonDown;
			else
				field.className = regionPushTopButtonDown;
		}
	}
	else if ($.inArray (field.id, enumSortingCategory) > -1)
	{
		console.log("called1");
		//do nothing, as it's handled in the MarkAsSelected() function
	}
	else if (field.className != pushTopButtonDown)
	{
		console.log("called2");
		field.className = pushTopButtonDown;
	}
	
	//marks the group that changed and sends new query to server
	MarkAsSelected(field.id);
}

//------------------------------------------------------------------------------------
//	HoverOutButton(field)
//		Info:	called when a button is hovered-out
//------------------------------------------------------------------------------------
function HoverOutButton(field)
{
	if ($.inArray (field.id, enumElo) > -1)
	{
		if (field.className != pushTopButtonDownImage && field.className != pushTopButtonDown)
		{
			if (field.id == enumElo[0])
				field.className = topButton;
			else
				field.className = topButtonImage;
		}
	}
	else if	($.inArray (field.id, enumRegion) > -1)
	{
		if (field.className != regionPushTopButtonDown && field.className != pushTopButtonDown)
		{
			if (field.id == enumRegion[0])
				field.className = topButton;
			else
				field.className = regionTopButton;
		}
	}
	else if (field.className != pushTopButtonDown)
		field.className = topButton;
}

//------------------------------------------------------------------------------------
//	HoverButton(field)
//		Info: 	called when a button is moused-over
//------------------------------------------------------------------------------------
function HoverButton(field)
{
	if ($.inArray (field.id, enumElo) > -1)
	{
		if (field.className != pushTopButtonDownImage && field.className != pushTopButtonDown)
		{
			if (field.id == enumElo[0])
				field.className = hoverTopButton;
			else
				field.className = hoverTopButtonImage;
		}
	}
	else if	($.inArray (field.id, enumRegion) > -1)
	{
		if (field.className != regionPushTopButtonDown && field.className != pushTopButtonDown)
		{
			if (field.id == enumRegion[0])
				field.className = hoverTopButton;
			else
				field.className = regionHoverTopButton;
		}
	}
	else if (field.className != pushTopButtonDown)
		field.className = hoverTopButton;
}

//------------------------------------------------------------------------------------
//	PushOtherButtonsUp(field)
//		Info: 	pushes up all other buttons in the same group
//------------------------------------------------------------------------------------
function PushOtherButtonsUp(field)
{
	var pArray = [];
	var tempId = field.id;
	//category group
	if ($.inArray (tempId, enumCategories) > -1)
	{
		pArray = RemoveItem(enumCategories, tempId);
		for (element in pArray)
			document.getElementById(pArray[element]).className = topButton;
	}
	//tiers group
	else if ($.inArray (tempId, enumElo) > -1)
	{
		pArray = RemoveItem(enumElo, tempId);
		for (element in pArray)
		{
			//if it's allElo, treat it like a regular button
			if (pArray[element] == enumElo[0])
				document.getElementById(pArray[element]).className = topButton;
			else
				document.getElementById(pArray[element]).className = topButtonImage;
		}
	}
	//region group
	else if ($.inArray (tempId, enumRegion) > -1)
	{
		pArray = RemoveItem(enumRegion, tempId);
		for (element in pArray)
		{
			//if it's allRegion, treat it like a regular button
			if (pArray[element] == enumRegion[0])
				document.getElementById(pArray[element]).className = topButton;
			else
				document.getElementById(pArray[element]).className = regionTopButton;
		}
	}
	//queue group
	else if ($.inArray (tempId, enumQueue) > -1)
	{
		pArray = RemoveItem(enumQueue, tempId);
		for (element in pArray)
			document.getElementById(pArray[element]).className = topButton;
	}
}

//------------------------------------------------------------------------------------
//	MarkAsSelected(fieldId)
//		Info: 	checks the group type of the button that was pushed down, marks it accordingly, then sends query. //------------------------------------------------------------------------------------
function MarkAsSelected(fieldId)
{
	if ($.inArray (fieldId, enumElo) > -1)
	{
		if (fieldId == "eloAll")
			selectedElo = "all";
		else
			selectedElo = fieldId;
	}
	else if ($.inArray (fieldId, enumRegion) > -1)
	{
		if (fieldId == "regionAll")
			selectedRegion = "all";
		else
			selectedRegion = fieldId;
	}
	else if ($.inArray (fieldId, enumQueue) > -1)
		selectedQueue = fieldId;
	else if ($.inArray (fieldId, enumCategories) > -1)
		selectedCategory = fieldId;
	else if ($.inArray (fieldId, enumSortingCategory) > -1)
	{
		var tempArrayCategory = [];
		if (fieldId == selectedSortingCategory)	//if it's the same, change the OrderBy
		{
			tempArrayCategory = RemoveItem(enumOrderBy, selectedOrderBy);		//copy array and remove selectedOrderBy
			selectedOrderBy = tempArrayCategory[0];								//use the remaining one as the new selectedOrderBy
		}
		else	//change the SortingCategory
		{
			tempArrayCategory = RemoveItem(enumSortingCategory, selectedSortingCategory);	//copy array and remove selectedSortingCategory
			previousSelectedSortingCategory = selectedSortingCategory;			//save the old category so that table creation knows if category or order has changed
			selectedSortingCategory = tempArrayCategory[0];						//use the remaining one as the new selectedSortingCategory
			selectedOrderBy = enumOrderBy[0];									//reset the orderBy to DESC
		}
	}
	SendQuery(selectedCategory + '_' + selectedQueue + '_' + selectedRegion + '_' + selectedElo, selectedSortingCategory, selectedOrderBy, selectedCategory);
}

//------------------------------------------------------------------------------------------------------------------------
//	GLOBAL HELPER FUNCTIONS
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//	RemoveItem(array, pItem)
//		Info: 	helper function to remove item from an array. Does NOT modify original array.
//		Source: http://stackoverflow.com/questions/3954438/remove-item-from-array-by-value
//------------------------------------------------------------------------------------
function RemoveItem(array, pItem)
{
	var tempArray = array.slice();
    for(var i in tempArray)
	{
        if(tempArray[i] == pItem)
		{
            tempArray.splice(i,1);
            break;
    	}
    }
	return tempArray;
}

//------------------------------------------------------------------------------------------------------------------------
//	AUDIO
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//	PlaySoundtrack()
//		Info:	plays soundtrack. Called on html's onLoad()
//------------------------------------------------------------------------------------
function PlaySoundtrack()
{
	var soundtrack = document.getElementById("soundtrack");
	soundtrack.loop = true;
	soundtrack.play();
}

//------------------------------------------------------------------------------------
//	MuteUnmute()
//		Info:	handle Mute/Unmute for soundtrack
//------------------------------------------------------------------------------------
function MuteUnmute()
{
	var pPlayer = document.getElementById("soundtrack");
	pPlayer.muted = !pPlayer.muted;
	
	document.getElementById("muteUnmuteButton").outerHTML = (pPlayer.muted) ? MUTED_BUTTON : UNMUTED_BUTTON;	
}