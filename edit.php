<?php
include 'db.php';
$id = (int)$_GET['id'];

$result = $conn->query("SELECT * FROM videos WHERE id = $id");
$data = $result->fetch_assoc();

$notif = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $thumbnail = $_POST['thumbnail'];
    $url = $_POST['url'];

    $stmt = $conn->prepare("UPDATE videos SET judul=?, thumbnail=?, url=? WHERE id=?");
    $stmt->bind_param("sssi", $judul, $thumbnail, $url, $id);
    $stmt->execute();
    $stmt->close();

    $notif = "✅ Data berhasil diperbarui. <a href='mod.php'>Kembali</a>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Video</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      padding: 20px;
      margin: 0;
    }

    .container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #e74c3c;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }

    button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background-color: #e74c3c;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #c0392b;
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

    @media (max-width: 600px) {
      .container {
        padding: 15px;
        margin: 10px;
      }
    }
  </style>
</head>
<body>

<?php if ($notif): ?>
  <div class="notif"><?= $notif ?></div>
<?php endif; ?>

<div class="container">
  <h2>Edit Video</h2>
  <form method="post">
    <label>Judul</label>
    <input type="text" name="judul" value="<?= htmlspecialchars($data['judul']) ?>" required>

    <label>URL Thumbnail</label>
    <input type="text" name="thumbnail" value="<?= $data['thumbnail'] ?>" required>

    <label>URL Video Streamtape</label>
    <input type="text" name="url" value="<?= $data['url'] ?>" required>

    <button type="submit">💾 Simpan</button>
  </form>
</div>

</body>
</html>
