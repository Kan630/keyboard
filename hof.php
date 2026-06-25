<?php
header('Content-Type: application/json');

$allowed = ['sentence', 'accountant'];

function hof_read($mode) {
  $file = __DIR__ . "/data/hof_{$mode}.json";
  if (!file_exists($file)) return [];
  $data = json_decode(file_get_contents($file), true);
  return is_array($data) ? $data : [];
}

function flag_emoji($code) {
  $code = strtoupper(trim($code ?? ''));
  if (strlen($code) !== 2 || !ctype_alpha($code)) return '';
  $base = 0x1F1E6 - ord('A');
  $u = function($cp) {
    return pack('C4', 0xF0|($cp>>18), 0x80|(($cp>>12)&0x3F), 0x80|(($cp>>6)&0x3F), 0x80|($cp&0x3F));
  };
  return $u(ord($code[0]) + $base) . $u(ord($code[1]) + $base);
}

// Per sentence (remaining===0 only): keep top-3 individual runs with player info.
// Sentences sorted by their best run's WPM descending.
function hof_aggregate($list) {
  $by = [];
  foreach ($list as $e) {
    if (($e['remaining'] ?? 1) !== 0) continue;
    $sent = $e['sentence'] ?? '';
    if ($sent === '') continue;
    if (!isset($by[$sent])) {
      $by[$sent] = ['sentence' => $sent, 'pack' => $e['pack'] ?? '', 'runs' => []];
    }
    $by[$sent]['runs'][] = [
      'name'      => $e['name']      ?? '',
      'wpm'       => (int)($e['wpm'] ?? 0),
      'corrected' => (int)($e['corrected'] ?? 0),
      'flag'      => $e['flag']      ?? '',
      'country'   => $e['country']   ?? '',
      'date'      => $e['date']      ?? '',
    ];
  }

  $result = [];
  foreach ($by as $data) {
    usort($data['runs'], function($a, $b) { return $b['wpm'] - $a['wpm']; });
    $runs = array_slice($data['runs'], 0, 3);
    $result[] = [
      'sentence' => $data['sentence'],
      'pack'     => $data['pack'],
      'best_wpm' => $runs[0]['wpm'] ?? 0,
      'runs'     => $runs,
    ];
  }

  usort($result, function($a, $b) { return $b['best_wpm'] - $a['best_wpm']; });
  return array_slice($result, 0, 20);
}

// ── GET: per-sentence leaderboard ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $mode = $_GET['mode'] ?? '';
  if (!in_array($mode, $allowed, true)) { http_response_code(400); echo '[]'; exit; }
  echo json_encode(hof_aggregate(hof_read($mode)));
  exit;
}

// ── POST: append entry, persist full history, return per-sentence leaderboard ─
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true);
  if (!$body) { http_response_code(400); echo '{"ok":false}'; exit; }

  $mode = $body['mode'] ?? '';
  if (!in_array($mode, $allowed, true)) { http_response_code(400); echo '{"ok":false}'; exit; }

  $e = $body['entry'] ?? [];

  $raw     = strtoupper(substr((string)($e['country'] ?? ''), 0, 2));
  $country = (strlen($raw) === 2 && ctype_alpha($raw)) ? $raw : '';
  $flag    = flag_emoji($country);
  $record = [
    'name'      => substr(strip_tags($e['name']      ?? 'Anonymous'), 0, 24),
    'wpm'       => (int)($e['wpm']       ?? 0),
    'accuracy'  => (int)($e['accuracy']  ?? 0),
    'remaining' => (int)($e['remaining'] ?? 0),
    'corrected' => (int)($e['corrected'] ?? 0),
    'pack'      => substr(strip_tags($e['pack']      ?? ''), 0, 32),
    'sentence'  => substr(strip_tags($e['sentence']  ?? ''), 0, 300),
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
  $list[]  = $record;

  rewind($fp);
  ftruncate($fp, 0);
  fwrite($fp, json_encode($list));
  flock($fp, LOCK_UN);
  fclose($fp);

  echo json_encode(['ok' => true, 'entries' => hof_aggregate($list)]);
  exit;
}

http_response_code(405);
echo '{"ok":false}';
