<?php
// Start the session
session_start();

require_once 'lib/firebaseLib.php';
require_once 'page/constant/firebase-setting.php';
        const DEFAULT_PATH = '/user';

// Successfully login
if (isset($_POST['uid'])) {
    $userid = $_POST['uid'];

    // --- reading a user from Firebase ---
    $user_json = $firebase->get(DEFAULT_PATH . '/' . $userid);
    $user = json_decode($user_json, true);

    if ($user['account_status'] == 'enable') {
        // Set session variables
        $_SESSION["userid"] = $userid;
        header('Location: index.php');
    } else {
        // Set session variables
        $_SESSION["error"] = 'disable';
        header('Location: login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="keyword" content="">

        <title>Login - WRS Singapore Zoo</title>

        <!-- For IE 9 and below. ICO should be 32x32 pixels in size -->
        <!--[if IE]><link rel="shortcut icon" href="assets/logo.ico"><![endif]-->

        <!-- Touch Icons - iOS and Android 2.1+ 180x180 pixels in size. --> 
        <link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">

        <!-- Firefox, Chrome, Safari, IE 11+ and Opera. 196x196 pixels in size. -->
        <link rel="icon" href="assets/logo.ico">

        <!-- Bootstrap core CSS -->
        <link href="assets/css/bootstrap.css" rel="stylesheet">
        <!--external css-->
        <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link href="assets/lineicons/style.css" rel="stylesheet">    
        <link href="assets/js/jquery-ui/jquery-ui.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="assets/css/style.css" rel="stylesheet">
        <link href="assets/css/style-responsive.css" rel="stylesheet">

        <link href="assets/css/ladda.min.css" rel="stylesheet" />

        <script src="https://www.gstatic.com/firebasejs/live/3.0/firebase.js"></script>
        <script src="assets/js/firebase-setting.js"></script>
    </head>

    <body>
        <div id="login-page">
            <div class="container">
                <form id="loginForm" class="form-login" method="post">
                    <h2 class="form-login-heading">Singapore Zoo Dashboard</h2>
                    <div class="login-wrap">
                        <input name="uid" type="hidden" value="">
                        <input name="userid" type="text" class="form-control" placeholder="User ID" autofocus>
                        <br>
                        <input name="password" type="password" class="form-control" placeholder="Password">
                        <label class="checkbox">
                            <span class="pull-right">
                                <!--a data-toggle="modal" href="login.html#myModal"> Forgot Password?</a-->		
                            </span>
                        </label>
                        <button id="btnLogin" class="btn btn-theme btn-block ladda-button" data-style="expand-right" data-color="red" data-size="s" type="submit"><i class="fa fa-lock"></i> SIGN IN</button>		
                    </div>

                    <!-- Modal -->
                    <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Forgot Password ?</h4>
                                </div>
                                <div class="modal-body">
                                    <p>Enter your e-mail address below to reset your password.</p>
                                    <input type="text" name="email" placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">

                                </div>
                                <div class="modal-footer">
                                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                                    <button class="btn btn-theme" type="button">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- modal -->

                </form>
            </div>
        </div>

        <!-- js placed at the end of the document so the pages load faster -->
        <script src="assets/js/jquery-1.8.3.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>

        <!--BACKSTRETCH-->
        <!-- You can use an image of whatever size. This script will stretch to fit in any screen size.-->
        <script type="text/javascript" src="assets/js/jquery.backstretch.min.js"></script>
        <script src="assets/js/spin.min.js"></script>
        <script src="assets/js/ladda.min.js"></script>
        <script>
            $.backstretch("assets/img/login-bg.jpg", {fade: 'slow'});
<?php
if (isset($_SESSION["error"]) == 'disable') {
    echo 'alert("Your account has been disabled. Please contact the Admin.");';
}
?>

            $("#btnLogin").click(function (event) {
                // Create a new instance of ladda for the specified button
                var l = Ladda.create(document.querySelector('#btnLogin'));
                // Start loading
                l.start();
                
                var userId = $('#loginForm').find('input[name="userid"]').val();
                var userPassword = $('#loginForm').find('input[name="password"]').val();

                firebase.auth().signOut().then(function () {
                    // Sign-out successful.
                    //alert("A previous user has been signed out.");
                    firebase.auth().signInWithEmailAndPassword(userId, userPassword).then(function () {
                        // User is signed in.
                        var user = firebase.auth().currentUser;
                        var uid;
                        if (user != null) {
                            uid = user.uid; // The user's ID, unique to the Firebase project.
                            // Set the uid value to a hidden field
                            $('#loginForm').find('input[name="uid"]').val(uid);

                            var userId = user.uid;
                            return firebase.database().ref('/user/' + userId).once('value').then(function (snapshot) {
                                var userType = snapshot.val().type;
                                if (userType == 'manager') {
                                    $("#loginForm").submit();
                                }
                                else {
                                    alert('Sorry, you do not have the privilege to access.');
                                    // Stop loading
                                    l.stop();
                                }
                            }); //End DB Get query
                        }
                        else {
                            // No user is signed in.
                            alert("An error has just occured. Please try again.");
                            // Stop loading
                            l.stop();
                        }

                    }).catch(function (error) {
                        // Handle Errors here.
                        var errorCode = error.code;
                        var errorMessage = error.message;
                        var customMessage = 'Either the username/password is incorrect. Please try again.';
                        alert(customMessage);
                        // Stop loading
                        l.stop();
                    });

                }, function (error) {
                    // An error happened.
                    l.stop();
                });

                event.preventDefault();
            });
        </script>
    </body>
</html>