<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherEquipmentProfile extends Model
{
    protected $fillable = [
        'teacher_id',
        'has_stable_internet',
        'internet_type',
        'internet_speed_mbps',
        'has_laptop_or_desktop',
        'device_type',
        'device_os',
        'has_camera',
        'camera_type',
        'has_proper_lighting',
        'lighting_type',
        'has_noise_canceling_headset',
        'headset_model',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'has_stable_internet'         => 'boolean',
        'has_laptop_or_desktop'       => 'boolean',
        'has_camera'                  => 'boolean',
        'has_proper_lighting'         => 'boolean',
        'has_noise_canceling_headset' => 'boolean',
        'submitted_at'                => 'datetime',
    ];

    /**
     * The four core requirements for teaching eligibility.
     * Returns array of [label, met (bool), detail].
     */
    public function requirementsStatus(): array
    {
        return [
            [
                'key'    => 'internet',
                'label'  => 'Stable Internet Connection',
                'detail' => $this->internet_type ? ucwords(str_replace('_', ' ', $this->internet_type))
                    . ($this->internet_speed_mbps ? " · {$this->internet_speed_mbps} Mbps" : '') : null,
                'met'    => $this->has_stable_internet,
            ],
            [
                'key'    => 'device',
                'label'  => 'Laptop or Desktop Computer with Camera',
                'detail' => ($this->device_type ? ucfirst($this->device_type) : null)
                    . ($this->device_os ? " — {$this->device_os}" : '')
                    . ($this->camera_type ? ' · ' . ucwords(str_replace('_', ' ', $this->camera_type)) . ' camera' : ''),
                'met'    => $this->has_laptop_or_desktop && $this->has_camera,
            ],
            [
                'key'    => 'lighting',
                'label'  => 'Proper Lighting',
                'detail' => $this->lighting_type ? ucwords(str_replace('_', ' ', $this->lighting_type)) : null,
                'met'    => $this->has_proper_lighting,
            ],
            [
                'key'    => 'headset',
                'label'  => 'Noise-Canceling Headset',
                'detail' => $this->headset_model,
                'met'    => $this->has_noise_canceling_headset,
            ],
        ];
    }

    /**
     * Compute and persist the overall status from the four requirements.
     */
    public function computeStatus(): string
    {
        $metCount = collect($this->requirementsStatus())->where('met', true)->count();

        if ($metCount === 4) {
            $status = 'meets_requirements';
        } elseif ($metCount >= 2) {
            $status = 'needs_improvement';
        } else {
            $status = 'incomplete';
        }

        $this->status = $status;
        return $status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'meets_requirements' => 'badge-green',
            'needs_improvement'  => 'badge-amber',
            default              => 'badge-slate',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'meets_requirements' => 'Ready to Teach',
            'needs_improvement'  => 'Needs Improvement',
            default              => 'Incomplete',
        };
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
