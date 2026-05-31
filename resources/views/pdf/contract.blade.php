<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>EduBridge Independent Contractor Agreement — {{ $contract->teacher->name }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #1e293b; line-height: 1.6; }

    /* ── Page header ── */
    .header { border-bottom: 3px solid #7c3aed; padding-bottom: 14px; margin-bottom: 18px; display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .logo-text { font-size: 20pt; font-weight: bold; color: #7c3aed; }
    .logo-sub  { font-size: 8pt; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 13pt; font-weight: bold; color: #0f172a; }
    .doc-meta  { font-size: 7.5pt; color: #64748b; margin-top: 3px; }

    /* ── Section headings ── */
    h2 { font-size: 10.5pt; font-weight: bold; color: #7c3aed; margin: 16px 0 5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; text-transform: uppercase; letter-spacing: 0.03em; }
    h3 { font-size: 9.5pt; font-weight: bold; color: #0f172a; margin: 10px 0 3px; }
    p  { margin-bottom: 6px; text-align: justify; }
    ul { padding-left: 20px; margin-bottom: 8px; }
    ol { padding-left: 20px; margin-bottom: 8px; }
    li { margin-bottom: 3px; }

    /* ── Key terms grid ── */
    .terms-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .terms-grid td { border: 1px solid #e2e8f0; padding: 6px 10px; font-size: 8.5pt; vertical-align: top; width: 25%; }
    .terms-grid .label { font-size: 7pt; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 2px; }
    .terms-grid .value { font-weight: bold; color: #0f172a; }

    /* ── Parties table ── */
    .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .parties-table td { border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 8.5pt; vertical-align: top; width: 50%; }
    .parties-table .party-label { font-size: 7pt; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 3px; }
    .parties-table .party-name  { font-size: 11pt; font-weight: bold; color: #0f172a; }
    .parties-table .party-detail { font-size: 8pt; color: #475569; }

    /* ── Clause numbering ── */
    .clause { margin-bottom: 10px; }
    .clause-num { font-weight: bold; color: #7c3aed; }

    /* ── Policy section ── */
    .policy-box { border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 12px; page-break-inside: avoid; }
    .policy-header { background: #f8fafc; padding: 6px 12px; border-bottom: 1px solid #e2e8f0; }
    .policy-header .policy-title { font-size: 9.5pt; font-weight: bold; color: #1e293b; }
    .policy-header .policy-meta  { font-size: 7pt; color: #94a3b8; }
    .policy-body { padding: 10px 12px; font-size: 8.5pt; color: #334155; white-space: pre-wrap; word-wrap: break-word; }

    /* ── Signature block ── */
    .sig-box { border: 2px solid #10b981; border-radius: 6px; padding: 14px 16px; margin-top: 22px; background: #f0fdf4; }
    .sig-badge { font-size: 12pt; margin-bottom: 5px; }
    .sig-name  { font-size: 12pt; font-weight: bold; color: #065f46; margin-bottom: 4px; }
    .sig-box table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .sig-box td { font-size: 8pt; padding: 3px 0; vertical-align: top; }
    .sig-box td.label { width: 140px; color: #64748b; }
    .sig-box td.value { color: #0f172a; font-weight: bold; }
    .sig-legal { font-size: 7.5pt; color: #065f46; margin-top: 10px; }

    /* ── Notice box ── */
    .notice-box { background: #fefce8; border: 1px solid #fef08a; border-radius: 4px; padding: 8px 12px; margin: 10px 0; font-size: 8.5pt; color: #713f12; }

    /* ── Footer ── */
    .footer { margin-top: 28px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 7pt; color: #94a3b8; text-align: center; }

    /* ── Watermark ── */
    .signed-stamp {
        position: fixed;
        bottom: 80px; right: 40px;
        transform: rotate(-25deg);
        font-size: 38pt;
        font-weight: bold;
        color: rgba(16,185,129,0.10);
        border: 6px solid rgba(16,185,129,0.10);
        padding: 6px 14px;
        border-radius: 6px;
        pointer-events: none;
    }
</style>
</head>
<body>

<div class="signed-stamp">SIGNED</div>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-left">
        <div class="logo-text">EduBridge</div>
        <div class="logo-sub">by KMG Vital Links (Pvt) Ltd · edu.kmgvitallinks.co.uk</div>
    </div>
    <div class="header-right">
        <div class="doc-title">Independent Contractor Agreement</div>
        <div class="doc-meta">Teaching Services Contract · Version {{ $contract->version }}</div>
        <div class="doc-meta">Generated {{ now()->format('d M Y, H:i') }} UTC &nbsp;·&nbsp; Ref: EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }}</div>
    </div>
</div>

<div class="notice-box">
    <strong>IMPORTANT NOTICE:</strong> This is a legally binding Independent Contractor Agreement. By electronically signing this document, both parties acknowledge and accept all terms and conditions set out herein. Please read this agreement in its entirety before signing.
</div>

{{-- ── 1. Parties ── --}}
<h2>1. Parties to This Agreement</h2>
<p>This Independent Contractor Agreement ("<strong>Agreement</strong>") is entered into between the following parties:</p>
<table class="parties-table">
    <tr>
        <td>
            <span class="party-label">Platform Operator ("the Company")</span>
            <span class="party-name">KMG Vital Links (Pvt) Ltd</span>
            <div class="party-detail">
                Harare, Zimbabwe<br>
                legal@kmgvitallinks.co.uk<br>
                edu.kmgvitallinks.co.uk
            </div>
        </td>
        <td>
            <span class="party-label">Independent Contractor ("the Teacher / Contractor")</span>
            <span class="party-name">{{ $contract->teacher->name }}</span>
            <div class="party-detail">
                Email: {{ $contract->teacher->email }}<br>
                User ID: #{{ $contract->teacher_id }}<br>
                Contract Status: <strong>{{ strtoupper($contract->status) }}</strong>
            </div>
        </td>
    </tr>
</table>

{{-- ── 2. Key Terms ── --}}
<h2>2. Key Contract Terms at a Glance</h2>
<table class="terms-grid">
    <tr>
        <td><span class="label">Hourly Rate (USD)</span><span class="value">${{ number_format((float)($contract->rate_usd ?? 0), 2) }}</span></td>
        <td><span class="label">Payment Terms</span><span class="value">{{ $contract->payment_terms ?? '—' }}</span></td>
        <td><span class="label">Contract Term</span><span class="value">{{ $contract->term_months ? $contract->term_months.' months' : 'Rolling' }}</span></td>
        <td><span class="label">Exclusivity</span><span class="value">{{ $contract->exclusivity === 'exclusive' ? 'Exclusive' : 'Non-Exclusive' }}</span></td>
    </tr>
    <tr>
        <td><span class="label">Issued</span><span class="value">{{ optional($contract->issued_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Effective From</span><span class="value">{{ optional($contract->effective_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Expires</span><span class="value">{{ optional($contract->expires_at)->format('d M Y') ?? 'On termination' }}</span></td>
        <td><span class="label">Contract Version</span><span class="value">v{{ $contract->version }}</span></td>
    </tr>
</table>

{{-- ── 3. Nature of Engagement ── --}}
<h2>3. Nature of Engagement — Independent Contractor Status</h2>

<div class="clause">
<p><span class="clause-num">3.1 Independent Contractor.</span> The Contractor is engaged as an independent contractor and not as an employee, agent, partner, or joint venture partner of the Company. Nothing in this Agreement shall be construed to create an employment relationship, partnership, agency, or joint venture between the parties. The Contractor expressly acknowledges and agrees that they are self-employed for all purposes.</p>

<p><span class="clause-num">3.2 No Employment Benefits.</span> As an independent contractor, the Contractor is not entitled to and shall not receive any employment benefits from the Company, including but not limited to: annual leave, sick leave, maternity/paternity leave, pension contributions, medical aid, unemployment insurance, workers' compensation, or any other benefits typically provided to employees.</p>

<p><span class="clause-num">3.3 Tax Obligations.</span> The Contractor is solely responsible for all applicable taxes, including income tax, value-added tax (VAT) (where applicable), and any other levies arising from remuneration received under this Agreement. The Company shall not withhold taxes on behalf of the Contractor unless required by applicable law. The Contractor indemnifies the Company against any liability arising from the Contractor's failure to comply with their tax obligations.</p>

<p><span class="clause-num">3.4 Tools and Equipment.</span> Unless otherwise agreed in writing, the Contractor shall provide their own tools, technology, equipment, and materials necessary to perform the Services. The Company provides access to the EduBridge platform as a delivery mechanism, not as a provision of working tools.</p>

<p><span class="clause-num">3.5 Freedom to Engage Others.</span> Subject to Clause 11 (Non-Solicitation) and Clause 5 (Intellectual Property), the Contractor retains the right to provide similar services to other clients, unless this Agreement specifies exclusive engagement in Clause 2 above.</p>

<p><span class="clause-num">3.6 Control.</span> The Company has no right to direct or control the manner or means by which the Contractor performs the Services, other than specifying the results to be achieved, the curriculum framework, scheduling requirements, and the platform standards set out in this Agreement and any applicable policies.</p>
</div>

{{-- ── 4. Services and Deliverables ── --}}
<h2>4. Services and Deliverables</h2>

<div class="clause">
<p><span class="clause-num">4.1 Scope of Services.</span> The Contractor agrees to provide educational teaching and tutoring services ("Services") via the EduBridge platform, including but not limited to: delivering live online sessions, creating and marking assignments, providing student feedback, participating in curriculum activities, and maintaining accurate session records.</p>

<p><span class="clause-num">4.2 Schedule.</span> Sessions shall be scheduled through the EduBridge platform's scheduling system. The Contractor agrees to honour all confirmed session bookings. Cancellations must be made with a minimum of 24 hours' notice except in cases of genuine emergency, which must be communicated to the Company immediately.</p>

<p><span class="clause-num">4.3 Session Logging.</span> Following each session, the Contractor must submit an accurate session log through the EduBridge platform within 24 hours, recording actual duration, student attendance, and session notes. Inaccurate or fraudulent session logging constitutes a material breach of this Agreement.</p>

<p><span class="clause-num">4.4 Quality Standards.</span> The Contractor agrees to maintain a high standard of teaching quality, including preparedness, punctuality, student engagement, and professional conduct in all interactions through the platform.</p>

<p><span class="clause-num">4.5 Curriculum Compliance.</span> The Contractor shall deliver Services in alignment with the curriculum materials, learning outcomes, and subject guidelines provided or approved by the Company. Any deviations must be pre-approved in writing.</p>
</div>

{{-- ── 5. Compensation and Payment ── --}}
<h2>5. Compensation and Payment</h2>

<div class="clause">
<p><span class="clause-num">5.1 Rate.</span> The Company agrees to pay the Contractor at the rate of <strong>USD ${{ number_format((float)($contract->rate_usd ?? 0), 2) }} per hour</strong> for Services performed and verified through the EduBridge platform.</p>

<p><span class="clause-num">5.2 Payment Schedule.</span> {{ $contract->payment_terms ?? 'Payment shall be made monthly, within 14 days of month-end, subject to submission and approval of session logs.' }} Payment is conditional on the submission of accurate session logs and approval by the Company's administration.</p>

<p><span class="clause-num">5.3 Payment Method.</span> Payment shall be made via the payout method registered by the Contractor in their EduBridge profile (bank transfer, EcoCash, InnBucks, or such other method as agreed in writing). The Contractor is responsible for keeping their payout details current and accurate.</p>

<p><span class="clause-num">5.4 Disputed Payments.</span> If the Contractor disputes any payment, they must notify the Company in writing within 14 days of the payment date. Failure to dispute within this period constitutes acceptance of the payment as rendered.</p>

<p><span class="clause-num">5.5 Deductions.</span> The Company reserves the right to deduct from any payment amounts owed by the Contractor to the Company arising from: platform fee recoveries, chargebacks, student refunds attributable to Contractor fault, or penalties arising from material breach as outlined in this Agreement.</p>

<p><span class="clause-num">5.6 Rate Review.</span> The hourly rate may be reviewed and adjusted by mutual written agreement between the parties, with a minimum of 30 days' written notice. Any rate change will be reflected in a new contract version issued to the Contractor.</p>
</div>

{{-- ── 6. Code of Conduct ── --}}
<h2>6. Code of Conduct and Professional Standards</h2>

<div class="clause">
<p><span class="clause-num">6.1 Professional Conduct.</span> The Contractor shall at all times conduct themselves professionally, respectfully, and in good faith when interacting with students, parents, other teachers, and Company staff. This includes all communications within the EduBridge platform, in live sessions, via messaging, email, and any associated communication channels.</p>

<p><span class="clause-num">6.2 Appropriate Conduct with Students.</span> The Contractor must maintain appropriate professional boundaries with all students at all times. The Contractor shall not:</p>
<ul>
    <li>Engage in any romantic, sexual, or inappropriately personal relationship or communication with any student;</li>
    <li>Request, obtain, or use students' personal contact details outside of the EduBridge platform without explicit written Company approval;</li>
    <li>Share personal opinions on political, religious, or other divisive topics in a manner that could cause distress to students;</li>
    <li>Use demeaning, offensive, discriminatory, or abusive language or behaviour towards any student;</li>
    <li>Conduct, facilitate, or recommend unofficial tutoring sessions outside the platform with students enrolled on EduBridge.</li>
</ul>

<p><span class="clause-num">6.3 Safeguarding.</span> The Contractor must comply with all applicable child safeguarding laws and regulations. The Contractor must report any safeguarding concerns immediately to the Company's designated safeguarding lead. Failure to report a safeguarding concern constitutes a serious breach of this Agreement and may result in immediate termination and referral to relevant authorities.</p>

<p><span class="clause-num">6.4 Drugs and Alcohol.</span> The Contractor must not be under the influence of alcohol, illegal drugs, or any substance that impairs their ability to deliver safe and effective teaching during any session.</p>

<p><span class="clause-num">6.5 No Discrimination.</span> The Contractor shall not discriminate against any student, parent, or colleague on the basis of race, ethnicity, gender, gender identity, sexual orientation, religion, disability, age, or any other protected characteristic. Breach of this clause constitutes a material breach and will result in immediate termination of this Agreement.</p>

<p><span class="clause-num">6.6 Social Media and Public Statements.</span> The Contractor shall not make public statements, posts, or publications (including on social media) that could reasonably be construed as representing the Company's views without prior written approval, or that disparage or bring the Company, the EduBridge platform, its students, or staff into disrepute.</p>

<p><span class="clause-num">6.7 Platform Integrity.</span> The Contractor shall not manipulate, abuse, or exploit any features of the EduBridge platform, including falsifying session logs, attendance records, assignment grades, or any other platform data.</p>
</div>

{{-- ── 7. Intellectual Property ── --}}
<h2>7. Intellectual Property and Content Ownership</h2>

<div class="clause">
<p><span class="clause-num">7.1 Company-Owned Content.</span> All course materials, curricula, lesson frameworks, quizzes, assessments, platform design, branding, and related materials created by the Company ("Company Materials") remain the exclusive intellectual property of KMG Vital Links (Pvt) Ltd. The Contractor is granted a limited, non-transferable licence to use Company Materials solely for the purpose of delivering the Services during the term of this Agreement.</p>

<p><span class="clause-num">7.2 Content Created by the Contractor.</span> Any educational content, lesson plans, videos, assignments, quiz questions, resource materials, or other materials created by the Contractor specifically for delivery through the EduBridge platform in connection with the Services ("<strong>Platform Content</strong>") shall, upon creation, be assigned to and owned exclusively by KMG Vital Links (Pvt) Ltd. The Contractor hereby irrevocably assigns all intellectual property rights in Platform Content to the Company. The Company may use, adapt, publish, and distribute such content without further consent or compensation to the Contractor beyond the agreed rate.</p>

<p><span class="clause-num">7.3 Pre-Existing Materials.</span> Where the Contractor incorporates their own pre-existing intellectual property into Platform Content, the Contractor grants the Company a perpetual, royalty-free, worldwide licence to use, reproduce, and adapt such materials in connection with the platform.</p>

<p><span class="clause-num">7.4 No Infringement.</span> The Contractor warrants that any content they create and deliver through the platform does not infringe the intellectual property rights of any third party. The Contractor indemnifies the Company against any claims, losses, or damages arising from such infringement.</p>

<p><span class="clause-num">7.5 Recording of Sessions.</span> The Company may record live sessions delivered by the Contractor for quality assurance, archiving, and supplementary learning purposes. The Contractor consents to such recording and acknowledges that recordings constitute Company property.</p>
</div>

{{-- ── 8. Confidentiality ── --}}
<h2>8. Confidentiality and Data Protection</h2>

<div class="clause">
<p><span class="clause-num">8.1 Confidential Information.</span> The Contractor agrees to keep strictly confidential all information relating to the Company's business, students, pricing, technical systems, platform architecture, strategies, financial information, and any other information designated as confidential or that a reasonable person would understand to be confidential ("<strong>Confidential Information</strong>").</p>

<p><span class="clause-num">8.2 Non-Disclosure.</span> The Contractor shall not, during or after the term of this Agreement, disclose any Confidential Information to any third party without prior written consent from the Company, except as required by law, court order, or regulatory authority, in which case the Contractor must notify the Company promptly before disclosure.</p>

<p><span class="clause-num">8.3 Student Data.</span> The Contractor acknowledges that student personal data is particularly sensitive. The Contractor must comply with applicable data protection laws (including the Zimbabwe Data Protection Act where applicable) and the Company's data protection policies. Student data may only be accessed and used for the purpose of delivering the Services and must not be retained, copied, shared, or used for any other purpose.</p>

<p><span class="clause-num">8.4 Return of Information.</span> Upon termination of this Agreement, the Contractor must immediately return or destroy (as directed) all Confidential Information and Company Materials in their possession, and must not retain any copies.</p>

<p><span class="clause-num">8.5 Duration.</span> The confidentiality obligations in this Clause 8 survive the termination or expiry of this Agreement indefinitely.</p>
</div>

{{-- ── 9. Platform Usage ── --}}
<h2>9. Platform Usage, Technology and Equipment</h2>

<div class="clause">
<p><span class="clause-num">9.1 Platform Access.</span> The Company grants the Contractor a limited, non-exclusive, non-transferable licence to access and use the EduBridge platform solely for the purpose of delivering the Services. This licence terminates immediately upon termination of this Agreement.</p>

<p><span class="clause-num">9.2 Account Security.</span> The Contractor is solely responsible for maintaining the security and confidentiality of their login credentials. The Contractor must not share account access with any third party. Any unauthorised access to the Contractor's account must be reported to the Company immediately.</p>

<p><span class="clause-num">9.3 Acceptable Use.</span> The Contractor must not use the platform for any unlawful purpose, to distribute spam, malware, or harmful content, to access data of other users beyond what is necessary for their role, or in any way that disrupts, damages, or places an unreasonable burden on the platform.</p>

<p><span class="clause-num">9.4 Technical Requirements.</span> The Contractor is responsible for maintaining the necessary hardware, internet connectivity, and software to participate in live sessions and use the platform to the required standard. The Company does not guarantee the availability of the platform and is not liable for service interruptions beyond its reasonable control.</p>

<p><span class="clause-num">9.5 Equipment Scheme.</span> Where the Company has provided equipment or facilitated an equipment loan to the Contractor, the terms of that separate equipment agreement shall apply. Such equipment remains Company property and must be returned upon termination of this Agreement.</p>
</div>

{{-- ── 10. Background Checks & KYC ── --}}
<h2>10. Identity Verification and Background Checks</h2>

<div class="clause">
<p><span class="clause-num">10.1 KYC Verification.</span> The Contractor acknowledges that they are required to complete the Company's Know Your Customer (KYC) verification process, including submission of valid identity documents, proof of qualification, and a selfie with identification. This Agreement is conditional upon satisfactory completion of the KYC process.</p>

<p><span class="clause-num">10.2 Accuracy of Information.</span> The Contractor warrants that all information and documents provided during the KYC verification process are genuine, accurate, and up to date. Submission of false, forged, or misleading documentation constitutes a material breach and will result in immediate termination of this Agreement and may be reported to relevant authorities.</p>

<p><span class="clause-num">10.3 Background Checks.</span> The Company reserves the right to conduct or commission background and reference checks at any time. The Contractor consents to such checks and agrees to cooperate fully with the process.</p>

<p><span class="clause-num">10.4 Qualification Verification.</span> The Contractor warrants that they hold all qualifications, certifications, and professional registrations stated in their profile and that such qualifications are current and in good standing. The Contractor must promptly notify the Company if any qualification is withdrawn, suspended, or revoked.</p>
</div>

{{-- ── 11. Non-Solicitation ── --}}
<h2>11. Non-Solicitation</h2>

<div class="clause">
<p><span class="clause-num">11.1 Non-Solicitation of Students.</span> During the term of this Agreement and for a period of <strong>twelve (12) months</strong> after its termination or expiry, the Contractor shall not, directly or indirectly, solicit, approach, or offer private tutoring or educational services to any student who was enrolled on EduBridge at any time during the Contractor's engagement with the Company, whether through direct contact, referral, or via a competing platform.</p>

<p><span class="clause-num">11.2 Non-Solicitation of Staff and Contractors.</span> During the term of this Agreement and for a period of twelve (12) months following termination, the Contractor shall not solicit, recruit, or encourage any employee, agent, or contractor of the Company to leave their engagement with the Company.</p>

<p><span class="clause-num">11.3 Reasonableness.</span> The Contractor acknowledges that these restrictions are reasonable given the Company's legitimate business interests in protecting its student relationships and workforce. If any restriction is found to be unenforceable by a court of competent jurisdiction, it shall be modified to the minimum extent necessary to make it enforceable.</p>
</div>

{{-- ── 12. Term and Termination ── --}}
<h2>12. Term and Termination</h2>

<div class="clause">
<p><span class="clause-num">12.1 Term.</span> This Agreement commences on the Effective Date shown in Clause 2 and shall continue for the Contract Term specified, unless earlier terminated in accordance with this Clause.</p>

<p><span class="clause-num">12.2 Termination by Notice.</span> Either party may terminate this Agreement without cause by giving the other party <strong>thirty (30) days'</strong> written notice. During the notice period, the Contractor shall continue to honour scheduled sessions and the Company shall continue to remunerate for Services delivered.</p>

<p><span class="clause-num">12.3 Immediate Termination for Cause.</span> The Company may terminate this Agreement with immediate effect, without notice or compensation, if the Contractor:</p>
<ul>
    <li>Commits a material breach of this Agreement that is incapable of remedy, or fails to remedy a remediable breach within 7 days of written notice;</li>
    <li>Engages in fraudulent, dishonest, or grossly negligent conduct;</li>
    <li>Is found to have submitted false documentation during KYC or at any time;</li>
    <li>Breaches the Code of Conduct provisions in Clause 6, including any safeguarding failure or discriminatory conduct;</li>
    <li>Violates any applicable law, regulation, or professional standard;</li>
    <li>Becomes insolvent, bankrupt, or is otherwise unable to meet their obligations under this Agreement;</li>
    <li>Engages in conduct that brings or is likely to bring the Company into disrepute.</li>
</ul>

<p><span class="clause-num">12.4 Effect of Termination.</span> Upon termination: (a) the Contractor's platform access is revoked immediately; (b) all outstanding payments for Services duly delivered and logged are payable within 30 days, subject to any permitted deductions; (c) obligations under Clauses 7, 8, and 11 survive termination.</p>

<p><span class="clause-num">12.5 Renewal.</span> At the end of the Contract Term, this Agreement may be renewed by mutual written agreement on such terms as the parties may agree at the time, including any revised rate of remuneration.</p>
</div>

{{-- ── 13. Indemnity & Liability ── --}}
<h2>13. Indemnification and Limitation of Liability</h2>

<div class="clause">
<p><span class="clause-num">13.1 Contractor Indemnity.</span> The Contractor shall indemnify, defend, and hold harmless the Company, its directors, officers, employees, and agents from and against any claims, damages, losses, costs, and expenses (including reasonable legal fees) arising from: (a) the Contractor's breach of this Agreement; (b) the Contractor's negligence, wilful misconduct, or unlawful acts; (c) any third-party intellectual property infringement by the Contractor; (d) the Contractor's failure to comply with applicable tax, employment, or professional obligations.</p>

<p><span class="clause-num">13.2 Limitation of Liability.</span> To the maximum extent permitted by applicable law, the Company's total aggregate liability to the Contractor under or in connection with this Agreement shall not exceed the total fees paid to the Contractor in the three (3) months immediately preceding the event giving rise to the claim.</p>

<p><span class="clause-num">13.3 Exclusion of Consequential Loss.</span> Neither party shall be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profit or loss of business opportunity, even if advised of the possibility of such damages.</p>

<p><span class="clause-num">13.4 Force Majeure.</span> Neither party shall be liable for any delay or failure to perform obligations under this Agreement due to circumstances beyond their reasonable control, including but not limited to acts of God, government actions, pandemic, power failure, or internet service disruption. The affected party must notify the other promptly and make all reasonable efforts to resume performance.</p>
</div>

{{-- ── 14. Dispute Resolution ── --}}
<h2>14. Dispute Resolution and Governing Law</h2>

<div class="clause">
<p><span class="clause-num">14.1 Governing Law.</span> This Agreement shall be governed by and construed in accordance with the laws of Zimbabwe. Any reference to applicable law includes all relevant statutory instruments, regulations, and subsidiary legislation in force from time to time.</p>

<p><span class="clause-num">14.2 Good Faith Resolution.</span> In the event of any dispute arising out of or in connection with this Agreement, the parties shall first attempt to resolve the dispute in good faith through direct negotiation between senior representatives of each party within 21 days of written notice of the dispute.</p>

<p><span class="clause-num">14.3 Mediation.</span> If the dispute is not resolved through negotiation within 21 days (or such longer period as agreed), either party may refer the matter to mediation before a mutually agreed mediator. The costs of mediation shall be shared equally.</p>

<p><span class="clause-num">14.4 Arbitration.</span> If mediation fails to resolve the dispute within 30 days of the appointment of a mediator, the dispute shall be referred to and finally resolved by binding arbitration in accordance with the Arbitration Act [Chapter 7:15] of Zimbabwe. The arbitration shall be conducted in Harare by a single arbitrator agreed by the parties or, failing agreement, appointed by the President of the Law Society of Zimbabwe. The language of arbitration shall be English.</p>

<p><span class="clause-num">14.5 Jurisdiction.</span> Notwithstanding the above, either party reserves the right to seek urgent injunctive or other equitable relief from a court of competent jurisdiction where necessary to prevent immediate and irreparable harm.</p>
</div>

{{-- ── 15. General Provisions ── --}}
<h2>15. General Provisions</h2>

<div class="clause">
<p><span class="clause-num">15.1 Entire Agreement.</span> This Agreement, together with any policies and schedules incorporated by reference, constitutes the entire agreement between the parties relating to its subject matter and supersedes all prior agreements, representations, and understandings, whether written or oral, relating to the same subject matter.</p>

<p><span class="clause-num">15.2 Amendments.</span> No amendment, modification, or variation of this Agreement shall be valid unless made in writing and signed (or electronically accepted) by both parties. A new contract version issued via the EduBridge platform and signed electronically by the Contractor constitutes a valid written amendment.</p>

<p><span class="clause-num">15.3 Severability.</span> If any provision of this Agreement is held by a court or arbitrator to be invalid, illegal, or unenforceable, that provision shall be modified to the minimum extent necessary to make it enforceable, or if modification is not possible, it shall be severed from this Agreement. The remaining provisions shall continue in full force and effect.</p>

<p><span class="clause-num">15.4 Waiver.</span> No failure or delay by either party in exercising any right or remedy under this Agreement shall constitute a waiver of that right or remedy. A waiver of any breach shall not be deemed a waiver of any subsequent breach.</p>

<p><span class="clause-num">15.5 Notices.</span> Notices under this Agreement shall be sent by email to the email addresses set out in Clause 1, or to such other address as a party may notify in writing. Notices are effective on the date of sending if sent during business hours, or the next business day if sent outside business hours.</p>

<p><span class="clause-num">15.6 Electronic Signing.</span> The parties agree that electronic signatures (including typed name, checkbox confirmation, and associated metadata captured by the EduBridge platform) constitute valid and binding signatures for the purposes of this Agreement and have the same legal effect as handwritten signatures.</p>

<p><span class="clause-num">15.7 No Assignment.</span> The Contractor may not assign, sub-contract, or otherwise transfer any rights or obligations under this Agreement without prior written consent from the Company. The Company may assign this Agreement to any successor entity, affiliate, or acquirer of its business.</p>

<p><span class="clause-num">15.8 Counterparts.</span> This Agreement may be executed in counterparts (including electronic counterparts), each of which shall be an original, and all of which together shall constitute one and the same instrument.</p>
</div>

@if($contract->addendum)
{{-- ── Addendum ── --}}
<h2>16. Addendum / Special Conditions</h2>
<p>The following special conditions have been agreed between the parties and form part of this Agreement:</p>
<p>{{ $contract->addendum }}</p>
@endif

{{-- ── Policies Incorporated by Reference ── --}}
<h2>{{ $contract->addendum ? '17' : '16' }}. Platform Policies Incorporated by Reference</h2>
<p style="font-size:8.5pt;color:#475569;margin-bottom:10px;">
    The following Company policies were active and in force at the time this contract was signed. They form part of this Agreement and are binding on the Contractor. The full text of each policy as accepted is reproduced below. In the event of a conflict between these policies and the terms above, the terms above shall prevail.
</p>

@foreach($policies as $policy)
<div class="policy-box">
    <div class="policy-header">
        <span class="policy-title">{{ $policy->title }}</span>
        <span class="policy-meta"> &nbsp;·&nbsp; v{{ $policy->version }} &nbsp;·&nbsp; {{ str_replace('_', ' ', $policy->category) }} &nbsp;·&nbsp; Effective {{ optional($policy->effective_at)->format('d M Y') }}</span>
    </div>
    <div class="policy-body">{{ $policy->body }}</div>
</div>
@endforeach

{{-- ── Signature Block ── --}}
<div class="sig-box">
    <div class="sig-badge">✅ Electronically Signed</div>
    <div class="sig-name">{{ $contract->signed_name }}</div>
    <table>
        <tr>
            <td class="label">Full Legal Name</td>
            <td class="value">{{ $contract->signed_name }}</td>
        </tr>
        <tr>
            <td class="label">Capacity</td>
            <td class="value">Independent Contractor (Teacher)</td>
        </tr>
        <tr>
            <td class="label">Signed On</td>
            <td class="value">{{ $contract->signed_at?->format('d M Y, H:i') }} UTC</td>
        </tr>
        <tr>
            <td class="label">IP Address</td>
            <td class="value">{{ $contract->signed_ip ?? 'not recorded' }}</td>
        </tr>
        <tr>
            <td class="label">User Agent</td>
            <td class="value" style="font-size:7pt;">{{ Str::limit($contract->signed_user_agent ?? 'not recorded', 80) }}</td>
        </tr>
        <tr>
            <td class="label">Contract Expires</td>
            <td class="value">{{ optional($contract->expires_at)->format('d M Y') ?? 'On termination' }}</td>
        </tr>
        <tr>
            <td class="label">Document Reference</td>
            <td class="value">EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }}</td>
        </tr>
        <tr>
            <td class="label">Counterparty</td>
            <td class="value">KMG Vital Links (Pvt) Ltd (authorised by platform issuance)</td>
        </tr>
    </table>
    <p class="sig-legal">
        By signing this Agreement electronically, the Contractor confirms they have read, understood, and agreed to be bound by all terms and conditions set out herein, including all incorporated policies listed above. This electronic signature is legally binding under the laws of Zimbabwe and constitutes the Contractor's full and unconditional acceptance of this Independent Contractor Agreement.
    </p>
</div>

{{-- ── Footer ── --}}
<div class="footer">
    <p>KMG Vital Links (Pvt) Ltd &nbsp;·&nbsp; Harare, Zimbabwe &nbsp;·&nbsp; legal@kmgvitallinks.co.uk &nbsp;·&nbsp; edu.kmgvitallinks.co.uk</p>
    <p style="margin-top:3px;">Document Reference: EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }} &nbsp;·&nbsp; Generated {{ now()->format('d M Y H:i') }} UTC &nbsp;·&nbsp; This document was generated automatically by EduBridge.</p>
</div>

</body>
</html>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>EduBridge Teaching Contract — {{ $contract->teacher->name }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.55; }

    /* ── Page header ── */
    .header { border-bottom: 3px solid #7c3aed; padding-bottom: 14px; margin-bottom: 20px; display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .logo-text { font-size: 20pt; font-weight: bold; color: #7c3aed; }
    .logo-sub  { font-size: 8pt; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 14pt; font-weight: bold; color: #0f172a; }
    .doc-meta  { font-size: 8pt; color: #64748b; margin-top: 3px; }

    /* ── Sections ── */
    h2 { font-size: 11pt; font-weight: bold; color: #7c3aed; margin: 18px 0 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    h3 { font-size: 10pt; font-weight: bold; color: #334155; margin: 12px 0 4px; }
    p  { margin-bottom: 6px; }
    ul { padding-left: 18px; margin-bottom: 6px; }
    li { margin-bottom: 3px; }

    /* ── Key terms grid ── */
    .terms-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .terms-grid td { border: 1px solid #e2e8f0; padding: 7px 10px; font-size: 9pt; vertical-align: top; width: 25%; }
    .terms-grid .label { font-size: 7.5pt; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 2px; }
    .terms-grid .value { font-weight: bold; color: #0f172a; }

    /* ── Policy section ── */
    .policy-box { border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 14px; page-break-inside: avoid; }
    .policy-header { background: #f8fafc; padding: 7px 12px; border-bottom: 1px solid #e2e8f0; }
    .policy-header .policy-title { font-size: 10pt; font-weight: bold; color: #1e293b; }
    .policy-header .policy-meta  { font-size: 7.5pt; color: #94a3b8; }
    .policy-body { padding: 10px 12px; font-size: 9pt; color: #334155; white-space: pre-wrap; word-wrap: break-word; }

    /* ── Signature block ── */
    .sig-box { border: 2px solid #10b981; border-radius: 6px; padding: 14px 16px; margin-top: 24px; background: #f0fdf4; }
    .sig-box .sig-badge { font-size: 13pt; margin-bottom: 6px; }
    .sig-box .sig-name  { font-size: 13pt; font-weight: bold; color: #065f46; margin-bottom: 4px; }
    .sig-box table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .sig-box td { font-size: 8.5pt; padding: 3px 0; vertical-align: top; }
    .sig-box td.label { width: 130px; color: #64748b; }
    .sig-box td.value { color: #0f172a; font-weight: bold; }

    /* ── Footer ── */
    .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 7.5pt; color: #94a3b8; text-align: center; }

    /* ── Watermark for signed ── */
    .signed-stamp {
        position: fixed;
        bottom: 80px; right: 40px;
        transform: rotate(-25deg);
        font-size: 38pt;
        font-weight: bold;
        color: rgba(16,185,129,0.12);
        border: 6px solid rgba(16,185,129,0.12);
        padding: 6px 14px;
        border-radius: 6px;
        pointer-events: none;
    }
</style>
</head>
<body>

<div class="signed-stamp">SIGNED</div>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-left">
        <div class="logo-text">EduBridge</div>
        <div class="logo-sub">by KMG Vital Links (Pvt) Ltd · edu.kmgvitallinks.co.uk</div>
    </div>
    <div class="header-right">
        <div class="doc-title">Teaching Contract</div>
        <div class="doc-meta">Version {{ $contract->version }} · Generated {{ now()->format('d M Y, H:i') }} UTC</div>
    </div>
</div>

{{-- ── Parties ── --}}
<h2>1. Parties</h2>
<table class="terms-grid">
    <tr>
        <td><span class="label">Platform Operator</span><span class="value">KMG Vital Links (Pvt) Ltd</span></td>
        <td><span class="label">Teacher / Contractor</span><span class="value">{{ $contract->teacher->name }}</span></td>
        <td><span class="label">Teacher Email</span><span class="value">{{ $contract->teacher->email }}</span></td>
        <td><span class="label">Contract Status</span><span class="value">{{ strtoupper($contract->status) }}</span></td>
    </tr>
</table>

{{-- ── Key Terms ── --}}
<h2>2. Key Contract Terms</h2>
<table class="terms-grid">
    <tr>
        <td><span class="label">Hourly Rate (USD)</span><span class="value">${{ number_format((float)($contract->rate_usd ?? 0), 2) }}</span></td>
        <td><span class="label">Payment Terms</span><span class="value">{{ $contract->payment_terms ?? '—' }}</span></td>
        <td><span class="label">Contract Term</span><span class="value">{{ $contract->term_months ? $contract->term_months.' months' : '—' }}</span></td>
        <td><span class="label">Exclusivity</span><span class="value">{{ str_replace('_', '-', $contract->exclusivity ?? '—') }}</span></td>
    </tr>
    <tr>
        <td><span class="label">Issued</span><span class="value">{{ optional($contract->issued_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Effective From</span><span class="value">{{ optional($contract->effective_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Expires</span><span class="value">{{ optional($contract->expires_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Contract Version</span><span class="value">v{{ $contract->version }}</span></td>
    </tr>
</table>

@if($contract->addendum)
<h2>3. Addendum / Special Conditions</h2>
<p>{{ $contract->addendum }}</p>
@endif

{{-- ── Incorporated Policies ── --}}
<h2>{{ $contract->addendum ? '4' : '3' }}. Policies Incorporated by Reference</h2>
<p style="font-size:9pt;color:#64748b;margin-bottom:10px;">
    The following policies were in force at the time this contract was signed. The full text of each policy as accepted is reproduced below.
</p>

@foreach($policies as $policy)
<div class="policy-box">
    <div class="policy-header">
        <span class="policy-title">{{ $policy->title }}</span>
        <span class="policy-meta"> &nbsp;·&nbsp; v{{ $policy->version }} &nbsp;·&nbsp; {{ str_replace('_', ' ', $policy->category) }} &nbsp;·&nbsp; Effective {{ optional($policy->effective_at)->format('d M Y') }}</span>
    </div>
    <div class="policy-body">{{ $policy->body }}</div>
</div>
@endforeach

{{-- ── Signature Block ── --}}
<div class="sig-box">
    <div class="sig-badge">✅ Electronically Signed</div>
    <div class="sig-name">{{ $contract->signed_name }}</div>
    <table>
        <tr>
            <td class="label">Full Legal Name</td>
            <td class="value">{{ $contract->signed_name }}</td>
        </tr>
        <tr>
            <td class="label">Signed On</td>
            <td class="value">{{ $contract->signed_at?->format('d M Y, H:i') }} UTC</td>
        </tr>
        <tr>
            <td class="label">IP Address</td>
            <td class="value">{{ $contract->signed_ip ?? 'not recorded' }}</td>
        </tr>
        <tr>
            <td class="label">Contract Expires</td>
            <td class="value">{{ optional($contract->expires_at)->format('d M Y') ?? 'No expiry set' }}</td>
        </tr>
        <tr>
            <td class="label">Document Reference</td>
            <td class="value">EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }}</td>
        </tr>
    </table>
    <p style="font-size:8pt;color:#065f46;margin-top:10px;">
        This electronic signature constitutes a legally binding agreement between the teacher named above and KMG Vital Links (Pvt) Ltd under the laws of Zimbabwe. The signing metadata above provides a complete audit trail.
    </p>
</div>

{{-- ── Footer ── --}}
<div class="footer">
    <p>KMG Vital Links (Pvt) Ltd &nbsp;·&nbsp; Harare, Zimbabwe &nbsp;·&nbsp; legal@kmgvitallinks.co.uk &nbsp;·&nbsp; edu.kmgvitallinks.co.uk</p>
    <p style="margin-top:3px;">This document was generated automatically by EduBridge. Document reference: EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }}</p>
</div>

</body>
</html>
