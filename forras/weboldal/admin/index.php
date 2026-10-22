<?php
// 8D Navigator admin: edit site content, upload files, read messages, restore backups.

declare(strict_types=1);
require dirname(__DIR__) . '/inc/bootstrap.php';
require __DIR__ . '/schema.php';

define('AUTH_FILE', CONTENT_DIR . '/auth.json');
define('SETUP_FILE', CONTENT_DIR . '/setup-code.txt');
define('THROTTLE_FILE', CONTENT_DIR . '/login-throttle.json');
define('MAX_UPLOAD_MB', 25);
const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'png', 'jpg', 'jpeg', 'webp'];
const IMAGE_EXT = ['png', 'jpg', 'jpeg', 'webp'];

header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('8dn_admin');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict', 'secure' => $https]);
session_start();

// ---------- helpers ----------

function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
}
function flash(string $type, string $text): void {
    $_SESSION['flash'][] = [$type, $text];
}
function go(string $p, string $extra = ''): void {
    header('Location: ./?p=' . rawurlencode($p) . $extra, true, 303);
    exit;
}
function logged_in(): bool {
    if (empty($_SESSION['admin'])) return false;
    if (time() - ($_SESSION['seen'] ?? 0) > 4 * 3600) { // 4 hours idle
        $_SESSION = [];
        return false;
    }
    $_SESSION['seen'] = time();
    return true;
}
function throttle_key(): string {
    return hash('sha256', client_ip() . '|admin');
}
function throttled(): int {
    $t = read_json(THROTTLE_FILE);
    $k = throttle_key();
    return (isset($t[$k]['until']) && $t[$k]['until'] > time()) ? (int)ceil(($t[$k]['until'] - time()) / 60) : 0;
}
function login_failed(): void {
    $t = read_json(THROTTLE_FILE);
    foreach ($t as $k => $v) if (($v['last'] ?? 0) < time() - 86400) unset($t[$k]);
    $k = throttle_key();
    $t[$k]['count'] = ($t[$k]['count'] ?? 0) + 1;
    $t[$k]['last'] = time();
    if ($t[$k]['count'] >= 5) { $t[$k]['until'] = time() + 900; $t[$k]['count'] = 0; }
    write_json(THROTTLE_FILE, $t);
}
function human_size(int $b): string {
    return $b >= 1048576 ? number_format($b / 1048576, 1, ',', ' ') . ' MB' : max(1, (int)round($b / 1024)) . ' KB';
}
function hu_date(int $ts): string {
    return date('Y.m.d. H:i', $ts);
}
function uploads(): array {
    $out = [];
    foreach (glob(UPLOAD_DIR . '/*') ?: [] as $f) {
        $n = basename($f);
        if (!is_file($f) || $n[0] === '.' || $n === 'index.html') continue;
        $out[$n] = ['size' => filesize($f), 'time' => filemtime($f)];
    }
    ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
}
function messages(): array {
    $list = [];
    foreach (glob(MESSAGE_DIR . '/*.json') ?: [] as $f) {
        $m = read_json($f);
        if ($m) $list[] = $m;
    }
    usort($list, fn($a, $b) => strcmp($b['id'], $a['id']));
    return $list;
}
function message_file(string $id): string {
    return MESSAGE_DIR . '/' . preg_replace('/[^0-9a-f\-]/', '', $id) . '.json';
}
function safe_filename(string $name): string {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $base = pathinfo($name, PATHINFO_FILENAME);
    $base = strtr($base, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ö'=>'o','ő'=>'o','ú'=>'u','ü'=>'u','ű'=>'u','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ö'=>'O','Ő'=>'O','Ú'=>'U','Ü'=>'U','Ű'=>'U']);
    $base = trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', $base), '-') ?: 'file';
    $base = mb_substr($base, 0, 80);
    $n = "{$base}.{$ext}";
    $i = 2;
    while (file_exists(UPLOAD_DIR . '/' . $n)) $n = "{$base}-" . $i++ . ".{$ext}";
    return $n;
}

// ---------- schema-driven form ----------

$GLOBALS['warnings'] = [];
$GLOBALS['fid'] = 0;

function render_fields(array $fields, array $data, string $prefix): string {
    $h = '';
    foreach ($fields as $f) {
        [$key, $type, $label] = $f;
        $name = $prefix . '[' . $key . ']';
        $val = $data[$key] ?? ($f['default'] ?? '');
        $id = 'f' . (++$GLOBALS['fid']);
        $hint = isset($f['hint']) ? '<small class="a-hint">' . e($f['hint']) . '</small>' : '';
        switch ($type) {
            case 'heading':
                $h .= '<h2 class="a-fs-head">' . e($label) . '</h2>';
                break;
            case 'text':
            case 'email':
                $h .= '<div class="a-f"><label for="' . $id . '">' . e($label) . '</label><input id="' . $id . '" type="' . ($type === 'email' ? 'email' : 'text') . '" name="' . e($name) . '" value="' . e($val) . '"' . (isset($f['item_title']) ? ' data-item-title' : '') . '>' . $hint . '</div>';
                break;
            case 'number':
                $h .= '<div class="a-f a-f--short"><label for="' . $id . '">' . e($label) . '</label><input id="' . $id . '" type="number" name="' . e($name) . '" value="' . e($val) . '" min="' . (int)($f['min'] ?? 0) . '" max="' . (int)($f['max'] ?? 9999) . '">' . $hint . '</div>';
                break;
            case 'textarea':
                $h .= '<div class="a-f"><label for="' . $id . '">' . e($label) . '</label><textarea id="' . $id . '" name="' . e($name) . '" rows="' . (int)($f['rows'] ?? 4) . '">' . e($val) . '</textarea>' . $hint . '</div>';
                break;
            case 'checkbox':
                $h .= '<div class="a-f a-check"><input type="hidden" name="' . e($name) . '" value="0"><input id="' . $id . '" type="checkbox" name="' . e($name) . '" value="1"' . ($val ? ' checked' : '') . '><label for="' . $id . '">' . e($label) . '</label></div>';
                break;
            case 'select':
                $o = '';
                foreach ($f['options'] as $k => $l) $o .= '<option value="' . e($k) . '"' . ((string)$k === (string)$val ? ' selected' : '') . '>' . e($l) . '</option>';
                $h .= '<div class="a-f"><label for="' . $id . '">' . e($label) . '</label><select id="' . $id . '" name="' . e($name) . '">' . $o . '</select>' . $hint . '</div>';
                break;
            case 'file':
                $o = '<option value="">— nincs fájl —</option>';
                $files = uploads();
                if ($val !== '' && !isset($files[$val])) $o .= '<option value="" selected>(hiányzó fájl: ' . e($val) . ')</option>';
                foreach ($files as $n => $_) $o .= '<option value="' . e($n) . '"' . ($n === $val ? ' selected' : '') . '>' . e($n) . '</option>';
                $h .= '<div class="a-f"><label for="' . $id . '">' . e($label) . '</label><select id="' . $id . '" name="' . e($name) . '">' . $o . '</select><small class="a-hint">Új fájlt a <a href="./?p=files">Fájlok</a> menüben tölthetsz fel.</small></div>';
                break;
            case 'group':
                $inner = render_fields($f['fields'], is_array($val) ? $val : [], $name);
                $h .= $label !== '' ? '<fieldset class="a-group"><legend>' . e($label) . '</legend>' . $inner . '</fieldset>' : $inner;
                break;
            case 'list':
                $items = is_array($val) ? $val : [];
                $h .= '<div class="a-list" data-list>';
                $h .= '<div class="a-list-head"><span>' . e($label) . ' <span class="a-count" data-count>' . count($items) . '</span></span><button type="button" class="a-btn a-btn--small" data-expand-all>Mind kinyit</button></div>';
                $h .= '<div class="a-items" data-items>';
                foreach ($items as $i => $item) $h .= render_item($f, is_array($item) ? $item : [], $name . '[' . $i . ']', false);
                $h .= '</div>';
                $h .= '<template data-template>' . render_item($f, [], $name . '[__i__]', true) . '</template>';
                $h .= '<button type="button" class="a-btn a-btn--add" data-add>+ ' . e($f['add'] ?? 'Új elem') . '</button>';
                $h .= '</div>';
                break;
        }
    }
    return $h;
}

function render_item(array $listField, array $item, string $prefix, bool $open): string {
    $titleKey = $listField['item'] ?? '';
    $subKey = $listField['sub'] ?? '';
    $fields = array_map(function ($f) use ($titleKey) {
        if ($f[0] === $titleKey) $f['item_title'] = true;
        return $f;
    }, $listField['fields']);
    $title = trim((string)($item[$titleKey] ?? '')) ?: 'Új elem';
    $sub = $subKey !== '' ? trim((string)($item[$subKey] ?? '')) : '';
    $hidden = array_key_exists('visible', $item) && !$item['visible'];
    return '<div class="a-item' . ($open ? ' is-open' : '') . '" data-item>'
        . '<div class="a-item-bar">'
        . '<button type="button" class="a-item-toggle" data-toggle aria-expanded="' . ($open ? 'true' : 'false') . '">'
        . ($sub !== '' ? '<span class="a-item-sub">' . e($sub) . '</span>' : '')
        . '<span data-title>' . e($title) . '</span>' . ($hidden ? ' <span class="a-pill">rejtett</span>' : '') . '</button>'
        . '<button type="button" class="a-icon" data-up title="Feljebb" aria-label="Feljebb">↑</button>'
        . '<button type="button" class="a-icon" data-down title="Lejjebb" aria-label="Lejjebb">↓</button>'
        . '<button type="button" class="a-icon a-icon--del" data-remove title="Törlés" aria-label="Törlés">✕</button>'
        . '</div><div class="a-item-body">' . render_fields($fields, $item, $prefix) . '</div></div>';
}

function clean_fields(array $fields, $in, $old): array {
    $in = is_array($in) ? $in : [];
    $out = is_array($old) ? $old : [];
    foreach ($fields as $f) {
        [$key, $type, $label] = $f;
        if ($type === 'heading') continue;
        $v = $in[$key] ?? null;
        switch ($type) {
            case 'text':
                $out[$key] = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)$v)), 0, 400);
                break;
            case 'textarea':
                $out[$key] = mb_substr(trim(str_replace("\r", '', (string)$v)), 0, 20000);
                break;
            case 'email':
                $v = trim((string)$v);
                if (filter_var($v, FILTER_VALIDATE_EMAIL)) $out[$key] = $v;
                else $GLOBALS['warnings'][] = "„{$label}”: érvénytelen e-mail cím, a régi érték maradt.";
                break;
            case 'number':
                $out[$key] = max((int)($f['min'] ?? 0), min((int)($f['max'] ?? 9999), (int)$v));
                break;
            case 'checkbox':
                $out[$key] = (string)$v === '1';
                break;
            case 'select':
                $out[$key] = array_key_exists((string)$v, $f['options']) ? (string)$v : (string)array_key_first($f['options']);
                break;
            case 'file':
                $n = basename((string)$v);
                $out[$key] = ($n !== '' && is_file(UPLOAD_DIR . '/' . $n)) ? $n : '';
                break;
            case 'group':
                $out[$key] = clean_fields($f['fields'], $v, $out[$key] ?? []);
                break;
            case 'list':
                $items = [];
                foreach ((is_array($v) ? $v : []) as $item) {
                    if (is_array($item)) $items[] = clean_fields($f['fields'], $item, []);
                }
                $out[$key] = $items;
                break;
        }
    }
    return $out;
}

