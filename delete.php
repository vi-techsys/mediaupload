<?php
require_once('dbconn.php');
require_once('session.php');
$id = $_GET['id'];
//get file record
$stmt = $pdo->prepare('select * from my_media where mid =?');
$stmt->execute([$id]);
$medias = $stmt->fetchAll(PDO::FETCH_ASSOC);
$dis_file = $medias[0];
$stmt = $pdo->prepare('delete from my_media where mid = ?');
$stmt->execute([$id]);
unlink($dis_file['mpath']);
header('location:dashboard.php');
