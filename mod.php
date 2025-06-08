<?php
include 'db.php';

$order = $_GET['order'] ?? 'baru';
$search = $_GET['search'] ?? '';

$videosPerPage = 18;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startFrom = ($page - 1) * $videosPerPage;

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$totalSql = "SELECT COUNT(*) as total FROM videos";
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $totalSql .= " WHERE title LIKE '%$searchEscaped%'";
}
$totalResult = $conn->query($totalSql);
$totalVideos = 0;
if ($totalResult && $totalResult->num_rows > 0) {
    $totalRow = $totalResult->fetch_assoc();
    $totalVideos = $totalRow['total'];
}
$totalPages = ceil($totalVideos / $videosPerPage);

$searchSql = "";
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $searchSql = "WHERE title LIKE '%$searchEscaped%'";
}

switch ($order) {
    case 'lama':
        $sql = "SELECT * FROM videos $searchSql ORDER BY uploaded_at ASC LIMIT $startFrom, $videosPerPage";
        break;
    case 'random':
        $sql = "SELECT * FROM videos $searchSql ORDER BY RAND() LIMIT $startFrom, $videosPerPage";
        break;
    default:
        $sql = "SELECT * FROM videos $searchSql ORDER BY uploaded_at DESC LIMIT $startFrom, $videosPerPage";
        break;
}

$result = $conn->query($sql);
if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Video</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f8f8f8;
    }

    .navbar {
      background-color: #fff;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .hamburger {
      font-size: 24px;
      cursor: pointer;
      flex: 0 0 auto;
    }

    .logo {
      flex: 1;
      text-align: center;
      font-weight: bold;
      font-size: 20px;
      color: #e74c3c;
    }

    .search-form {
      flex: 0 0 auto;
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-form input[type="text"] {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      width: 200px;
    }

    .search-form button {
      position: absolute;
      right: 5px;
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: #e74c3c;
      padding: 8px;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: -250px;
      width: 250px;
      height: 100%;
      background-color: #333;
      color: white;
      transition: left 0.3s;
      z-index: 2000;
      padding-top: 60px;
    }

    .sidebar a {
      display: block;
      padding: 15px 20px;
      text-decoration: none;
      color: white;
      border-bottom: 1px solid #444;
    }

    .sidebar a:hover {
      background-color: #444;
    }

    .sidebar.show {
      left: 0;
    }

    #overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1500;
      display: none;
    }

    .video-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      padding: 0 10px 20px;
    }

    .video-item {
      background: white;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      width: calc(100% / 6 - 15px);
      text-decoration: none;
      color: inherit;
      position: relative;
    }

    .video-item img {
      width: 100%;
      height: 120px;
      object-fit: cover;
    }

    .video-title {
      padding: 10px;
      font-size: 14px;
      text-align: center;
    }

    .video-actions {
      position: absolute;
      top: 6px;
      right: 6px;
      display: flex;
      gap: 5px;
    }

    .video-actions a {
      background: rgba(0,0,0,0.6);
      color: white;
      text-decoration: none;
      padding: 2px 5px;
      border-radius: 4px;
      font-size: 12px;
    }

    .pagination {
      text-align: center;
      margin: 20px;
    }

    .pagination a, .pagination span {
      display: inline-block;
      margin: 2px;
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ccc;
      background: #eee;
      color: black;
      text-decoration: none;
      font-weight: bold;
    }

    .pagination span.current {
      background: white;
      border: 2px solid #e74c3c;
    }

    .pagination .prev, .pagination .next {
      background: #333;
      color: white;
    }

    .pagination .next {
      background: #e74c3c;
    }

    form.sorting {
      text-align: center;
      margin: 20px 0;
    }

    select {
      padding: 8px 12px;
      font-size: 14px;
      border-radius: 6px;
      border: 1px solid #ccc;
      margin: 0 5px;
    }

    @media (max-width: 600px) {
      .navbar {
        flex-direction: row;
        flex-wrap: nowrap;
      }
      .logo {
        text-align: center;
        flex: 1;
        font-size: 20px;
      }
      .search-form {
        flex-shrink: 0;
        margin-left: 10px;
      }
      .search-form input[type="text"] {
        width: 120px;
      }
      .video-item {
        width: calc(50% - 10px);
      }
    }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <a href="javascript:void(0)" onclick="toggleSidebar()">✖ Tutup</a>
  <a href="index.php">🏠 Home</a>
  <a href="upload.php">➕ Upload Video</a>
   <a href="mod.php">✏️ Edit Video</a>
</div>

<div id="overlay"></div>

<div class="navbar">
  <span class="hamburger" onclick="toggleSidebar()">☰</span>
  <div class="logo">Kelola Video</div>
  <form method="get" action="" class="search-form">
    <input type="text" name="search" placeholder="Cari video..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">🔍</button>
  </form>
</div>

<form method="get" class="sorting">
  <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
  <label for="order">Urutkan:</label>
  <select name="order" id="order" onchange="this.form.submit()">
    <option value="baru" <?= $order == 'baru' ? 'selected' : '' ?>>Paling Baru</option>
    <option value="lama" <?= $order == 'lama' ? 'selected' : '' ?>>Paling Lama</option>
    <option value="random" <?= $order == 'random' ? 'selected' : '' ?>>Acak</option>
  </select>
</form>

<div class="video-container">
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="video-item">
      <div class="video-actions">
        <a href="edit.php?id=<?= $row['id'] ?>" title="Edit">✏️</a>
        <a href="hapus.php?id=<?= $row['id'] ?>" title="Hapus" onclick="return confirm('Yakin ingin menghapus video ini?')">🗑</a>
      </div>
      <a href="video.php?id=<?= $row['id'] ?>">
        <img src="<?= $row['thumbnail'] ?>" alt="Thumbnail">
        <div class="video-title"><?= htmlspecialchars($row['judul']) ?></div>
        <div style="text-align:center; padding-bottom: 10px; color: #777; font-size: 13px;">
          👁️ <?= $row['views'] ?> penonton
        </div>
      </a>
    </div>
  <?php endwhile; ?>
</div>

<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="prev">&larr; Sebelumnya</a>
  <?php endif; ?>

  <?php
    $range = 2;
    for ($i = 1; $i <= $totalPages; $i++) {
      if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)) {
        if ($i == $page) {
          echo "<span class='current'>$i</span>";
        } else {
          echo "<a href='?order=$order&search=".urlencode($search)."&page=$i'>$i</a>";
        }
      } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
        echo "<span>...</span>";
      }
    }
  ?>

  <?php if ($page < $totalPages): ?>
    <a href="?order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="next">Selanjutnya &rarr;</a>
  <?php endif; ?>
</div>

<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('show');
    overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('overlay').style.display = 'none';
  }
  document.getElementById('overlay').addEventListener('click', closeSidebar);
</script>

</body>
</html>
