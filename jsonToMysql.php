<?php
	//--------------------------------------------------------------------------------------------------------------
	//Author: Raimondo Previdi
	//Date: 8-20-2015
	//Contact: raimondo.previdi@gmail.com
	//Sources: 	- http://www.kodingmadesimple.com/2014/12/how-to-insert-json-data-into-mysql-php.html
	//			- php.net
	//Info: This script is intented to run in a loop for about 8.3 hours: it grabs Riot's JSON data on the NA 5v5
	//		Ranked games from patch 5.11, stores it in PHP arrays, pushes them properly formatted to my own MySQL
	//		Database. This is meant as a first automated test. If it's successful, I will automate the rest of the
	//		matches on the other regions, queue types, and patch.
	//--------------------------------------------------------------------------------------------------------------
		
	//allow loop to last up to ~13 hours
	set_time_limit(50000);
	
	//connect to mysql DB using credentials
	require_once 'private/app_config.php';
	connect();
	
	//make sure only I can run the rest of the script -- or at least make it difficult for intruders :)
	if($_GET['key'] == EXECUTE_URL_PASSWORD)
	{
		//grab Riot's Ranked NA games from 5.11
		$m_511_NA_matchID_jsondata = file_get_contents('data/5-11/RANKED_SOLO/NA.json');
		$m_511_NA_matchID = json_decode($m_511_NA_matchID_jsondata, true);
	
		//loop through each match to save data
		for($currentMatchID = 0; $currentMatchID < count($m_511_NA_matchID); $currentMatchID++)
		{
			$match_jsondata = file_get_contents('https://na.api.pvp.net/api/lol/na/v2.2/match/'. $m_511_NA_matchID[$currentMatchID] . '?api_key=' . API_KEY);
			$match = json_decode($match_jsondata, true);
			
			//general (match)
			$matchId = $match['matchId'];
			$region = $match['region'];
			$matchDuration = $match['matchDuration'];
			$queueType = $match['queueType'];
			$participantArray = array();
			$banArray = array();
			
			//loop through each participant
			for($participantNumber = 0; $participantNumber < 10; $participantNumber++)
			{
				//general (participant)
				$participantArray[$participantNumber]["participantId"] = $match['participants'][$participantNumber]['participantId'];
				$participantArray[$participantNumber]["spell1Id"] = $match['participants'][$participantNumber]['spell1Id'];
				$participantArray[$participantNumber]["spell2Id"] = $match['participants'][$participantNumber]['spell2Id'];
				$participantArray[$participantNumber]["championId"] = $match['participants'][$participantNumber]['championId'];
				$participantArray[$participantNumber]["teamId"] = $match['participants'][$participantNumber]['teamId'];
				$participantArray[$participantNumber]["highestAchievedSeasonTier"] = $match['participants'][$participantNumber]['highestAchievedSeasonTier'];
				//timeline (participant)
				$participantArray[$participantNumber]["role"] = $match['participants'][$participantNumber]['timeline']['role'];
				$participantArray[$participantNumber]["lane"] = $match['participants'][$participantNumber]['timeline']['lane'];
				//stats (participant)
				$participantArray[$participantNumber]["kills"] = $match['participants'][$participantNumber]['stats']['kills'];
				$participantArray[$participantNumber]["assists"] = $match['participants'][$participantNumber]['stats']['assists'];
				$participantArray[$participantNumber]["deaths"] = $match['participants'][$participantNumber]['stats']['deaths'];
				$participantArray[$participantNumber]["item0"] = $match['participants'][$participantNumber]['stats']['item0'];
				$participantArray[$participantNumber]["item1"] = $match['participants'][$participantNumber]['stats']['item1'];
				$participantArray[$participantNumber]["item2"] = $match['participants'][$participantNumber]['stats']['item2'];
				$participantArray[$participantNumber]["item3"] = $match['participants'][$participantNumber]['stats']['item3'];
				$participantArray[$participantNumber]["item4"] = $match['participants'][$participantNumber]['stats']['item4'];
				$participantArray[$participantNumber]["item5"] = $match['participants'][$participantNumber]['stats']['item5'];
				$participantArray[$participantNumber]["item6"] = $match['participants'][$participantNumber]['stats']['item6'];
				$participantArray[$participantNumber]["winner"] = ($match['participants'][$participantNumber]['stats']['winner'] ? "YES" : "NO");
				$participantArray[$participantNumber]["physicalDamage"] = $match['participants'][$participantNumber]['stats']['physicalDamageDealtToChampions'];
				$participantArray[$participantNumber]["magicDamage"] = $match['participants'][$participantNumber]['stats']['magicDamageDealtToChampions'];
				$participantArray[$participantNumber]["trueDamage"] = $match['participants'][$participantNumber]['stats']['trueDamageDealtToChampions'];
				$participantArray[$participantNumber]["totalDamage"] = $match['participants'][$participantNumber]['stats']['totalDamageDealtToChampions'];
				$participantArray[$participantNumber]["totalHeal"] = $match['participants'][$participantNumber]['stats']['totalHeal'];
				$participantArray[$participantNumber]["goldEarned"] = $match['participants'][$participantNumber]['stats']['goldEarned'];
				$participantArray[$participantNumber]["minionsKilled"] = $match['participants'][$participantNumber]['stats']['minionsKilled'];
				$participantArray[$participantNumber]["neutralMinionsKilled"] = $match['participants'][$participantNumber]['stats']['neutralMinionsKilled'];
				$participantArray[$participantNumber]["neutralMinionsKilledTeam"] = $match['participants'][$participantNumber]['stats']['neutralMinionsKilledTeamJungle'];
				$participantArray[$participantNumber]["neutralMinionsKilledEnemy"] = $match['participants'][$participantNumber]['stats']['neutralMinionsKilledEnemyJungle'];
				$participantArray[$participantNumber]["towerKills"] = $match['participants'][$participantNumber]['stats']['towerKills'];
			}
			
			//loop through the two teams and bans to grab the Banned Champions
			for($teamNumber = 0; $teamNumber < 2; $teamNumber++)
			{				
				for($banNumber = 0; $banNumber < 3; $banNumber++)
				{
					if ($match['teams'][$teamNumber]["bans"])
					{
						if ($match['teams'][$teamNumber]["bans"][$banNumber]["championId"])
							$banArray[] = $match['teams'][$teamNumber]["bans"][$banNumber]["championId"];
						else
							$banArray[] = '-1';
					}
					else
						$banArray[] = '-1';
				}
			}
					
			//insert match data into MySQL's 'match' table (one row)
			$QUERY_MATCH = "INSERT INTO matches(MatchId, Region, MatchDuration, QueueType, Ban1, Ban2, Ban3, Ban4, Ban5, Ban6) VALUES($matchId, '$region', $matchDuration, '$queueType', $banArray[0], $banArray[1], $banArray[2], $banArray[3], $banArray[4], $banArray[5])";
			$result_match = mysql_query($QUERY_MATCH) or die ('Error : ' . mysql_error());
			
			//insert participant data into MySQL's 'participants' table (ten rows)
			$QUERY_PARTICIPANT = "INSERT INTO participants(ParticipantId, MatchId, Spell1Id, Spell2Id, ChampionId, TeamId, HighestAchievedSeasonTier, Role, Lane, Kills, Assists, Deaths, Item0, Item1, Item2, Item3, Item4, Item5, Item6, Winner, PhysicalDamage, MagicDamage, TrueDamage, TotalDamage, TotalHeal, GoldEarned, MinionsKilled, NeutralMinionsKilled, NeutralMinionsKilledTeam, NeutralMinionsKilledEnemy, TowerKills)";
			$rows = array(); 
			foreach($participantArray as $row)
			{
				$rows[] = '(' .$row["participantId"]. ', ' .$matchId. ', ' .$row["spell1Id"]. ', ' .$row["spell2Id"]. ', ' .$row["championId"]. ', ' .$row["teamId"]. ', "' .$row["highestAchievedSeasonTier"]. '", "' .$row["role"]. '", "' .$row["lane"]. '", ' .$row["kills"]. ', ' .$row["assists"]. ', ' .$row["deaths"]. ', ' .$row["item0"]. ', ' .$row["item1"]. ', ' .$row["item2"]. ', ' .$row["item3"]. ', ' .$row["item4"]. ', ' .$row["item5"]. ', ' .$row["item6"]. ', "' .$row["winner"]. '", ' .$row["physicalDamage"]. ', ' .$row["magicDamage"]. ', ' .$row["trueDamage"]. ', ' .$row["totalDamage"]. ', ' .$row["totalHeal"]. ', ' .$row["goldEarned"]. ', ' .$row["minionsKilled"]. ', ' .$row["neutralMinionsKilled"]. ', ' .$row["neutralMinionsKilledTeam"]. ', ' .$row["neutralMinionsKilledEnemy"]. ', ' .$row["towerKills"]. ')';
			}
			mysql_query($QUERY_PARTICIPANT .' VALUES'. implode(',', $rows) .';') or die ('Error : ' . mysql_error());
						
			//pause for 3 seconds b/w each API request, so I don't hit the rate limit
			sleep(3);
		} 
		echo "COMPLETE";
	}
	else
	{
		echo "unauthorized execution";
	}
?>