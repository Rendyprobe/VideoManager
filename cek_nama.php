<?php
session_start();
include 'db.php';

$session_id = session_id();
$stmt = $conn->prepare("SELECT name FROM chat_users WHERE session_id = ?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "set";
} else {
    echo "unset";
}
?>
