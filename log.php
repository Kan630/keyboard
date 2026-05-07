<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['event'])) { http_response_code(400); exit; }

$allowed = ['visit', 'exercise_complete'];
if (!in_array($data['event'], $allowed, true)) { http_response_code(400); exit; }

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
];

file_put_contents(__DIR__ . '/data/events.log', json_encode($record) . "\n", FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');
echo '{"ok":true}';
