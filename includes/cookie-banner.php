<?php
/*
 * Cookie notice.
 *
 * Two things here need consent: Google Analytics, and the Google Maps embed.
 * The booking form's session cookie is strictly necessary and needs none.
 *
 * "Accept all" loads analytics and the map. "Essential only" loads no
 * analytics, and the map stays a placeholder until somebody taps it. Analytics is opt-in — nothing
 * is sent to Google until somebody accepts. The footer's "Cookie settings"
 * link reopens this so a choice can be changed later.
 *
 * The choice is kept in localStorage rather than a cookie, so declining
 * cookies does not itself set one.
 */
function render_cookie_banner(): void
{
    ?>
    <div class="cookie" id="cookie-banner" hidden>
        <div class="cookie-inner">
            <p class="cookie-text">
                <?php if (!empty($GLOBALS['booking']['enabled']) || !empty($GLOBALS['ordering']['enabled'])): ?>
                    We use one cookie to keep our forms working.
                <?php endif; ?>
                <?php if (!empty($GLOBALS['analytics']['ga4_id'])): ?>
                    With your OK we use Google Analytics to see which pages people use, so we can
                    improve the site, and show a Google map. Both may set cookies. No ads.
                <?php else: ?>
                    With your OK we show a map from Google, which may set its own cookies. No ads, no tracking.
                <?php endif; ?>
                You can change your mind any time with "Cookie settings" at the bottom of the page.
                <a href="privacy.php#cookies">Privacy notice</a>.
            </p>
            <?php /* Equal prominence: the DPC requires "reject" to be as easy and as
                     visible as "accept", so both buttons are styled the same. */ ?>
            <div class="cookie-do">
                <button type="button" class="btn btn-light" data-cookie="essential">Reject non-essential</button>
                <button type="button" class="btn btn-light" data-cookie="all">Accept all</button>
            </div>
        </div>
    </div>
    <?php
}
