<?php

require_once '../lib/firebaseLib.php';

        const DEFAULT_URL = 'https://visualise-mandai.firebaseio.com';
        const DEFAULT_TOKEN = 'VpbdkNsaBRjyGeRPi81wW0iUFZWLKT0teehiknWH';
        const DEFAULT_PATH = '/user';

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

// --- reading the stored string ---
$user_json = $firebase->get(DEFAULT_PATH);

$user_array = json_decode($user_json, true);

$new_array = array();

foreach ($user_array as $key => $user) {
    $userName = 'NIL';
    $userEmail = 'NIL';
    $userGroup = 'NIL';
    $userStatus = 'off';
    $userType = 'NIL';
    $latitude = 0;
    $longitude = 0;

    if (isset($user['name'])) {
        $userName = $user['name'];
    }
    if (isset($user['email'])) {
        $userEmail = $user['email'];
    }
    if (isset($user['group'])) {
        $userGroup = key($user['group']);
    }
    if (isset($user['status'])) {
        $userStatus = $user['status'];
    }
    if (isset($user['type'])) {
        $userType = $user['type'];
    }
    if (isset($user['latitude'])) {
        $latitude = $user['latitude'];
    }
    if (isset($user['longitude'])) {
        $longitude = $user['longitude'];
    }

    // Get all users who are currently at work
    if ($userStatus != 'off') {
        $eachUser = array(
            'userid' => $key,
            'name' => $userName,
            'email' => $userEmail,
            'type' => $userType,
            'group' => $userGroup,
            'status' => $userStatus,
            'latitude' => $latitude,
            'longitude' => $longitude
        );

        array_push($new_array, $eachUser);
    }
}

//print_r($new_array);
echo json_encode($new_array, true);
?>