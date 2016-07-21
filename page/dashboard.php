<?php $this->layout('layout', ['title' => 'Dashboard - WRS Singapore Zoo']) ?>
<?php
//error_reporting(E_ERROR | E_PARSE);
//Credit to "Designed by Freepik and distributed by Flaticon"

require_once 'lib/Forecast.php';
$threads = 10;
$forecast = new Forecast('f2bf4fe3c92a3385cddae35a69436398', $threads);
$apiURL = 'https://api.analytics.lbasense.com/StatsSummary?';
//$apiURL = 'https://api.analytics.lbasense.com/VisitorCounts?';
// Singapore Zoo
$latitude = 1.404359;
$longitude = 103.793012;

$regionName = array('Entire Site',
    'KFC Restaurant at KidzWorld',
    'Australian Outback',
    'Entrance behind ticket counters',
    'Ah Meng Restaurant',
    'SPH Kiosk',
    'WHRC',
    'Amphitheatre',
    'Elephant Show',
    'Taxi Stand');

$yesterday = date("Y-m-d", strtotime("-1 days"));
$sevendayago = date("Y-m-d", strtotime("-7 days"));
$fromDate = $sevendayago;
$toDate = date("Y-m-d");

//Set default date range
if (!isset($_POST['fromDate']) && !isset($_POST['toDate'])) {
    $_POST['fromDate'] = $fromDate;
    $_POST['toDate'] = $toDate;
}

