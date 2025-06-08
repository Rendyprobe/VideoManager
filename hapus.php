<?php
include 'db.php';

$notif = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $get = $conn->query("SELECT * FROM videos WHERE id = $id");
    $data = $get->fetch_assoc();

    if ($data) {
        $url = $data['url'];

        // Ekstrak file_code dari URL Streamtape
        if (preg_match('#/v/([a-zA-Z0-9]+)#', $url, $matches)) {
            $fileCode = $matches[1];

            // Hapus dari Streamtape
            $login = 'ead384c194122063879f';
            $key = 'L3gvwy39yKFR1z3';
            $deleteUrl = "https://api.streamtape.com/file/delete?login=$login&key=$key&file_code=$fileCode";
            @file_get_contents($deleteUrl);
        }

        // Hapus dari database
        $conn->query("DELETE FROM videos WHERE id = $id");
        $notif = "✅ Video berhasil dihapus. <a href='mod.php'>Kembali</a>";
    } else {
        $notif = "❌ Video tidak ditemukan. <a href='mod.php'>Kembali</a>";
    }
} else {
    $notif = "❌ ID tidak valid. <a href='mod.php'>Kembali</a>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hapus Video</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      padding: 20px;
      margin: 0;
    }

    .notif {
      background: #dff0d8;
      color: #2d572c;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      max-width: 500px;
      margin: 30px auto;
      font-weight: bold;
      font-size: 20px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    a {
      color: #e74c3c;
      text-decoration: none;
      font-weight: bold;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="notif"><?= $notif ?></div>

</body>
</html>
