<?php
header('Content-Type: application/json');

$allowed = ['sentence', 'accountant'];

function hof_sort(&$list) {
  usort($list, function($a, $b) {
    $ra = $a['remaining'] ?? 0; $rb = $b['remaining'] ?? 0;
    if ($ra !== $rb) return $ra - $rb;
    if ($b['accuracy'] !== $a['accuracy']) return $b['accuracy'] - $a['accuracy'];
    return $b['wpm'] - $a['wpm'];
  });
}

function hof_read($mode) {
  $file = __DIR__ . "/data/hof_{$mode}.json";
  if (!file_exists($file)) return [];
  $data = json_decode(file_get_contents($file), true);
  return is_array($data) ? $data : [];
}

function get_country($ip) {
  if (empty($ip)) return '';
  if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return '';
  $ctx  = stream_context_create(['http' => ['timeout' => 3]]);
  $resp = @file_get_contents('http://ip-api.com/json/' . rawurlencode($ip) . '?fields=countryCode', false, $ctx);
  if (!$resp) return '';
  $data = json_decode($resp, true);
  return $data['countryCode'] ?? '';
}

function flag_emoji($code) {
  $code = strtoupper(trim($code ?? ''));
  if (strlen($code) !== 2 || !ctype_alpha($code)) return '';
  $base = 0x1F1E6 - ord('A');
  return mb_chr(ord($code[0]) + $base, 'UTF-8') . mb_chr(ord($code[1]) + $base, 'UTF-8');
}

// ── GET: return top 10 for a mode ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $mode = $_GET['mode'] ?? '';
  if (!in_array($mode, $allowed, true)) { http_response_code(400); echo '[]'; exit; }
  $list = hof_read($mode);
  hof_sort($list);
  echo json_encode(array_slice($list, 0, 10));
  exit;
}

// ── POST: append entry to full history, persist, return top 10 ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true);
  if (!$body) { http_response_code(400); echo '{"ok":false}'; exit; }

  $mode = $body['mode'] ?? '';
  if (!in_array($mode, $allowed, true)) { http_response_code(400); echo '{"ok":false}'; exit; }

  $ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
  if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
  $country = get_country($ip);
  $flag    = flag_emoji($country);

  $e = $body['entry'] ?? [];
  $record = [
    'name'      => substr(strip_tags($e['name']      ?? 'Anonymous'), 0, 24),
    'wpm'       => (int)($e['wpm']       ?? 0),
    'accuracy'  => (int)($e['accuracy']  ?? 0),
    'remaining' => (int)($e['remaining'] ?? 0),
    'corrected' => (int)($e['corrected'] ?? 0),
    'pack'      => substr(strip_tags($e['pack']      ?? ''), 0, 32),
    'flag'      => $flag,
    'country'   => $country,
    'date'      => substr($e['date']     ?? '', 0, 32),
    'ts'        => date('c'),
  ];

  $file = __DIR__ . "/data/hof_{$mode}.json";
  $fp   = fopen($file, 'c+');
  flock($fp, LOCK_EX);

  $content = stream_get_contents($fp);
  $list    = $content ? (json_decode($content, true) ?? []) : [];
  $list[]  = $record;               // keep full history

  rewind($fp);
  ftruncate($fp, 0);
  fwrite($fp, json_encode($list));
  flock($fp, LOCK_UN);
  fclose($fp);

  hof_sort($list);
  echo json_encode(['ok' => true, 'entries' => array_slice($list, 0, 10)]);
  exit;
}

http_response_code(405);
echo '{"ok":false}';
