<?php require_once('header.php');
require_once('dbconn.php');
require_once('setup.php');
?>
<!-- Masthead-->
<header class="masthead">
    <div class="container px-4 px-lg-5 h-100">
        <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-6 col-md-6 col-sm-12 align-self-baseline">
                <?php
                $msg = '';
                $failed = false;
                if (!empty($_POST)) {
                    $username = addslashes($_POST['username']);
                    $password = addslashes($_POST['password']);
                    $stmt = $pdo->prepare('select * from admin where username = ? and password =?');
                    $stmt->execute([$username, md5($password)]);
                    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($user)) {
                        $_SESSION['logged_in'] = true;
                        $_SESSION['email'] = $user[0]['email'];
                        $_SESSION['id'] = $user[0]['id'];
                        header('location:dashboard.php');
                    } else {
                        $msg = 'Login failed';
                        $failed = false;
                    }
                } else {
                    $failed = false;
                }
                ?>
                <?php if (!$failed) { ?>
                    <h5 style="color: orangered; border-radius:5px;"><?php echo  $msg ?></h5>
                    <form id="contactForm" action="" method="post">
                        <div class="form-floating mb-3">
                            <input class="form-control" name="username" id="username" type="text" placeholder="Enter your Username..." required />
                            <label for="username">Username</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="password" name="password" type="password" placeholder="Enter your Password..." required />
                            <label for="username">Password</label>
                        </div>
                        <!-- Submit Button-->
                        <div class="d-grid"><button class="btn btn-primary btn-xl" id="submitButton" type="submit">Submit</button></div>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</header>
<?php require_once('footer.php') ?>