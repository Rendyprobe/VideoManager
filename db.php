<?php
$host = 'sql203.infinityfree.com';
$user = 'if0_38638198';
$pass = 'Rendy123109';
$dbname = 'if0_38638198_videos';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
