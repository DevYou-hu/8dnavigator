<?php
// Contact form handler. Saves the message for the admin inbox and emails a notification.
// Answers JSON to the fetch() call in js/main.js, and redirects when JavaScript is off.

require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/topics.php';

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function respond(bool $ok, string $error = ''): void {
    global $wantsJson;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        if (!$ok) http_response_code(422);
        echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $error]);
    } else {
        header('Location: contact.php' . ($ok ? '?sent=1' : ''), true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: contact.php', true, 303);
    exit;
}

// Bots: filled honeypot, or submitted faster than a person can type. Pretend success.
if (!empty($_POST['website'])) respond(true);
$t = (int)($_POST['t'] ?? 0);
if ($t > 0 && time() - $t < 3) respond(true);

// Rate limit: 5 messages per hour per visitor. Only a hash of the IP is kept, for one hour.
$rlFile = CONTENT_DIR . '/ratelimit.json';
$rl = read_json($rlFile);
$now = time();
$key = hash('sha256', client_ip() . '|8dn');
foreach ($rl as $k => $stamps) {
    $rl[$k] = array_values(array_filter((array)$stamps, fn($s) => $s > $now - 3600));
    if (!$rl[$k]) unset($rl[$k]);
}
if (count($rl[$key] ?? []) >= 5) {
    respond(false, 'Too many messages in a short time. Please try again later or email us directly.');
}

function field(string $key, int $max): string {
    $v = trim((string)($_POST[$key] ?? ''));
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    return mb_substr($v, 0, $max);
}

$first   = field('first_name', 80);
$last    = field('last_name', 80);
$email   = field('email', 160);
$company = field('company', 160);
$topic   = field('topic', 40);
$message = field('message', 5000);
$consent = ($_POST['consent'] ?? '') === 'yes';
$recipient = (string)c('settings.notify_email', 'info@8dnavigator.com');
$publicEmail = (string)c('settings.email', 'info@8dnavigator.com');

if ($first === '' || $last === '')              respond(false, 'Enter your first and last name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Enter a valid email address.');
if (mb_strlen($message) < 10)                   respond(false, 'Write a short message (at least 10 characters).');
if (!$consent)                                  respond(false, 'Tick the consent box so we may reply to you.');
foreach ([$first, $last, $email, $company] as $v) {
    if (preg_match('/[\n\r]/', $v)) respond(false, 'Invalid characters in the form.');
}

$topicLabel = TOPICS[$topic] ?? TOPICS['general'];

// 1. Save to the admin inbox
if (!is_dir(MESSAGE_DIR)) mkdir(MESSAGE_DIR, 0750, true);
$id = date('Ymd-His') . '-' . bin2hex(random_bytes(3));
$saved = write_json(MESSAGE_DIR . "/{$id}.json", [
    'id' => $id,
    'received' => date('c'),
    'first_name' => $first,
    'last_name' => $last,
    'email' => $email,
    'company' => $company,
    'topic' => $topicLabel,
    'message' => $message,
    'consent' => 'given ' . date('Y-m-d H:i'),
    'read' => false,
]);

// 2. Email notification
$subject = '=?UTF-8?B?' . base64_encode("[8D Navigator] {$topicLabel}: {$first} {$last}") . '?=';
$body  = "New message from the 8dnavigator.com contact form\n";
$body .= str_repeat('-', 50) . "\n";
$body .= "Name:    {$first} {$last}\n";
$body .= "Email:   {$email}\n";
$body .= "Company: " . ($company !== '' ? $company : '-') . "\n";
$body .= "Topic:   {$topicLabel}\n";
$body .= str_repeat('-', 50) . "\n\n";
$body .= $message . "\n\n";
$body .= str_repeat('-', 50) . "\nReply to this email to answer. The message is also saved in the website admin.\n";
$headers = implode("\r\n", [
    'From: 8D Navigator website <' . $publicEmail . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
]);
$mailed = function_exists('mail') && @mail($recipient, $subject, $body, $headers, '-f' . $publicEmail);

if (!$saved && !$mailed) {
    respond(false, "The message could not be sent. Please email us directly at {$publicEmail}.");
}

$rl[$key][] = $now;
write_json($rlFile, $rl);
respond(true);
