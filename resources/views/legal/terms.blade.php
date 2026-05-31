@extends('layouts.legal')

@section('title', 'Terms of Service')
@section('hero-title', 'Terms of Service')
@section('meta-description', 'EduBridge Terms of Service — your rights and responsibilities when using the platform.')
@section('last-updated', 'May 2026')

@section('content')

<p class="lead text-base text-slate-700 font-medium">
    Please read these Terms of Service carefully before using <strong>EduBridge</strong> (<a href="https://edu.kmgvitallinks.co.uk">edu.kmgvitallinks.co.uk</a>), operated by <strong>KMG Vital Links (Pvt) Ltd</strong>. By creating an account or using the platform, you agree to be bound by these Terms.
</p>

<h2>1. About EduBridge</h2>
<p>EduBridge is an online learning platform owned and operated by <strong>KMG Vital Links (Private) Limited</strong>, a company incorporated in Zimbabwe (hereinafter "KMG", "we", "us", or "our"). EduBridge provides O-Level and A-Level educational content, live teaching sessions, AI-assisted study tools, and related services to students and teachers in Zimbabwe and the diaspora.</p>
<ul>
    <li><strong>Registered name:</strong> KMG Vital Links (Pvt) Ltd</li>
    <li><strong>Country of incorporation:</strong> Zimbabwe</li>
    <li><strong>Registered office:</strong> Harare, Zimbabwe</li>
    <li><strong>Corporate website:</strong> <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener">www.kmgvitallinks.co.uk</a></li>
    <li><strong>Platform URL:</strong> <a href="https://edu.kmgvitallinks.co.uk" target="_blank" rel="noopener">edu.kmgvitallinks.co.uk</a></li>
    <li><strong>Contact:</strong> <a href="mailto:info@kmgvitallinks.co.uk">info@kmgvitallinks.co.uk</a></li>
</ul>

<h2>2. Acceptance of Terms</h2>
<p>By registering for an account, browsing, or using any feature of EduBridge, you confirm that:</p>
<ul>
    <li>You are at least 13 years old (users under 18 must have parental or guardian consent).</li>
    <li>You have read, understood, and agree to these Terms in full.</li>
    <li>You have the legal authority to enter into a binding agreement.</li>
</ul>
<p>If you do not agree to these Terms, you must not use EduBridge.</p>

<h2>3. User Accounts</h2>
<h3>3.1 Registration</h3>
<p>You must provide accurate, current, and complete information when registering. You are responsible for maintaining the confidentiality of your password and for all activity that occurs under your account.</p>

<h3>3.2 Account security</h3>
<p>You must notify us immediately at <a href="mailto:info@kmgvitallinks.co.uk">info@kmgvitallinks.co.uk</a> if you suspect any unauthorised use of your account. We are not liable for any loss arising from unauthorised access resulting from your failure to keep your credentials secure.</p>

<h3>3.3 Account types</h3>
<p>EduBridge has two primary account types: <strong>Students</strong> (learners) and <strong>Teachers</strong> (tutors). Each type has specific rights and obligations described in sections 4 and 5 below.</p>

<h2>4. Student Terms</h2>
<h3>4.1 Enrolment</h3>
<p>When you enrol in a course, you receive a personal, non-transferable, limited licence to access and view the course content for the duration of your access period. Enrolment does not transfer any intellectual property rights to you.</p>

<h3>4.2 Access periods</h3>
<p>Course access is granted for the period you purchase (hourly, monthly, or termly). Free promotional access is limited to the advertised promotional period. After expiry, access is revoked unless renewed.</p>

<h3>4.3 Acceptable academic use</h3>
<p>Course materials are for your personal study only. You must not share login credentials, screen-record lessons for distribution, or use course content to create competing products. See also our <a href="{{ route('acceptable-use') }}">Acceptable Use Policy</a>.</p>

<h2>5. Teacher Terms</h2>
<h3>5.1 Eligibility</h3>
<p>To register as a teacher, you must be a qualified educator with verifiable credentials. KMG reserves the right to verify qualifications and reject or suspend any teacher account that fails verification.</p>

<h3>5.2 Content ownership and licence</h3>
<p>You retain ownership of original content you create and upload. By uploading content to EduBridge, you grant KMG a worldwide, royalty-free, non-exclusive licence to host, display, stream, and make that content available to enrolled students on the platform.</p>

<h3>5.3 Teacher conduct</h3>
<p>Teachers agree to deliver content of a professional standard, to maintain the accuracy and appropriateness of their materials, and to adhere to the EduBridge Teacher Code of Conduct. Teachers are bound by a separate Teaching Contract issued through the platform.</p>

<h3>5.4 Earnings and payments</h3>
<p>Teacher earnings are governed by the Teacher Payment Terms and the platform's fee structure, which may be updated from time to time. Payments are made in USD or ZWG as agreed, subject to successful completion of platform verification.</p>

