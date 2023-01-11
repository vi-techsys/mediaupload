<?php require_once('header.php');
require_once('dbconn.php');
require_once('session.php');
$msg = '';
?>
<!-- Masthead-->

<header class="masthead">
    <div class="container px-4 px-lg-5 h-100">
        <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-8 col-md-8 col-sm-12 align-self-baseline">
                <?php
                $id = $_SESSION['id'];
                if (isset($_POST['submittheform'])) {
                    $username = addslashes($_POST['username']);
                    $psw = addslashes($_POST['password']);
                    if ($psw != '') {
                        $psw2 = addslashes($_POST['confirm_password']);
                        if ($psw == $psw2) {
                            $stmt = $pdo->prepare('update admin set username = ?, password =? where id =?');
                            $stmt->execute([$username, md5($psw), $id]);
                            $msg = "Profile updated";
                        } else {
                            $msg = "Password does not match";
                        }
                    } else {
                        $stmt = $pdo->prepare('update admin set username = ? where id =?');
                        $stmt->execute([$username, $id]);
                        $msg = "Profile updated";
                    }
                } ?>
                <h5 style="color: orangered; border-radius:5px;"><?php echo  $msg ?></h5>
                <form id="contactForm" action="" method="post" enctype="multipart/form-data">
                    <?php
                    $stmt = $pdo->prepare('select * from admin where id =' . $id);
                    $stmt->execute();
                    $admin = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="form-floating mb-3">
                        <input class="form-control" value="<?php echo $admin != null ? $admin[0]['username'] : '' ?>" name="username" value="" id="username" type="text" placeholder="Username" required />
                        <label for="username">Username</label>
                    </div>
                    <small style="color:white;"><i>Leave the password empty if you wish not to change the password</i></small>
                    <div class="form-floating mb-3">
                        <input class="form-control" name="password" value="" id="password" type="password" placeholder="Password" />
                        <label for="password">Password</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" name="confirm_password" value="" id="confirm_password" type="password" placeholder="Confirm Password" />
                        <label for="confirm_password">Confirm Password</label>
                    </div>
                    <!-- Submit Button-->
                    <div class="d-grid"><input class="btn btn-primary btn-xl" id="submittheform" name="submittheform" type="submit" value="Submit"></div>
                </form>
            </div>
        </div>
    </div>
</header>
<?php require_once('footer.php') ?>