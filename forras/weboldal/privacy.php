<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

/** Company value, or a visible placeholder while it is still empty in the admin. */
function v(string $path, string $placeholder): string {
    $val = trim((string)c($path));
    return $val !== '' ? e($val) : '<span class="todo">[' . e($placeholder) . ']</span>';
}

$email = e(c('settings.email'));
$months = (int)c('settings.message_retention_months', 24);
$retention = $months % 12 === 0 ? ($months / 12) . ' year' . ($months / 12 > 1 ? 's' : '') : $months . ' months';

page_start('', 'Privacy Policy · 8D Navigator', 'How 8D Navigator processes personal data under the GDPR.', 'privacy.php');
?>
<main id="main">
  <section class="page-hero" aria-labelledby="page-title">
    <div class="wrap">
      <span class="crumbs"><a href="./">Home</a> / Privacy policy</span>
      <span class="eyebrow">GDPR</span>
      <h1 id="page-title">Privacy policy</h1>
      <p>How we handle personal data when you visit 8dnavigator.com or contact us, under the EU General Data Protection Regulation (GDPR).</p>
    </div>
  </section>

  <section class="section">
    <div class="wrap legal">
      <nav aria-label="On this page">
        <a href="#controller">1. Data controller</a>
        <a href="#summary">2. Summary</a>
        <a href="#processing">3. What we process and why</a>
        <a href="#cookies">4. Cookies and tracking</a>
        <a href="#recipients">5. Recipients and processors</a>
        <a href="#transfers">6. Transfers outside the EEA</a>
        <a href="#security">7. Security</a>
        <a href="#rights">8. Your rights</a>
        <a href="#complaints">9. Complaints</a>
        <a href="#changes">10. Changes</a>
      </nav>

      <article>
        <p class="meta-line">Effective date: <?= v('company.privacy_effective', 'effective date') ?></p>

        <h2 id="controller">1. Data controller</h2>
        <p>The controller of your personal data is:</p>
        <ul>
          <li><strong>Name:</strong> <?= v('company.legal_name', 'legal entity name') ?>, operating as 8D Navigator</li>
          <li><strong>Registered office:</strong> <?= v('company.address', 'registered office address') ?></li>
          <li><strong>Company registration number:</strong> <?= v('company.reg_no', 'company registration number') ?></li>
          <li><strong>Tax number:</strong> <?= v('company.tax_no', 'tax number') ?></li>
          <li><strong>Email for data protection requests:</strong> <?= $email ?></li>
        </ul>
        <p>We have not appointed a data protection officer, because the law does not require one for our activities.</p>

        <h2 id="summary">2. Summary</h2>
        <ul>
          <li>We only collect what you send us through the contact form or by email.</li>
          <li>We use it to answer you and to send you the resources you ask for.</li>
          <li>We do not use analytics, advertising or social media tracking, and we do not sell or share your data for marketing.</li>
          <li>Fonts and all other files are served from our own server. Your browser does not contact third parties when you visit this site.</li>
        </ul>

        <h2 id="processing">3. What we process and why</h2>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th scope="col">Purpose</th><th scope="col">Data</th><th scope="col">Legal basis</th><th scope="col">Retention</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Answering contact form messages and emails</td>
                <td>First and last name, email address, company (optional), topic, message content</td>
                <td>Your consent, GDPR Art. 6(1)(a); for requests about our services, steps before a contract, Art. 6(1)(b)</td>
                <td>Form messages are deleted automatically after <?= e($retention) ?>. Emails are kept for <?= e($retention) ?> after our last exchange, or deleted earlier if you withdraw consent.</td>
              </tr>
              <tr>
                <td>Sending requested templates and training materials</td>
                <td>Name, email address, the requested resource</td>
                <td>Your request and consent, Art. 6(1)(a)</td>
                <td>As above</td>
              </tr>
              <tr>
                <td>Organising online and on-site trainings</td>
                <td>Contact person’s name, email, company, training details, participant names if you provide them</td>
                <td>Steps before or performance of an agreement, Art. 6(1)(b)</td>
                <td>For the duration of the cooperation and 5 years after, in line with the Hungarian Civil Code limitation period</td>
              </tr>
              <tr>
                <td>Preventing abuse of the contact form</td>
                <td>A one-way hash of your IP address</td>
                <td>Our legitimate interest in protecting the form from spam, Art. 6(1)(f)</td>
                <td>1 hour</td>
              </tr>
              <tr>
                <td>Operating and securing the website</td>
                <td>IP address, date and time, requested page, browser type (server log)</td>
                <td>Our legitimate interest in a secure website, Art. 6(1)(f)</td>
                <td>Deleted automatically by the hosting provider after <?= v('company.log_retention', 'log retention period') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p>Providing your data is voluntary. Without a name and email address we cannot reply to you. We do not use your data for automated decision-making or profiling.</p>
        <p>Please do not send us patient data, health data or confidential product information through the contact form. If an investigation needs such information, we agree a secure way to exchange it first.</p>

        <h2 id="cookies">4. Cookies and tracking</h2>
        <p>This website does not set cookies for visitors and does not use analytics or tracking tools, so we do not show a cookie banner. If we add any non-essential cookies in the future, we will ask for your consent first and update this policy.</p>

        <h2 id="recipients">5. Recipients and processors</h2>
        <p>Only the 8D Navigator team can access your messages. We use the following processor, bound by a data processing agreement under GDPR Art. 28:</p>
        <ul>
          <li><strong>Web hosting and email:</strong> <?= v('company.hosting', 'hosting provider name, registered office, website') ?>. Servers located in the European Union.</li>
        </ul>
        <p>We disclose personal data to authorities only when the law requires it.</p>

        <h2 id="transfers">6. Transfers outside the EEA</h2>
        <p>We do not transfer your personal data outside the European Economic Area.</p>

        <h2 id="security">7. Security</h2>
        <p>The website is served over an encrypted HTTPS connection. Messages are stored in a protected area of our web server that cannot be reached from the internet, and the administration area is protected by a password. We keep the amount of stored data to the minimum needed.</p>

        <h2 id="rights">8. Your rights</h2>
        <p>Under GDPR Articles 15 to 21 you have the right to:</p>
        <ul>
          <li><strong>access</strong> the personal data we hold about you and get a copy of it;</li>
          <li>have inaccurate data <strong>corrected</strong>;</li>
          <li>have your data <strong>erased</strong>;</li>
          <li><strong>restrict</strong> the processing of your data;</li>
          <li>receive your data in a portable format (<strong>data portability</strong>);</li>
          <li><strong>object</strong> to processing based on our legitimate interest;</li>
          <li><strong>withdraw your consent</strong> at any time. This does not affect processing carried out before the withdrawal.</li>
        </ul>
        <p>Send your request to <?= $email ?>. We answer within one month. In complex cases we may extend this by two more months, and we will tell you why.</p>

        <h2 id="complaints">9. Complaints</h2>
        <p>If you think we handle your data unlawfully, please contact us first so we can fix it. You also have the right to lodge a complaint with the Hungarian supervisory authority:</p>
        <ul>
          <li><strong>Nemzeti Adatvédelmi és Információszabadság Hatóság (NAIH)</strong></li>
          <li>Address: 1055 Budapest, Falk Miksa utca 9–11, Hungary</li>
          <li>Postal address: 1363 Budapest, Pf. 9</li>
          <li>Email: ugyfelszolgalat@naih.hu · Website: <a href="https://naih.hu" rel="noopener">naih.hu</a></li>
        </ul>
        <p>You can also go to court. The case may be brought before the court of your place of residence.</p>

        <h2 id="changes">10. Changes to this policy</h2>
        <p>We will update this policy when our data handling changes. The current version is always available on this page, with its effective date at the top.</p>
      </article>
    </div>
  </section>
</main>
<?php page_end();
