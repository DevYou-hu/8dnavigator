<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$h = c('home');
$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => '8D Navigator',
    'url' => SITE_URL,
    'logo' => SITE_URL . 'assets/logo.jpg',
    'image' => SITE_URL . hero_image(),
    'email' => c('settings.email'),
    'slogan' => c('settings.tagline'),
    'description' => 'Quality management support for the medical device industry: nonconformances, customer and supplier complaint management, investigations and corrective and preventive actions.',
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Budapest', 'addressCountry' => 'HU'],
    'areaServed' => 'Europe',
    'knowsAbout' => ['CAPA', '8D problem solving', 'Root cause analysis', 'Complaint handling', 'Nonconformance management', 'ISO 13485', '21 CFR Part 820'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

page_start('home', '8D Navigator · ' . c('settings.tagline'),
    'Quality management support for the medical device industry: nonconformances, customer and supplier complaints, investigations and CAPA. Free SOP templates, logs, report forms and trainings.',
    '', '<script type="application/ld+json">' . $jsonLd . '</script>');
?>
<main id="main">

  <section class="hero" aria-labelledby="hero-title">
    <div class="wrap">
      <div class="hero-grid">
        <div class="hero-copy">
          <span class="eyebrow"><?= e($h['hero_eyebrow']) ?></span>
          <h1 id="hero-title"><?= inline($h['hero_title']) ?></h1>
          <p class="hero-lede"><?= inline($h['hero_lede']) ?></p>
          <div class="hero-actions">
            <a class="btn btn--primary" href="resources.php"><?= e($h['hero_button']) ?> <?= icon('arrow') ?></a>
            <a class="btn btn--ghost" href="contact.php?topic=investigation"><?= e($h['hero_button2']) ?></a>
          </div>
        </div>
        <aside class="hero-free" aria-label="What we provide for free">
          <span class="label">Free of charge</span>
          <ul>
            <li><?= icon('doc') ?><div>SOP templates<span>Procedures for NC, complaints, SCAR and CAPA</span></div></li>
            <li><?= icon('table') ?><div>Logs and report forms<span>8D report, investigation report, trending logs</span></div></li>
            <li><?= icon('cap') ?><div>Training materials<span>Slides and exercises for your own sessions</span></div></li>
            <li><?= icon('people') ?><div>Online and on-site training<span>Live sessions with your team</span></div></li>
          </ul>
        </aside>
      </div>
      <div class="hero-panorama">
        <img src="<?= e(hero_image()) ?>" width="1024" height="283" alt="A compass, gears under a magnifying glass and a digital profile over a blue data grid">
      </div>
    </div>
  </section>

  <section class="section" id="about" aria-labelledby="about-title">
    <div class="wrap about">
      <div style="display:grid;gap:22px">
        <span class="eyebrow">About us</span>
        <h2 id="about-title"><?= e($h['about_title']) ?></h2>
        <blockquote class="mission">
          <?= inline($h['about_mission']) ?>
          <cite>Our mission</cite>
        </blockquote>
      </div>
      <div class="about-body">
        <?= paras($h['about_text']) ?>
<?php if ($creds = lines($h['credentials'])): ?>
        <ul class="creds" aria-label="Experience">
<?php foreach ($creds as $cr): ?>
          <li><?= e($cr) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section section--surface" id="services" aria-labelledby="services-title">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">What we support</span>
        <h2 id="services-title"><?= e($h['services_title']) ?></h2>
        <p class="lede"><?= inline($h['services_lede']) ?></p>
      </div>
      <div class="focus">
<?php foreach ($h['services'] as $s): ?>
        <article>
          <span class="ref"><?= e($s['ref']) ?></span>
          <h3><?= e($s['title']) ?></h3>
          <p><?= inline($s['text']) ?></p>
<?php if ($b = lines($s['bullets'])): ?>
          <ul>
<?php foreach ($b as $li): ?>
            <li><?= inline($li) ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
        </article>
<?php endforeach; ?>
      </div>
<?php if (trim($h['services_note']) !== ''): ?>
      <p class="lede" style="margin-top:28px;font-size:1rem"><?= inline($h['services_note']) ?></p>
<?php endif; ?>
    </div>
  </section>

  <section class="section" id="method" aria-labelledby="method-title">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">The 8D method</span>
        <h2 id="method-title"><?= e($h['method_title']) ?></h2>
        <p class="lede"><?= inline($h['method_lede']) ?></p>
      </div>
      <ol class="route">
<?php foreach ($h['method_steps'] as $st): ?>
        <li<?= !empty($st['highlight']) ? ' class="is-north"' : '' ?>><span class="d"><?= e($st['code']) ?></span><h3><?= e($st['title']) ?><?= !empty($st['highlight']) ? '<span class="flag">True north</span>' : '' ?></h3><p><?= inline($st['text']) ?></p></li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>

  <section class="section section--surface" id="free" aria-labelledby="free-title">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Free resources</span>
        <h2 id="free-title"><?= e($h['free_title']) ?></h2>
        <p class="lede"><?= inline($h['free_lede']) ?></p>
      </div>
      <div class="free-grid">
        <a class="free-card" href="resources.php#templates">
          <span class="icon"><?= icon('doc') ?></span>
          <h3>SOPs, logs and reports<span class="tag-free">Free</span></h3>
          <p>Procedure templates for nonconformance, complaint, SCAR and CAPA handling, with matching logs and report forms.</p>
          <span class="more">See the templates →</span>
        </a>
        <a class="free-card" href="resources.php#training-materials">
          <span class="icon"><?= icon('cap') ?></span>
          <h3>Training materials<span class="tag-free">Free</span></h3>
          <p>Slide decks, exercises and case studies on 8D, root cause analysis, human error and complaint handling.</p>
          <span class="more">See the materials →</span>
        </a>
        <a class="free-card" href="resources.php#trainings">
          <span class="icon"><?= icon('people') ?></span>
          <h3>Online and on-site training<span class="tag-free">Free</span></h3>
          <p>Live sessions for your team, online or at your site, built around your own cases.</p>
          <span class="more">Request a training →</span>
        </a>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="standards-title">
    <div class="wrap">
      <div class="section-head" style="margin-bottom:24px">
        <span class="eyebrow">Regulatory frame</span>
        <h2 id="standards-title">Written against the standards you are audited to</h2>
      </div>
      <div class="standards">
<?php foreach ($h['standards'] as $sd): ?>
        <div><b><?= e($sd['name']) ?></b><span><?= e($sd['desc']) ?></span></div>
<?php endforeach; ?>
      </div>
    </div>
  </section>

<?php if (!empty($h['faq'])): ?>
  <section class="section section--surface" id="faq" aria-labelledby="faq-title">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Questions</span>
        <h2 id="faq-title">Frequently asked questions</h2>
      </div>
      <div class="faq">
<?php foreach ($h['faq'] as $f): ?>
        <details>
          <summary><?= e($f['q']) ?></summary>
          <div><?= paras($f['a']) ?></div>
        </details>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

  <section class="cta-band" aria-labelledby="cta-title">
    <div class="wrap">
      <div>
        <h2 id="cta-title"><?= e($h['cta_title']) ?></h2>
        <p><?= inline($h['cta_text']) ?></p>
      </div>
      <a class="btn btn--brand" href="contact.php">Contact us</a>
    </div>
  </section>

</main>
<?php page_end();
