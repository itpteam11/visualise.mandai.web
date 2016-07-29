<?php $this->layout('layout', ['title' => 'Dashboard - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/Forecast.php';
require_once 'page/constant/forecast-setting.php';
require_once 'page/constant/lbasense-setting.php';

/* Get all the region name via DRFC API call */
$dataPath = getAllRegionURL_api();
$regionName_json = file_get_contents($dataPath);
$regionName = json_decode($regionName_json);
$regionName = get_object_vars($regionName);
$regionNameNoKey_json = json_encode(array_values($regionName));
/* End */

/* Set default date for the past 7 days */
$sevendayago = date("Y-m-d", strtotime("-7 days"));
$fromDate = $sevendayago;
$toDate = date("Y-m-d");

function sec_to_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor($seconds % 3600 / 60);
    $seconds = $seconds % 60;

    if($hours > 0){
        return sprintf("%d Hr %02d Min %02d Sec", $hours, $minutes, $seconds);
    }
    //else
    return sprintf("%02d Min %02d Sec", $minutes, $seconds);
}

//Set default date range when page is first loaded
if (!isset($_POST['fromDate']) && !isset($_POST['toDate'])) {
    $_POST['fromDate'] = $fromDate;
    $_POST['toDate'] = $toDate;
}

//Based on user date range input
if (isset($_POST['fromDate']) && isset($_POST['toDate'])) {
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];

    //Convert the date string into proper DateTime object
    $begin = new DateTime($fromDate);
    $end = new DateTime($toDate);
    //$end = $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval, $end);

    /* Get past weather data via Forecast API call */
    $daterange_array = array('x');
    $tempMin_array = array('Min. Temperature');
    $tempMax_array = array('Max. Temperature');
    $tempAvg_array = array('Avg. Temperature');

    // Build requests for current time each month in last 75 years
    $requests = array();

    $totalDayNum = 0;
    foreach ($daterange as $date) {
        $dateString = $date->format("Y-m-d");
        array_push($daterange_array, $dateString);

        // New DateTime Object
        $dateObject = new DateTime($date->format("Y-m-d"));
        $dateObject->add(new DateInterval('P1D'));

        $requests[] = array(
            'latitude' => LAT_SGZoo,
            'longitude' => LNG_SGZoo,
            'time' => strtotime($date->format("Y-m-d")),
            'units' => 'si',
            'timezone' => TIMEZONE
        );
        $totalDayNum++;
    }

    // Make requests to the API
    $responses = $forecast->getData($requests);
    $i = 0;
    foreach ($responses as $response) {

        if ($daily = $response->getDaily()) {
            $daily = $daily[$i];
            $time = date("Y-m-d", $daily->getTime());

            $tempMin = $daily->getTemperatureMin();
            $tempMax = $daily->getTemperatureMax();
            $tempAvg = ($tempMin + $tempMax) / 2;

            $summary = $daily->getSummary();

            array_push($tempMin_array, $tempMin);
            array_push($tempMax_array, $tempMax);
            array_push($tempAvg_array, $tempAvg);
        }
    }
    //array('x','..','..',....)
    //array_unshift($daterange_array, 'x');
    //["x","..","..",....]
    $daterange_json = json_encode($daterange_array);

    $tempMin_json = json_encode($tempMin_array);
    $tempAvg_json = json_encode($tempAvg_array);
    $tempMax_json = json_encode($tempMax_array);
    /* End - Get past weather data */


    /* Get Footfall data via DFRC API call */
    $dataPath = getStatsSummaryURL_api($fromDate, $toDate);
    $footfall_json = file_get_contents($dataPath);
    $footfall_array = json_decode($footfall_json, true);

    /* To store and format all data for each region in a sequential order */
    $numVisitors_arrayAll = array();
    $numReturningVisitors_arrayAll = array();
    $avgDuration_arrayAll = array();

    /* Iterate all the region to get footfall for each region */
    $i = 0;
    foreach ($footfall_array as $region_array) {

        $numVisitors_array = array($region_array['regionName']);
        $numReturningVisitors_array = array($region_array['regionName']);
        $avgDuration_array = array($region_array['regionName']);

        //if region is Elephant Show
        if ($region_array['regionId'] == 8) {
            if (isset($region_array['summaryStats'][0]) && $region_array['summaryStats'][0]['date'] == '2016-07-04') {

                //Find out the number of days from start date to the date 
                //which the sensor started operating
                $date1 = new DateTime($fromDate);
                $date2 = new DateTime('2016-07-04');
                $diffDayNum = $date2->diff($date1)->format("%a");

                //Prepend value with 0 based on the number of days sensor not operating
                for ($n = 0; $n < $diffDayNum; $n++) {
                    array_push($numVisitors_array, 0);
                    array_push($numReturningVisitors_array, 0);
                    array_push($avgDuration_array, 0);
                }
            }
        }//End if
        //if region is Taxi Stand
        if ($region_array['regionId'] == 9) {
            if (isset($region_array['summaryStats'][0]) && $region_array['summaryStats'][0]['date'] == '2016-06-22') {

                //Find out the number of days from start date to the date 
                //which the sensor started operating
                $date1 = new DateTime($fromDate);
                $date2 = new DateTime('2016-06-22');
                $diffDayNum = $date2->diff($date1)->format("%a");

                //Prepend value with 0 based on the number of days sensor not operating
                for ($n = 0; $n < $diffDayNum; $n++) {
                    array_push($numVisitors_array, 0);
                    array_push($numReturningVisitors_array, 0);
                    array_push($avgDuration_array, 0);
                }
            }
        }//End if

        $totalVisitor = 0;
        $totalReturningVisitor = 0;
        $totalAvgDuration = 0;
        //Iterate all the value of each date for a single region
        foreach ($region_array['summaryStats'] as $regionDate_array) {

            array_push($numVisitors_array, $regionDate_array['numVisitors']);
            array_push($numReturningVisitors_array, $regionDate_array['numReturningVisitors']);
            array_push($avgDuration_array, $regionDate_array['avgDuration']);

            $regionName = $region_array['regionName'];
            $totalVisitor += $regionDate_array['numVisitors'];
            $totalReturningVisitor += $regionDate_array['numReturningVisitors'];
            $totalAvgDuration += $regionDate_array['avgDuration'];
        }
        $regionTotalVisitor_array[$regionName] = $totalVisitor;
        $regionReturningVisitor_array[$regionName] = $totalReturningVisitor;
        $regionAvgDuraton_array[$regionName] = $totalAvgDuration;

        //push each region array value into all container array
        array_push($numVisitors_arrayAll, $numVisitors_array);
        array_push($numReturningVisitors_arrayAll, $numReturningVisitors_array);
        array_push($avgDuration_arrayAll, $avgDuration_array);

        $i++;
    }//End foreach
    
    //Remove the first region "Entire Site"
    array_shift($numVisitors_arrayAll);
    array_shift($numReturningVisitors_arrayAll);
    array_shift($avgDuration_arrayAll);
    array_shift($regionTotalVisitor_array);
    array_shift($regionReturningVisitor_array);
    array_shift($regionAvgDuraton_array);
    
    //Start - Sum up the total to be displayed in "Summary" section
    //Find out the highest value in the array
    $mostVisitorRegion = array_search(max($regionTotalVisitor_array), $regionTotalVisitor_array);
    $mostVisitorRegion_num = $regionTotalVisitor_array[$mostVisitorRegion];

    $mostReturningVisitorRegion = array_search(max($regionReturningVisitor_array), $regionReturningVisitor_array);
    $mostReturningVisitorRegion_num = $regionReturningVisitor_array[$mostReturningVisitorRegion];

    $mostAvgDurationRegion = array_search(max($regionAvgDuraton_array), $regionAvgDuraton_array);
    //Get the total value which is incorrect
    //$mostAvgDurationRegion_num = $regionAvgDuraton_array[$mostAvgDurationRegion];
    //Find the highest value
    $mostAvgDurationRegion_num = max($avgDuration_array);
    //End
} //End if post

