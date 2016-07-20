<?php
$this->layout('layout', ['title' => 'Heatmap - WRS Singapore Zoo']);

$countData_json = file_get_contents('http://localhost/wrs/page/heatmap-content.php?content=countData_json');
$planes = file_get_contents('http://localhost/wrs/page/heatmap-content.php?content=infoData_json');
?>
<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i>  <?= $this->e($page_title) ?></h3>
    <!-- page start-->
    Last Updated: <span id="last_updated">NIL</span>
    <div id="map"></div>
    <!-- page end-->
</section>

<!--script for this page-->
<script>
    var countData_json = <?php echo $countData_json; ?>;
    var planes = <?php echo $planes; ?>;
    var baseLayer;
    var heatmapLayer;
    var map;

    function auto_load() {
        //countData_json = <?php echo $countData_json; ?>;
        //planes = <?php echo $planes; ?>;

        $.ajax({
            url: "page/heatmap-content.php?content=last_updated",
            cache: false,
            success: function (data) {
                $("#last_updated").html(data);
            }
        });

        $.ajax({
            url: "page/heatmap-content.php?content=countData_json",
            cache: false,
            dataType: 'json',
            success: function (data) {
                countData_json = data;
                //console.log(countData_json);

                var countData = {
                    max: 8,
                    data: countData_json
                };
                heatmapLayer.setData(countData);
                //heatmapLayer.redraw();
            }
        });

        $.ajax({
            url: "page/heatmap-content.php?content=infoData_json",
            cache: false,
            dataType: 'json',
            success: function (data) {
                planes = data;
                //console.log(planes);
                        
alert(planes[1][0]); 
//console.log(planes[1][0]);

                for (var i = 0; i < planes.length; i++) {
                    L.marker([planes[i][1], planes[i][2]])
                            .setPopupContent(planes[i][0]);
                }

            }
        });



        // make accessible for debugging
        layer = heatmapLayer;

        // Causes the layer to clear all the tiles and request them again.
        //baseLayer.redraw();

        //alert(layer);
        //console.log(layer);
    }

    $(document).ready(function () {
        baseLayer = L.tileLayer(
                'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> Contributors ',
                    maxZoom: 28
                }
        );



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


        heatmapLayer = new HeatmapOverlay(cfg);

        map = new L.Map('map', {
            center: new L.LatLng(1.40375, 103.79374),
            zoom: 17,
            layers: [baseLayer, heatmapLayer]
        });
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
            //alert(planes[i][1] + " - " + planes[i][2]);
        }

        auto_load(); //Call auto_load() function when DOM is Ready
    });

    //Refresh auto_load() function after 10000 milliseconds
    setInterval(auto_load, 3000);

</script>