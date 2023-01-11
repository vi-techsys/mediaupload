<?php
$db_username = 'sa';
$db_password = 'CV%65gvn@3';
//$mss_conn = new PDO('sqlsrv:Server=163.123.180.105;Database=HD_Rest_Cashier', $db_username, $db_password);

$connectionInfo = array("Database" => "HD_Rest_Cashier", 'UID' => $db_username, 'PSW' => $db_password);
$con = sqlsrv_connect('163.123.180.105', $connectionInfo);
