<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['event'])) { http_response_code(400); exit; }

$allowed = ['visit', 'exercise_complete'];
if (!in_array($data['event'], $allowed, true)) { http_response_code(400); exit; }

$raw_cc  = strtoupper(substr((string)($data['country'] ?? ''), 0, 2));
$country = (strlen($raw_cc) === 2 && ctype_alpha($raw_cc)) ? $raw_cc : '';
$u = function($cp) {
  return pack('C4', 0xF0|($cp>>18), 0x80|(($cp>>12)&0x3F), 0x80|(($cp>>6)&0x3F), 0x80|($cp&0x3F));
};
$flag = $country
  ? $u(ord($country[0]) + 0x1F1E6 - ord('A')) . $u(ord($country[1]) + 0x1F1E6 - ord('A'))
  : '';

$record = [
    'ts'       => date('c'),
    'ip'       => $_SERVER['REMOTE_ADDR'] ?? '',
    'event'    => $data['event'],
    'layout'   => substr($data['layout']   ?? '', 0, 32),
    'wpm'      => (int)($data['wpm']       ?? 0),
    'accuracy' => (int)($data['accuracy']  ?? 0),
    'errors'   => (int)($data['errors']    ?? 0),
    'dur'      => substr($data['dur']      ?? '', 0, 16),
    'sentmode' => substr($data['sentmode'] ?? '', 0, 8),
    'country'  => $country,
    'flag'     => $flag,
];

file_put_contents(__DIR__ . '/data/events.log', json_encode($record) . "\n", FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');
echo '{"ok":true}';
