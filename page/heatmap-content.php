<?php
//header('Content-type: application/json');
$apiURL = "https://api.sap.lbasense.com/CurrentSAPValuePerRegion?";

$queryParameter = array(
    'user' => 'sitstudents',
    'pass' => 'aiurldd952jeu49r',
    'siteId' => '282');

$dataPath = $apiURL . http_build_query($queryParameter);
$content = file_get_contents($dataPath);
$content_array = json_decode($content, true);

$regionContent_array = array(
    array("region" => "KFC Restaurant at KidzWorld", "lat" => "1.40354", "lng" => "103.79683", "count" => 0),
    array("region" => "Australian Outback", "lat" => "1.40580", "lng" => "103.79314", "count" => 0),
    array("region" => "Entrance behind ticket counters", "lat" => "1.40493", "lng" => "103.79104", "count" => 0),
    array("region" => "Ah Meng Restaurant", "lat" => "1.40415", "lng" => "103.79355", "count" => 0),
    array("region" => "SPH Kiosk", "lat" => "1.40228", "lng" => "103.79597", "count" => 0),
    array("region" => "WHRC", "lat" => "1.40400", "lng" => "103.79146", "count" => 0),
    array("region" => "Amphitheatre", "lat" => "1.40460", "lng" => "103.7949", "count" => 0),
    array("region" => "Elephant Show", "lat" => "1.40527", "lng" => "103.79565", "count" => 0),
    array("region" => "Taxi Stand", "lat" => "1.40471", "lng" => "103.79049", "count" => 0)
);

//var_dump($content_array);
$last_updated = date("F j, Y, g:i a", strtotime($content_array["date"]));

$i = 0;
foreach ($content_array["sapInformation"] as $region_array) {

    $regionContent_array[$i]["count"] = $region_array["numVisitors"];
    $i++;
}

//var_dump($regionContent_array);
$countData_array = array();
$infoData_array = array();

$countData_json = '[';
$infoData_json = '[';

foreach($regionContent_array as $regionContent){
    $countData_set = '{"lat":' . $regionContent["lat"];
    $countData_set .= ', "lng":' . $regionContent["lng"];
    //$countData_set .= ", count:" . $regionContent["count"] . "}";
    $countData_set .= ', "count":' . rand(0, 1000) . "}";
    
    array_push($countData_array, $countData_set);
    
    $infoData_set = '["<b>' . $regionContent["region"] . "</b><br>";
    //$infoData_set .= 'Total Visitor: ' . $regionContent["count"] . '",';
    $infoData_set .= 'Total Visitor: ' . rand(0, 1000) . '",';
    $infoData_set .= $regionContent["lat"] . ", " . $regionContent["lng"] . "]";
    array_push($infoData_array, $infoData_set);
}

$countData_array_separated = implode(",", $countData_array);
$infoData_array_separated = implode(",", $infoData_array);

$countData_json .= $countData_array_separated . ']';
$infoData_json .= $infoData_array_separated . ']';

if(isset($_GET["content"])){
    switch($_GET["content"]){
        case 'last_updated':
            echo $last_updated;
            break;
        
        case 'countData_json':
            echo $countData_json;
            break;
        
        case 'infoData_json':
            echo $infoData_json;
            break;
        
        default:
            echo "Invalid query parameter passed in.";
            break;
    }
    
}
else{
    echo "No query parameter passed in.";
}


?>