//Based on user date range input
if (isset($_POST['fromDate']) && isset($_POST['toDate'])) {
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];

    $queryParameter = array('user' => 'sitstudents',
        'pass' => 'aiurldd952jeu49r',
        'siteId' => '282',
        'populationType' => '0',
        'startTime' => $fromDate,
        'endTime' => $toDate,
        'resolution' => 'days');

    $dataPath = $apiURL . http_build_query($queryParameter) . "&region=-1";
    $content = file_get_contents($dataPath);

    //Format the date into comma serpated
    $begin = new DateTime($fromDate);
    $end = new DateTime($toDate);
    //$end = $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval, $end);

    $daterange_array = array();
    $tempMin_array = array();
    $tempMax_array = array();
    $tempAvg_array = array();

    // Build requests for current time each month in last 75 years
    $requests = array();

    foreach ($daterange as $date) {
        $dateString = "'" . $date->format("Y-m-d") . "'";
        array_push($daterange_array, $dateString);
        // New DateTime Object
        $dateObject = new DateTime($date->format("Y-m-d"));
        $dateObject->add(new DateInterval('P1D'));

        $requests[] = array(
            'latitude' => $latitude,
            'longitude' => $longitude,
            'time' => strtotime($date->format("Y-m-d")),
            'units' => 'si',
            'timezone' => 'Asia/Singapore'
        );
    }

    // Make requests to the API
    $responses = $forecast->getData($requests);
    $i = 0;
    foreach ($responses as $response) {
        //print_r($response->getRawData());
        if ($daily = $response->getDaily()) {
            $daily = $daily[$i];
            $time = date("Y-m-d", $daily->getTime());

            //$tempMin = $daily->getTemperatureMin() ? number_format($daily->getTemperatureMin(), 2) . '&#8457;' : "unknown";
            $tempMin = $daily->getTemperatureMin();
            $tempMax = $daily->getTemperatureMax();
            $tempAvg = ($tempMin + $tempMax) / 2;

            $summary = $daily->getSummary();

            array_push($tempMin_array, $tempMin);
            array_push($tempMax_array, $tempMax);
            array_push($tempAvg_array, $tempAvg);
        }
    }

    //Remove the last day value as footfall won't be
    //showing the last day value
    array_pop($tempMin_array);
    array_pop($tempMax_array);
    array_pop($tempAvg_array);

    $daterange_separated = implode(",", $daterange_array);
    $tempMin_separated = implode(",", $tempMin_array);
    $tempMax_separated = implode(",", $tempMax_array);
    $tempAvg_separated = implode(",", $tempAvg_array);

    $content_array = json_decode($content, true);

    //print_r($content_array);
    $content = json_encode($content_array, true);

    $numVisitors_arrayAll = array();
    $numReturningVisitors_arrayAll = array();
    $avgDuration_arrayAll = array();

    $i = 0;
    foreach ($content_array as $region_array) {
        //print_r($region_array);

        $numVisitors_array = array();
        $numReturningVisitors_array = array();
        $avgDuration_array = array();

        //if region is Elephant Show
        if ($region_array["regionId"] == 8) {
            if ($region_array["summaryStats"][0]["date"] == "2016-07-04") {

                $date1 = new DateTime($fromDate);
                $date2 = new DateTime("2016-07-04");
                $diffDayNum = $date2->diff($date1)->format("%a");

                for ($n = 0; $n < $diffDayNum; $n++) {
                    array_push($numVisitors_array, 0);
                    array_push($numReturningVisitors_array, 0);
                    array_push($avgDuration_array, 0);
                }
            }
        }

        //if region is Taxi Stand
        if ($region_array["regionId"] == 9) {
            if ($region_array["summaryStats"][0]["date"] == "2016-06-22") {

                $date1 = new DateTime($fromDate);
                $date2 = new DateTime("2016-06-22");
                $diffDayNum = $date2->diff($date1)->format("%a");

                for ($n = 0; $n < $diffDayNum; $n++) {
                    array_push($numVisitors_array, 0);
                    array_push($numReturningVisitors_array, 0);
                    array_push($avgDuration_array, 0);
                }
            }
        }

        foreach ($region_array['summaryStats'] as $regionDate_array) {
            //var_dump($regionDate_array);

            array_push($numVisitors_array, $regionDate_array['numVisitors']);
            array_push($numReturningVisitors_array, $regionDate_array['numReturningVisitors']);
            array_push($avgDuration_array, $regionDate_array['avgDuration']);
        }

        array_push($numVisitors_arrayAll, $numVisitors_array);
        array_push($numReturningVisitors_arrayAll, $numReturningVisitors_array);
        array_push($avgDuration_arrayAll, $avgDuration_array);

        $numVisitors_separated[$i] = implode(",", $numVisitors_array);
        $numReturningVisitors_separated[$i] = implode(",", $numReturningVisitors_array);
        $avgDuration_separated[$i] = implode(",", $avgDuration_array);
        $i++;
    }

    $regionTotalVisitor_array = array(
        //$regionName[0] => array_sum($numVisitors_arrayAll[0]),
        $regionName[1] => array_sum($numVisitors_arrayAll[1]),
        $regionName[2] => array_sum($numVisitors_arrayAll[2]),
        $regionName[3] => array_sum($numVisitors_arrayAll[3]),
        $regionName[4] => array_sum($numVisitors_arrayAll[4]),
        $regionName[5] => array_sum($numVisitors_arrayAll[5]),
        $regionName[6] => array_sum($numVisitors_arrayAll[6]),
        $regionName[7] => array_sum($numVisitors_arrayAll[7])
    );

    $regionReturningVisitor_array = array(
        //$regionName[0] => array_sum($numReturningVisitors_arrayAll[0]),
        $regionName[1] => array_sum($numReturningVisitors_arrayAll[1]),
        $regionName[2] => array_sum($numReturningVisitors_arrayAll[2]),
        $regionName[3] => array_sum($numReturningVisitors_arrayAll[3]),
        $regionName[4] => array_sum($numReturningVisitors_arrayAll[4]),
        $regionName[5] => array_sum($numReturningVisitors_arrayAll[5]),
        $regionName[6] => array_sum($numReturningVisitors_arrayAll[6]),
        $regionName[7] => array_sum($numReturningVisitors_arrayAll[7])
    );

    $regionAvgDuraton_array = array(
        //$regionName[0] => array_sum($avgDuration_arrayAll[0]),
        $regionName[1] => array_sum($avgDuration_arrayAll[1]),
        $regionName[2] => array_sum($avgDuration_arrayAll[2]),
        $regionName[3] => array_sum($avgDuration_arrayAll[3]),
        $regionName[4] => array_sum($avgDuration_arrayAll[4]),
        $regionName[5] => array_sum($avgDuration_arrayAll[5]),
        $regionName[6] => array_sum($avgDuration_arrayAll[6]),
        $regionName[7] => array_sum($avgDuration_arrayAll[7])
    );

    $mostVisitorRegion = array_search(max($regionTotalVisitor_array), $regionTotalVisitor_array);
    $mostVisitorRegion_num = $regionTotalVisitor_array[$mostVisitorRegion];

    $mostReturningVisitorRegion = array_search(max($regionReturningVisitor_array), $regionReturningVisitor_array);
    $mostReturningVisitorRegion_num = $regionReturningVisitor_array[$mostReturningVisitorRegion];

    $mostAvgDurationRegion = array_search(max($regionAvgDuraton_array), $regionAvgDuraton_array);
    $mostAvgDurationRegion_num = $regionAvgDuraton_array[$mostAvgDurationRegion];
} //End if post

