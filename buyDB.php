<?php

    $host = 'localhost';
    $userName = 'root';
    $password = '';
    $dbName = 'lottery';

    $conn = new mysqli($host, $userName, $password, $dbName);

    if(!$conn){
        die("Connection failed: ");
    }

    

?>