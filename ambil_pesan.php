<?php
include 'db.php';
$query = "SELECT cm.message, cu.name, cm.created_at FROM chat_messages cm
          JOIN chat_users cu ON cm.user_id = cu.user_id ORDER BY cm.created_at DESC LIMIT 50";
$result = $conn->query($query);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode(array_reverse($messages));
?>