// ---------- housekeeping: delete messages past the retention period ----------

function purge_messages(): void {
    $months = max(1, (int)c('settings.message_retention_months', 24));
    $limit = strtotime("-{$months} months");
    foreach (glob(MESSAGE_DIR . '/*.json') ?: [] as $f) {
        $received = strtotime((string)(read_json($f)['received'] ?? '')) ?: filemtime($f);
        if ($received < $limit) unlink($f);
    }
}

// ---------- POST actions ----------

$p = (string)($_GET['p'] ?? 'dashboard');
$sections = admin_sections();
$hasAuth = is_file(AUTH_FILE);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        flash('err', 'A munkamenet lejárt. Próbáld újra.');
        go($p);
    }

    if ($action === 'setup' && !$hasAuth) {
        $code = trim((string)@file_get_contents(SETUP_FILE));
        $pw = (string)($_POST['password'] ?? '');
        if ($m = throttled()) {
            flash('err', "Túl sok sikertelen próbálkozás. Próbáld újra {$m} perc múlva.");
        } elseif ($code === '' || !hash_equals($code, trim((string)($_POST['code'] ?? '')))) {
            login_failed();
            flash('err', 'Hibás telepítési kód.');
        } elseif (mb_strlen($pw) < 10) {
            flash('err', 'A jelszó legalább 10 karakter legyen.');
        } elseif ($pw !== (string)($_POST['password2'] ?? '')) {
            flash('err', 'A két jelszó nem egyezik.');
        } else {
            write_json(AUTH_FILE, ['hash' => password_hash($pw, PASSWORD_DEFAULT), 'created' => date('c')]);
            @unlink(SETUP_FILE);
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['seen'] = time();
            flash('ok', 'Kész. Jelszó beállítva, be vagy jelentkezve.');
        }
        go('dashboard');
    }

    if ($action === 'login') {
        if ($m = throttled()) {
            flash('err', "Túl sok sikertelen próbálkozás. Próbáld újra {$m} perc múlva.");
        } else {
            $auth = read_json(AUTH_FILE);
            if (!empty($auth['hash']) && password_verify((string)($_POST['password'] ?? ''), $auth['hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin'] = true;
                $_SESSION['seen'] = time();
                go('dashboard');
            }
            login_failed();
            flash('err', 'Hibás jelszó.');
        }
        go('login');
    }

    if (!logged_in()) go('login');

    switch ($action) {
        case 'logout':
            $_SESSION = [];
            session_destroy();
            header('Location: ./', true, 303);
            exit;

        case 'save':
            $sid = (string)($_POST['section'] ?? '');
            if (!isset($sections[$sid])) go('dashboard');
            $s = $sections[$sid];
            $all = content();
            $all[$s['root']] = clean_fields($s['fields'], $_POST['d'] ?? [], $all[$s['root']] ?? []);
            if (save_content($all)) {
                flash('ok', 'Mentve. A változás már látszik az oldalon.');
                foreach ($GLOBALS['warnings'] as $w) flash('warn', $w);
            } else {
                flash('err', 'Nem sikerült menteni. Ellenőrizd, hogy a content mappa írható-e a tárhelyen.');
            }
            go($sid);

        case 'upload':
            $f = $_FILES['file'] ?? null;
            if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) { flash('err', 'Nem választottál fájlt.'); go('files'); }
            if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $f['size'] > MAX_UPLOAD_MB * 1048576) {
                flash('err', 'A fájl túl nagy. Legfeljebb ' . MAX_UPLOAD_MB . ' MB lehet (a tárhely ennél kisebb korlátot is szabhat).');
                go('files');
            }
            if ($f['error'] !== UPLOAD_ERR_OK) { flash('err', 'A feltöltés nem sikerült (hibakód: ' . (int)$f['error'] . ').'); go('files'); }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXT, true)) {
                flash('err', 'Ez a fájltípus nem engedélyezett. Engedélyezett: ' . implode(', ', ALLOWED_EXT) . '.');
                go('files');
            }
            if (in_array($ext, IMAGE_EXT, true) && !@getimagesize($f['tmp_name'])) {
                flash('err', 'A fájl nem érvényes kép.');
                go('files');
            }
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            $name = safe_filename($f['name']);
            if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
                flash('err', 'A fájlt nem sikerült elmenteni. Ellenőrizd, hogy az uploads mappa írható-e.');
                go('files');
            }
            @chmod(UPLOAD_DIR . '/' . $name, 0644);
            if (($_POST['as_hero'] ?? '') === '1') {
                if (!in_array($ext, IMAGE_EXT, true)) {
                    flash('warn', "A(z) {$name} feltöltve, de főoldali képnek csak PNG, JPG vagy WEBP használható.");
                } else {
                    $all = content(); $all['settings']['hero_image'] = $name; save_content($all);
                    flash('ok', "Feltöltve, és beállítva főoldali képnek: {$name}");
                    go('files');
                }
            }
            flash('ok', "Feltöltve: {$name}. Most az Erőforrások menüben hozzárendelheted egy dokumentumhoz.");
            go('files');

        case 'delete_file':
            $n = basename((string)($_POST['name'] ?? ''));
            if ($n !== '' && $n[0] !== '.' && is_file(UPLOAD_DIR . '/' . $n)) {
                unlink(UPLOAD_DIR . '/' . $n);
                $all = content();
                $changed = false;
                foreach ($all['resources']['items'] as &$it) if (($it['file'] ?? '') === $n) { $it['file'] = ''; $changed = true; }
                unset($it);
                if (($all['settings']['hero_image'] ?? '') === $n) { $all['settings']['hero_image'] = ''; $changed = true; }
                if ($changed) save_content($all);
                flash('ok', "Törölve: {$n}");
            }
            go('files');

        case 'set_hero':
            $n = basename((string)($_POST['name'] ?? ''));
            $all = content();
            $all['settings']['hero_image'] = ($n !== '' && is_file(UPLOAD_DIR . '/' . $n)) ? $n : '';
            save_content($all);
            flash('ok', $all['settings']['hero_image'] ? "Főoldali kép beállítva: {$n}" : 'Visszaállítva az eredeti főoldali képre.');
            go('files');

        case 'msg_toggle':
            $file = message_file((string)($_POST['id'] ?? ''));
            if (is_file($file)) { $m = read_json($file); $m['read'] = empty($m['read']); write_json($file, $m); }
            go('messages', isset($_POST['open']) ? '&id=' . rawurlencode((string)$_POST['id']) : '');

        case 'msg_delete':
            $file = message_file((string)($_POST['id'] ?? ''));
            if (is_file($file)) { unlink($file); flash('ok', 'Üzenet törölve.'); }
            go('messages');

        case 'restore':
            $n = basename((string)($_POST['name'] ?? ''));
            $data = read_json(BACKUP_DIR . '/' . $n, null);
            if (!preg_match('/^content-[\w-]+\.json$/', $n) || !is_array($data) || !isset($data['home'], $data['resources'])) {
                flash('err', 'Ez a mentés nem állítható vissza.');
            } elseif (save_content($data)) {
                flash('ok', 'Visszaállítva. A visszaállítás előtti állapot is bekerült a mentések közé.');
            }
            go('backups');

        case 'password':
            $auth = read_json(AUTH_FILE);
            $new = (string)($_POST['new'] ?? '');
            if (!password_verify((string)($_POST['current'] ?? ''), $auth['hash'] ?? '')) flash('err', 'A jelenlegi jelszó hibás.');
            elseif (mb_strlen($new) < 10) flash('err', 'Az új jelszó legalább 10 karakter legyen.');
            elseif ($new !== (string)($_POST['new2'] ?? '')) flash('err', 'A két új jelszó nem egyezik.');
            else {
                $auth['hash'] = password_hash($new, PASSWORD_DEFAULT);
                $auth['changed'] = date('c');
                write_json(AUTH_FILE, $auth);
                flash('ok', 'Jelszó megváltoztatva.');
            }
            go('password');
    }
    go('dashboard');
}

