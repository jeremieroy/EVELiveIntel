<?php
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 
 * This file generate json files of eve online factions and eve online systems data from a mysql eve online static dump database 
 */

$db = new mysqli('localhost', 'root', '', 'eve_static_dump');


function compute_bounds($array)
{
	$minX = INF;
	$maxX = -INF;
	$minY = INF;
	$maxY = -INF;
	foreach ($array as &$p) {
	 	if($p["x"] < $minX) $minX = $p["x"];
	    if($p["x"] > $maxX) $maxX = $p["x"];
	    if($p["y"] < $minY) $minY = $p["y"];
	    if($p["y"] > $maxY) $maxY = $p["y"];
	}

	/*
	$w =  $maxX -  $minX;
	$h =  $maxY -  $minY;
	$scale_size = 1.0 / max($w,$h);

	//normalize and invert axes
	foreach ($array as &$p)	{    
	    $p["x"] = ($p["x"] - $minX)*$scale_size;
	    $p["y"] = ($h - $p["y"] + $minY)*$scale_size;
	}
	$w*=$scale_size;
	$h*=$scale_size;
	*/

	return array( "min_x"=>$minX,
				  "max_x"=>$maxX,
				  "min_y"=>$minY,
				  "max_y"=>$maxY);
}

function dump($filename, $res)
{
	file_put_contents( $filename,json_encode($res) );
	echo "dumped: $filename<br/>";
}

$faction_map = array();
$regions_map = array();
$system_map = array();

function init_maps($db)
{
	global $faction_map, $regions_map, $system_map;

	$sql = "SELECT factionID AS id FROM chrfactions WHERE 1 ORDER BY factionID";
	$result = mysqli_query($db, $sql);
	
	$count = 0;
	while($item = mysqli_fetch_assoc( $result )) {
		$id = intval($item['id']);
		$faction_map[$id] = $count;
		$count++;
	}
	echo "factions count= $count<br/>";	
	//*********************************************
	$sql = "SELECT regionID AS id FROM mapRegions WHERE 1 ORDER BY regionID";
	$result = mysqli_query($db, $sql);
	
	$count = 0;
	while($item = mysqli_fetch_assoc( $result )) {
		$id = intval($item['id']);
		$regions_map[$id] = $count;
		$count++;
	}
	echo "regions count= $count<br/>";
	//*********************************************
	$sql = "SELECT solarSystemID AS id FROM mapSolarSystems WHERE 1 ORDER BY solarSystemID";
	$result = mysqli_query($db, $sql);
	
	$count = 0;
	$d = array();
	while($item = mysqli_fetch_assoc( $result )) {
		$id = intval($item['id']);
		$system_map[$id] = $count;
		$count++;
		array_push($d, $id);
	}	
	echo "systems count= $count<br/>";
}

$faction_offset = 500001;
$regions_KS_offset = 10000001;
$regions_WS_offset = 11000001;
$system_KS_offset = 30000001;

function get_factions($db)
{
	global $faction_offset;	
	$sql = "SELECT factionID AS id,
				   factionName AS name,
	               description AS description
	               FROM chrfactions
	               WHERE 1 ORDER BY factionID ";
	
	$result = mysqli_query($db, $sql);
	$factions_array = array();
	while($faction = mysqli_fetch_assoc( $result )) {
		$faction['id'] = intval($faction['id'] -$faction_offset );
		array_push($factions_array, $faction);
	}

	$out = array();	
	$out["faction_offset"] = $faction_offset;
	$out["factions"] = $factions_array;
	return $out;
}

function get_KS_regions($db)
{
	global $faction_offset, $regions_KS_offset, $regions_WS_offset,$system_KS_offset;
	$sql = "SELECT regionID AS id,
				   regionName AS name,
				   factionID AS factionID,
	               ROUND(x) / 10000000000000000 AS x,
	               ROUND(z) / 10000000000000000 AS y
	               FROM mapRegions
	               WHERE regionID < $regions_WS_offset";

	$result = mysqli_query($db, $sql);
	$regions_array= array();
	while($region = mysqli_fetch_assoc( $result )) {
		$region_out = array();
		$region_out['id'] = intval($region['id'] - $regions_KS_offset);
		$region_out['name'] = $region['name'];
		$region_out['x'] = floatval($region['x']);
		$region_out['y'] = floatval($region['y']);
		if($region['factionID']!=NULL)
		{
			$region_out['factionID'] = intval($region['factionID'] - $faction_offset);
		}else
		{
			$region_out['factionID'] = -1;
		}
		$region_out['systems'] = get_system($db, $region, $system_KS_offset );
		array_push($regions_array, $region_out);
	}

	$out = array();
	$out["region_offset"] = $regions_KS_offset;
	$out["system_offset"] = $system_KS_offset;
	$out["faction_offset"] = $faction_offset;
	$out["regions"] = $regions_array;
	return $out;
}

function getSystemsLinks($db, $system_id, $system_offset) {
    $sql = "SELECT toSolarSystemID AS id FROM mapSolarSystemJumps WHERE fromSolarSystemID = $system_id";
    $result = mysqli_query($db, $sql);
	$jumps = array();
	while($system = mysqli_fetch_assoc( $result )) {
		array_push($jumps, intval( $system['id'] -  $system_offset));
    }
    return $jumps;
}

function get_system($db, $region, $system_offset)
{
	global $faction_offset;

	$sql = "SELECT solarSystemID AS id,
	               solarSystemName AS name,
	               factionID AS factionID,
	               security AS security,
	               ROUND(x) / 10000000000000000 AS x,
	               ROUND(z) / 10000000000000000 AS y
	               FROM mapSolarSystems
	               WHERE regionID=".$region['id'];

	$result = mysqli_query($db, $sql);

	$system_array= array();
	while($system = mysqli_fetch_assoc( $result )) {
		$system_out = array();
		
		$system_out['id'] = intval( $system['id'] - $system_offset);
		$system_out['name'] = $system['name'];
		$system_out['x'] = floatval($system['x']);
		$system_out['y'] = floatval($system['y']);
		$system_out['sec']= floatval($system['security']);

		if($system['factionID'] != $region['factionID'])
		{			
			if($system['factionID']!=NULL)
			{
				$system_out["factionID"] = intval($system['factionID'] - $faction_offset);
			}else{
				$system_out["factionID"] = -1;
			}
		}
		$system_out['links'] = getSystemsLinks($db, $system['id'],  $system_offset );
		
		
		array_push($system_array, $system_out);
	}
	return $system_array;
}

?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
</head>
<body>
<script>
</script>

<?php
init_maps($db);

$factions = get_factions($db);
dump("../data/factions.json", $factions );

$regions = get_KS_regions($db);
dump("../data/region_KS.json", $regions );

//$regions["bounds"] = compute_bounds($regions["items"]);
/*
$regions = get_WS_regions($db);
$regions["bounds"] = compute_bounds($regions["items"]);
dump("../data/region_WS.json", $regions );
*/

?>

</body>
