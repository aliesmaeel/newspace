<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalContent extends Model
{
    protected $fillable = [
        'controller_name',
        'controller_address',
        'privacy_contact_email',
        'privacy_policy_html',
        'cookie_policy_html',
        'cookie_notice_text',
    ];

    public static function defaults(): array
    {
        $brand = config('brand.name', 'SAKOUR Family Enterprise');

        return [
            'controller_name' => $brand,
            'controller_address' => null,
            'privacy_contact_email' => config('mail.admin_address'),
            'cookie_notice_text' => 'We use essential cookies so the site can work securely (for example, when you log in or pay). We do not use marketing or analytics cookies. See our Cookie policy and Privacy policy for details.',
            'privacy_policy_html' => self::defaultPrivacyPolicyHtml($brand),
            'cookie_policy_html' => self::defaultCookiePolicyHtml($brand),
        ];
    }

    private static function defaultPrivacyPolicyHtml(string $brand): string
    {
        return <<<HTML
<h2>Who we are</h2>
<p>This privacy policy explains how {$brand} (“we”, “us”) uses personal information when you use our website and services.</p>
<p><strong>Data controller:</strong> [Add your legal name in the admin dashboard]<br>
<strong>Address:</strong> [Add your UK address in the admin dashboard]<br>
<strong>Privacy contact:</strong> [Add your privacy email in the admin dashboard]</p>

<h2>Our approach to your data</h2>
<p>We use your information only to provide our services—such as accounts, bookings, events, and support. We <strong>do not sell</strong> your personal data. We <strong>do not use</strong> your data for email marketing, newsletters, or promotional campaigns.</p>

<h2>What we collect</h2>
<p>Depending on how you use the site, we may collect:</p>
<ul>
<li><strong>Account details</strong> — name, email address, phone number, password (stored securely), and optional profile preferences (for example, areas of interest).</li>
<li><strong>Booking information</strong> — name, email, phone, appointment date and time, program selected, and any message you include.</li>
<li><strong>Event registration</strong> — your registration and payment status for events you sign up for.</li>
<li><strong>Contact messages</strong> — information you send through our contact form (name, email, phone, subject, and message).</li>
<li><strong>Payment-related data</strong> — we do not store full card details on our servers. Payments are processed by Stripe; we may receive confirmation of payment and related references needed to manage bookings and event registrations.</li>
<li><strong>Technical data</strong> — essential session and security data (see our Cookie policy).</li>
</ul>

<h2>Why we use your information</h2>
<p>We process personal data only where necessary to:</p>
<ul>
<li>Create and manage your account and verify your email address.</li>
<li>Process bookings, program subscriptions, and event registrations.</li>
<li>Respond to contact enquiries and provide customer support.</li>
<li>Send <strong>transactional</strong> emails (for example, email verification, booking confirmations, event registration updates). We do not send marketing emails.</li>
<li>Keep our website secure and prevent fraud or abuse.</li>
<li>Meet legal or regulatory obligations where applicable.</li>
</ul>
<p><strong>Legal bases (UK GDPR):</strong> performance of a contract (when you book or register), steps at your request before entering a contract, legitimate interests (security, service administration, responding to enquiries), and consent where we ask for it explicitly.</p>

<h2>Who we share data with</h2>
<p>We share personal data only with service providers that help us run the website and deliver our services:</p>
<ul>
<li><strong>Stripe</strong> — payment processing for programs and events.</li>
<li><strong>Zoho Mail</strong> (or similar email infrastructure) — sending transactional emails such as verification and booking confirmations.</li>
<li><strong>Hosting and infrastructure providers</strong> — where our website and data are hosted.</li>
</ul>
<p>These providers process data on our instructions and only for the purposes described above. We do not authorise them to use your data for their own marketing.</p>

<h2>International transfers</h2>
<p>Some providers (for example Stripe or email services) may process data outside the UK. Where this happens, we rely on appropriate safeguards such as UK-approved contractual terms or equivalent protections required by UK data protection law.</p>

<h2>How long we keep data</h2>
<p>We keep personal data only as long as needed for the purposes above, including:</p>
<ul>
<li>Account information — while your account is active and for a reasonable period after closure if needed for legal or administrative purposes.</li>
<li>Bookings, event registrations, and contact enquiries — for the time needed to administer the service, resolve disputes, and comply with law.</li>
<li>Payment records — as required for financial and tax obligations.</li>
</ul>

<h2>Your rights</h2>
<p>Under UK data protection law you have rights including:</p>
<ul>
<li>Access to your personal data.</li>
<li>Correction of inaccurate data.</li>
<li>Erasure in certain circumstances.</li>
<li>Restriction or objection to processing in certain circumstances.</li>
<li>Data portability where applicable.</li>
</ul>
<p>To exercise your rights, contact us using the privacy contact details above. You also have the right to complain to the UK Information Commissioner’s Office (ICO): <a href="https://ico.org.uk" target="_blank" rel="noopener noreferrer">ico.org.uk</a>.</p>

<h2>Changes to this policy</h2>
<p>We may update this policy from time to time. The latest version will always be published on this page.</p>
<p><em>Last updated: please set the date when you review this policy in the admin dashboard.</em></p>
HTML;
    }

    private static function defaultCookiePolicyHtml(string $brand): string
    {
        return <<<HTML
<h2>About cookies</h2>
<p>Cookies are small text files stored on your device when you use websites. UK rules (PECR and UK GDPR) require us to tell you how we use cookies.</p>
<p>{$brand} uses <strong>only essential cookies</strong> needed to run the website securely. We do <strong>not</strong> use analytics, advertising, or social-media tracking cookies.</p>

<h2>Essential cookies we use</h2>
<table>
<thead>
<tr><th>Cookie</th><th>Purpose</th><th>Duration</th></tr>
</thead>
<tbody>
<tr>
<td>Session cookie (for example <code>laravel_session</code>)</td>
<td>Keeps you logged in and maintains your session securely.</td>
<td>Session / as configured by your browser</td>
</tr>
<tr>
<td><code>XSRF-TOKEN</code></td>
<td>Security cookie that helps protect forms and API requests from cross-site attacks.</td>
<td>Session</td>
</tr>
<tr>
<td>Stripe cookies (during checkout)</td>
<td>When you pay for a program or event, Stripe may set cookies to process your payment securely. See <a href="https://stripe.com/gb/cookie-settings" target="_blank" rel="noopener noreferrer">Stripe’s cookie information</a>.</td>
<td>As set by Stripe during payment</td>
</tr>
</tbody>
</table>

<h2>Cookie notice on this site</h2>
<p>Because we only use essential cookies, we show a short notice to explain this. We do not ask you to accept marketing or analytics cookies because we do not use them.</p>

<h2>How to control cookies</h2>
<p>You can block or delete cookies through your browser settings. If you disable essential cookies, parts of the site (such as logging in or completing a payment) may not work correctly.</p>

<h2>Questions</h2>
<p>For questions about cookies or your personal data, see our Privacy policy or contact us using the details given there.</p>
HTML;
    }
}