// ---------- views ----------

$authed = $hasAuth && logged_in();
if ($authed) purge_messages();
$unread = $authed ? count(array_filter(messages(), fn($m) => empty($m['read']))) : 0;

$nav = [
    'dashboard' => 'Áttekintés',
    'home' => 'Főoldal',
    'faq' => 'Kérdések (GYIK)',
    'resources' => 'Erőforrások',
    'contact' => 'Kapcsolat oldal',
    'files' => 'Fájlok és képek',
    'messages' => 'Üzenetek',
    'settings' => 'Beállítások',
    'company' => 'Cégadatok (GDPR)',
    'backups' => 'Korábbi verziók',
    'password' => 'Jelszó',
];
if ($authed && !isset($nav[$p])) $p = 'dashboard';

?><!doctype html>
<html lang="hu">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($authed ? ($nav[$p] . ' · ') : '') ?>8D Navigator admin</title>
<link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="../css/style.css?v=4">
<link rel="stylesheet" href="admin.css?v=2">
</head>
<body class="a-body">
<?php
$flashes = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
$flashHtml = '';
foreach ($flashes as [$t, $msg]) $flashHtml .= '<div class="a-flash a-flash--' . e($t) . '" role="status">' . e($msg) . '</div>';

if (!$authed):
    $setup = !$hasAuth;
