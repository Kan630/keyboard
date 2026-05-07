<?php
// ── Auth ─────────────────────────────────────────────────────────────────────
define('ADMIN_PASSWORD', 'kbm-admin');   // change this

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pw'])) {
  if ($_POST['pw'] === ADMIN_PASSWORD) $_SESSION['kbm_admin'] = true;
  else $error = true;
}
if (isset($_POST['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

if (empty($_SESSION['kbm_admin'])) {
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8">
<title>KBM Admin — Login</title>
<style>
  body { font-family: sans-serif; background:#0d1117; color:#e6edf3;
         display:flex; align-items:center; justify-content:center; min-height:100vh; }
  form { background:#161b22; border:1px solid #30363d; border-radius:12px;
         padding:32px; display:flex; flex-direction:column; gap:12px; min-width:280px; }
  input[type=password] { padding:8px 12px; border-radius:6px; border:1px solid #30363d;
                          background:#21262d; color:#e6edf3; font-size:1rem; }
  button { padding:8px 16px; border-radius:6px; border:none; background:#58a6ff;
           color:#0d1117; font-weight:700; cursor:pointer; }
  .err { color:#f85149; font-size:.85rem; }
</style></head><body>
<form method="post">
  <h2 style="margin:0;color:#58a6ff">KBM Admin</h2>
  <input type="password" name="pw" placeholder="Password" autofocus>
  <button type="submit">Login</button>
  <?php if (!empty($error)) echo '<p class="err">Wrong password.</p>'; ?>
</form>
</body></html>
<?php exit; }

// ── Data helpers ─────────────────────────────────────────────────────────────
function read_json($path) {
  if (!file_exists($path)) return [];
  $d = json_decode(file_get_contents($path), true);
  return is_array($d) ? $d : [];
}

function read_log($path) {
  if (!file_exists($path)) return [];
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  return array_reverse(array_filter(array_map(fn($l) => json_decode($l, true), $lines)));
}

$hof_s = read_json(__DIR__ . '/data/hof_sentence.json');
$hof_a = read_json(__DIR__ . '/data/hof_accountant.json');
$events = read_log(__DIR__ . '/data/events.log');

// sort hof by: remaining ASC, accuracy DESC, wpm DESC
$hof_sort = function(&$list) {
  usort($list, function($a,$b){
    if (($a['remaining']??0) !== ($b['remaining']??0)) return ($a['remaining']??0)-($b['remaining']??0);
    if ($b['accuracy'] !== $a['accuracy']) return $b['accuracy']-$a['accuracy'];
    return $b['wpm']-$a['wpm'];
  });
};
$hof_sort($hof_s);
$hof_sort($hof_a);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>KBM Admin</title>
<style>
  *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
         background:#0d1117; color:#e6edf3; padding:24px 20px; }
  h1   { color:#58a6ff; font-size:1.5rem; margin-bottom:4px; }
  h2   { color:#58a6ff; font-size:1rem; margin:24px 0 10px; border-bottom:1px solid #30363d; padding-bottom:6px; }
  .meta { color:#8b949e; font-size:.8rem; margin-bottom:20px; }
  table { width:100%; border-collapse:collapse; font-size:.82rem; margin-bottom:8px; }
  th   { background:#21262d; color:#8b949e; text-align:left; padding:6px 8px;
         font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; }
  td   { padding:5px 8px; border-bottom:1px solid #21262d; }
  tr:hover td { background:#161b22; }
  .ok  { color:#3fb950; }
  .err { color:#f85149; }
  .muted { color:#8b949e; }
  .logout { float:right; padding:4px 12px; border-radius:6px; border:1px solid #30363d;
            background:#21262d; color:#e6edf3; font-size:.8rem; cursor:pointer; }
  .summary { display:flex; gap:24px; margin-bottom:16px; }
  .card { background:#161b22; border:1px solid #30363d; border-radius:8px;
          padding:12px 18px; min-width:100px; }
  .card .val { font-size:1.4rem; font-weight:700; color:#58a6ff; }
  .card .lbl { font-size:.7rem; color:#8b949e; }
  .ev-visit { color:#8b949e; }
</style>
</head><body>

<form method="post" style="display:inline">
  <button name="logout" class="logout">Logout</button>
</form>
<h1>Keyboard Master — Admin</h1>
<p class="meta">Server time: <?= date('Y-m-d H:i:s') ?></p>

<?php
$total_ex = count(array_filter($events, fn($e) => ($e['event']??'') === 'exercise_complete'));
$total_vi = count(array_filter($events, fn($e) => ($e['event']??'') === 'visit'));
?>
<div class="summary">
  <div class="card"><div class="val"><?= count($events) ?></div><div class="lbl">Total events</div></div>
  <div class="card"><div class="val"><?= $total_vi ?></div><div class="lbl">Visits</div></div>
  <div class="card"><div class="val"><?= $total_ex ?></div><div class="lbl">Exercises done</div></div>
  <div class="card"><div class="val"><?= count($hof_s) ?></div><div class="lbl">Writer entries</div></div>
  <div class="card"><div class="val"><?= count($hof_a) ?></div><div class="lbl">Accountant entries</div></div>
</div>

<!-- ── Hall of Fame: Writer ───────────────────────────────────────────── -->
<h2>Hall of Fame — Writer (all <?= count($hof_s) ?> entries)</h2>
<?php if ($hof_s): ?>
<table>
  <tr><th>#</th><th>Name</th><th>WPM</th><th>Accuracy</th><th>Remaining</th><th>Corrections</th><th>Date</th><th>Saved</th></tr>
  <?php foreach ($hof_s as $i => $e): ?>
  <tr>
    <td class="muted"><?= $i+1 ?></td>
    <td><?= h($e['name']) ?></td>
    <td class="ok"><?= h($e['wpm']) ?></td>
    <td><?= h($e['accuracy']) ?>%</td>
    <td class="<?= ($e['remaining']??0) > 0 ? 'err' : 'ok' ?>"><?= $e['remaining']??0 ?></td>
    <td><?= $e['corrected']??0 ?></td>
    <td><?= h($e['date']) ?></td>
    <td class="muted"><?= h($e['ts']??'') ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php else: ?><p class="muted">No entries yet.</p><?php endif; ?>

<!-- ── Hall of Fame: Accountant ──────────────────────────────────────── -->
<h2>Hall of Fame — Accountant (all <?= count($hof_a) ?> entries)</h2>
<?php if ($hof_a): ?>
<table>
  <tr><th>#</th><th>Name</th><th>WPM</th><th>Accuracy</th><th>Remaining</th><th>Corrections</th><th>Date</th><th>Saved</th></tr>
  <?php foreach ($hof_a as $i => $e): ?>
  <tr>
    <td class="muted"><?= $i+1 ?></td>
    <td><?= h($e['name']) ?></td>
    <td class="ok"><?= h($e['wpm']) ?></td>
    <td><?= h($e['accuracy']) ?>%</td>
    <td class="<?= ($e['remaining']??0) > 0 ? 'err' : 'ok' ?>"><?= $e['remaining']??0 ?></td>
    <td><?= $e['corrected']??0 ?></td>
    <td><?= h($e['date']) ?></td>
    <td class="muted"><?= h($e['ts']??'') ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php else: ?><p class="muted">No entries yet.</p><?php endif; ?>

<!-- ── Event log ─────────────────────────────────────────────────────── -->
<h2>Event log — last <?= min(count($events),500) ?> of <?= count($events) ?></h2>
<?php $shown = array_slice($events, 0, 500); ?>
<table>
  <tr><th>Time</th><th>Event</th><th>IP</th><th>Layout</th><th>WPM</th><th>Accuracy</th><th>Remaining</th><th>Corrections</th><th>Duration</th><th>Mode</th></tr>
  <?php foreach ($shown as $e): ?>
  <tr class="<?= ($e['event']??'') === 'visit' ? 'ev-visit' : '' ?>">
    <td class="muted"><?= h($e['ts']??'') ?></td>
    <td><?= h($e['event']??'') ?></td>
    <td class="muted"><?= h($e['ip']??'') ?></td>
    <td><?= h($e['layout']??'') ?></td>
    <td><?= h($e['wpm']??'') ?></td>
    <td><?= isset($e['accuracy']) ? h($e['accuracy']).'%' : '' ?></td>
    <td><?= h($e['errors']??'') ?></td>
    <td><?= h($e['corrected']??'') ?></td>
    <td><?= h($e['dur']??'') ?></td>
    <td><?= h($e['sentmode']??'') ?></td>
  </tr>
  <?php endforeach; ?>
</table>

</body></html>
