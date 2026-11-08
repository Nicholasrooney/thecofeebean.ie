<?php
$pageTitle       = 'Privacy notice | The Coffee Bean';
$pageDescription = 'How The Coffee Bean uses cookies and any details you give us, how long we keep them, and how to ask for a copy or have them deleted.';
$pageRobots      = 'noindex, follow';
$activePage      = 'privacy';

require_once __DIR__ . '/includes/seo.php';
$pageSchema = [seo_breadcrumbs(['Privacy notice' => 'privacy.php'])];
require __DIR__ . '/includes/header.php';

$contact = $site['email'] ?: ($site['phone'] ?: 'us in the café');
$updated = '8 November 2026';
?>

<section class="page-intro">
    <div class="container">
        <h1>Privacy notice</h1>
        <p>What we collect, why, and what you can ask us to do about it. Last updated <?= e($updated) ?>.</p>
    </div>
</section>

<div class="container prose">

    <h2>Who we are</h2>
    <p>
        <?= e($site['name']) ?><?= $site['address'] ? ', ' . e($site['address']) : '' ?><?= !empty($site['eircode']) ? ', ' . e($site['eircode']) : '' ?>.
        We are the data controller for the information described here.
        <?php if ($site['email']): ?>
            You can reach us at <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><?= $site['phone'] ? ' or on ' . e($site['phone']) : '' ?>.
        <?php elseif ($site['phone']): ?>
            You can reach us on <?= e($site['phone']) ?>, or ask in the café.
        <?php endif; ?>
    </p>

    <?php if (empty($booking['enabled']) && empty($ordering['enabled']) && empty($catering['enabled'])): ?>
        <p>This website has no forms and no accounts. We do not ask you for any
           personal details here. The only information involved is what the
           cookies and services below collect, and only if you allow them.</p>
    <?php endif; ?>

    <?php if (!empty($booking['enabled'])): ?>
    <h2>When you book a table</h2>
    <p>We ask for your name, phone number and email address, plus the date, time,
       number of guests, any seating preference and anything you tell us in the
       notes box. We use it to hold your table, to contact you if something
       changes, and to keep a record of the booking. Our lawful basis is that we
       need it to provide the booking you asked for.</p>
    <p>Booking records are kept for <?= (int) ($booking['retention_months'] ?? 24) ?> months after your
       most recent visit, then your name and contact details are permanently
       removed. An anonymous count of covers is kept for staff planning.</p>
    <?php endif; ?>

    <?php if (!empty($ordering['enabled'])): ?>
    <h2>When you order for collection</h2>
    <p>We ask for your name, phone number and email address, plus your order,
       collection time and any note you add. We use them to make your order,
       send you a receipt, and ring you if there is a problem. Our lawful basis
       is that we need them to fulfil the order you placed. Order records are
       kept for <?= (int) ($booking['retention_months'] ?? 24) ?> months for our accounts, then deleted.</p>
    <p>Your basket is kept in your own browser (local storage) until you place
       the order, so it is not lost if you leave the page. It is never sent to
       anyone else.</p>
    <?php endif; ?>

    <?php if (!empty($booking['enabled']) || !empty($ordering['enabled'])): ?>
    <h2>Marketing emails</h2>
    <p>We only email you about offers if you ticked the box asking us to. You can
       tell us to stop at any time and we will. We keep your email address for
       this until you do.</p>
    <?php endif; ?>

    <h2>Who else handles data for us</h2>
    <ul>
        <li><strong>Hostinger</strong> hosts this website, on servers in the EU.</li>
        <?php if (!empty($analytics['ga4_id'])): ?>
            <li><strong>Google Analytics</strong> (Google Ireland Ltd), only if you choose "Accept all". See Cookies below.</li>
        <?php endif; ?>
        <li><strong>Google Maps</strong> (Google Ireland Ltd), only if you accept or tap "Show the map".</li>
    </ul>
    <p>Google may process data in the United States. Those transfers are covered by
       the EU-US Data Privacy Framework, under which Google is certified, and by
       Google's standard contractual clauses. We do not sell your information or
       share it for anyone else's marketing.</p>

    <h2 id="cookies">Cookies</h2>
    <?php if (!empty($booking['enabled']) || !empty($ordering['enabled'])): ?>
        <p><strong>Strictly necessary.</strong> One cookie of our own keeps our forms
           secure while you fill them in. It is deleted when you close your browser
           and does not track you.</p>
    <?php else: ?>
        <p><strong>Our own.</strong> This site sets no cookies of its own. Your cookie
           choice is remembered in your browser's local storage (not a cookie) for
           six months, then we ask again.</p>
    <?php endif; ?>
    <?php if (!empty($analytics['ga4_id'])): ?>
        <p><strong>Google Analytics</strong> (only if you choose "Accept all"). Helps us
           see which pages people use, for example whether people tap the menu or
           directions. It sets cookies whose names start with <code>_ga</code>, which
           last up to 2 years. We do not send Google your name, phone number, email or
           anything you type, we have advertising features switched off, and Google
           does not store your full IP address. Nothing is sent to Google unless you
           accept.</p>
    <?php else: ?>
        <p><strong>Analytics.</strong> We do not use analytics at the moment. If we add
           Google Analytics it will only run if you choose "Accept all", and this notice
           will be updated.</p>
    <?php endif; ?>
    <p><strong>Google Maps.</strong> The map on our home page comes from Google, who may
       set their own cookies when it loads. It only loads if you choose "Accept all",
       or tap the map to load it that once. Until then nothing is requested from Google.</p>
    <p>You can change your mind at any time with <strong>Cookie settings</strong> at the
       bottom of every page. Choosing "Reject non-essential" stops analytics, deletes its
       cookies and hides the map.</p>

    <h2>Your rights</h2>
    <p>Under GDPR you can ask us to:</p>
    <ul>
        <li>give you a copy of everything we hold about you</li>
        <li>correct anything that is wrong</li>
        <li>delete your details</li>
        <li>stop using them for marketing</li>
        <li>restrict or object to how we use them</li>
    </ul>
    <p>Contact us as above. We will reply within one month.</p>

    <h2>Complaints</h2>
    <p>If you are unhappy with how we have handled your information, please tell
       us first so we can put it right. You also have the right to complain to the
       Data Protection Commission, the Irish supervisory authority, at
       <a href="https://www.dataprotection.ie" target="_blank" rel="noopener">dataprotection.ie</a>.</p>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
