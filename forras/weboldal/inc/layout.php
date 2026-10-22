<?php
// Shared page chrome: <head>, header, footer.

const SITE_URL = 'https://8dnavigator.com/';

function brand_html(): string {
    return '<a class="brand" href="./" aria-label="8D Navigator home">' . logo_mark()
        . '<span class="brand-text"><span class="brand-name">8DNavigator<small>.com</small></span>'
        . '<span class="brand-tag">' . e(c('settings.tagline')) . '</span></span></a>';
}

function page_start(string $current, string $title, string $description, string $path, string $extraHead = ''): void {
    $url = SITE_URL . $path;
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($url) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="8D Navigator">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($url) ?>">
<meta property="og:image" content="<?= e(SITE_URL . hero_image()) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="preload" href="assets/fonts/archivo-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="css/style.css?v=4">
<?= $extraHead ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>

<header class="site-header">
  <div class="wrap">
    <?= brand_html() ?>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>
    <nav class="nav" id="site-nav" aria-label="Main">
<?php foreach (['home' => ['./', 'Home'], 'resources' => ['resources.php', 'Resources'], 'contact' => ['contact.php', 'Contact']] as $key => [$href, $label]): ?>
      <a href="<?= $href ?>"<?= $key === $current ? ' aria-current="page"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
      <a class="btn btn--primary" href="resources.php">Free templates</a>
    </nav>
  </div>
</header>
<?php
}

function page_end(): void {
    $email = c('settings.email');
    ?>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-about">
        <?= brand_html() ?>
        <p>Quality management support for the medical device industry. <?= e(c('settings.location')) ?>.</p>
      </div>
      <div>
        <h2>Site</h2>
        <ul>
          <li><a href="./">Home</a></li>
          <li><a href="resources.php">Resources</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div>
        <h2>Contact</h2>
        <ul>
          <li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
          <li><?= e(c('settings.location')) ?></li>
          <li><a href="privacy.php">Privacy policy</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> 8D Navigator. All rights reserved.</span>
      <button class="to-top" type="button" data-to-top>Back to top ↑</button>
    </div>
  </div>
</footer>
<script src="js/main.js?v=2"></script>
</body>
</html>
<?php
}

/** The small "doc" icon set used on the home page cards. */
function icon(string $name): string {
    $p = [
        'doc'    => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
        'table'  => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/>',
        'cap'    => '<path d="M2 7l10-4 10 4-10 4z"/><path d="M6 9v5c0 1.7 2.7 3 6 3s6-1.3 6-3V9"/>',
        'people' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.4 3.2-5.5 6.5-5.5s5.9 2.1 6.5 5.5M16 4.5a3.5 3.5 0 0 1 0 7M18 14.6c1.9.7 3.2 2.6 3.5 5.4"/>',
        'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'info'   => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>',
        'down'   => '<path d="M12 4v12M6 10l6 6 6-6M5 20h14"/>',
    ][$name] ?? '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}
