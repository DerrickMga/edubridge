<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EquipmentLoanApplication;
use App\Models\TeacherEquipmentProfile;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Show the equipment requirements page + teacher's current profile + loan history.
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->equipmentProfile;
        $loans   = $user->equipmentLoans()->latest()->get();

        return view('teacher.equipment.index', compact('profile', 'loans'));
    }

    /**
     * Save / update the teacher's equipment self-declaration.
     */
    public function saveProfile(Request $request)
    {
        $data = $request->validate([
            'has_stable_internet'         => ['required', 'boolean'],
            'internet_type'               => ['nullable', 'in:fibre,dsl,cable,mobile_4g,mobile_5g,satellite,other'],
            'internet_speed_mbps'         => ['nullable', 'integer', 'min:1', 'max:10000'],
            'has_laptop_or_desktop'       => ['required', 'boolean'],
            'device_type'                 => ['nullable', 'in:laptop,desktop,tablet'],
            'device_os'                   => ['nullable', 'string', 'max:80'],
            'has_camera'                  => ['required', 'boolean'],
            'camera_type'                 => ['nullable', 'in:built_in,external_webcam,phone'],
            'has_proper_lighting'         => ['required', 'boolean'],
            'lighting_type'               => ['nullable', 'in:natural,ring_light,softbox,led_panel,desk_lamp,other'],
            'has_noise_canceling_headset' => ['required', 'boolean'],
            'headset_model'               => ['nullable', 'string', 'max:120'],
        ]);

        $data['submitted_at'] = now();

        $profile = TeacherEquipmentProfile::updateOrCreate(
            ['teacher_id' => $request->user()->id],
            array_merge($data, ['teacher_id' => $request->user()->id])
        );

        $profile->computeStatus();
        $profile->save();

        return back()->with('success', 'Equipment profile saved. Status: ' . $profile->statusLabel());
    }

    /**
     * Submit a new equipment loan application.
     */
    public function applyForLoan(Request $request)
    {
        $availableItems = array_keys(EquipmentLoanApplication::availableItems());

        $data = $request->validate([
            'items_requested'        => ['required', 'array', 'min:1'],
            'items_requested.*'      => ['required', 'string', 'in:' . implode(',', $availableItems)],
            'purpose'                => ['required', 'string', 'min:30', 'max:1000'],
            'amount_requested_usd'   => ['required', 'numeric', 'min:10', 'max:1000'],
            'repayment_period_months'=> ['required', 'integer', 'in:3,6,9,12,18,24'],
            'employment_context'     => ['nullable', 'string', 'max:800'],
            'teacher_notes'          => ['nullable', 'string', 'max:500'],
            // Supporting documents
            'income_proof'           => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'address_proof'          => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'quotation'              => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'quotation_notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = $request->user()->id;
        $baseDir = "equipment-docs/{$userId}";

        // Store documents privately (not publicly accessible)
        $data['income_proof_path']  = $request->file('income_proof')
            ->store("{$baseDir}/income", 'private');
        $data['address_proof_path'] = $request->file('address_proof')
            ->store("{$baseDir}/address", 'private');

        if ($request->hasFile('quotation')) {
            $data['quotation_path'] = $request->file('quotation')
                ->store("{$baseDir}/quotations", 'private');
        }

        // Remove raw file keys — only paths stored in DB
        unset($data['income_proof'], $data['address_proof'], $data['quotation']);

        $data['teacher_id'] = $userId;

        EquipmentLoanApplication::create($data);

        return back()->with('success', 'Your loan application has been submitted with supporting documents. We will review it within 3–5 business days.');
    }

    /**
     * Download a private document attached to one of the teacher's own loan applications.
     */
    public function downloadDocument(EquipmentLoanApplication $loan, string $type)
    {
        abort_if($loan->teacher_id !== auth()->id(), 403);

        $paths = [
            'income'     => $loan->income_proof_path,
            'address'    => $loan->address_proof_path,
            'quotation'  => $loan->quotation_path,
        ];

        abort_if(! isset($paths[$type]) || ! $paths[$type], 404);

        return response()->download(
            storage_path('app/private/' . $paths[$type]),
            basename($paths[$type])
        );
    }
}
