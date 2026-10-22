<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$r = c('resources');
$anchors = ['templates' => 'templates', 'logs' => 'logs', 'materials' => 'training-materials'];
$byCat = [];
foreach ($r['items'] as $it) {
    if (empty($it['visible'])) continue;
    $byCat[$it['category']][] = $it;
}

page_start('resources', 'Resources · 8D Navigator',
    'Free SOP templates, logs, report forms, training materials and online or on-site trainings for nonconformance, complaint and CAPA management in the medical device industry.',
    'resources.php');
?>
<main id="main">
  <section class="page-hero" aria-labelledby="page-title">
    <div class="wrap">
      <span class="crumbs"><a href="./">Home</a> / Resources</span>
      <span class="eyebrow">Free of charge</span>
      <h1 id="page-title">Resources</h1>
      <p><?= inline($r['intro']) ?></p>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="note" style="margin-bottom:32px">
        <?= icon('info') ?>
        <p><?= inline($r['how_to']) ?></p>
      </div>

      <div class="filters" role="group" aria-label="Filter resources">
        <button class="chip" type="button" data-filter="all" aria-pressed="true">All</button>
<?php foreach ($r['categories'] as $key => $cat): if (empty($byCat[$key])) continue; ?>
        <button class="chip" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($cat['title']) ?> <span class="n"><?= count($byCat[$key]) ?></span></button>
<?php endforeach; ?>
<?php if (!empty($r['trainings'])): ?>
        <button class="chip" type="button" data-filter="trainings" aria-pressed="false">Trainings <span class="n"><?= count($r['trainings']) ?></span></button>
<?php endif; ?>
      </div>

<?php foreach ($r['categories'] as $key => $cat): if (empty($byCat[$key])) continue; ?>
      <div class="res-group" id="<?= e($anchors[$key] ?? $key) ?>" data-group="<?= e($key) ?>">
        <header>
          <h2><?= e($cat['title']) ?></h2>
          <p><?= inline($cat['text']) ?></p>
        </header>
        <div class="doc-list">
<?php foreach ($byCat[$key] as $it):
        $file = !empty($it['public_download']) ? upload_url((string)$it['file']) : '';
        $topic = trim($it['code'] . ' ' . $it['title']); ?>
          <article class="doc">
            <div class="doc-head"><span class="id"><?= e($it['code']) ?></span><span><?= e($it['revision']) ?></span></div>
            <div class="doc-body"><h3><?= e($it['title']) ?></h3><p><?= inline($it['description']) ?></p></div>
            <div class="doc-foot">
              <div class="doc-meta"><?php foreach ([$it['format'], $it['ref']] as $m) if (trim((string)$m) !== '') echo '<span>' . e($m) . '</span>'; ?></div>
<?php if ($file !== ''): ?>
              <a href="<?= e($file) ?>" download>Download <?= icon('down') ?></a>
<?php else: ?>
              <a href="contact.php?topic=<?= rawurlencode($topic) ?>">Request free copy <span aria-hidden="true">→</span></a>
<?php endif; ?>
            </div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
<?php endforeach; ?>

<?php if (!empty($r['trainings'])): ?>
      <div class="res-group" id="trainings" data-group="trainings">
        <header>
          <h2><?= e($r['trainings_title']) ?></h2>
          <p><?= inline($r['trainings_text']) ?></p>
        </header>
        <div class="training-grid">
<?php foreach ($r['trainings'] as $t): ?>
          <article class="training">
            <span class="kicker"><?= e($t['kicker']) ?></span>
            <h3><?= e($t['title']) ?></h3>
            <p><?= inline($t['text']) ?></p>
            <dl>
              <dt>Topics</dt><dd><?= e($t['topics']) ?></dd>
              <dt>Format</dt><dd><?= e($t['format']) ?></dd>
              <dt>Cost</dt><dd><?= e($t['cost']) ?></dd>
            </dl>
            <a class="btn btn--brand" href="contact.php?topic=<?= rawurlencode($t['topic_key'] ?: 'general') ?>"><?= e($t['button']) ?></a>
          </article>
<?php endforeach; ?>
        </div>
      </div>
<?php endif; ?>
    </div>
  </section>

  <section class="cta-band" aria-labelledby="cta-title">
    <div class="wrap">
      <div>
        <h2 id="cta-title"><?= e($r['cta_title']) ?></h2>
        <p><?= inline($r['cta_text']) ?></p>
      </div>
      <a class="btn btn--brand" href="contact.php?topic=resource">Ask us</a>
    </div>
  </section>
</main>
<?php page_end();
