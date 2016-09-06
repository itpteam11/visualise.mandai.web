<?php $this->layout('layout', ['title' => 'Threshold Setting - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/firebaseLib.php';
require_once 'page/constant/firebase-setting.php';
require_once 'page/constant/lbasense-setting.php';

        const DEFAULT_PATH = '/region-setting';

$errorFlag = FALSE;
$dataPath = getAllRegionURL_api();
$content = file_get_contents($dataPath);
$content_array = json_decode($content, true);

if ($content == FALSE) {
    $errorFlag = TRUE;
}

// --- reading all threshold value ---
$threshold_content = $firebase->get(DEFAULT_PATH);

$threshold_array = array(array("threshold" => 0));

if (isset($_POST["submit"])) {

    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            $data = array("threshold" => $value);
            $firebase->update(DEFAULT_PATH . '/' . $key, $data);
        }
    }
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
        <?php if ($errorFlag == TRUE) { ?>
            <div class="col-lg-12">
                <div class="alert alert-danger">Sorry, the server is down.</div>
            </div>
        <?php
        } else {
            ?>
            <div class="col-lg-5">
                <div class="form-panel">
                    <h4 class="mb"><i class="fa fa-angle-right"></i> Current Threshold</h4>
                    <form class="form-horizontal style-form" method="post">
                        <?php
                        foreach ($content_array as $key => $region) {
                            // --- reading the stored string ---
                            $threshold = $firebase->get(DEFAULT_PATH . '/' . $key . '/threshold');
                            ?>

                            <div class="form-group">
                                <label class="col-sm-6 col-sm-6 control-label"><?php echo $region; ?></label>
                                <div class="col-sm-3">
                                    <input name="<?php echo $key; ?>" type="number" class="form-control" min="0" required value=<?php echo $threshold; ?>>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <button name="submit" type="submit" class="btn btn-theme ladda-button" data-style="expand-right" data-color="red" data-size="s">Set Threshold</button>
                    </form>
                </div>
            </div><!-- col-lg-12-->
        <?php } ?>        
    </div><!-- /row -->
</section>