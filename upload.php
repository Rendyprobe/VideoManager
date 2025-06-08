<?php
// Jika metode POST untuk menyimpan metadata
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['judul'], $_POST['thumbnail'], $_POST['url'])) {
    include 'db.php';

    $judul = $_POST['judul'];
    $thumbnail = $_POST['thumbnail'];
    $url = str_replace('/v/', '/e/', $_POST['url']);
    $orientasi = (strpos($url, 'portrait') !== false) ? 'portrait' : 'landscape';

    $stmt = $conn->prepare("INSERT INTO videos (judul, thumbnail, orientasi, url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $judul, $thumbnail, $orientasi, $url);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "✅ Metadata video disimpan!";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload Video ke Streamtape</title>
  <style>
    body {
      font-family: sans-serif;
      padding: 20px;
      max-width: 600px;
      margin: auto;
    }
    form {
      background: #f9f9f9;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    input, button {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }
    button {
      background-color: #4CAF50;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }
    .progress-container {
      background: #e0e0e0;
      border-radius: 10px;
      overflow: hidden;
      height: 24px;
      display: none;
    }
    .progress-bar {
      height: 100%;
      width: 0%;
      color: #fff;
      text-align: center;
      line-height: 24px;
      font-size: 12px;
      font-weight: bold;
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      background-size: 200% 200%;
      animation: flowing 1.5s linear infinite;
      transition: width 0.3s ease;
    }
    @keyframes flowing {
      0% { background-position: 0% 50%; }
      100% { background-position: 100% 50%; }
    }
  </style>
</head>
<body>

<h2>Upload Video ke Streamtape</h2>

<form id="uploadForm">
  <div class="progress-container" id="progressContainer">
    <div class="progress-bar" id="progressBar">0%</div>
  </div>

  <label>Pilih video:</label>
  <input type="file" id="videoFile" accept="video/*" required>

  <label>Pilih Gambar Thumbnail:</label>
  <input type="file" id="thumbnailFile" accept="image/*" required>

  <button type="submit">Upload Video</button>
</form>

<div id="result" style="margin-top: 20px;"></div>

<script>
async function uploadThumbnailToBackend(file) {
  const formData = new FormData();
  formData.append('thumbnail', file);

  const response = await fetch('upload-thumbnail.php', {
    method: 'POST',
    body: formData
  });

  if (!response.ok) throw new Error('❌ Gagal kirim request ke backend');
  const data = await response.json();

  if (data && data.image && data.image.url) {
    return data.image.url;
  } else {
    throw new Error('❌ Gagal upload thumbnail ke ImgBB (respon kosong)');
  }
}

const form = document.getElementById('uploadForm');
const fileInput = document.getElementById('videoFile');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const resultBox = document.getElementById('result');

form.addEventListener('submit', async function(e) {
  e.preventDefault();

  const file = fileInput.files[0];
  const thumbnailFile = document.getElementById('thumbnailFile').files[0];
  if (!file || !thumbnailFile) {
    alert('Mohon pilih video dan thumbnail.');
    return;
  }

  let thumbnailUrl = '';
  try {
    thumbnailUrl = await uploadThumbnailToBackend(thumbnailFile);
  } catch (err) {
    alert(err.message);
    return;
  }

  const streamtapeApi = 'https://api.streamtape.com/file/ul?login=ead384c194122063879f&key=L3gvwy39yKFR1z3';
  const response = await fetch(streamtapeApi);
  const data = await response.json();
  if (data.status !== 200) {
    alert("❌ Gagal mendapatkan upload URL Streamtape.");
    return;
  }

  const uploadUrl = data.result.url;
  const formData = new FormData();
  formData.append('file1', file);

  progressContainer.style.display = 'block';
  const xhr = new XMLHttpRequest();
  xhr.open('POST', uploadUrl);

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      const percent = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percent + '%';
      progressBar.textContent = percent + '%';
    }
  };

  xhr.onload = async function() {
    const result = JSON.parse(xhr.responseText);
    if (result.status !== 200) {
      alert("❌ Upload gagal: " + result.msg);
      return;
    }

    const fileUrl = result.result.url;
    const title = file.name.split('.').slice(0, -1).join('.').replace(/[_-]/g, ' ');

    const metaData = new FormData();
    metaData.append('judul', title);
    metaData.append('thumbnail', thumbnailUrl);
    metaData.append('url', fileUrl);

    const save = await fetch('', { method: 'POST', body: metaData });
    const saveText = await save.text();

    progressBar.textContent = '✅ Selesai';
    resultBox.innerHTML = `
      <p><strong>Judul:</strong> ${title}</p>
      <p><strong>URL:</strong> <a href="${fileUrl}" target="_blank">${fileUrl}</a></p>
      <img src="${thumbnailUrl}" style="max-width:200px;">
      <p>${saveText}</p>
    `;
  };

  xhr.onerror = () => alert('❌ Upload gagal karena koneksi.');
  xhr.send(formData);
});
</script>

</body>
</html>
