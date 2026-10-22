<?php
// Shared setup for public pages and the admin: paths, content loading, helpers.

declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CONTENT_DIR', ROOT . '/content');
define('CONTENT_FILE', CONTENT_DIR . '/content.json');
define('BACKUP_DIR', CONTENT_DIR . '/backups');
define('MESSAGE_DIR', CONTENT_DIR . '/messages');
define('UPLOAD_DIR', ROOT . '/uploads');
define('UPLOAD_URL', 'uploads/');
define('BACKUPS_KEPT', 40);

mb_internal_encoding('UTF-8');

/** Whole content tree, read once per request. */
function content(): array {
    static $c = null;
    if ($c === null) {
        $raw = @file_get_contents(CONTENT_FILE);
        $c = $raw ? json_decode($raw, true) : null;
        if (!is_array($c)) {
            http_response_code(500);
            exit('Content file is missing or damaged. Restore a backup in the admin.');
        }
    }
    return $c;
}

/** Read a value by dotted path, e.g. c('home.hero_title'). */
function c(string $path, $default = '') {
    $node = content();
    foreach (explode('.', $path) as $k) {
        if (!is_array($node) || !array_key_exists($k, $node)) return $default;
        $node = $node[$k];
    }
    return $node;
}

/** Escape for HTML. */
function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Inline formatting: **bold** and [highlight]. Input is escaped first. */
function inline(string $s): string {
    $s = e($s);
    $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
    $s = preg_replace('/\[(.+?)\]/u', '<em>$1</em>', $s);
    return $s;
}

/** Text with blank-line separated paragraphs → <p> elements. */
function paras(string $s, string $attr = ''): string {
    $out = '';
    foreach (preg_split('/\n\s*\n/u', trim(str_replace("\r", '', $s))) as $p) {
        if (trim($p) === '') continue;
        $out .= '<p' . $attr . '>' . nl2br(inline(trim($p)), false) . '</p>' . "\n";
    }
    return $out;
}

/** Lines of a textarea → array of non-empty trimmed lines. */
function lines($v): array {
    if (is_array($v)) return array_values(array_filter(array_map('trim', $v), 'strlen'));
    return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', (string)$v))), 'strlen'));
}

/** Atomic JSON write with a lock. */
function write_json(string $file, array $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    $tmp = $file . '.tmp-' . bin2hex(random_bytes(4));
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return rename($tmp, $file);
}

function read_json(string $file, $default = []) {
    $raw = @file_get_contents($file);
    $d = $raw ? json_decode($raw, true) : null;
    return is_array($d) ? $d : $default;
}

/** Save content, keeping the previous version as a backup. */
function save_content(array $data): bool {
    if (!is_dir(BACKUP_DIR)) mkdir(BACKUP_DIR, 0750, true);
    if (is_file(CONTENT_FILE)) {
        copy(CONTENT_FILE, BACKUP_DIR . '/content-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
        $all = glob(BACKUP_DIR . '/content-*.json') ?: [];
        sort($all);
        while (count($all) > BACKUPS_KEPT) unlink(array_shift($all));
    }
    return write_json(CONTENT_FILE, $data);
}

function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** URL of an uploaded file, or '' if it does not exist. */
function upload_url(string $name): string {
    $name = basename($name);
    return ($name !== '' && is_file(UPLOAD_DIR . '/' . $name)) ? UPLOAD_URL . rawurlencode($name) : '';
}

/**
 * The compass mark, traced from the original logo drawing (Excel shapes, EMU / 10000).
 * Colours are the logo's own; on dark grounds CSS sets --logo-disc to white behind it.
 */
function logo_mark(string $class = ''): string {
    return '<svg' . ($class ? ' class="' . $class . '"' : '') . ' viewBox="0 0 232.29 228.79" aria-hidden="true">'
        . '<ellipse cx="116.14" cy="114.41" rx="116.14" ry="114.37" style="fill:var(--logo-disc,transparent)"/>'
        . '<ellipse cx="116.14" cy="114.41" rx="103.51" ry="101.73" fill="none" stroke="#234691" stroke-width="25.27"/>'
        . '<polygon points="93.09,114.56 116.33,23.06 139.58,114.56" fill="#960000"/>'
        . '<polygon points="93.04,114.49 139.52,114.49 116.28,205.98" fill="#234691"/>'
        . '<polygon points="101.97,28.02 116.14,0 130.32,28.02" style="fill:var(--logo-cut,#fff)"/>'
        . '<polygon points="101.99,202.24 130.29,202.24 116.14,228.79" style="fill:var(--logo-cut,#fff)"/>'
        . '</svg>';
}

function hero_image(): string {
    $u = upload_url((string)c('settings.hero_image'));
    return $u !== '' ? $u : 'assets/hero.png';
}
