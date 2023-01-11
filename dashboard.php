<?php require_once('header.php');
require_once('dbconn.php');
require_once('session.php');
?>
<!-- Masthead-->

<header class="masthead">
    <div class="container px-4 px-lg-5 h-100">
        <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-12 col-md-12 col-sm-12 align-self-baseline">
                <h3 style="color:white;">Media Uploaded</h3>
                <table class="table table-responsive table-danger table-bordered">
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Edit</th>
                        <th>Delete</th>
                        <th>View</th>
                    </tr>
                    <?php

                    $stmt = $pdo->prepare('select * from my_media');
                    $stmt->execute();
                    $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($medias as $m) { ?>
                        <tr>
                            <td><?= $m['mid'] ?></td>
                            <td><?= $m['mdescription'] ?></td>
                            <td><?= '<a class ="btn btn-sm btn-warning" href = "edit.php?id=' . $m['mid'] . '">Edit</a></a>' ?></td>
                            <td><button data-id="<?php echo $m['mid']; ?>" onclick="delete_(this.dataset.id)" class="btn btn-danger btn-sm">Delete</button></td>
                            <td><?= '<a class ="btn btn-sm btn-success" href = "' . $m['mpath'] . '" target ="__blank">View</a></a>' ?></td>
                        </tr>
                    <?php }
                    ?>
                </table>
            </div>
        </div>
    </div>
</header>
<?php require_once('footer.php') ?>

<script>
    function delete_(id) {
        if (id != "") {
            if (confirm("Delete file?")) {
                location.href = "<?php echo ('delete.php?id='); ?>" + id;
            }
        }
    }
</script>