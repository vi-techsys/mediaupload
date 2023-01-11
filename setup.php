<?php
$stmt = $pdo->prepare('select * from admin');
$stmt->execute();
$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($user)) {
    $stmt = $pdo->prepare('insert into admin (username, password) values(?,?)');
    $stmt->execute(['admin', md5('admin')]);
}
