<?php

$SERVERNAME = "localhost";
$USERNAME = "root";
$PASSWORD = "";
$DATABASE = "my_francescoarzilli3g";

$conn = new mysqli($SERVERNAME, $USERNAME, $PASSWORD, $DATABASE);

if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);
