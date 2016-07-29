<?php

        const lbasense_username = 'sitstudents';
        const lbasense_password = 'aiurldd952jeu49r';
        const lbasense_siteId = '282';

//Used for dashboard
function getAllRegionURL_api() {
    $apiURL_base = 'https://api.sites.lbasense.com/RegionNames?';

    $queryParameter = array('user' => lbasense_username,
        'pass' => lbasense_password);

    $fullpath = $apiURL_base . http_build_query($queryParameter);
    return $fullpath;
}

//Used for dashboard
function getStatsSummaryURL_api($fromDate, $toDate) {
    $apiURL_base = 'https://api.analytics.lbasense.com/StatsSummary?';
    $queryParameter = array('user' => 'sitstudents',
        'pass' => 'aiurldd952jeu49r',
        'siteId' => '282',
        'populationType' => '0',
        'startTime' => $fromDate,
        'endTime' => $toDate,
        'resolution' => 'days');

    $fullpath = $apiURL_base . http_build_query($queryParameter) . "&region=-1";

    return $fullpath;
}

//Used for heatmap
function getSAPValuePerRegionURL_api() {
    $apiURL_base = "https://api.sap.lbasense.com/CurrentSAPValuePerRegion?";

    $queryParameter = array(
        'user' => lbasense_username,
        'pass' => lbasense_password,
        'siteId' => lbasense_siteId);

    $fullpath = $apiURL_base . http_build_query($queryParameter);
    return $fullpath;
}

?>