/* Format the data to be used by the C3.js chart API */
$daterangeAll = $daterange_json . ",\n";
$weather_json = $tempMin_json . "," . $tempAvg_json . "," . $tempMax_json;

$totalVisitorAll_json = substr(json_encode($numVisitors_arrayAll), 1, -1) . ",\n";
$totalVisitor_json = '[' . $daterangeAll . $totalVisitorAll_json . $weather_json . "]";

$returningVisitorAll_json = substr(json_encode($numReturningVisitors_arrayAll), 1, -1) . ",\n";
$returningVisitor_json = '[' . $daterangeAll . $returningVisitorAll_json . $weather_json . "]";
;

$avgDurationAll_json = substr(json_encode($avgDuration_arrayAll), 1, -1) . ',';
$avgDuration_json = '[' . $daterangeAll . $avgDurationAll_json . $weather_json . "]";
;
/* End */

//Convert to UNIX timestamp for Firebase query
$fromDate_timestamp = strtotime($fromDate);
$toDate_timestamp = strtotime($toDate);
?>

<section class="wrapper site-min-height">
    <h3><i class="fa fa-angle-right"></i> <?= $this->e($page_title) ?></h3>
    <!-- page start-->
    <div class="row mt">
        <div class="col-lg-12">
            <!-- -- 1st ROW OF PANELS ---->
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3 mb">
                    <div class="form-panel">
                        <div class="white-header">
                            <h4 class="mb"><i class="fa fa-angle-right"></i> Date Selection</h4>
                        </div>
                        <form class="form-horizontal style-form" action="" method="post">
                            <div class="form-group">
                                <label class="col-sm-2 col-sm-2 control-label">From </label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="fromDate" id="txtFromDate" value="<?php echo $fromDate; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-sm-2 control-label">To </label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="toDate" id="txtToDate" value="<?php echo $toDate; ?>">
                                </div>
                            </div>
                            <div class="steps">
                                <input type="submit" value="Go">
                            </div>
                        </form>      		
                    </div><!-- /form-panel -->
                    <div class="form-panel">
                        <h4 class="mb"><i class="fa fa-angle-right"></i> Summary in <?php echo $totalDayNum; ?> Day(s)</h4>
                        <form id="score" class="form-horizontal style-form" action="" method="get">
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-4 centered">
                                    <img src="assets/img/teamwork.png" alt="Crowded" align="middle" height="42" width="42"><br>
                                    <label>Most Crowded</label>
                                </div>
                                <div id="score" class="col-sm-8 centered">
                                    <h4><?php echo $mostVisitorRegion; ?></h4>
                                    <b><?php echo $mostVisitorRegion_num; ?> people counted so far</b>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-4 centered">
                                    <img src="assets/img/leadership.png" alt="Popular Attraction" align="middle" height="42" width="42"><br>
                                    <label>Most Returning Visitor</label>
                                </div>
                                <div class="col-sm-8 centered">
                                    <h4><?php echo $mostReturningVisitorRegion; ?></h4>
                                    <b><?php echo $mostReturningVisitorRegion_num; ?> people counted so far</b>
                                </div>
                            </div>
                            <div id="score" class="form-group">
                                <div class="col-sm-4 col-sm-4 centered">
                                    <img src="assets/img/time.png" alt="Longest Time Spent" align="middle" height="42" width="42"><br>
                                    <label>Longest Avg Time Spent</label>
                                </div>
                                <div class="col-sm-8 centered">
                                    <h4><?php echo $mostAvgDurationRegion; ?></h4>
                                    <b><?php echo sec_to_time($mostAvgDurationRegion_num); ?></b>
                                </div>
                            </div>
                        </form>      		
                    </div><!-- /form-panel -->
                </div><!-- /col-lg-4 -->

                <!-- Start of second column -->
                <div class="col-lg-9 col-md-9 col-sm-9 mb">
                    <div class="content-panel">
                        <h4><i class="fa fa-angle-right"></i> Footfall with Temperature</h4>
                        <div class="panel-body">
                            <div class="centered">
                                <div class="btn-group">
                                    <button id="btnTotalVisitor" type="button" class="btn btn-theme">Total Visitor</button>
                                    <button id="btnReturningVisitor" type="button" class="btn btn-theme">Returning Visitor</button>
                                    <button id="btnAvgDuration" type="button" class="btn btn-theme">Average Duration</button>
                                </div>
                            </div>
                            <div id="chartFootfall"></div>
                            <div class="btn-group">
                                <button id="btnBar" type="button" class="btn btn-default">Bar</button>
                                <button id="btnLine" type="button" class="btn btn-default">Line</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 mb">
                            <div class="content-panel">
                                <h4><i class="fa fa-angle-right"></i> Tweet Sentiment Analysis</h4>
                                <div id="tweetContent" class="ds pre-scrollable">
                                    <div class="centered"><img src="assets/img/spinner.gif" alt="Loading" height="42" width="42"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 mb">
                            <div class="content-panel">
                                <h4><i class="fa fa-angle-right"></i> Tweet Sentiment Summary</h4>
                                <div class="panel-body">
                                    <div id="chartTweet" class="centered"><img src="assets/img/spinner.gif" alt="Loading" height="42" width="42"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 mb">
                            <div class="content-panel">
                                <h4><i class="fa fa-angle-right"></i> Facebook Post Sentiment Analysis</h4>
                                <div id="fbContent" class="ds pre-scrollable">
                                    <div class="centered"><img src="assets/img/spinner.gif" alt="Loading" height="42" width="42"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 mb">
                            <div class="content-panel">
                                <h4><i class="fa fa-angle-right"></i> Facebook Sentiment Summary</h4>
                                <div class="panel-body">
                                    <div id="chartFacebook" class="centered"><img src="assets/img/spinner.gif" alt="Loading" height="42" width="42"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of second column -->
            </div>
        </div>
    </div>
    <!-- page end-->