?>
<main class="a-login">
  <div class="a-login-card">
    <div class="a-login-brand"><?= logo_mark() ?><span>8D Navigator <b>admin</b></span></div>
    <?= $flashHtml ?>
<?php if ($setup): ?>
    <h1>Első beállítás</h1>
    <p class="a-muted">Add meg a telepítési kódot (a <code>content/setup-code.txt</code> fájlban van), és válassz jelszót. Ezt csak egyszer kell megtenni.</p>
    <form method="post" action="./?p=dashboard" class="a-stack">
      <?= csrf_field() ?><input type="hidden" name="action" value="setup">
      <div class="a-f"><label for="code">Telepítési kód</label><input id="code" name="code" type="text" autocomplete="off" required></div>
      <div class="a-f"><label for="pw">Új jelszó (legalább 10 karakter)</label><input id="pw" name="password" type="password" autocomplete="new-password" minlength="10" required></div>
      <div class="a-f"><label for="pw2">Jelszó még egyszer</label><input id="pw2" name="password2" type="password" autocomplete="new-password" minlength="10" required></div>
      <button class="btn btn--brand" type="submit">Jelszó beállítása</button>
    </form>
<?php else: ?>
    <h1>Bejelentkezés</h1>
    <form method="post" action="./?p=login" class="a-stack">
      <?= csrf_field() ?><input type="hidden" name="action" value="login">
      <div class="a-f"><label for="pw">Jelszó</label><input id="pw" name="password" type="password" autocomplete="current-password" required autofocus></div>
      <button class="btn btn--brand" type="submit">Belépés</button>
    </form>
