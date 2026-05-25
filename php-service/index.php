<?php
header('Content-Type: application/json');

$host   = getenv('DB_HOST')     ?: 'db';
$user   = getenv('DB_USER')     ?: 'root';
$pass   = getenv('DB_PASSWORD') ?: 'alit';
$dbname = getenv('DB_NAME')     ?: 'p12tugas';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => $conn->connect_error]);
    exit;
}

// Buat tabel jika belum ada
$conn->query("
    CREATE TABLE IF NOT EXISTS mahasiswa (
        id   INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        npm  VARCHAR(20)  NOT NULL
    )
");

$method = $_SERVER['REQUEST_METHOD'];
$uri    = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$id     = isset($uri[1]) && is_numeric($uri[1]) ? (int)$uri[1] : null;
$body   = json_decode(file_get_contents('php://input'), true);

// CREATE
if ($method === 'POST' && $uri[0] === 'mahasiswa') {
    $nama = $conn->real_escape_string($body['nama']);
    $npm  = $conn->real_escape_string($body['npm']);
    $conn->query("INSERT INTO mahasiswa (nama, npm) VALUES ('$nama', '$npm')");
    http_response_code(201);
    echo json_encode(['id' => $conn->insert_id, 'nama' => $nama, 'npm' => $npm]);
}

// READ ALL
elseif ($method === 'GET' && $uri[0] === 'mahasiswa' && !$id) {
    $result = $conn->query("SELECT * FROM mahasiswa");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
}

// READ ONE
elseif ($method === 'GET' && $uri[0] === 'mahasiswa' && $id) {
    $result = $conn->query("SELECT * FROM mahasiswa WHERE id = $id");
    $row = $result->fetch_assoc();
    echo $row ? json_encode($row) : json_encode(['message' => 'Tidak ditemukan']);
}

// UPDATE
elseif ($method === 'PUT' && $uri[0] === 'mahasiswa' && $id) {
    $nama = $conn->real_escape_string($body['nama']);
    $npm  = $conn->real_escape_string($body['npm']);
    $conn->query("UPDATE mahasiswa SET nama='$nama', npm='$npm' WHERE id=$id");
    echo $conn->affected_rows
        ? json_encode(['id' => $id, 'nama' => $nama, 'npm' => $npm])
        : json_encode(['message' => 'Tidak ditemukan']);
}

// DELETE
elseif ($method === 'DELETE' && $uri[0] === 'mahasiswa' && $id) {
    $conn->query("DELETE FROM mahasiswa WHERE id=$id");
    echo $conn->affected_rows
        ? json_encode(['message' => 'Data berhasil dihapus'])
        : json_encode(['message' => 'Tidak ditemukan']);
}

else {
    http_response_code(404);
    echo json_encode(['message' => 'Route tidak ditemukan']);
}

$conn->close();
?>