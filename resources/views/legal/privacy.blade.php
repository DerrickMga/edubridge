@extends('layouts.legal')

@section('title', 'Privacy Policy')
@section('hero-title', 'Privacy Policy')
@section('meta-description', 'How EduBridge collects, uses, and protects your personal data — KMG Vital Links (Pvt) Ltd.')
@section('last-updated', 'May 2026')

@section('content')

<p class="lead text-base text-slate-700 font-medium">
    This Privacy Policy explains how <strong>KMG Vital Links (Pvt) Ltd</strong> ("KMG", "we", "us", "our") collects and processes personal data when you use <strong>EduBridge</strong> at <a href="https://edu.kmgvitallinks.co.uk">edu.kmgvitallinks.co.uk</a>. We are committed to protecting your privacy and handling your data responsibly.
</p>

<h2>1. Data Controller</h2>
<p>The data controller for EduBridge is:</p>
<ul>
    <li><strong>KMG Vital Links (Pvt) Ltd</strong></li>
    <li>Harare, Zimbabwe</li>
    <li>Email: <a href="mailto:legal@kmgvitallinks.co.uk">legal@kmgvitallinks.co.uk</a></li>
    <li>Website: <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener">www.kmgvitallinks.co.uk</a></li>
</ul>

<h2>2. Information We Collect</h2>
<h3>2.1 Information you provide</h3>
<ul>
    <li><strong>Registration data:</strong> Full name, email address, password (stored as a secure hash), phone number, country, and role (student or teacher).</li>
    <li><strong>Profile information:</strong> Avatar, educational level, subjects of interest, preferred currency.</li>
    <li><strong>Teacher credentials:</strong> Qualifications, ID document, professional experience, and bank/mobile money details for payment purposes.</li>
    <li><strong>Payment data:</strong> Billing details entered during checkout. We do <em>not</em> store full card numbers — these are handled directly by our payment processors.</li>
    <li><strong>Communications:</strong> Messages sent through in-platform chat, support enquiries, or AI tutor conversations.</li>
    <li><strong>Content:</strong> Assignments, quiz answers, discussion posts, and files you upload.</li>
</ul>

<h3>2.2 Information collected automatically</h3>
<ul>
    <li><strong>Usage data:</strong> Pages viewed, lessons watched, time spent, quiz attempts, and feature interactions.</li>
    <li><strong>Device &amp; browser data:</strong> IP address, browser type, operating system, screen resolution, and referring URL.</li>
    <li><strong>Session data:</strong> Login timestamps, session duration, and activity logs.</li>
    <li><strong>Cookies and local storage:</strong> See our <a href="{{ route('cookies') }}">Cookie Policy</a> for full details.</li>
</ul>

<h2>3. How We Use Your Information</h2>
<p>We use your data to:</p>
<ul>
    <li>Create and manage your account.</li>
    <li>Deliver and personalise your learning experience.</li>
    <li>Process enrolments and payments.</li>
    <li>Send transactional communications (receipts, enrolment confirmations, password resets).</li>
    <li>Send service announcements, policy updates, and promotional offers (you may opt out of marketing emails at any time).</li>
    <li>Provide and improve the Chiedza AI tutor service.</li>
    <li>Detect and prevent fraud, abuse, and security incidents.</li>
    <li>Comply with our legal obligations under Zimbabwean law.</li>
    <li>Analyse platform usage to improve product features and educational outcomes.</li>
</ul>

<h2>4. Legal Basis for Processing</h2>
<p>We rely on the following legal bases:</p>
<ul>
    <li><strong>Contract performance:</strong> Processing necessary to fulfil your enrolment and deliver the service you purchased.</li>
    <li><strong>Legitimate interests:</strong> Security monitoring, fraud prevention, analytics, and product improvement.</li>
    <li><strong>Consent:</strong> For optional communications, analytics cookies, and AI tutor personalisation.</li>
    <li><strong>Legal obligation:</strong> Where we are required to retain or disclose data under Zimbabwean law.</li>
</ul>

<h2>5. Sharing Your Information</h2>
<p>We do <strong>not</strong> sell your personal data to third parties. We share data only in the following circumstances:</p>

<h3>5.1 Payment processors</h3>
<p>When you make a payment, your billing information is shared with the relevant payment provider. Each provider operates under its own privacy policy:</p>
<ul>
    <li><strong>Stripe</strong> (USD card payments) — <a href="https://stripe.com/privacy" target="_blank" rel="noopener">stripe.com/privacy</a></li>
    <li><strong>PayFast</strong> (USD/ZAR) — <a href="https://www.payfast.co.za/legal/privacy-policy" target="_blank" rel="noopener">payfast.co.za</a></li>
    <li><strong>Paynow Zimbabwe</strong> (ZWG) — subject to Paynow's terms</li>
    <li><strong>EcoCash</strong> (ZWG) — subject to EcoCash's terms</li>
    <li><strong>InnBucks</strong> (USD) — subject to InnBucks' terms</li>