<?php endif; ?>
    <p class="a-muted a-small"><a href="../">← Vissza a weboldalra</a></p>
  </div>
</main>
</body>
</html>
<?php
    exit;
endif;
?>
<div class="a-shell">
  <aside class="a-side">
    <a class="a-side-brand" href="./"><?= logo_mark() ?><span>8D Navigator<small>admin</small></span></a>
    <nav class="a-nav" aria-label="Admin menü">
<?php foreach ($nav as $k => $label): ?>
      <a href="./?p=<?= e($k) ?>"<?= $k === $p ? ' aria-current="page"' : '' ?>><?= e($label) ?><?= $k === 'messages' && $unread ? ' <span class="a-badge">' . $unread . '</span>' : '' ?></a>
<?php endforeach; ?>
    </nav>
    <div class="a-side-foot">
      <a href="../" target="_blank" rel="noopener">Weboldal megnyitása ↗</a>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button type="submit" class="a-link">Kijelentkezés</button></form>
    </div>
  </aside>

  <main class="a-main" id="main">
    <?= $flashHtml ?>
<?php
// ---------- section editor ----------
if (isset($sections[$p])):
    $s = $sections[$p];
    $data = content()[$s['root']] ?? [];
?>
    <header class="a-head">
      <div>
        <h1><?= e($s['title']) ?></h1>
        <p class="a-muted"><?= e($s['intro']) ?></p>
      </div>
      <a class="a-btn" href="../<?= e($s['view']) ?>" target="_blank" rel="noopener">Megnézem az oldalon ↗</a>
    </header>
    <form method="post" class="a-form" data-dirty-guard>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="section" value="<?= e($p) ?>">
      <?= render_fields($s['fields'], $data, 'd') ?>
      <div class="a-savebar">
        <span class="a-muted" data-dirty-note hidden>Nem mentett változások</span>
        <button class="btn btn--brand" type="submit">Mentés</button>
      </div>
    </form>