<h2>6. Payments and Billing</h2>
<p>Payments are processed by third-party providers including Stripe (USD), PayFast (USD/ZAR), Paynow Zimbabwe (ZWG), and EcoCash (ZWG). By making a payment, you also agree to the terms of the applicable payment provider. All prices are displayed in the currency shown at checkout.</p>
<p>For full details on refunds and cancellations, see our <a href="{{ route('refund') }}">Refund Policy</a>.</p>

<h2>7. Intellectual Property</h2>
<p>All platform software, design, branding, and KMG-created content (including the Chiedza AI tutor) are the exclusive property of KMG Vital Links (Pvt) Ltd or its licensors and are protected by copyright, trademark, and other intellectual property laws. You may not copy, reproduce, redistribute, or create derivative works from platform materials without our prior written consent.</p>

<h2>8. Prohibited Conduct</h2>
<p>You must not:</p>
<ul>
    <li>Use EduBridge for any unlawful purpose or in violation of Zimbabwean law.</li>
    <li>Attempt to gain unauthorised access to any part of the platform or its infrastructure.</li>
    <li>Harass, threaten, or abuse other users or staff.</li>
    <li>Post false, defamatory, or misleading information.</li>
    <li>Upload malware, spam, or any harmful code.</li>
    <li>Circumvent access controls, digital rights management, or payment systems.</li>
    <li>Scrape or harvest data from EduBridge without our written permission.</li>
</ul>
<p>For the full list of prohibited activities, see our <a href="{{ route('acceptable-use') }}">Acceptable Use Policy</a>.</p>

<h2>9. Privacy and Data Protection</h2>
<p>Your privacy is important to us. Our <a href="{{ route('privacy') }}">Privacy Policy</a> explains in detail how we collect, use, store, and protect your personal data, including data shared with payment processors and third-party services. By using EduBridge you consent to those practices.</p>

<h2>10. Disclaimer of Warranties</h2>
<p>EduBridge is provided on an "as-is" and "as-available" basis. While we take all reasonable care to ensure the platform functions reliably and the educational content is accurate, we make no warranties, express or implied, regarding completeness, accuracy, reliability, or fitness for a particular purpose.</p>
<p>We do not guarantee that EduBridge will be uninterrupted, error-free, or free from viruses. Internet connectivity and network reliability are outside our control.</p>

<h2>11. Limitation of Liability</h2>
<p>To the maximum extent permitted by Zimbabwean law, KMG Vital Links (Pvt) Ltd shall not be liable for:</p>
<ul>
    <li>Indirect, incidental, or consequential damages arising from your use of EduBridge.</li>
    <li>Loss of profits, data, or educational opportunity.</li>
    <li>Failure to pass examinations or achieve academic results.</li>
    <li>Actions of third-party payment providers.</li>
</ul>
<p>Our total aggregate liability to you shall not exceed the total amount you paid to EduBridge in the 12 months preceding the claim.</p>

<h2>12. Termination</h2>
<p>We reserve the right to suspend or terminate any account that violates these Terms, our Acceptable Use Policy, or applicable law, with or without notice. Upon termination:</p>
<ul>
    <li>Your access to the platform and all enrolled courses will be revoked.</li>
    <li>Content you uploaded (teacher accounts) may be removed.</li>
    <li>Accrued earnings owed to teachers will be paid according to the payment schedule, less any amounts we are entitled to withhold.</li>
</ul>
<p>You may close your account at any time by contacting <a href="mailto:info@kmgvitallinks.co.uk">info@kmgvitallinks.co.uk</a>.</p>

<h2>13. Changes to These Terms</h2>
<p>We may update these Terms periodically. When we do, we will update the "Last updated" date at the top of this page and notify registered users by email or in-platform notification. Continued use of EduBridge after changes constitutes acceptance of the revised Terms.</p>

<h2>14. Governing Law and Disputes</h2>
<p>These Terms are governed by and construed in accordance with the laws of <strong>Zimbabwe</strong>. Any disputes arising under these Terms shall be subject to the exclusive jurisdiction of the courts of Zimbabwe. If you are located outside Zimbabwe, you agree to submit to Zimbabwean jurisdiction for matters relating to this platform.</p>

<h2>15. Contact Us</h2>
<p>If you have any questions about these Terms, please contact:</p>
<ul>
    <li><strong>KMG Vital Links (Pvt) Ltd</strong></li>
    <li>Harare, Zimbabwe</li>
    <li>Email: <a href="mailto:legal@kmgvitallinks.co.uk">legal@kmgvitallinks.co.uk</a></li>
    <li>General: <a href="mailto:info@kmgvitallinks.co.uk">info@kmgvitallinks.co.uk</a></li>
    <li>Website: <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener">www.kmgvitallinks.co.uk</a></li>
</ul>

@endsection
