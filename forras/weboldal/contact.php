<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/topics.php';

$ct = c('contact');
$email = c('settings.email');

// Preselect the topic from links such as contact.php?topic=training-online or ?topic=8DN-SOP-01 ...
$topicParam = mb_substr(trim((string)($_GET['topic'] ?? '')), 0, 160);
$selected = 'general';
$prefill = '';
if ($topicParam !== '') {
    if (isset(TOPICS[$topicParam])) {
        $selected = $topicParam;
    } else {
        $selected = 'resource';
        $prefill = "I would like to request: {$topicParam}\n\n";
    }
}
$sent = ($_GET['sent'] ?? '') === '1';

page_start('contact', 'Contact · 8D Navigator',
    'Contact 8D Navigator to request free quality templates and trainings, or to get support with a nonconformance, complaint or CAPA investigation.',
    'contact.php');
?>
<main id="main">
  <section class="page-hero" aria-labelledby="page-title">
    <div class="wrap">
      <span class="crumbs"><a href="./">Home</a> / Contact</span>
      <span class="eyebrow">Contact</span>
      <h1 id="page-title"><?= e($ct['title']) ?></h1>
      <p><?= inline($ct['intro']) ?></p>
    </div>
  </section>

  <section class="section">
    <div class="wrap contact-grid">
      <form class="form" id="contact-form" action="send.php" method="post" novalidate>
        <div class="form-status<?= $sent ? ' ok' : '' ?>" id="form-status" role="status" tabindex="-1"<?= $sent ? '' : ' hidden' ?>><?= $sent ? 'Thank you. Your message has been sent. We will reply by email.' : '' ?></div>

        <div class="form-row">
          <div class="field">
            <label for="cf-first">First name <span class="req" aria-hidden="true">*</span></label>
            <input id="cf-first" name="first_name" type="text" autocomplete="given-name" maxlength="80" required aria-describedby="err-first_name">
            <span class="error" id="err-first_name" hidden></span>
          </div>
          <div class="field">
            <label for="cf-last">Last name <span class="req" aria-hidden="true">*</span></label>
            <input id="cf-last" name="last_name" type="text" autocomplete="family-name" maxlength="80" required aria-describedby="err-last_name">
            <span class="error" id="err-last_name" hidden></span>
          </div>
        </div>

        <div class="form-row">
          <div class="field">
            <label for="cf-email">Work email <span class="req" aria-hidden="true">*</span></label>
            <input id="cf-email" name="email" type="email" autocomplete="email" maxlength="160" required aria-describedby="err-email">
            <span class="error" id="err-email" hidden></span>
          </div>
          <div class="field">
            <label for="cf-company">Company <span class="hint">(optional)</span></label>
            <input id="cf-company" name="company" type="text" autocomplete="organization" maxlength="160">
          </div>
        </div>

        <div class="field">
          <label for="cf-topic">What is it about?</label>
          <select id="cf-topic" name="topic">
<?php foreach (TOPICS as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $key === $selected ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="cf-message">Message <span class="req" aria-hidden="true">*</span></label>
          <textarea id="cf-message" name="message" maxlength="5000" required aria-describedby="cf-message-hint err-message"><?= e($prefill) ?></textarea>
          <span class="hint" id="cf-message-hint">Please do not include patient data or confidential product details.</span>
          <span class="error" id="err-message" hidden></span>
        </div>

        <div class="hp" aria-hidden="true">
          <label for="cf-website">Leave this field empty</label>
          <input id="cf-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>
        <input type="hidden" name="t" value="<?= time() ?>">

        <div class="field">
          <label class="consent" for="cf-consent">
            <input id="cf-consent" name="consent" type="checkbox" value="yes" required aria-describedby="err-consent">
            <span>I agree that 8D Navigator uses my details to answer this request, as described in the <a href="privacy.php">privacy policy</a>. <span class="req" aria-hidden="true">*</span></span>
          </label>
          <span class="error" id="err-consent" hidden></span>
        </div>

        <div>
          <button class="btn btn--primary" type="submit">Send message</button>
        </div>
      </form>

      <aside class="aside" aria-label="Other ways to reach us">
        <div>
          <h2>Direct contact</h2>
          <dl style="margin-top:18px">
            <div>
              <dt>Email</dt>
              <dd class="copy-row"><span><?= e($email) ?></span><button class="copy-btn" type="button" data-copy="<?= e($email) ?>">Copy</button></dd>
            </div>
            <div>
              <dt>Office</dt>
              <dd><?= e(c('settings.location')) ?></dd>
            </div>
<?php if (trim(c('settings.languages')) !== ''): ?>
            <div>
              <dt>Languages</dt>
              <dd><?= e(c('settings.languages')) ?></dd>
            </div>
<?php endif; ?>
          </dl>
        </div>
<?php if ($steps = lines($ct['next_steps'])): ?>
        <div>
          <h2>What happens next</h2>
          <ol class="steps" style="margin-top:18px">
<?php foreach ($steps as $s): ?>
            <li><?= inline($s) ?></li>
<?php endforeach; ?>
          </ol>
        </div>
<?php endif; ?>
      </aside>
    </div>
  </section>
</main>
<?php page_end();
