<?php

// Turn off error reporting
error_reporting(0);

require_once '../lib/firebaseLib.php';
require_once 'constant/firebase-setting.php';
require_once 'constant/lbasense-setting.php';
        const DEFAULT_PATH = '/region-setting';

$errorFlag = false;
$dataPath = getSAPValuePerRegionURL_api();
$SAP_json = file_get_contents($dataPath);
$content_array = json_decode($SAP_json, true);

if ($SAP_json === false) {
    //There is an error in accessing the API URL
    $errorFlag = true;
    
    $last_updated = 'N/A<div class="alert alert-danger">Sorry, the server is down.</div>';
}
else{
    $last_updated = date("F j, Y, g:i a", strtotime($content_array["date"]));
}

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

// --- reading the stored string ---
$regionContent_json = $firebase->get(DEFAULT_PATH);
$regionContent_array = json_decode($regionContent_json, true);

//Shift an element "Entire Site" off the beginning of array
array_shift($regionContent_array);

$countData_array = array();
$infoData_array = array();

$countData_json = '[';
$infoData_json = '[';

$tableHTML = '<table>' .
        '<tr><th>Region</th><th>Threshold</th></tr>';

$statusMsg = null;

$i = 0;
foreach ($regionContent_array as $regionContent) {

    //Start - Data for heatmap
    //if entire site, then assign zero to visitor count as this entire site
    //value is not available in this array and will not be shown
    //Or if the API URL is down, then set all region visitor count to zero
    if ($i == 0 || $errorFlag == true) {
        $visitorCount = 0;
    } else {
        $visitorCount = $content_array["sapInformation"][$i - 1]["numVisitors"];
    }

    $countData_set = '{"lat":' . $regionContent["lat"];
    $countData_set .= ', "lng":' . $regionContent["lng"];
    $countData_set .= ", count:" . $visitorCount . "}";
    
    //For demo purpose
    //$countData_set .= ', "count":' . rand(0, 1000) . "}";

    array_push($countData_array, $countData_set);
    //End - Data for heatmap
    //Start - Data for popup
    $infoData_set = '{"content":"<b>' . $regionContent["region"] . '</b><br>';
    $infoData_set .= 'Total Visitor: ' . $visitorCount . '",';
    
    //For demo purpose
    //$infoData_set .= 'Total Visitor: ' . rand(0, 1000) . '",';
    
    $infoData_set .= '"lat":' . $regionContent["lat"] . ', "lng":' . $regionContent["lng"] . ', ';
    $infoData_set .= '"count":' . $visitorCount . ', "threshold":' . $regionContent_array[$i]['threshold'] . ',';
    //if visitor count reached/exceeded the set threshold
    if ($visitorCount >= $regionContent_array[$i]['threshold']) {
        $statusMsg = $regionContent["region"] . ' (' . $visitorCount . '/' . $regionContent_array[$i]['threshold'] . ")\\n";
    }

    $infoData_set .= '"status":"' . $statusMsg . '"}';
    array_push($infoData_array, $infoData_set);
    $statusMsg = null;

    //End - Data for popup
    //Start - Threshold Table HTML
    
    //For demo purpose
    //$visitorCount = rand(0, 1000);    
    
    $cssColor = 'text-success';
    if ($visitorCount >= $regionContent_array[$i]['threshold']) {
        $cssColor = 'text-danger';
    }

    $tableHTML .= '<tr><td width="75%">' . $regionContent["region"] . '</td>';
    $tableHTML .= '<td class="centered" width="25%"><span class="' . $cssColor . '">' . $visitorCount . ' / ';
    $tableHTML .= $regionContent_array[$i]['threshold'] . '</span></td></tr>';
    //End - Threshold Table HTML

    $i++;
} //End foreach

$tableHTML .= '</table>';

$countData_array_separated = implode(",", $countData_array);
$infoData_array_separated = implode(",", $infoData_array);

$countData_json .= $countData_array_separated . ']';
$infoData_json .= $infoData_array_separated . ']';

if (isset($_GET["content"])) {
    switch ($_GET["content"]) {
        case 'last_updated':
            echo $last_updated;
            break;

        case 'countData_json':
            echo $countData_json;
            break;

        case 'infoData_json':
            echo $infoData_json;
            break;

        case 'region_json':
            echo $regionContent_json;
            break;

        case 'threshold':
            echo $tableHTML;
            break;

        default:
            echo "Invalid query parameter passed in.";
            break;
    } //End Switch    
} else {
    echo "No query parameter passed in.";
}
?>