$date_dataset = "['x', " . $daterange_separated . "],\n";
$totalVisitor_json = '[' . $date_dataset;
$returningVisitor_json = '[' . $date_dataset;
$avgDuration_json = '[' . $date_dataset;

for ($id = 1; $id <= 9; $id++) {
    $totalVisitor_dataset[$id] = "['" . $regionName[$id] . "'," . $numVisitors_separated[$id] . "]\n";
    $returningVisitor_dataset[$id] = "['" . $regionName[$id] . "'," . $numReturningVisitors_separated[$id] . "]\n";
    $avgDuration_dataset[$id] = "['" . $regionName[$id] . "'," . $avgDuration_separated[$id] . "]\n";
}

$totalVisitor_json .= implode(",", $totalVisitor_dataset);
$returningVisitor_json .= implode(",", $returningVisitor_dataset);
$avgDuration_json .= implode(",", $avgDuration_dataset);

$totalVisitor_json .= "]";
$returningVisitor_json .= "]";
$avgDuration_json .= "]";
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
                            <h3 class="mb"><i class="fa fa-angle-right"></i> Date Selection</h3>
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
                        <h3 class="mb"><i class="fa fa-angle-right"></i> Summary</h3>

                        <form class="form-horizontal style-form" action="" method="get">
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-4 centered">
                                    <img src="assets/img/teamwork.png" alt="Crowded" align="middle" height="42" width="42">
                                    <br>
                                    <label class="control-label"><center>Most Crowded</center></label>
                                </div>
                                
                                <div class="col-sm-8 centered">
                                    <h4><?php echo $mostVisitorRegion; ?></h4>
                                    <h5><?php echo $mostVisitorRegion_num; ?> people</h5>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-4 centered">
                                    <img src="assets/img/leadership.png" alt="Popular Attraction" align="middle" height="42" width="42">
                                    <br>
                                    <label class="control-label"><center>Most Returning Visitor</center></label>
                                </div>
                                
                                <div class="col-sm-8 centered" vertical-align="middle">
                                    <h4><?php echo $mostReturningVisitorRegion; ?></h4>
                                    <h5><?php echo $mostReturningVisitorRegion_num; ?> people</h5>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-4 centered">
                                    <img src="assets/img/time.png" alt="Longest Time Spent" align="middle" height="42" width="42">
                                    <br>
                                    <label class="control-label"><center>Longest Average Time Spent</center></label>
                                </div>
                                
                                <div class="col-sm-8 centered">
                                    <h4><?php echo $mostAvgDurationRegion; ?></h4>
                                    <h5><?php echo $mostAvgDurationRegion_num; ?> sec</h5>
                                </div>
                            </div>
                        </form>      		
                    </div><!-- /form-panel -->

                    <div class="content-panel">
                        <h3><i class="fa fa-angle-right"></i> Tweet</h3>

                            <div class="ds">
                                <div class="desc">
                                    <div class="thumb">
                                        <span class="badge bg-theme"><i class="fa fa-clock-o"></i></span>
                                    </div>
                                    <div class="details">
                                        <p><muted>2 Minutes Ago</muted><br>
                                        <a href="#">James Brown</a> subscribed to your newsletter.<br>
                                        </p>
                                    </div>
                                </div>
                            </div>

                    </div>

                </div><!-- /col-lg-4 -->


                <div class="col-lg-9 col-md-9 col-sm-9 mb">
                    <div class="content-panel">
                        <h3><i class="fa fa-angle-right"></i> Footfall</h3>
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
                </div>

                <div class="col-lg-9 col-md-9 col-sm-9 mb">
                    <div class="content-panel">
                        <h3><i class="fa fa-angle-right"></i> Weather</h3>
                        <div class="panel-body">
                            <div id="chartWeather"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- page end-->
</section>

<script>

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

    $(document).ready(function () {
        $("#btnTotalVisitor").trigger("click");
        $("#btnBar").trigger("click");
    });

    $(".btn-group > .btn").click(function () {
        $(this).addClass("active").siblings().removeClass("active");
    });

    var dataColumn = <?php echo $totalVisitor_json; ?>;
    var dataReturning = <?php echo $returningVisitor_json; ?>;
    var dataDuration = <?php echo $avgDuration_json; ?>;

    function generateFootfallChart(chartTitle, xAxisTitle, yAxisTitle, data) {
        var chart = c3.generate({
            data: {
                x: 'x',
                columns: data,
                labels: false,
                type: 'bar',
                onclick: function (d, element) {
                    console.log("onclick", d, element);
                },
                onmouseover: function (d) {
                    console.log("onmouseover", d);
                },
                onmouseout: function (d) {
                    console.log("onmouseout", d);
                }
            },
            zoom: {
                enabled: true
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
                        //format: function (x) {
                        //    return x + 1;
                        //}
                        //format: function (x) { return x.getFullYear(); }
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
//            max: 30
                },
            },
            bindto: '#chartFootfall'
        });

        return chart;
    }
