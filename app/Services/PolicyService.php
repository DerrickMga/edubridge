<?php

namespace App\Services;

use App\Models\TeacherContract;
use App\Models\TeacherPolicy;
use App\Models\TeacherPolicyAcknowledgement;
use App\Models\User;
use App\Notifications\ContractSignedNotification;
use Illuminate\Support\Facades\DB;

class PolicyService
{
    /**
     * Issue (or return existing pending) contract for a teacher.
     * Snapshots the current active version of every policy so the contract
     * is locked to the terms in force at issuance time.
     */
    public function issueContract(User $teacher, array $attrs, ?int $issuedBy = null): TeacherContract
    {
        $existing = TeacherContract::where('teacher_id', $teacher->id)
            ->whereIn('status', ['pending', 'signed'])
            ->latest('id')->first();
        if ($existing && $existing->status === 'pending') {
            return $existing;
        }

        $snapshot = TeacherPolicy::currentBySlug()->values()
            ->map(fn (TeacherPolicy $p) => [
                'policy_id' => $p->id,
                'slug'      => $p->slug,
                'title'     => $p->title,
                'version'   => $p->version,
                'category'  => $p->category,
            ])->all();

        $latestVersion = (int) (TeacherContract::where('teacher_id', $teacher->id)->max('version') ?? 0);

        return TeacherContract::create(array_merge([
            'teacher_id'     => $teacher->id,
            'version'        => $latestVersion + 1,
            'status'         => 'pending',
            'issued_at'      => now(),
            'effective_at'   => now(),
            'rate_usd'       => $teacher->hourly_rate_usd,
            'payment_terms'  => 'Monthly, paid within 14 days of month-end',
            'term_months'    => 12,
            'exclusivity'    => 'non_exclusive',
            'terms_snapshot' => $snapshot,
            'issued_by'      => $issuedBy,
        ], $attrs));
    }

    /**
     * Sign a contract — also writes an acknowledgement for every snapshotted
     * policy so the audit trail captures exactly which versions were agreed to.
     */
    public function signContract(TeacherContract $contract, string $typedName, string $ip, ?string $userAgent): TeacherContract
    {
        if ($contract->status !== 'pending') {
            return $contract;
        }
        if ($contract->expires_at === null && $contract->term_months) {
            $contract->expires_at = now()->addMonths((int) $contract->term_months);
        }

        DB::transaction(function () use ($contract, $typedName, $ip, $userAgent) {
            $contract->fill([
                'status'            => 'signed',
                'signed_at'         => now(),
                'signed_name'       => $typedName,
                'signed_ip'         => $ip,
                'signed_user_agent' => substr((string) $userAgent, 0, 500),
            ])->save();

            // Supersede prior signed contracts.
            TeacherContract::where('teacher_id', $contract->teacher_id)
                ->where('id', '!=', $contract->id)
                ->where('status', 'signed')
                ->update(['status' => 'superseded']);

            foreach ((array) $contract->terms_snapshot as $term) {
                $this->acknowledge(
                    User::find($contract->teacher_id),
                    (int) ($term['policy_id'] ?? 0),
                    (int) ($term['version'] ?? 0),
                    $ip,
                    $userAgent,
                );
            }
        });

        $fresh = $contract->fresh();

        // Send signed-contract email with PDF attached.
        try {
            $fresh->teacher->notify(new ContractSignedNotification($fresh));
        } catch (\Throwable $e) {
            // Non-fatal: log but don't break the signing flow.
            logger()->error('ContractSignedNotification failed: ' . $e->getMessage());
        }

        return $fresh;
    }

    public function acknowledge(?User $teacher, int $policyId, int $version, ?string $ip, ?string $userAgent): void
    {
        if (! $teacher || ! $policyId || ! $version) return;
        TeacherPolicyAcknowledgement::firstOrCreate(
            ['teacher_id' => $teacher->id, 'policy_id' => $policyId, 'version' => $version],
            [
                'acknowledged_at' => now(),
                'ip_address'      => $ip,
                'user_agent'      => substr((string) $userAgent, 0, 500),
            ],
        );
    }

    /** Outstanding (un-acked) policies for a teacher — used in the dashboard banner. */
    public function outstandingPoliciesFor(User $teacher): \Illuminate\Support\Collection
    {
        $current = TeacherPolicy::currentBySlug();
        $acked = TeacherPolicyAcknowledgement::where('teacher_id', $teacher->id)
            ->get()->keyBy(fn ($a) => $a->policy_id.'-'.$a->version);

        return $current->values()->filter(function (TeacherPolicy $p) use ($acked) {
            return ! $acked->has($p->id.'-'.$p->version);
        })->values();
    }

    public function activeContract(User $teacher): ?TeacherContract
    {
        return TeacherContract::where('teacher_id', $teacher->id)
            ->where('status', 'signed')
            ->latest('signed_at')->first();
    }
}
