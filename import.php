<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File gagal diunggah']);
    exit;
}

$file = $_FILES['file'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format harus XLSX, XLS, atau CSV']);
    exit;
}

if ($file['size'] > 100 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 100 MB']);
    exit;
}

$root = __DIR__;
$uploadDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'DCMS';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Folder uploads gagal dibuat']);
    exit;
}

$period = preg_replace('/[^0-9-]/', '', $_POST['period'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Periode harus YYYY-MM']);
    exit;
}

$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
$destination = $uploadDir . DIRECTORY_SEPARATOR . $period . '-' . $safeName;
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'File tidak dapat disimpan ke folder DCMS']);
    exit;
}

$python = getenv('PYTHON_EXE') ?: 'python';
$script = $root . DIRECTORY_SEPARATOR . 'process_import.py';
$command = escapeshellarg($python) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($destination) . ' ' . escapeshellarg($period);
exec($command . ' 2>&1', $output, $exitCode);
$resultText = trim(implode("\n", $output));
if ($exitCode !== 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memproses file', 'detail' => $resultText]);
    exit;
}

$result = json_decode($resultText, true);
if (!$result || empty($result['success'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Output pemrosesan tidak valid', 'detail' => $resultText]);
    exit;
}

$manifestPath = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'manifest.json';
$manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
if (!is_array($manifest)) $manifest = [];
$manifest[$period] = ['file' => basename($destination), 'imported_at' => date('c'), 'rows' => $result['rows'] ?? 0];
file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$result['stored_file'] = 'uploads/DCMS/' . basename($destination);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