//http://stackoverflow.com/questions/24754239/how-to-change-tooltip-content-in-c3js
    function tooltip_contents(d, defaultTitleFormat, defaultValueFormat, color) {
        var $$ = this, config = $$.config, CLASS = $$.CLASS,
                titleFormat = config.tooltip_format_title || defaultTitleFormat,
                nameFormat = config.tooltip_format_name || function (name) {
                    return name;
                },
                valueFormat = config.tooltip_format_value || defaultValueFormat,
                text, i, title, value, name, bgcolor;

        // You can access all of data like this:
        console.log($$.data.targets);

        for (i = 0; i < d.length; i++) {
            if (!(d[i] && (d[i].value || d[i].value === 0))) {
                continue;
            }

            // ADD
            if (d[i].name === 'data2') {
                continue;
            }

            if (!text) {
                title = 'MY TOOLTIP'
                text = "<table class='" + CLASS.tooltip + "'>" + (title || title === 0 ? "<tr><th colspan='2'>" + title + "</th></tr>" : "");
            }

            name = nameFormat(d[i].name);
            value = valueFormat(d[i].value, d[i].ratio, d[i].id, d[i].index);
            bgcolor = $$.levelColor ? $$.levelColor(d[i].value) : color(d[i].id);

            text += "<tr class='" + CLASS.tooltipName + "-" + d[i].id + "'>";
            text += "<td class='name'><span style='background-color:" + bgcolor + "'></span>" + name + "</td>";
            text += "<td class='value'>" + value + "</td>";
            text += "</tr>";
        }
        return text + "</table>";
    }

    var chartWeather = c3.generate({
        data: {
            x: 'x',
            columns: [
                ['x', <?php echo $daterange_separated; ?>],
                ['Min. Temperature', <?php echo $tempMin_separated; ?>],
                ['Avg Temperature', <?php echo $tempAvg_separated; ?>],
                ['Max. Temperature', <?php echo $tempMax_separated; ?>]
            ],
            labels: true,
            type: 'spline',
            onclick: function (d, element) {
                console.log("onclick", d, element);
            },
            onmouseover: function (d) {
                console.log("onmouseover", d);
            },
            onmouseout: function (d) {
                console.log("onmouseout", d);
            }
        },
        zoom: {
            enabled: true
        },
        legend: {
            position: 'bottom'
        },
        title: {
            text: 'Daily Temperature'
        },
        axis: {
            x: {
                label: {
                    text: 'Date',
                    position: 'outer-center',
                    type: 'categorized'
                },
                type: 'timeseries',
                tick: {
                    //format: function (x) {
                    //    return x + 1;
                    //}
                    //format: function (x) { return x.getFullYear(); }
                    format: '%Y-%m-%d' // format string is also available for timeseries data
                }
            },
            y: {
                label: {
                    text: 'Temperature in Celsius',
                    position: 'outer-middle'
                }
            }
        },
        bar: {
            width: {
                ratio: 0.7,
//            max: 30
            },
        },
        tooltip: {
            contents: tooltip_contents
        },
        bindto: '#chartWeather'
    });

    $("#btnBar").click(function () {
        chart.transform('bar');
    });

    $("#btnLine").click(function () {
        chart.transform('spline');
    });
    var chart;
    $("#btnTotalVisitor").click(function () {
        //chart.destroy();
        // Generate the chart
        chartTitle = 'Total Visitors in Each Region';
        xAxisTitle = 'Region';
        yAxisTitle = 'Total Visitors';
        chart = generateFootfallChart(chartTitle, xAxisTitle, yAxisTitle, dataColumn);
    });

    $("#btnReturningVisitor").click(function () {
        // Destroy the chart
        //chart.destroy();
        chartTitle = 'Total Returning Visitors in Each Region';
        xAxisTitle = 'Region';
        yAxisTitle = 'Total Returning Visitors';
        chart = generateFootfallChart(chartTitle, xAxisTitle, yAxisTitle, dataReturning);
    });

    $("#btnAvgDuration").click(function () {
        // Destroy the chart
        //chart.destroy();
        chartTitle = 'Average Duration in Each Region';
        xAxisTitle = 'Region';
        yAxisTitle = 'Second';
        chart = generateFootfallChart(chartTitle, xAxisTitle, yAxisTitle, dataDuration);
    });
</script>