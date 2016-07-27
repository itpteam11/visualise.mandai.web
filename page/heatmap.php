<?php
$this->layout('layout', ['title' => 'Heatmap - WRS Singapore Zoo']);

//$_SERVER['HTTP_HOST'] == 'localhost:8080'
//$_SERVER['SERVER_NAME'] == 'localhost'
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . '/wrs/page/';
$countData_json = file_get_contents($baseURL . 'heatmap-content.php?content=countData_json');
$planes = file_get_contents($baseURL . 'heatmap-content.php?content=infoData_json');
$user_json = file_get_contents($baseURL . 'user-content.php');
$region_json = file_get_contents($baseURL . 'heatmap-content.php?content=region_json');
?>
<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i>  <?= $this->e($page_title) ?></h3>
    <!-- page start-->
    <p>Last Updated: <span id="last_updated">NIL</span></p>
    <div id="map"></div>
    <!-- page end-->
</section>

<!--script for this page-->
<script src="https://www.gstatic.com/firebasejs/live/3.0/firebase.js"></script>
<script>

    $(document).ready(function () {

        /* http://stackoverflow.com/questions/9912145/leaflet-how-to-find-existing-markers-and-delete-markers */
        //var markers = new L.FeatureGroup();
        //markers.bindPopup("<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh, viverra non, semper suscipit, posuere a, pede.</p><p>Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci. Aenean dignissim pellentesque.</p>");
        //map.addLayer(markers);
        countData_json = <?php echo $countData_json; ?>;
        planes = <?php echo $planes; ?>;
        users = <?php echo $user_json; ?>;
        // Initialize Firebase
        var config = {
            apiKey: "AIzaSyDHk-JZlTUWkaYv9l-1h2qNTAss_S-lzoc",
            authDomain: "visualise-mandai.firebaseapp.com",
            databaseURL: "https://visualise-mandai.firebaseio.com",
            storageBucket: "",
        };
        firebase.initializeApp(config);
        // Get a reference to the database service
        database = firebase.database().ref("/");
        userMarkers_arr = [];
        markerAssigned = [];
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
            latField: 'lat',
            lngField: 'lng',
            valueField: 'count'
        };
        heatmapLayer = new HeatmapOverlay(cfg);
        map = new L.Map('map', {
            center: new L.LatLng(1.4037706522911413, 103.79240155220032),
            zoom: 17,
            zoomControl: true,
            layers: [baseLayer, heatmapLayer]
        });
        map.zoomControl.setPosition('topright');
        var MarkerIcon = L.Icon.extend({
            options: {
                iconSize: [38, 38],
                iconAnchor: [20, 34],
                popupAnchor: [0, -20]
            }
        });
        greenIcon = new MarkerIcon({iconUrl: 'assets/img/marker-green.png'}),
                redIcon = new MarkerIcon({iconUrl: 'assets/img/marker-pink.png'});
        staffIcon = L.Icon.Label.extend({
            options: {
                iconUrl: 'assets/img/personal.png',
                shadowUrl: null,
                iconSize: new L.Point(24, 24),
                iconAnchor: new L.Point(0, 1),
                labelAnchor: new L.Point(26, 0),
                wrapperAnchor: new L.Point(12, 13)
            }
        });
        staffAssignedIcon = L.Icon.Label.extend({
            options: {
                iconUrl: 'assets/img/pin-red.png',
                shadowUrl: null,
                iconSize: new L.Point(34, 34),
                iconAnchor: new L.Point(0, 1),
                labelAnchor: new L.Point(36, 0),
                wrapperAnchor: new L.Point(12, 13)
            }
        });
        // control that shows state info on hover
        var info = L.control({position: 'topleft'});
        info.onAdd = function (map) {
            this._div = L.DomUtil.create('div', 'info');
            this.update();
            return this._div;
        };
        info.update = function (props) {
            this._div.innerHTML = '<p>Loading Threshold...</p>' +
                    '<div class="centered"><img src="assets/img/spinner.gif" alt="Loading" height="42" width="42"></div>';
        };
        info.addTo(map);

        //Start - Call those functions when DOM is Ready
        populateRegionMarker(map, planes);
        populate(map, users, true);
        auto_load();
        //End

        //Start - Refresh those functions function after 10000 milliseconds
        setInterval(auto_load, 60000);
        setInterval(function () {
            populateRegionMarker(map, planes);
        }, 60000);
        setInterval(function () {
            populate(map, users, false);
        }, 60000);
        //End
    });
    function auto_load() {
        //To get last updated time
        $.ajax({
            url: "page/heatmap-content.php?content=last_updated",
            cache: false,
            success: function (data) {
                $("#last_updated").html(data);
            }
        });
        //To get visitor count for heatmap
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
        //To get visitor count and threshold value for info panel
        $.ajax({
            url: "page/heatmap-content.php?content=threshold",
            cache: false,
            success: function (data) {
                $(".info").html(data);
            }
        });
        //To get visitor count and manpower count for pop up,
        //threshold status for alert
        $.ajax({
            url: "page/heatmap-content.php?content=infoData_json",
            cache: false,
            dataType: 'json',
            success: function (data) {
                planes = data;
            }
        });
        //To get all users currently signed in for marker display
        $.ajax({
            url: "page/user-content.php",
            cache: false,
            dataType: 'json',
            success: function (data) {
                users = data;
                //alert(JSON.stringify(toArray(users)));
            }
        });
        //For heatmap
        countData = {
            max: 8,
            data: countData_json
        };
        heatmapLayer.setData(countData);
        // make accessible for debugging
        //layer = heatmapLayer;
        //End for heatmap

    } //End auto_load()


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
    function populateRegionMarker(map, planes) {
        var notify = false;
        var alertMsg = "The following region(s) exceeded the threshold:\n";
        markers_arr = [];
        //Iterate all the region to add info to popup panel
        for (var i = 0; i < planes.length; i++) {

            // If current footfall is less than threshold, show green marker
            if (planes[i].count < planes[i].threshold) {
                var marker = new L.marker([planes[i].lat, planes[i].lng], {icon: greenIcon, riseOnHover: true});
            }
            // else show red marker
            else {
                var marker = new L.marker([planes[i].lat, planes[i].lng], {icon: redIcon, riseOnHover: true});
            }

            marker.bindPopup(planes[i].content, {closeButton: false}).addTo(map);
            //Automatically pop up the info panel when mouse over
            marker.on('mouseover', function (e) {
                this.openPopup();
            });
            marker.on('mouseout', function (e) {
                this.closePopup();
            });
            markers_arr.push(marker);
            if (planes[i].status != '') {
                alertMsg += '- ' + planes[i].status;
                notify = true;
            }
        } //End for loop

        //Send notification if above threshold
        if (notify == true) {
            //alertMsg += 'Last Update: ' + new Date(new Date().getTime()).toLocaleTimeString();
            //alert(alertMsg);

            firebase.database().ref("/user/")
                    .orderByChild('type')
                    //.equalTo('staff')
                    .once('value').then(function (snapshot) {

                //If there is at least a user
                if (snapshot.numChildren() > 0) {
                    var userObj = snapshot.val();
                    // Get a key for a new Notification.
                    var newNotificationKey = database.child('notification').push().key;
                    //console.log('New notification key is ' + newNotificationKey);

                    //Update the notification content first
                    var updates = {};
                    updates['/notification/' + newNotificationKey + '/sender'] = 'SG Zoo';
                    //updates['/notification/' + newNotificationKey + '/receiver/' + marker.userID] = false;
                    updates['/notification/' + newNotificationKey + '/content'] = alertMsg;
                    updates['/notification/' + newNotificationKey + '/timestamp'] = firebase.database.ServerValue.TIMESTAMP;
                    firebase.database().ref().update(updates);
                    //Iterate all the users    
                    for (var userID in userObj) {
                        console.log(userID);
                        var updates = {};
                        updates['/notification-lookup/' + userID + '/receive/' + newNotificationKey] = false;
                        firebase.database().ref().update(updates);
                    }

                }
            });
        } // End if
    }

    function populate(map, users, firstRun) {
        //if this function is called second time, then remove all the user markers
        if (firstRun == false) {
            for (i = 0; i < userMarkers_arr.length; i++) {
                //console.log("firstRun false");
                map.removeLayer(userMarkers_arr[i]);
            }
        }

        //Iterate all the users to assign markers on map
        for (var i = 0; i < users.length; i++) {
            var markerStaff = new L.Marker(
                    //getRandomLatLng(map), //For demo purpose
                    new L.LatLng(users[i].latitude, users[i].longitude),
                    {icon: new staffIcon({labelText: users[i].name}), draggable: true, riseOnHover: true}
            ).addTo(map); // End Marker

            markerStaff.userID = users[i].userid;
            markerStaff.username = users[i].name;
            markerStaff.type = users[i].type;
            markerStaff.status = users[i].status;
            markerStaff.latitude = users[i].latitude;
            markerStaff.longitude = users[i].longitude;

            markerStaff.on('dragstart', function (event) {
                var marker = event.target;
                originalPosition = marker.getLatLng();
            });
            markerStaff.on('dragend', function (event) {
                var marker = event.target;
                var newPosition = marker.getLatLng();

                var assignedRegion = getNearestRegion(newPosition.lat, newPosition.lng);

                //If it is a valid region
                if (assignedRegion != 'Outside region') {

                    var question = 'Do you want to assign ' + marker.username + ' to ' + assignedRegion + '?';
                    question += "\nOtherwise, click 'Cancel' button.";
                    question += "\n\nMessage to be sent:";
                    msg = 'Please go to "' + assignedRegion + '" to manage the crowd now.';
                    var notificationMsg = prompt(question, msg);
                    //if user click on 'OK' in the prompt dialog             
                    if (notificationMsg != null) {

                        if (marker.userID in markerAssigned) {
                            map.removeLayer(markerAssigned[marker.userID]);
                        }
                        markerAssigned[marker.userID] = new L.Marker(
                                newPosition,
                                {icon: new staffAssignedIcon({labelText: "Assigned to " + marker.username}), riseOnHover: true}
                        ).addTo(map); // End Marker


                        var updates = {};
                        updates['/user/' + markerStaff.userID + '/assigned_region'] = assignedRegion;
                        updates['/user/' + markerStaff.userID + '/assigned_lat'] = newPosition.lat;
                        updates['/user/' + markerStaff.userID + '/assigned_lng'] = newPosition.lng;
                        updates['/user/' + markerStaff.userID + '/assigned_timestamp'] = firebase.database.ServerValue.TIMESTAMP;
                        firebase.database().ref().update(updates);
                        // Get a key for a new Notification.
                        var newNotificationKey = database.child('notification').push().key;
                        console.log('New notification key is ' + newNotificationKey);
                        var updates = {};
                        updates['/notification-lookup/' + marker.userID + '/receive/' + newNotificationKey] = false;
                        updates['/notification/' + newNotificationKey + '/sender'] = 'SG Zoo';
                        //updates['/notification/' + newNotificationKey + '/receiver/' + marker.userID] = false;
                        updates['/notification/' + newNotificationKey + '/content'] = notificationMsg;
                        updates['/notification/' + newNotificationKey + '/latitude'] = newPosition.lat;
                        updates['/notification/' + newNotificationKey + '/longitude'] = newPosition.lng;
                        updates['/notification/' + newNotificationKey + '/timestamp'] = firebase.database.ServerValue.TIMESTAMP;
                        firebase.database().ref().update(updates);
                        alert('A notification has been sent.');
                        //alert(markerStaff.username + ' has been assigned to ' + assignedRegion + ' and a message has been sent.');
                    } //End if
                }
                //Not a valid region
                else {
                    //Reset the marker back to original position
                    //markerStaff.setLatLng(originalPosition);
                    alert('This is not a valid region, please try dragging near to the marker again.');
                }
                //Reset the marker back to original position
                populate(map, users, false);
            }); //End Marker Dragend event

            //console.log(marker.username);
            userMarkers_arr.push(markerStaff);
        } //End for loop

        return false;
    } //End function populate
    /*
     * Base on Haversine Formula to get distance between 2 lat lng coordinates
     * Reference http://www.codecodex.com/wiki/Calculate_Distance_Between_Two_Points_on_a_Globe#JavaScript
     */
    function getDistance(lat1, lon1, lat2, lon2) {
        //var R = 6371; // km  
        var R = 6371000; // km  
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.asin(Math.sqrt(a));
        var d = R * c;
        return d;
    }

    function getNearestRegion(userLat, userLon) {
        var regionContent_array = <?php echo $region_json; ?>;
        //iterate through all the regions
        for (var i = 0; i < regionContent_array.length; i++) {
            var regionLat = regionContent_array[i].lat;
            var regionLon = regionContent_array[i].lng;
            var distanceMeter = getDistance(regionLat, regionLon, userLat, userLon);
            //if the distance is within the region radius, then return region name
            if (distanceMeter <= regionContent_array[i].radius) {
                return regionContent_array[i].region;
            }
        }
        //else
        return 'Outside region';
    }
</script>