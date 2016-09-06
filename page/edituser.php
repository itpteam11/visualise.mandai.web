<?php $this->layout('layout', ['title' => 'Edit User - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/firebaseLib.php';
require_once 'page/constant/firebase-setting.php';
        const DEFAULT_PATH = '/user';

$userid = null;
$userName = 'NIL';
$userEmail = 'NIL';
$userType = 'NIL';
$userGroup = 'NIL';

if (isset($_GET['id'])) {
    $userid = $_GET['id'];
}

//If form is submitted
if (isset($_POST["submit"])) {

    if (empty($_POST["position"]) == false) {
        $userType = $_POST["position"];
    }
    if (empty($_POST["department"]) == false) {
        $userGroup = $_POST["department"];
        //die('t');
    }

    $user_array = array(
        "name" => $_POST['name'],
        "email" => $_POST['email'],
        "type" => $userType,
        "group" => array($userGroup => $userType)
    );

    $serverMsg = $firebase->update(DEFAULT_PATH . '/' . $userid, $user_array);
    //For debugging purpose
    //die(print_r($user_array));
}
// --- reading a user from Firebase ---
$user_json = $firebase->get(DEFAULT_PATH . '/' . $userid);
$user = json_decode($user_json, true);

if (isset($user["name"])) {
    $userName = $user["name"];
}
if (isset($user["email"])) {
    $userEmail = $user["email"];
}
if (isset($user["type"])) {
    $userType = $user["type"];
}
if (isset($user["group"])) {
    $userGroup = key($user["group"]);
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
                            <input name="name" type="text" class="form-control" value="<?php echo $userName; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Email</label>
                        <div class="col-sm-6">
                            <input name="email" type="email" class="form-control" value="<?php echo $userEmail; ?>" readonly="" required>
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
                    <button name="submit" type="submit" class="btn btn-theme ladda-button" data-style="expand-right" data-color="red" data-size="xs">Update</button>
                    <a href="index.php?page=userlist"><button type="button" class="btn btn-info">Back</button></a>
                </form>
            </div>
        </div><!-- col-lg-12-->      	
    </div><!-- /row -->
</section>