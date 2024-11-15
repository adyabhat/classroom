<?php
    /*
    A PHP script is executed on the server, and the plain HTML result is sent back to the browser.
    Basic PHP Syntax: A PHP script can be placed anywhere in the document.
        A PHP script starts with <?php and ends with ?>
        comment: like c
    pdo vs mysqli: PDO will work on 12 different database systems, whereas MySQLi will only work with MySQL databases. pdo: php data objects, a method to connect mysql through php
    */

    $host = "localhost";
    $username = "root";
    $password = "adya";
    $db = 'gcc';

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        //  echo "Connected successfully!"; // might have to remove this (or not, if there's no way to reach this page through gui)
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    } 
?>