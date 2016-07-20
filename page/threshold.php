<?php $this->layout('layout', ['title' => 'Threshold Setting - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/firebaseLib.php';

$apiURL = 'https://api.sites.lbasense.com/RegionNames?';

$queryParameter = array('user' => 'sitstudents',
    'pass' => 'aiurldd952jeu49r');

$dataPath = $apiURL . http_build_query($queryParameter);
$content = file_get_contents($dataPath);
$content_array = json_decode($content, true);

        const DEFAULT_URL = 'https://visualise-mandai.firebaseio.com';
        const DEFAULT_TOKEN = 'VpbdkNsaBRjyGeRPi81wW0iUFZWLKT0teehiknWH';
        const DEFAULT_PATH = '/threshold-setting';

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

// --- reading the stored string ---
$threshold_content = $firebase->get(DEFAULT_PATH);

$threshold_array = array(0 => array("region" => "Entire Site", "threshold" => 0));

if (isset($_POST["submit"])) {

    foreach ($_POST as $key => $value) {
        if($key != 'submit'){
            array_push($threshold_array, array(
                "region" => $content_array[$key],
                "threshold" => $value)
            );
        }
    }
    //die(print_r($threshold_array));
    $success = $firebase->set(DEFAULT_PATH, $threshold_array);
    //die(print_r($success));
    
} elseif ($threshold_content == "null") {
    //die(print_r($threshold_content));

    foreach ($content_array as $key => $region) {
        array_push($threshold_array, array("region" => $region,
            "threshold" => 0)
        );
    }

    $firebase->set(DEFAULT_PATH, $threshold_array);
}


//print_r($threshold_content);
?>
<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i> <?= $this->e($page_title) ?></h3>

    <?php
    if (isset($_POST["submit"])) {
        ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <b>Success!</b> The new thresholds have been set.
        </div>
    <?php } ?>

    <!-- BASIC FORM ELELEMNTS -->
    <div class="row mt">
        <div class="col-lg-5">
            <div class="form-panel">
                <h4 class="mb"><i class="fa fa-angle-right"></i> Current Threshold</h4>
                <p><b>Note:</b> Set 0 to region you do not wish to have any threshold.</p>
                <br>
                <form class="form-horizontal style-form" method="post">

                    <?php
                    foreach ($content_array as $key => $region) {
                        // --- reading the stored string ---
                        $threshold = $firebase->get(DEFAULT_PATH . '/' . $key . '/threshold');
                        ?>

                        <div class="form-group">
                            <label class="col-sm-6 col-sm-6 control-label"><?php echo $region; ?></label>
                            <div class="col-sm-3">
                                <input name="<?php echo $key; ?>" type="number" class="form-control" min="0" step="10" value=<?php echo $threshold; ?>>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <button name="submit" type="submit" class="btn btn-theme">Set Threshold</button>
                </form>
            </div>
        </div><!-- col-lg-12-->      	
    </div><!-- /row -->



</section>
