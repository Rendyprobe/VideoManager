<?php
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT * FROM videos WHERE id = $id");
$video = $result->fetch_assoc();

if (!$video) {
    die("Video tidak ditemukan!");
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $conn->query("UPDATE videos SET views = views + 1 WHERE id = $id");
  }

$video_url = htmlspecialchars($video['url']);
$download_url = str_replace('/e/', '/v/', $video_url);
$orientation = isset($video['orientation']) ? strtolower($video['orientation']) : 'landscape';
$aspect_ratio = ($orientation === 'portrait') ? 177.77 : 56.25;

$order = $_GET['order'] ?? 'random';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$videosPerPage = 12;
$startFrom = ($page - 1) * $videosPerPage;

$whereClause = "WHERE id != $id";
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $whereClause .= " AND title LIKE '%$searchEscaped%'";
}

$countSql = "SELECT COUNT(*) as total FROM videos $whereClause";
$countResult = $conn->query($countSql);
$countRow = $countResult->fetch_assoc();
$totalVideos = $countRow['total'];
$totalPages = ceil($totalVideos / $videosPerPage);

switch ($order) {
    case 'lama':
        $sql = "SELECT * FROM videos $whereClause ORDER BY upload_date ASC LIMIT $startFrom, $videosPerPage";
        break;
    case 'baru':
        $sql = "SELECT * FROM videos $whereClause ORDER BY upload_date DESC LIMIT $startFrom, $videosPerPage";
        break;
    case 'random':
    default:
        $sql = "SELECT * FROM videos $whereClause ORDER BY RAND() LIMIT $startFrom, $videosPerPage";
        break;
}

$recommendation = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?></title>
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
            position: relative;
            width: 100vw;
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: <?= $aspect_ratio ?>%;
            background: #000;
            overflow: hidden;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        h2 {
            font-size: 18px;
            padding: 16px;
            text-align: center;
        }

        h3.recommendation-title {
            text-align: center;
            font-size: 16px;
            margin-top: 40px;
            margin-bottom: 10px;
        }

        form.sorting {
            text-align: center;
            margin-bottom: 20px;
        }

        select, button {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin: 0 5px;
        }

        .video-recommendation {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            padding: 0 10px 20px;
        }

        .video-item {
            width: 200px;
            background: white;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .video-item:hover {
            transform: scale(1.02);
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

        .pagination {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 40px;
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

        .pagination a:hover {
            background: #ddd;
        }

        .pagination .prev, .pagination .next {
            background: #333;
            color: white;
        }

        .pagination .next {
            background: #e74c3c;
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

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="javascript:void(0)" onclick="toggleSidebar()">✖ Tutup</a>
    <a href="index.php">🏠 Home</a>
    <a href="upload.php">➕ Upload Video</a>
    <a href="mod.php">✏️ Edit Video</a>
</div>

<!-- Overlay -->
<div id="overlay"></div>

<!-- Navbar -->
<div class="navbar">
  <span class="hamburger" onclick="toggleSidebar()">☰</span>
  <div class="logo">BOKEPINDO</div>
  <form method="get" action="index.php" class="search-form">
    <input type="text" name="search" placeholder="Cari video..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">🔍</button>
  </form>
</div>

<h2><?= htmlspecialchars($video['judul']) ?></h2>

<div class="video-container">
    <iframe src="<?= $video_url ?>" allowfullscreen></iframe>
</div>

<div style="text-align:center; margin-top: 10px;">
    <a href="<?= $download_url ?>" target="_blank" style="display:inline-block; padding:10px 20px; background:#e74c3c; color:white; text-decoration:none; border-radius:6px; font-weight:bold;">
        Download Video
    </a>
</div>

<h3 class="recommendation-title">Rekomendasi Video Lain</h3>

<form method="get" class="sorting">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
    <label for="order">Urutkan:</label>
    <select name="order" id="order" onchange="this.form.submit()">
        <option value="baru" <?= $order == 'baru' ? 'selected' : '' ?>>Paling Baru</option>
        <option value="lama" <?= $order == 'lama' ? 'selected' : '' ?>>Paling Lama</option>
        <option value="random" <?= $order == 'random' ? 'selected' : '' ?>>Acak</option>
    </select>
</form>

<div class="video-recommendation">
    <?php while ($row = $recommendation->fetch_assoc()): ?>
        <a href="video.php?id=<?= $row['id'] ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>" class="video-item">
            <img src="<?= $row['thumbnail'] ?>" alt="Thumbnail">
            <div class="video-title"><?= htmlspecialchars($row['title']) ?></div>
        </a>
    <?php endwhile; ?>
</div>

<!-- Pagination -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?id=<?= $id ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="prev">&larr; Sebelumnya</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="current"><?= $i ?></span>
        <?php else: ?>
            <a href="?id=<?= $id ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?id=<?= $id ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="next">Selanjutnya &rarr;</a>
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
