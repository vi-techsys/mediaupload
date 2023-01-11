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
                $id = $_GET['id'];
                if (isset($_POST['submittheform']) && isset($_FILES['fileToUpload'])) {
                    $name_file = $_FILES['fileToUpload']['name'];
                    $tmp_name = $_FILES['fileToUpload']['tmp_name'];
                    $desc = addslashes($_POST['desc']);
                    $target_dir_location = 'uploads/';
                    $ffile = $_POST['ffile'];
                    $fname = $target_dir_location . $name_file;
                    $link = '';
                    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                        $link = 'https://';
                    else
                        $link = 'http://';
                    $link .= $_SERVER['HTTP_HOST'] . '/mediaupload';
                    if (move_uploaded_file($tmp_name, $target_dir_location . $name_file)) {
                        $stmt = $pdo->prepare('update my_media set mpath =?, mdescription =?, mfilename = ? where mid = ?');
                        $stmt->execute([$fname, $desc, $link . '/' . $target_dir_location . $name_file, $id]);
                        if ($ffile != $name_file) {
                            unlink($ffile);
                        }
                        header('location:dashboard.php');
                    } else {
                        $msg = "The file was not uploaded";
                    }
                } ?>
                <h5 style="color: orangered; border-radius:5px;"><?php echo  $msg ?></h5>
                <form id="contactForm" action="" method="post" enctype="multipart/form-data">
                    <?php
                    $stmt = $pdo->prepare('select * from my_media where mid =' . $id);
                    $stmt->execute();
                    $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="form-floating mb-3">
                        <input class="" name="fileToUpload" value="" id="fileToUpload" name="fileToUpload" type="file" placeholder="Select file to upload" required />
                    </div>
                    <div class="form-floating mb-3">
                        <span style="color: white;">Last File uploaded before:<br>
                            <?php echo $medias != null ? $medias[0]['mfilename'] : '' ?>
                        </span>
                        <input type="hidden" name="ffile" value="<?php echo $medias != null ? $medias[0]['mpath'] : '' ?>">
                    </div>
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="desc" name="desc" type="text" placeholder="Enter description..." required><?php echo $medias != null ? $medias[0]['mdescription'] : '' ?></textarea>
                        <label for="desc">Description</label>
                    </div>
                    <!-- Submit Button-->
                    <div class="d-grid"><input class="btn btn-primary btn-xl" id="submittheform" name="submittheform" type="submit" value="Submit"></div>
                </form>
            </div>
        </div>
    </div>
</header>
<?php require_once('footer.php') ?>