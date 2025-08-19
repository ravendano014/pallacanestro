<?php
$database_username = 'Indes_goldslide'; /* User */
$database_password = 'ef99d455fe29d8a767b5eec740922efb1c4b9a61'; /* Password */
$database_DB = "Indes_goldslide";
try {
     $pdo_conn = new PDO( 'mysql:host=v2k5a1.h.filess.io;dbname='.$database_DB, $database_username, $database_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
     // $pdo_conn = new PDO( 'mysql:host=localhost;dbname='.$database_DB, $database_username, $database_password );
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
// Connections/Connection_PDO.php
