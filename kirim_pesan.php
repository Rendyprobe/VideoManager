<?php
session_start();
include 'db.php';

$session_id = session_id();
$message = $_POST['message'];

$stmt = $conn->prepare("SELECT user_id FROM chat_users WHERE session_id = ?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    echo "sent";
} else {
    echo "error";
}
?>
