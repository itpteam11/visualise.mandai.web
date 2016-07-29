<?php

/*
 * Requires to include firebaseLib.php first
 */

        const DEFAULT_URL = 'https://visualise-mandai.firebaseio.com';
        const DEFAULT_TOKEN = 'VpbdkNsaBRjyGeRPi81wW0iUFZWLKT0teehiknWH';

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);
?>