</section>

<script>
    /* Default data on first load */
    var chartType = 'bar';
    var dataColumn = <?php echo $totalVisitor_json; ?>;
    var dataReturning = <?php echo $returningVisitor_json; ?>;
    var dataDuration = <?php echo $avgDuration_json; ?>;

    // Get a reference to the database service
    var database = firebase.database().ref("/");

    $(document).ready(function () {
        //Activate those buttons when page loaded
        $("#btnTotalVisitor").trigger("click");
        $("#btnBar").trigger("click");
    });

    $("#txtFromDate").datepicker({
        constrainInput: true,
        dateFormat: "yy-mm-dd",
        minDate: "-180",
        maxDate: "-1",
        onSelect: function (selected) {
            $("#txtToDate").datepicker("option", "minDate", selected)
        }

    });
    $("#txtToDate").datepicker({
        constrainInput: true,
        dateFormat: "yy-mm-dd",
        minDate: "-180",
        maxDate: "0",
        onSelect: function (selected) {
            $("#txtFromDate").datepicker("option", "maxDate", selected)
        }
    });

    //Remove all other CSS class 'Active' when any of the button is clicked
    $(".btn-group > .btn").click(function () {
        $(this).addClass("active").siblings().removeClass("active");
    });

    /*
     * Data Category button
     */
    $("#btnTotalVisitor").click(function () {
        //chart.destroy();
        // Generate the chart
        chartTitle = 'Total Visitors in Each Region';
        xAxisTitle = 'Region';
        yAxisTitle = 'Total Visitors';
        chart = generateFootfallChart(chartType, chartTitle, xAxisTitle, yAxisTitle, dataColumn);
    });

    $("#btnReturningVisitor").click(function () {
        // Destroy the chart
        //chart.destroy();
        chartTitle = 'Total Returning Visitors in Each Region';
        xAxisTitle = 'Region';
        yAxisTitle = 'Total Returning Visitors';
        chart = generateFootfallChart(chartType, chartTitle, xAxisTitle, yAxisTitle, dataReturning);
    });

    $("#btnAvgDuration").click(function () {
        // Destroy the chart
        //chart.destroy();
        chartTitle = 'Average Duration in Each Region';
        xAxisTitle = 'Region';
        yAxisTitle = 'Second';
        chart = generateFootfallChart(chartType, chartTitle, xAxisTitle, yAxisTitle, dataDuration);
    });
    /*
     * End - Data Category button
     */

    /*
     * Bar or Spline button
     */
    $("#btnBar").click(function () {
        //Only transform the following data into bar chart
        chart.transform('bar', <?php echo $regionNameNoKey_json; ?>);
        chartType = 'bar';
    });

    $("#btnLine").click(function () {
        chart.transform('spline');
        chartType = 'spline';
    });
    /*
     * End - Bar or Spline button
     */

    //Redraw the chart whenever the hamburger menu is click
    $('.sidebar-toggle-box').click(function () {
        chart.flush();
    });
    /*
     * Start retrieving all Tweets
     */
    firebase.database().ref("/tweet/")
            .orderByChild('Timestamp')
            .startAt(<?php echo $fromDate_timestamp; ?>)
            .endAt(<?php echo $toDate_timestamp; ?>)
            .once('value').then(function (snapshot) {
        var totalTweetsNum = snapshot.numChildren();
        var tweetObj = snapshot.val();

        var positiveTweetsNum = 0;
        var neutralTweetsNum = 0;
        var negativeTweetsNum = 0;

        var htmlContent = '';
        var sentimentImg = '<img src="assets/img/neutral.png" alt="Neutral" height="22" width="22">';
        if (tweetObj != null) {

            var objKey = Object.keys(tweetObj);
            var firstKey = parseInt(objKey[0]);
            var totalKeyNum = firstKey + totalTweetsNum;
            console.log(totalKeyNum);
            //for (var i = 0; i < tweetObj.length; i++) {
            for (var i = firstKey; i < totalKeyNum; i++) {
                console.log(tweetObj[i]);
                if (true) {
                    switch (tweetObj[i].Sentiment) {
                        case 'Negative':
                            negativeTweetsNum++;
                            sentimentImg = '<img src="assets/img/sad.png" alt="Sad" height="22" width="22">';
                            break;

                        case 'Neutral':
                            neutralTweetsNum++;
                            sentimentImg = '<img src="assets/img/neutral.png" alt="Neutral" height="22" width="22">';
                            break;

                        case 'Positive':
                            positiveTweetsNum++;
                            sentimentImg = '<img src="assets/img/happy.png" alt="Happy" height="22" width="22">';
                            break;

                        default:
                            break;
                    }
                }
                else {
                    sentimentImg = '<img src="assets/img/neutral.png" alt="Neutral" height="22" width="22">';
                    neutralTweetsNum++;
                }
                var tweetDate = new Date(tweetObj[i].Date_Created);
                var format = "DDD, DD MMM YYYY, HH:MM:SS";
                var tweetDateFormatted = dateConvert(tweetDate, format);

                htmlContent += '<div class="desc"><div class="thumb">';
                htmlContent += sentimentImg;
                htmlContent += '</div>';
                htmlContent += '<div class="details">';
                htmlContent += '<p><muted>' + tweetDateFormatted + '</muted><br>';
                htmlContent += tweetObj[i].Tweet + '<br>';
                htmlContent += '</p></div></div>';
            } //End for loop
        }
        else {
            htmlContent += '<div class="details centered">';
            htmlContent += '<p><img src="assets/img/twitter-128.png" width="16" height="16"> ';
            htmlContent += 'No Tweet during this period.</p></div>';
        }
        $('#tweetContent').html(htmlContent);

        //If there is at least a Tweet, then generate the donut chart
        if (totalTweetsNum > 0) {
            var tweetData = {
                'positiveTweetsNum': positiveTweetsNum,
                'negativeTweetsNum': negativeTweetsNum,
                'neutralTweetsNum': neutralTweetsNum
            };

            generateDonutChart('Tweet Sentiment', tweetData, '#chartTweet');
        }
        //else display a message
        else {
            htmlContent = '<p><img src="assets/img/twitter-128.png" width="16" height="16"> No Tweet during this period.</p>';
            $('#chartTweet').html(htmlContent);
        }
    });
    /*
     * End retrieving all Tweets
     */

    /*
     * Start retrieving all Facebook Posts
     */
    firebase.database().ref("/facebook/")
            .orderByChild('Timestamp')
            .startAt(<?php echo $fromDate_timestamp; ?>)
            .endAt(<?php echo $toDate_timestamp; ?>)
            .once('value').then(function (snapshot) {
        var totalFbPostNum = snapshot.numChildren();
        var fbObj = snapshot.val();

        var positivePostNum = 0;
        var neutralPostNum = 0;
        var negativePostNum = 0;

        var htmlContent = '';
        var sentimentImg = '<img src="assets/img/neutral.png" alt="Neutral" height="22" width="22">';
        if (fbObj != null) {
            var objKey = Object.keys(fbObj);
            var firstKey = parseInt(objKey[0]);
            var totalKeyNum = firstKey + totalFbPostNum;
            for (var i = firstKey; i < totalKeyNum; i++) {
                switch (fbObj[i].Sentiment) {
                    case 'Negative':
                        negativePostNum++;
                        sentimentImg = '<img src="assets/img/sad.png" alt="Sad" height="22" width="22">';
                        break;
                    case 'Neutral':
                        neutralPostNum++;
                        sentimentImg = '<img src="assets/img/neutral.png" alt="Neutral" height="22" width="22">';
                        break;
                    case 'Positive':
                        positivePostNum++;
                        sentimentImg = '<img src="assets/img/happy.png" alt="Happy" height="22" width="22">';
                        break;
                    default:
                        break;
                }

                var fbPostDate = new Date(fbObj[i].Timestamp * 1000);
                var format = "DDD, DD MMM YYYY, HH:MM:SS";
                var fbPostDateFormatted = dateConvert(fbPostDate, format);

                htmlContent += '<div class="desc"><div class="thumb">';
                htmlContent += sentimentImg;
                htmlContent += '</div>';
                htmlContent += '<div class="details">';
                htmlContent += '<p><muted>' + fbPostDateFormatted + '</muted><br>';
                htmlContent += fbObj[i]['PostText '] + '<br>';
                htmlContent += '</p></div></div>';
            } //End for loop
        }
        else {
            htmlContent += '<div class="details centered">';
            htmlContent += '<p><img src="assets/img/square-facebook-128.png" width="16" height="16"> ';
            htmlContent += 'No Post during this period.</p></div>';
        }
        $('#fbContent').html(htmlContent);

        //If there is at least a FB Post, then generate the donut chart
        if (totalFbPostNum > 0) {
            var postData = {
                'positiveTweetsNum': positivePostNum,
                'negativeTweetsNum': negativePostNum,
                'neutralTweetsNum': neutralPostNum
            };

            generateDonutChart('Facebook Sentiment', postData, '#chartFacebook');
        }
        //else display a message
        else {
            htmlContent = '<p><img src="assets/img/square-facebook-128.png" width="16" height="16"> No Post during this period.</p>';
            $('#chartFacebook').html(htmlContent);
        }
    });
    /*
     * End retrieving all Facebook Posts
     */

    function generateFootfallChart(chartType, chartTitle, xAxisTitle, yAxisTitle, data) {

        var chart = c3.generate({
            data: {
                x: 'x',
                columns: data,
                type: chartType,
                types: {
                    'Min. Temperature': 'spline',
                    'Avg. Temperature': 'spline',
                    'Max. Temperature': 'spline',
                },
                axes: {
                    'Min. Temperature': 'y2',
                    'Avg. Temperature': 'y2',
                    'Max. Temperature': 'y2'
                },
                labels: {
                    format: {
                        'Min. Temperature': function (v) {
                            return v + "°C";
                        },
                        'Avg. Temperature': function (v) {
                            return v + "°C";
                        },
                        'Max. Temperature': function (v) {
                            return v + "°C";
                        }
                    }
                },
            },
            legend: {
                position: 'bottom'
            },
            title: {
                text: chartTitle
            },
            axis: {
                x: {
                    label: {
                        text: xAxisTitle,
                        position: 'outer-center',
                        type: 'categorized'
                    },
                    type: 'timeseries',
                    tick: {
                        format: '%Y-%m-%d' // format string is also available for timeseries data
                    }
                },
                y: {
                    label: {
                        text: yAxisTitle,
                        position: 'outer-middle'
                    }
                }
            },
            bar: {
                width: {
                    ratio: 0.7,
                    // max: 30
                },
            },
            bindto: '#chartFootfall'
        });

        return chart;
    }

    function generateDonutChart(chartTitle, tweetData, chartID) {
        var chart = c3.generate({
            data: {
                columns: [
                    ['Negative', tweetData.negativeTweetsNum],
                    ['Neutral', tweetData.neutralTweetsNum],
                    ['Positive', tweetData.positiveTweetsNum]
                ],
                type: 'donut',
                colors: {
                    'Negative': '#ff0000',
                    'Neutral': '#00ff00',
                    'Positive': '#ffff00'
                }
            },
            tooltip: {
                format: {
                    //title: function (d) { return 'Data ' + d; },
                    value: function (value, ratio, id) {
                        var format = d3.format(',');
                        return format(value);
                    }
                }
            },
            donut: {
                title: chartTitle
            },
            bindto: chartID
        });
    }

    function dateConvert(dateobj, format) {
        var year = dateobj.getFullYear();
        var month = ("0" + (dateobj.getMonth() + 1)).slice(-2);
        var date = ("0" + dateobj.getDate()).slice(-2);

        /*
         var hours = ("0" + dateobj.getHours()).slice(-2);
         var minutes = ("0" + dateobj.getMinutes()).slice(-2);
         var seconds = ("0" + dateobj.getSeconds()).slice(-2);
         */
        var hours = dateobj.getHours();
        var minutes = dateobj.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        var strTime = hours + ':' + minutes + ' ' + ampm;

        var day = dateobj.getDay();
        var months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
        var dates = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
        var converted_date = "";

        switch (format) {
            case "YYYY-MM-DD":
                converted_date = year + "-" + month + "-" + date;
                break;
            case "YYYY-MMM-DD DDD":
                converted_date = year + "-" + months[parseInt(month) - 1] + "-" + date + " " + dates[parseInt(day)];
                break;
            case "DDD, DD MMM YYYY, HH:MM:SS":
                converted_date = dates[parseInt(day)] + ", " + date + " " + months[parseInt(month) - 1] + " " + year + ", " + strTime;
                break;
        }

        return converted_date;
    }

</script>