<?php
// ---------- dashboard ----------
elseif ($p === 'dashboard'):
    $company = c('company', []);
    $missing = array_filter(['legal_name' => 'cégnév', 'address' => 'székhely', 'reg_no' => 'cégjegyzékszám', 'tax_no' => 'adószám', 'hosting' => 'tárhelyszolgáltató', 'privacy_effective' => 'hatálybalépés dátuma'], fn($k) => trim((string)($company[$k] ?? '')) === '', ARRAY_FILTER_USE_KEY);
    $items = c('resources.items', []);
    $withFile = count(array_filter($items, fn($i) => !empty($i['file'])));
?>
    <header class="a-head"><div><h1>Áttekintés</h1><p class="a-muted">Itt szerkesztheted a 8dnavigator.com tartalmát. Minden mentés után az előző állapot megmarad a „Korábbi verziók” között.</p></div></header>
    <div class="a-cards">
      <a class="a-card" href="./?p=messages"><b><?= $unread ?></b><span>olvasatlan üzenet</span></a>
      <a class="a-card" href="./?p=resources"><b><?= count($items) ?></b><span>dokumentum, ebből <?= $withFile ?> fájllal</span></a>
      <a class="a-card" href="./?p=files"><b><?= count(uploads()) ?></b><span>feltöltött fájl</span></a>
    </div>
<?php if ($missing): ?>
    <div class="a-flash a-flash--warn">Az adatvédelmi tájékoztatóból még hiányzik: <?= e(implode(', ', $missing)) ?>. <a href="./?p=company">Kitöltöm →</a></div>
<?php endif; ?>
    <section class="a-panel">
      <h2>Gyakori teendők</h2>
      <ul class="a-todo">
        <li><b>Új sablon közzététele:</b> töltsd fel a fájlt a <a href="./?p=files">Fájlok</a> menüben, majd az <a href="./?p=resources">Erőforrások</a> menüben add hozzá új dokumentumként vagy rendeld egy meglévőhöz.</li>
        <li><b>Szöveg javítása:</b> válaszd ki az oldalt a bal oldali menüben, írd át, és nyomd meg a Mentés gombot.</li>
        <li><b>Valami elromlott?</b> A <a href="./?p=backups">Korábbi verziók</a> menüben egy kattintással visszaállíthatod a korábbi állapotot.</li>
      </ul>
    </section>
    <section class="a-panel">
      <h2>Formázás a szövegmezőkben</h2>
      <dl class="a-help">
        <dt><code>**szöveg**</code></dt><dd><strong>félkövér</strong></dd>
        <dt><code>[szöveg]</code></dt><dd>kiemelés (a főcímben piros)</dd>
        <dt>üres sor</dt><dd>új bekezdés (a hosszabb szövegeknél)</dd>
        <dt>új sor</dt><dd>új listaelem (ahol a mező alatt „Soronként egy elem” áll)</dd>
      </dl>
    </section>
