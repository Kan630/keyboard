<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$allowed = ['sentence', 'accountant'];

// ── GET: return current HoF for a mode ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $mode = $_GET['mode'] ?? '';
  if (!in_array($mode, $allowed, true)) { http_response_code(400); echo '[]'; exit; }
  $file = __DIR__ . "/data/hof_{$mode}.json";
  echo file_exists($file) ? file_get_contents($file) : '[]';
  exit;
}

// ── POST: add entry, sort, trim to top 10, persist ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true);
  if (!$body) { http_response_code(400); echo '{"ok":false}'; exit; }

  $mode = $body['mode'] ?? '';
  if (!in_array($mode, $allowed, true)) { http_response_code(400); echo '{"ok":false}'; exit; }

  $e = $body['entry'] ?? [];
  $record = [
    'name'      => substr(strip_tags($e['name']      ?? 'Anonymous'), 0, 24),
    'wpm'       => (int)($e['wpm']       ?? 0),
    'accuracy'  => (int)($e['accuracy']  ?? 0),
    'remaining' => (int)($e['remaining'] ?? 0),
    'corrected' => (int)($e['corrected'] ?? 0),
    'date'      => substr($e['date']     ?? '', 0, 32),
  ];

  $file = __DIR__ . "/data/hof_{$mode}.json";
  $fp   = fopen($file, 'c+');
  flock($fp, LOCK_EX);

  $content = stream_get_contents($fp);
  $list    = $content ? (json_decode($content, true) ?? []) : [];

  $list[] = $record;

  usort($list, function($a, $b) {
    $ra = $a['remaining'] ?? 0; $rb = $b['remaining'] ?? 0;
    if ($ra !== $rb) return $ra - $rb;
    if ($b['accuracy'] !== $a['accuracy']) return $b['accuracy'] - $a['accuracy'];
    return $b['wpm'] - $a['wpm'];
  });

  $list = array_slice($list, 0, 10);

  rewind($fp);
  ftruncate($fp, 0);
  fwrite($fp, json_encode($list));
  flock($fp, LOCK_UN);
  fclose($fp);

  echo json_encode(['ok' => true, 'entries' => $list]);
  exit;
}

http_response_code(405);
echo '{"ok":false}';
