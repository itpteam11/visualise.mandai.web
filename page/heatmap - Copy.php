<?php
$this->layout('layout', ['title' => 'Heatmap - WRS Singapore Zoo']);
        
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
    $countData_set = "{lat:" . $regionContent["lat"];
    $countData_set .= ", lng:" . $regionContent["lng"];
    $countData_set .= ", count:" . $regionContent["count"] . "}";
    array_push($countData_array, $countData_set);
    
    $infoData_set = '["<b>' . $regionContent["region"] . "</b><br>";
    $infoData_set .= 'Total Visitor: ' . $regionContent["count"] . '",';
    $infoData_set .= $regionContent["lat"] . ", " . $regionContent["lng"] . "]";
    array_push($infoData_array, $infoData_set);
}

$countData_array_separated = implode(",", $countData_array);
$infoData_array_separated = implode(",", $infoData_array);

$countData_json .= $countData_array_separated . ']';
$infoData_json .= $infoData_array_separated . ']'
        
?>
<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i>  <?= $this->e($page_title) ?></h3>
    <!-- page start-->
    Last Updated: <?php echo $last_updated; ?>
    <div id="map"></div>
    <!-- page end-->
</section>
<!--script for this page-->
<script>
    window.onload = function () {

        var countData = {
            max: 8,
            data: <?php echo $countData_json; ?>
        };

        var baseLayer = L.tileLayer(
                'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> Contributors ',
                    maxZoom: 28
                }
        );

        var planes = <?php echo $infoData_json; ?>;

        var cfg = {
            // radius should be small ONLY if scaleRadius is true (or small radius is intended)
            "radius": 0.0005,
            "maxOpacity": .9,
            // scales the radius based on map zoom
            "scaleRadius": true,
            // if set to false the heatmap uses the global maximum for colorization
            // if activated: uses the data maximum within the current map boundaries 
            //   (there will always be a red spot with useLocalExtremas true)
            "useLocalExtrema": true,
            // which field name in your data represents the latitude - default "lat"
            latField: 'lat',
            // which field name in your data represents the longitude - default "lng"
            lngField: 'lng',
            // which field name in your data represents the data value - default "value"
            valueField: 'count'
        };


        var heatmapLayer = new HeatmapOverlay(cfg);


        var map = new L.Map('map', {
            center: new L.LatLng(1.40375, 103.79374),
            zoom: 17,
            layers: [baseLayer, heatmapLayer]
        });

        heatmapLayer.setData(countData);

        for (var i = 0; i < planes.length; i++) {
            markers = new L.marker([planes[i][1], planes[i][2]])
                    .bindPopup(planes[i][0])
                    .addTo(map);
            
                    markers.on('mouseover', function (e) {
            this.openPopup();
        });
        markers.on('mouseout', function (e) {
            this.closePopup();
        });
        }

        // make accessible for debugging
        layer = heatmapLayer;
        

    };

</script>