<?php
// ---------- files ----------
elseif ($p === 'files'):
    $files = uploads();
    $hero = (string)c('settings.hero_image');
    $usage = [];
    foreach (c('resources.items', []) as $it) if (!empty($it['file'])) $usage[$it['file']][] = $it['code'] ?: $it['title'];
?>
    <header class="a-head"><div><h1>Fájlok és képek</h1><p class="a-muted">Ide töltsd fel a sablonokat, oktatási anyagokat és képeket. Engedélyezett: <?= e(implode(', ', ALLOWED_EXT)) ?>, legfeljebb <?= MAX_UPLOAD_MB ?> MB.</p></div></header>
    <section class="a-panel">
      <h2>Új fájl feltöltése</h2>
      <form method="post" enctype="multipart/form-data" class="a-upload">
        <?= csrf_field() ?><input type="hidden" name="action" value="upload">
        <input type="file" name="file" id="file" required accept=".<?= e(implode(',.', ALLOWED_EXT)) ?>">
        <label class="a-check"><input type="checkbox" name="as_hero" value="1"> Legyen ez a főoldali kép (széles, kb. 3,6 : 1 arányú kép, legalább 1600 px széles)</label>
        <button class="btn btn--brand" type="submit">Feltöltés</button>
      </form>
    </section>
    <section class="a-panel">
      <h2>Főoldali kép</h2>
      <div class="a-hero-row">
        <img src="../<?= e(hero_image()) ?>" alt="" class="a-hero-thumb">
        <div>
          <p><?= $hero ? 'Saját kép: <b>' . e($hero) . '</b>' : 'Az eredeti kép látszik.' ?></p>
<?php if ($hero): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="set_hero"><input type="hidden" name="name" value=""><button class="a-btn" type="submit">Vissza az eredeti képre</button></form>
<?php endif; ?>
        </div>
      </div>
    </section>
    <section class="a-panel">
      <h2>Feltöltött fájlok (<?= count($files) ?>)</h2>
<?php if (!$files): ?>
      <p class="a-muted">Még nincs feltöltött fájl.</p>
<?php else: ?>
      <div class="a-table-wrap"><table class="a-table">
        <thead><tr><th>Fájl</th><th>Méret</th><th>Feltöltve</th><th>Használja</th><th></th></tr></thead>
        <tbody>
<?php foreach ($files as $n => $info): $isImg = in_array(strtolower(pathinfo($n, PATHINFO_EXTENSION)), IMAGE_EXT, true); ?>
          <tr>
            <td><a href="../uploads/<?= e(rawurlencode($n)) ?>" target="_blank" rel="noopener"><?= e($n) ?></a></td>
            <td><?= human_size($info['size']) ?></td>
            <td><?= hu_date($info['time']) ?></td>
            <td><?= isset($usage[$n]) ? e(implode(', ', $usage[$n])) : ($n === $hero ? 'főoldali kép' : '<span class="a-muted">—</span>') ?></td>
            <td class="a-actions">
<?php if ($isImg && $n !== $hero): ?>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="set_hero"><input type="hidden" name="name" value="<?= e($n) ?>"><button class="a-btn a-btn--small" type="submit">Főoldali kép</button></form>
<?php endif; ?>
              <form method="post" data-confirm="Biztosan törlöd: <?= e($n) ?>?"><?= csrf_field() ?><input type="hidden" name="action" value="delete_file"><input type="hidden" name="name" value="<?= e($n) ?>"><button class="a-btn a-btn--small a-btn--danger" type="submit">Törlés</button></form>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table></div>
<?php endif; ?>
    </section>
<?php
// ---------- messages ----------
elseif ($p === 'messages'):
    $msgs = messages();
    $openId = (string)($_GET['id'] ?? '');
    $months = (int)c('settings.message_retention_months', 24);
?>
    <header class="a-head"><div><h1>Üzenetek</h1><p class="a-muted">A kapcsolatfelvételi űrlapon érkezett üzenetek. Minden üzenetről e-mail értesítő is megy. A rendszer <?= $months ?> hónap után automatikusan törli őket (GDPR).</p></div></header>
