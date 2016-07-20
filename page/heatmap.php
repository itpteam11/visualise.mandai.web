<?php
$this->layout('layout', ['title' => 'Heatmap - WRS Singapore Zoo']);

$countData_json = file_get_contents('http://localhost/wrs/page/heatmap-content.php?content=countData_json');
$planes = file_get_contents('http://localhost/wrs/page/heatmap-content.php?content=infoData_json');
$user_json = file_get_contents('http://localhost/wrs/page/user-content.php');
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
    var users = <?php echo $user_json; ?>;

    var baseLayer = L.tileLayer(
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

    var heatmapLayer = new HeatmapOverlay(cfg);

    var map = new L.Map('map', {
        center: new L.LatLng(1.40375, 103.79374),
        zoom: 17,
        layers: [baseLayer, heatmapLayer]
    });

    var SweetIcon = L.Icon.Label.extend({
        options: {
            iconUrl: 'assets/img/personal.png',
            shadowUrl: null,
            iconSize: new L.Point(24, 24),
            iconAnchor: new L.Point(0, 1),
            labelAnchor: new L.Point(26, 0),
            wrapperAnchor: new L.Point(12, 13),
            labelClassName: 'sweet-deal-label'
        }
    });

    function auto_load() {

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
            },
            error: function (xhr, status, error) {
                //alert('An error has occurred, please refresh the page.');
                //var err = eval("(" + xhr.responseText + ")");
                //alert(err.Message);
                //alert(error);
            }
        });

        $.ajax({
            url: "page/heatmap-content.php?content=infoData_json",
            cache: false,
            dataType: 'json',
            success: function (data) {
                planes = data;
                //console.log(planes);
                //alert(planes[1][0]);

                for (var i = 0; i < planes.length; i++) {
                    markers_arr[i].setPopupContent(planes[i][0])
                }
            }
        });


        $.ajax({
            url: "page/user-content.php",
            cache: false,
            dataType: 'json',
            success: function (data) {
                users = data;
                //alert(JSON.stringify(toArray(users)));
                //console.log(users[1].name);
            }
        });

        countData = {
            max: 8,
            data: countData_json
        };

        heatmapLayer.setData(countData);

        // make accessible for debugging
        layer = heatmapLayer;

    } //End auto_load()

    function toArray(obj) {
        var result = [];
        for (var prop in obj) {
            var value = obj[prop];
            if (typeof value === 'object') {
                result.push(toArray(value));
            } else {
                result.push(value);
            }
        }
        return result;
    }



    $(document).ready(function () {
        testvar = 'hello';


        /* http://stackoverflow.com/questions/9912145/leaflet-how-to-find-existing-markers-and-delete-markers */
        markers_arr = new Array();

        for (var i = 0; i < planes.length; i++) {
            marker = new L.marker([planes[i][1], planes[i][2]])
                    .bindPopup(planes[i][0])
                    .addTo(map);

            marker.on('mouseover', function (e) {
                this.openPopup();
            });
            marker.on('mouseout', function (e) {
                this.closePopup();
            });

            markers_arr.push(marker);
        }

        //var markers = new L.FeatureGroup();


        //markers.bindPopup("<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh, viverra non, semper suscipit, posuere a, pede.</p><p>Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci. Aenean dignissim pellentesque.</p>");
        //map.addLayer(markers);

        populate(map, users);
        //L.DomUtil.get('populate').onclick = populate;
        auto_load(); //Call auto_load() function when DOM is Ready
    });

    //Refresh auto_load() function after 10000 milliseconds
    setInterval(auto_load, 5000);
    //setInterval(populate(map, users), 5000);
    setInterval(function(){ populate(map, users); }, 5000);

//https://github.com/jacobtoye/Leaflet.iconlabel
    function getRandomLatLng(map) {
        var bounds = map.getBounds(),
                southWest = bounds.getSouthWest(),
                northEast = bounds.getNorthEast(),
                lngSpan = northEast.lng - southWest.lng,
                latSpan = northEast.lat - southWest.lat;
        return new L.LatLng(
                southWest.lat + latSpan * Math.random(),
                southWest.lng + lngSpan * Math.random()
                );
    }

    function populate(map, users) {
        for (var i = 0; i < users.length; i++) {
// 
            marker = new L.Marker(
                    getRandomLatLng(map),
                    {icon: new SweetIcon({labelText: users[i].name}), draggable: true}
            ).addTo(map)
                    .on('dragend', function (event) {
                        var marker = event.target;
                        var position = marker.getLatLng();
                        alert(position);
                        //marker.setLatLng(originalPosition);
                        //marker.addTo(map); //originalPosition
                    });
        }

        return false;
    }

</script>