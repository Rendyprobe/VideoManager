<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['thumbnail'])) {
    $apiKey = 'e343a079f6f8bea9e40d5554a9fc6a12';

    // Pastikan file valid
    if ($_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Upload gagal: File tidak valid']);
        exit;
    }

    $filePath = $_FILES['thumbnail']['tmp_name'];
    $fileName = $_FILES['thumbnail']['name'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $postFields = [
        'image' => curl_file_create($filePath, mime_content_type($filePath), $fileName)
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Curl Error: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    // Jika sukses, kirimkan URL gambar
    if (isset($result['data']['url'])) {
        echo json_encode(['image' => ['url' => $result['data']['url']]]);
    } else {
        echo json_encode(['error' => 'Gagal upload ke ImgBB. Respon: ' . $response]);
    }
}
?>