<?php if (!$msgs): ?>
    <section class="a-panel"><p class="a-muted">Még nem érkezett üzenet.</p></section>
<?php else: ?>
    <div class="a-msgs">
<?php foreach ($msgs as $m): $open = $m['id'] === $openId; $replySubject = rawurlencode('Re: ' . $m['topic']); ?>
      <details class="a-msg<?= empty($m['read']) ? ' is-unread' : '' ?>"<?= $open ? ' open' : '' ?>>
        <summary>
          <span class="a-msg-from"><?= e($m['first_name'] . ' ' . $m['last_name']) ?><?= $m['company'] !== '' ? ' <span class="a-muted">· ' . e($m['company']) . '</span>' : '' ?></span>
          <span class="a-msg-topic"><?= e($m['topic']) ?></span>
          <span class="a-msg-date"><?= hu_date(strtotime($m['received'])) ?></span>
        </summary>
        <div class="a-msg-body">
          <p><b>E-mail:</b> <?= e($m['email']) ?> · <b>Hozzájárulás:</b> <?= e($m['consent']) ?></p>
          <div class="a-msg-text"><?= nl2br(e($m['message'])) ?></div>
          <div class="a-actions">
            <a class="btn btn--brand" href="mailto:<?= e($m['email']) ?>?subject=<?= $replySubject ?>">Válasz e-mailben</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="msg_toggle"><input type="hidden" name="id" value="<?= e($m['id']) ?>"><input type="hidden" name="open" value="1"><button class="a-btn" type="submit"><?= empty($m['read']) ? 'Olvasottnak jelöl' : 'Olvasatlannak jelöl' ?></button></form>
            <form method="post" data-confirm="Biztosan törlöd ezt az üzenetet?"><?= csrf_field() ?><input type="hidden" name="action" value="msg_delete"><input type="hidden" name="id" value="<?= e($m['id']) ?>"><button class="a-btn a-btn--danger" type="submit">Törlés</button></form>
          </div>
        </div>
      </details>
<?php endforeach; ?>
    </div>
<?php endif; ?>
<?php
// ---------- backups ----------
elseif ($p === 'backups'):
    $backups = glob(BACKUP_DIR . '/content-*.json') ?: [];
    rsort($backups);
?>
    <header class="a-head"><div><h1>Korábbi verziók</h1><p class="a-muted">Minden mentés előtt a rendszer elteszi az előző állapotot (az utolsó <?= BACKUPS_KEPT ?> verziót őrzi meg). Ha valamit elrontottál, állítsd vissza a hiba előtti verziót.</p></div></header>
    <section class="a-panel">
<?php if (!$backups): ?>
      <p class="a-muted">Még nincs korábbi verzió. Az első mentés után itt jelenik meg.</p>
<?php else: ?>
      <div class="a-table-wrap"><table class="a-table">
        <thead><tr><th>Mentés ideje</th><th></th></tr></thead>
        <tbody>
<?php foreach ($backups as $b): ?>
          <tr><td><?= hu_date(filemtime($b)) ?></td><td class="a-actions"><form method="post" data-confirm="Visszaállítod ezt a verziót? A mostani állapot is mentésre kerül."><?= csrf_field() ?><input type="hidden" name="action" value="restore"><input type="hidden" name="name" value="<?= e(basename($b)) ?>"><button class="a-btn a-btn--small" type="submit">Visszaállítás</button></form></td></tr>
<?php endforeach; ?>
        </tbody>
      </table></div>
<?php endif; ?>
    </section>
<?php
// ---------- password ----------
elseif ($p === 'password'):
?>
    <header class="a-head"><div><h1>Jelszó módosítása</h1><p class="a-muted">Legalább 10 karakteres jelszót használj. A legjobb egy jelszókezelővel generált jelszó.</p></div></header>
    <section class="a-panel">
      <form method="post" class="a-stack" style="max-width:420px">
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <div class="a-f"><label for="cur">Jelenlegi jelszó</label><input id="cur" name="current" type="password" autocomplete="current-password" required></div>
        <div class="a-f"><label for="new">Új jelszó</label><input id="new" name="new" type="password" autocomplete="new-password" minlength="10" required></div>
        <div class="a-f"><label for="new2">Új jelszó még egyszer</label><input id="new2" name="new2" type="password" autocomplete="new-password" minlength="10" required></div>
        <button class="btn btn--brand" type="submit">Jelszó módosítása</button>
      </form>
    </section>
<?php endif; ?>
  </main>
</div>
<script src="admin.js?v=1"></script>
</body>
</html>
