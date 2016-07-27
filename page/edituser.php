<?php $this->layout('layout', ['title' => 'Edit User - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/firebaseLib.php';

        const DEFAULT_URL = 'https://visualise-mandai.firebaseio.com';
        const DEFAULT_TOKEN = 'VpbdkNsaBRjyGeRPi81wW0iUFZWLKT0teehiknWH';
        const DEFAULT_PATH = '/user';

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

$userid = null;
if (isset($_GET['id'])) {
    $userid = $_GET['id'];
}
// --- reading from Firebase ---
$user_json = $firebase->get(DEFAULT_PATH . '/' . $userid);
$user = json_decode($user_json, true);

$userName = null;
$userEmail = null;
$userType = null;
$userGroup = null;

if(isset($user["name"])){
    $userName = $user["name"];
}
if(isset($user["email"])){
    $userEmail = $user["email"];
}
if(isset($user["type"])){
    $userType = $user["type"];
}
if(isset($user["group"])){
    $userGroup = key($user["group"]);
}

if (isset($_POST["submit"])) {

    foreach ($_POST as $key => $value) {
        $user_array = array(
            "name" => $_POST['name'],
            "email" => $_POST['email'],
            "type" => $_POST['position'],
            "group" => array($_POST['department']=>$_POST['position'])
        );
    }
    $success = $firebase->update(DEFAULT_PATH . '/' . $userid, $user_array);
    //die(print_r($success));
}
?>

<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i> <?= $this->e($page_title) ?></h3>

    <?php
    if (isset($_POST["submit"])) {
        ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <b>Success!</b> The user data has been updated.
        </div>
    <?php } ?>

    <!-- BASIC FORM ELELEMNTS -->
    <div class="row mt">
        <div class="col-lg-6">
            <div class="form-panel">
                <h4 class="mb"><i class="fa fa-angle-right"></i> Existing User</h4>
                <br>
                <form class="form-horizontal style-form" method="post">
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Name</label>
                        <div class="col-sm-6">
                            <input name="name" type="text" class="form-control" value="<?php echo $userName; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Email</label>
                        <div class="col-sm-6">
                            <input name="email" type="email" class="form-control" value="<?php echo $userEmail; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Position</label>
                        <div class="col-sm-6">
                            <input name="position" type="text" class="form-control" value="<?php echo $userType; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Department</label>
                        <div class="col-sm-6">
                            <input name="department" type="text" class="form-control" value="<?php echo $userGroup; ?>">
                        </div>
                    </div>
                    <button name="submit" type="submit" class="btn btn-theme">Update</button>
                    <a href="index.php?page=userlist"><button type="button" class="btn btn-info">Back</button></a>
                </form>
            </div>
        </div><!-- col-lg-12-->      	
    </div><!-- /row -->
</section>