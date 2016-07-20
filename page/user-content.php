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

    $eachUser = array(
        'userid' => $key,
        'name' => $userName,
        'email' => $userEmail,
        'type' => $user['type'],
        'group' => $userGroup,
        'status' => $userStatus
    );

    array_push($new_array, $eachUser);
}

//print_r($new_array);
echo json_encode($new_array, true);
?>