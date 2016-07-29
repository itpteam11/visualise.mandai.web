<?php
require_once '../lib/firebaseLib.php';
require_once 'constant/firebase-setting.php';
        const DEFAULT_PATH = '/user';

// --- reading all user ---
$user_json = $firebase->get(DEFAULT_PATH);

$user_array = json_decode($user_json, true);

$userFormatted_array = array();

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

        array_push($userFormatted_array, $eachUser);
    }
}

echo json_encode($userFormatted_array, true);
?>