</ul>

<h3>5.2 Service providers</h3>
<p>We use trusted third-party services to operate EduBridge, including cloud hosting, email delivery (transactional), and analytics. These providers access only the data necessary to perform their function and are contractually bound to keep it confidential.</p>

<h3>5.3 AI services</h3>
<p>The Chiedza AI tutor is powered by Anthropic's Claude API. Your questions and AI conversation content may be processed by Anthropic subject to <a href="https://www.anthropic.com/legal/privacy" target="_blank" rel="noopener">Anthropic's Privacy Policy</a>. We do not share your name or email with Anthropic.</p>

<h3>5.4 Legal requirements</h3>
<p>We may disclose your data to law enforcement, regulatory authorities, or courts in Zimbabwe where required by law or to protect the rights and safety of our users.</p>

<h2>6. International Transfers</h2>
<p>EduBridge operates globally and some of our service providers (such as Stripe and Anthropic) are based outside Zimbabwe. Where data is transferred internationally, we take steps to ensure an adequate level of data protection is in place, including contractual safeguards.</p>

<h2>7. Data Retention</h2>
<p>We retain your personal data for as long as your account is active and for a reasonable period thereafter for legal, audit, and backup purposes. Specifically:</p>
<ul>
    <li>Account data: retained until you request deletion, plus 12 months.</li>
    <li>Payment records: retained for 7 years to comply with financial record-keeping requirements.</li>
    <li>AI conversation logs: anonymised and retained for up to 90 days for quality improvement.</li>
    <li>Server logs: retained for 30 days.</li>
</ul>

<h2>8. Your Rights</h2>
<p>Subject to Zimbabwean data protection law, you have the right to:</p>
<ul>
    <li><strong>Access</strong> the personal data we hold about you.</li>
    <li><strong>Correct</strong> inaccurate or incomplete data.</li>
    <li><strong>Delete</strong> your data ("right to be forgotten") — note that some data must be retained for legal reasons.</li>
    <li><strong>Restrict</strong> or object to certain processing activities.</li>
    <li><strong>Portability</strong> — receive your data in a common machine-readable format.</li>
    <li><strong>Withdraw consent</strong> at any time where processing is based on consent.</li>
</ul>
<p>To exercise these rights, contact <a href="mailto:legal@kmgvitallinks.co.uk">legal@kmgvitallinks.co.uk</a>. We will respond within 30 days.</p>

<h2>9. Children's Privacy</h2>
<p>EduBridge is intended for users aged 13 and over. Users under the age of 18 must obtain parental or guardian consent before registering. If we become aware that we have inadvertently collected personal data from a child under 13 without parental consent, we will delete that data promptly. Parents or guardians may contact us at <a href="mailto:info@kmgvitallinks.co.uk">info@kmgvitallinks.co.uk</a> with concerns.</p>

<h2>10. Security</h2>
<p>We implement industry-standard security measures including HTTPS/TLS encryption, password hashing (bcrypt), server-side access controls, and regular security reviews. No system is entirely risk-free; if you believe your account has been compromised, contact us immediately.</p>

<h2>11. Cookies</h2>
<p>We use cookies and similar technologies to operate the platform and, with your consent, to collect analytics data. For full details, see our <a href="{{ route('cookies') }}">Cookie Policy</a>.</p>

<h2>12. Third-Party Links</h2>
<p>EduBridge may contain links to external websites, including the KMG Vital Links corporate site (<a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener">www.kmgvitallinks.co.uk</a>) and payment providers. We are not responsible for the privacy practices of those websites and encourage you to read their privacy policies.</p>

<h2>13. Changes to This Policy</h2>
<p>We may update this Privacy Policy periodically. Material changes will be notified by email or in-platform notification. The "Last updated" date at the top of this page will always reflect the most recent revision.</p>

<h2>14. Contact Us</h2>
<p>For all privacy-related enquiries, data subject requests, or complaints:</p>
<ul>
    <li><strong>KMG Vital Links (Pvt) Ltd</strong> — Data Privacy Team</li>
    <li>Email: <a href="mailto:legal@kmgvitallinks.co.uk">legal@kmgvitallinks.co.uk</a></li>
    <li>Website: <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener">www.kmgvitallinks.co.uk</a></li>
</ul>

@endsection
