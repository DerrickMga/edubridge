<?php

namespace Database\Seeders;

use App\Models\PolicyRiskEvent;
use App\Models\TeacherPolicy;
use Illuminate\Database\Seeder;

class TeacherPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'slug'     => 'code-of-conduct',
                'title'    => 'Teacher Code of Conduct',
                'category' => 'code_of_conduct',
                'body'     => <<<MD
**1. Professional conduct.** Teachers shall treat students, guardians, and platform staff with respect at all times. Discrimination, harassment, abuse, or any form of bullying is grounds for immediate suspension.

**2. Punctuality.** Teachers must start sessions at the scheduled time and provide at least 12 hours' notice for cancellations except in cases of emergency.

**3. Communication.** All student communication must occur through the EduBridge platform. Off-platform contact with minors is strictly prohibited.

**4. Safeguarding.** Teachers must immediately report any disclosure or suspicion of a child being at risk to platform admins.

**5. Substance use.** Conducting sessions under the influence of alcohol or unprescribed substances is prohibited.

Breach of this code may result in suspension, contract termination, and — where applicable — referral to authorities.
MD,
            ],
            [
                'slug'     => 'intellectual-property',
                'title'    => 'Intellectual Property & Content Ownership',
                'category' => 'ip',
                'body'     => <<<MD
**Ownership of teacher-created materials.** Lesson notes, slide decks, quizzes, and recordings produced by the Teacher for the EduBridge platform are jointly licensed to EduBridge for use on the platform during and after the contract term. The Teacher retains the right to use the materials elsewhere unless the contract states otherwise.

**Third-party content.** Teachers warrant that any material uploaded is either original, properly licensed, or used under fair-use principles, and indemnify EduBridge against any claims arising from infringement.

**Recordings.** By delivering a live session the Teacher consents to that session being recorded, stored, and made available to enrolled students for replay.

**Platform-created derivative works** (AI summaries, transcripts, embeddings) are owned by EduBridge.
MD,
            ],
            [
                'slug'     => 'confidentiality',
                'title'    => 'Confidentiality & Data Protection',
                'category' => 'confidentiality',
                'body'     => <<<MD
Teachers must not disclose student personal information, performance data, or platform business information to third parties without written consent.

Teachers must comply with applicable data-protection laws (Zimbabwe Data Protection Act, GDPR where applicable) when handling student data.

Suspected data incidents must be reported within 24 hours.
MD,
            ],
            [
                'slug'     => 'acceptable-use',
                'title'    => 'Acceptable Use of the Platform',
                'category' => 'acceptable_use',
                'body'     => <<<MD
Teachers shall not:
- Solicit students for off-platform tutoring or payment.
- Share login credentials.
- Upload malware or pirated material.
- Use AI tools to generate content that misrepresents originality without disclosure.
- Use the platform to promote unrelated commercial offerings.

Violation may result in account suspension and contract termination.
MD,
            ],
            [
                'slug'     => 'safeguarding',
                'title'    => 'Child Safeguarding',
                'category' => 'safeguarding',
                'body'     => <<<MD
EduBridge serves learners under 18. Teachers must:
- Complete safeguarding training within 30 days of signing.
- Never request a 1:1 video call with a minor without a guardian or recording.
- Never request personal photographs of students.
- Report any safeguarding concern via the in-platform reporting channel within 24 hours.

Failure to comply is grounds for immediate termination and may be reported to authorities.
MD,
            ],
            [
                'slug'     => 'payments-and-tax',
                'title'    => 'Payments, Hours, and Tax',
                'category' => 'payments',
                'body'     => <<<MD
Payments are calculated from logged shifts and approved by an admin. Teachers are independent contractors responsible for their own tax compliance.

Hours are recorded automatically from session start/end and rounded to the nearest 5 minutes. Disputed hours must be raised within 14 days.

Payouts occur monthly in the currency selected at onboarding and are subject to platform fees disclosed in the pricing settings page.
MD,
            ],
            [
                'slug'     => 'termination',
                'title'    => 'Termination & Notice',
                'category' => 'termination',
                'body'     => <<<MD
Either party may terminate this contract with 30 days' written notice.

EduBridge may terminate immediately for: gross misconduct, safeguarding breach, fraud, repeated no-shows (≥3 in 30 days), or breach of confidentiality.

On termination, the Teacher must hand over any in-progress materials and stop accessing student data within 7 days.
MD,
            ],
        ];

        foreach ($policies as $p) {
            TeacherPolicy::firstOrCreate(
                ['slug' => $p['slug'], 'version' => 1],
                [
                    'title'        => $p['title'],
                    'category'     => $p['category'],
                    'body'         => $p['body'],
                    'is_active'    => true,
                    'effective_at' => now(),
                ],
            );
        }

        // Default contingency matrix
        $events = [
            ['NO_SHOW', 'Teacher no-show for live session', 'attendance', 'high',
                'Session starts and the assigned teacher has not joined within 10 minutes.',
                "1. Auto-notify the teacher.\n2. Reassign session to a backup using the workforce auto-assigner.\n3. Log the incident; ≥3 in 30 days triggers termination review.",
                'termination'],
            ['LATE', 'Teacher more than 5 minutes late', 'attendance', 'low',
                'Session start time + 5 minutes elapsed and the teacher has not joined.',
                "1. Send teacher a reminder push notification.\n2. Record lateness in the incident log for performance review.",
                'code-of-conduct'],
            ['ABUSIVE_CONDUCT', 'Abusive or inappropriate language with a student', 'conduct', 'critical',
                'Student or parent reports verbal/written abuse, or AI transcript flags abusive language.',
                "1. Immediately suspend the teacher's account.\n2. Pull AI report + session recording.\n3. Admin review within 24 hours.\n4. Terminate contract if substantiated; refer to authorities if a minor is involved.",
                'safeguarding'],
            ['IP_BREACH', 'Uploaded pirated or third-party copyrighted material', 'ip', 'high',
                'DMCA complaint received, or automated content scan flags a resource.',
                "1. Take the resource offline immediately.\n2. Notify the teacher and request justification within 48 hours.\n3. Escalate to admin for warning or termination.",
                'intellectual-property'],
            ['OFF_PLATFORM_SOLICIT', 'Soliciting students off-platform', 'conduct', 'high',
                'Student reports the teacher asking for direct payment or contact off-platform.',
                "1. Immediately disable teacher chat access.\n2. Admin investigation within 72 hours.\n3. Terminate contract on first substantiated incident.",
                'acceptable-use'],
            ['DATA_LEAK', 'Suspected student data leak', 'safeguarding', 'critical',
                'Personal data of a student appears outside EduBridge or is shared with a third party.',
                "1. Lock affected teacher accounts.\n2. Begin breach assessment within 24 hours.\n3. Notify affected users + regulators per data-protection law.\n4. Determine if termination warranted.",
                'confidentiality'],
            ['HOURS_DISPUTE', 'Teacher disputes recorded hours', 'payment', 'medium',
                'Teacher raises a payout discrepancy within 14 days.',
                "1. Pull session attendance + recording metadata.\n2. Adjust hours where evidence supports the claim.\n3. Document adjustment with admin sign-off.",
                'payments-and-tax'],
            ['REPEATED_LATE', 'Repeated lateness (≥3 in 30 days)', 'attendance', 'medium',
                'Three LATE events in any rolling 30-day window.',
                "1. Trigger performance review with the teacher.\n2. Pause auto-assignments for 7 days.\n3. Escalate to NO_SHOW playbook if continues.",
                'code-of-conduct'],
            ['DEVICE_LOSS', 'Loss or theft of platform-issued equipment', 'technical', 'medium',
                'Teacher reports lost/stolen laptop, tablet, or peripherals issued via the equipment loan programme.',
                "1. Remotely revoke device access where possible.\n2. Open insurance claim.\n3. Determine repayment per equipment loan agreement.",
                null],
            ['LEGAL_INQUIRY', 'External legal inquiry naming a teacher', 'legal', 'critical',
                'Subpoena, regulator request, or court inquiry that names a teacher or their student.',
                "1. Notify counsel immediately.\n2. Preserve all related data (litigation hold).\n3. Suspend teacher pending resolution.",
                null],
        ];

        foreach ($events as [$code, $title, $cat, $sev, $trigger, $play, $policySlug]) {
            $policy = $policySlug ? TeacherPolicy::where('slug', $policySlug)->orderByDesc('version')->first() : null;
            PolicyRiskEvent::firstOrCreate(
                ['code' => $code],
                [
                    'title'             => $title,
                    'category'          => $cat,
                    'severity'          => $sev,
                    'trigger'           => $trigger,
                    'response_playbook' => $play,
                    'policy_id'         => $policy?->id,
                    'is_active'         => true,
                ],
            );
        }
    }
}
