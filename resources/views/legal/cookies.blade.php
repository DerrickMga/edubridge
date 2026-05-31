@extends('layouts.legal')

@section('title', 'Cookie Policy')
@section('hero-title', 'Cookie Policy')
@section('meta-description', 'How EduBridge uses cookies and similar technologies — KMG Vital Links (Pvt) Ltd.')
@section('last-updated', 'May 2026')

@section('content')

<p class="lead text-base text-slate-700 font-medium">
    This Cookie Policy explains how <strong>EduBridge</strong> (operated by <strong>KMG Vital Links (Pvt) Ltd</strong>) uses cookies and similar technologies when you visit <a href="https://edu.kmgvitallinks.co.uk">edu.kmgvitallinks.co.uk</a>.
</p>

<h2>1. What Are Cookies?</h2>
<p>Cookies are small text files stored on your device (computer, tablet, or phone) when you visit a website. They help the site remember information about your visit — for example, whether you're logged in, your language preference, or items in a shopping cart. Cookies can be "session cookies" (deleted when you close your browser) or "persistent cookies" (retained for a set period).</p>

<h2>2. Cookies We Use</h2>

<h3>2.1 Strictly Necessary Cookies</h3>
<p>These cookies are essential for EduBridge to function and cannot be disabled. They do not require your consent.</p>
<table class="w-full text-xs border-collapse my-4">
    <thead>
        <tr class="bg-slate-50 text-left">
            <th class="border border-slate-200 px-3 py-2 font-semibold">Cookie Name</th>
            <th class="border border-slate-200 px-3 py-2 font-semibold">Purpose</th>
            <th class="border border-slate-200 px-3 py-2 font-semibold">Duration</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="border border-slate-200 px-3 py-2 font-mono">{{ config('session.cookie') }}</td>
            <td class="border border-slate-200 px-3 py-2">Session management — keeps you logged in</td>
            <td class="border border-slate-200 px-3 py-2">Session / 2 hours</td>
        </tr>
        <tr class="bg-slate-50">
            <td class="border border-slate-200 px-3 py-2 font-mono">XSRF-TOKEN</td>
            <td class="border border-slate-200 px-3 py-2">Cross-site request forgery protection (security)</td>
            <td class="border border-slate-200 px-3 py-2">Session</td>
        </tr>
        <tr>
            <td class="border border-slate-200 px-3 py-2 font-mono">remember_web_*</td>
            <td class="border border-slate-200 px-3 py-2">"Remember me" login persistence</td>
            <td class="border border-slate-200 px-3 py-2">400 days</td>
        </tr>
    </tbody>
</table>

<h3>2.2 Functional Cookies</h3>
<p>These cookies remember your preferences and settings to provide a more personalised experience. They are set only as needed during your session.</p>
<table class="w-full text-xs border-collapse my-4">
    <thead>
        <tr class="bg-slate-50 text-left">
            <th class="border border-slate-200 px-3 py-2 font-semibold">Cookie Name</th>
            <th class="border border-slate-200 px-3 py-2 font-semibold">Purpose</th>
            <th class="border border-slate-200 px-3 py-2 font-semibold">Duration</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="border border-slate-200 px-3 py-2 font-mono">locale</td>
            <td class="border border-slate-200 px-3 py-2">Preferred language / locale setting</td>
            <td class="border border-slate-200 px-3 py-2">1 year</td>
        </tr>
        <tr class="bg-slate-50">
            <td class="border border-slate-200 px-3 py-2 font-mono">theme</td>
            <td class="border border-slate-200 px-3 py-2">Light/dark mode preference</td>
            <td class="border border-slate-200 px-3 py-2">1 year</td>
        </tr>
    </tbody>
</table>

<h3>2.3 Analytics Cookies (Optional)</h3>
<p>With your consent, we may use analytics cookies to understand how users interact with EduBridge, which pages are visited most, and where errors occur. This helps us improve the platform. These cookies are only set if you accept optional cookies.</p>
<p>We use only privacy-friendly, self-hosted analytics where possible and do not share raw analytics data with advertising networks.</p>

<h2>3. Third-Party Cookies</h2>
<p>Some embedded content and services on EduBridge may set their own cookies:</p>
<ul>
    <li><strong>Payment providers (Stripe, PayFast, Paynow, EcoCash):</strong> Set cookies during the payment checkout process to prevent fraud and verify transactions. These are strictly necessary for payment processing.</li>
    <li><strong>Zoom / video conferencing:</strong> When joining live sessions, the Zoom web client may set cookies for session management.</li>
    <li><strong>Font CDN (Bunny Fonts):</strong> Loads fonts from <code>fonts.bunny.net</code>, which is a privacy-friendly, GDPR-compliant alternative to Google Fonts. No tracking cookies are set.</li>
</ul>

<h2>4. Local Storage</h2>
<p>In addition to cookies, EduBridge uses browser <strong>local storage</strong> to store preferences such as video playback position, sidebar state, and notification read status. This data is stored locally on your device and is not transmitted to our servers except where needed for sync purposes.</p>

<h2>5. Managing Your Cookie Preferences</h2>
<p>You can control and manage cookies in several ways:</p>

<h3>5.1 Browser settings</h3>
<p>All modern browsers allow you to view, block, or delete cookies. Visit your browser's help documentation:</p>
<ul>
    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
    <li><a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences" target="_blank" rel="noopener">Mozilla Firefox</a></li>
    <li><a href="https://support.microsoft.com/en-gb/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">Microsoft Edge</a></li>
    <li><a href="https://support.apple.com/en-gb/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Apple Safari</a></li>
</ul>
<p><strong>Note:</strong> Blocking strictly necessary cookies will prevent you from logging in and using EduBridge.</p>

<h3>5.2 Opt-out of analytics</h3>
<p>If you have accepted analytics cookies and wish to withdraw consent, please email <a href="mailto:legal@kmgvitallinks.co.uk">legal@kmgvitallinks.co.uk</a> or clear your browser cookies and decline when prompted on your next visit.</p>

<h2>6. Changes to This Policy</h2>
<p>We may update this Cookie Policy to reflect changes in the cookies we use or applicable law. The "Last updated" date above will reflect the most recent version.</p>

<h2>7. Contact</h2>
<p>Questions about our use of cookies?</p>
<ul>
    <li>Email: <a href="mailto:legal@kmgvitallinks.co.uk">legal@kmgvitallinks.co.uk</a></li>
    <li>Website: <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener">www.kmgvitallinks.co.uk</a></li>
</ul>

@endsection
