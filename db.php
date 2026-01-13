<?php
$conn = mysqli_connect("localhost", "root", "", "post_app");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
session_start();
?>
