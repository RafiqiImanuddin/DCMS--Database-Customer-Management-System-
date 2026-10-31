<?php
header('Content-Type: application/json; charset=utf-8');

$historyFile = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'DCMS' . DIRECTORY_SEPARATOR . 'broadcast_history.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $history = is_file($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];
    echo json_encode(['success' => true, 'history' => is_array($history) ? array_reverse($history) : []], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$config = require __DIR__ . DIRECTORY_SEPARATOR . 'watzap_config.php';
$apiKey = trim((string)($config['api_key'] ?? getenv('WATZAP_API_KEY')));
$numberKey = trim((string)($config['number_key'] ?? getenv('WATZAP_NUMBER_KEY')));
$recipients = $input['recipients'] ?? [];

if ($apiKey === '' || $numberKey === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kredensial Watzap belum dikonfigurasi di backend']);
    exit;
}
if (!is_array($recipients) || count($recipients) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Belum ada penerima dipilih']);
    exit;
}

$results = [];
foreach ($recipients as $recipient) {
    $phone = preg_replace('/[^0-9]/', '', (string)($recipient['phone'] ?? ''));
    $message = trim((string)($recipient['message'] ?? ''));
    if ($phone === '' || $message === '') {
        $results[] = array_merge($recipient, ['phone' => $phone, 'success' => false, 'message' => 'Nomor atau pesan kosong']);
        continue;
    }
    if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);

    $payload = json_encode(['api_key' => $apiKey, 'number_key' => $numberKey, 'phone_no' => $phone, 'message' => $message], JSON_UNESCAPED_UNICODE);
    $ch = curl_init('https://api.watzap.id/v1/send_message');
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    $parsed = json_decode((string)$body, true);
    $ok = $httpCode >= 200 && $httpCode < 300 && empty($parsed['error']);
    $results[] = array_merge($recipient, [
        'phone' => $phone,
        'success' => $ok,
        'status' => $httpCode,
        'message' => $ok ? 'Terkirim/diterima API' : ($parsed['message'] ?? $parsed['status'] ?? $curlError ?: 'Gagal mengirim'),
    ]);
}

$successful = count(array_filter($results, fn($item) => $item['success']));
$batch = [
    'batch_id' => date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8),
    'created_at' => date('c'),
    'sent' => $successful,
    'failed' => count($results) - $successful,
    'total' => count($results),
    'recipients' => $results,
];

if (!is_dir(dirname($historyFile))) mkdir(dirname($historyFile), 0777, true);
$history = is_file($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];
if (!is_array($history)) $history = [];
$history[] = $batch;
file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

echo json_encode(['success' => $successful > 0, 'sent' => $successful, 'failed' => count($results) - $successful, 'results' => $results, 'batch' => $batch], JSON_UNESCAPED_UNICODE);
