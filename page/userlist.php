<?php $this->layout('layout', ['title' => 'Manage User - WRS Singapore Zoo']) ?>
<?php
require_once 'lib/firebaseLib.php';
require_once 'page/constant/firebase-setting.php';
        const DEFAULT_PATH = '/user';

$errorFlag = FALSE;
// --- reading all users ---
$user_json = $firebase->get(DEFAULT_PATH);
$user_array = json_decode($user_json, true);

if ($user_json == NULL) {
    $errorFlag = TRUE;
}
?>
<section class="wrapper">
    <h3><i class="fa fa-angle-right"></i> <?= $this->e($page_title) ?></h3>

    <div class="row mt">
        <div class="col-md-12">
            <?php if ($errorFlag == TRUE) { ?>
                <div class="alert alert-danger">Sorry, the server is down.</div>
            <?php } else {
                ?>

                <div class="content-panel">
                    <div class="row">
                        <div class="col-md-10"><h4><i class="fa fa-angle-right"></i> Users</h4></div>
                        <div class="col-md-2 text-center"><a href="index.php?page=adduser"><button type="button" class="btn btn-theme"><i class="fa fa-plus"></i> Add User</button></a></div>
                    </div>
                    <hr>
                    <table class="table table-striped table-advance table-hover">
                        <thead>
                            <tr>
                                <th><i class="fa fa-user"></i> Name</th>
                                <th><i class="fa fa-envelope"></i> Email</th>
                                <th><i class="fa fa-star"></i> Position</th>
                                <th><i class="fa fa-group"></i> Department</th>
                                <th><i class="fa fa-wrench"></i> Status</th>
                                <th><i class="fa fa-unlock-alt"></i> Account</th>
                                <th><i class=" fa fa-edit"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($user_array as $key => $user) {
                                $userName = 'NIL';
                                $userEmail = 'NIL';
                                $userType = 'NIL';
                                $userGroup = 'NIL';
                                $userStatus = 'off';
                                $userAccountStatus = 'disable';

                                if (isset($user['name'])) {
                                    $userName = $user['name'];
                                }
                                if (isset($user['email'])) {
                                    $userEmail = $user['email'];
                                }
                                if (isset($user['type'])) {
                                    $userType = $user['type'];
                                }
                                if (isset($user['group'])) {
                                    $userGroup = key($user['group']);
                                }
                                if (isset($user['status'])) {
                                    $userStatus = $user['status'];
                                }
                                if (isset($user['account_status'])) {
                                    $userAccountStatus = $user['account_status'];
                                }
                                ?>
                                <tr>
                                    <td><?php echo $userName; ?></td>
                                    <td><?php echo $userEmail; ?></td>
                                    <td><?php echo $userType; ?></td>
                                    <td><?php echo $userGroup; ?></td>
                                    <td><span class="label <?php echo ($userStatus == 'off') ? 'label-info' : 'label-success'; ?> label-mini"><?php echo $userStatus; ?></span></td>
                                    <td><span class="label <?php echo ($userAccountStatus == 'enable') ? 'label-success' : 'label-danger'; ?>"><?php echo $userAccountStatus; ?></span></td>
                                    <td>
                                        <a href="index.php?page=edituser&id=<?php echo $key; ?>"><button class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></button></a>
                                        <button class="btn btn-danger btn-xs" onclick="toggleAccountStatus('<?php echo $userAccountStatus; ?>', '<?php echo $userName; ?>', '<?php echo $key; ?>')"><i class="fa fa-lock"></i></button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div><!-- /content-panel -->

            <?php } ?>
        </div><!-- /col-md-12 -->
    </div><!-- /row -->

    <script>
        function toggleAccountStatus(accountStatus, username, userid) {
            var msg;
            var statusVerb = 'enable';
            if (accountStatus == 'enable') {
                statusVerb = 'disable';
            }
            var question = "Are you sure you want to " + statusVerb + " " + username + "'s account?";

            if (confirm(question) == true) {
                // Get a reference to the database service
                //var database = firebase.database().ref("/");

                var updates = {};
                updates['/user/' + userid + '/account_status'] = statusVerb;
                msg = firebase.database().ref().update(updates, onComplete);

            } else {
                msg = "No change has been made.";
            }
            //console.log(msg);
        }

        var onComplete = function (error) {
            if (error) {
                //console.log('Synchronization failed');
            } else {
                //console.log('Synchronization succeeded');
                location.reload();
            }
        };


    </script>
</section>