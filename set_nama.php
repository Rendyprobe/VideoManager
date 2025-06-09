<?php
session_start();
include 'db.php';

$name = $_POST['name'];
$session_id = session_id();

$stmt = $conn->prepare("INSERT INTO chat_users (session_id, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
$stmt->bind_param("ss", $session_id, $name);
$stmt->execute();
echo "success";
?>
