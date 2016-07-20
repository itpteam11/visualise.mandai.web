<?php $this->layout('layout', ['title' => 'Add User - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/firebaseLib.php';

        const DEFAULT_URL = 'https://visualise-mandai.firebaseio.com';
        const DEFAULT_TOKEN = 'VpbdkNsaBRjyGeRPi81wW0iUFZWLKT0teehiknWH';
        const DEFAULT_PATH = '/user';

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

if (isset($_POST['uid'])) {
    $userid = $_POST['uid'];
    $position = 'NIL';
    $department = 'NIL';
    if($_POST['position'] != NULL){
        $position = $_POST['position'];
    }
    if($_POST['department'] != NULL){
        $department = $_POST['department'];
    }
    $user_array = array(
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'type' => $position,
        'group' => array($department => $position),
        'status' => 'off',
        'account_status' => 'enable'
    );

    //die(print_r($user_array));
    $success = $firebase->set(DEFAULT_PATH . '/' . $userid, $user_array);
    header('Location: index.php?page=userlist');
}
?>

<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i> <?= $this->e($page_title) ?></h3>

    <?php
    if (isset($_POST["submit"])) {
        ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <b>Success!</b> The user has been created.
        </div>
    <?php } ?>

    <!-- BASIC FORM ELELEMNTS -->
    <div class="row mt">
        <div class="col-lg-6">
            <div class="form-panel">
                <h4 class="mb"><i class="fa fa-angle-right"></i> New User</h4>
                <br>
                <form id="addUserForm" class="form-horizontal style-form" method="post">

                    <input name="uid" type="hidden" value="">

                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Name</label>
                        <div class="col-sm-6">
                            <input name="name" type="text" class="form-control" value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Email</label>
                        <div class="col-sm-6">
                            <input name="email" type="email" class="form-control" value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Password</label>
                        <div class="col-sm-6">
                            <input name="password" type="text" class="form-control" value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Position</label>
                        <div class="col-sm-6">
                            <input name="position" type="text" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-sm-3 control-label">Department</label>
                        <div class="col-sm-6">
                            <input name="department" type="text" class="form-control" value="">
                        </div>
                    </div>
                    <button id="add" name="add" type="submit" class="btn btn-theme">Add</button>
                    <a href="index.php?page=userlist"><button type="button" class="btn btn-info">Back</button></a>
                </form>
            </div>
        </div><!-- col-lg-12-->      	
    </div><!-- /row -->
    <script src="https://www.gstatic.com/firebasejs/live/3.0/firebase.js"></script>
    <script>
        // Initialize Firebase
        var config = {
            apiKey: "AIzaSyDHk-JZlTUWkaYv9l-1h2qNTAss_S-lzoc",
            //apiKey: "VpbdkNsaBRjyGeRPi81wW0iUFZWLKT0teehiknWH",
            authDomain: "visualise-mandai.firebaseapp.com",
            databaseURL: "https://visualise-mandai.firebaseio.com",
            storageBucket: "",
        };
        firebase.initializeApp(config);

        // Get a reference to the database service
        var database = firebase.database().ref("/");

        $("#add").click(function (event) {

            var userName = $('#addUserForm').find('input[name="name"]').val();
            var userEmail = $('#addUserForm').find('input[name="email"]').val();
            var userPassword = $('#addUserForm').find('input[name="password"]').val();
            var userPosition = $('#addUserForm').find('input[name="position"]').val();
            var userDepartment = $('#addUserForm').find('input[name="department"]').val();

            firebase.auth().createUserWithEmailAndPassword(userEmail, userPassword).then(function () {
                // Create successful.
                var user = firebase.auth().currentUser;
                var name, email, photoUrl, uid;

                if (user != null) {
                    name = user.displayName;
                    email = user.email;
                    photoUrl = user.photoURL;
                    uid = user.uid;  // The user's ID, unique to the Firebase project. Do NOT use
                    // this value to authenticate with your backend server, if
                    // you have one. Use User.getToken() instead.

                    // Set the uid value to a hidden field
                    $('#addUserForm').find('input[name="uid"]').val(uid);
                    alert(uid);

                }
                //Submit the form after successful insertion
                $("#addUserForm").submit();

            }).catch(function (error) {
                // Handle Errors here.
                var errorCode = error.code;
                var errorMessage = error.message;

                switch (errorCode) {
                    case 'auth/email-already-in-use':
                    case 'auth/invalid-email':
                        $('#addUserForm').find('input[name="email"]').attr('id', 'focusedInput');
                        break;

                    case 'auth/operation-not-allowed':
                        break;

                    case 'auth/weak-password':
                        $('#addUserForm').find('input[name="password"]').attr('id', 'focusedInput');
                        break;

                    default:
                        break;
                }
                alert(errorMessage);

            }); //End Catch
            event.preventDefault();
        });

    </script>
</section>