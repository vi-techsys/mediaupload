<?php
header('Content-Type: application/json');
error_reporting(0);
require_once('dbconn.php');
$id = addslashes($_GET['id']);
$medias = array();
if (!empty($id)) {
    $stmt = $pdo->prepare('select * from my_media where mid = ' . $id);
    $stmt->execute();
    $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {

    $stmt = $pdo->prepare('select * from my_media');
    $stmt->execute();
    $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$mobj = array('media' => $medias);
$json = json_encode($mobj);
echo  $json;
