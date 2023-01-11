<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['id'] != true) {
    session_destroy();
    header('location:index.php');
}
