<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('price_usd')->paginate(20);
        return view('admin.plans.index', compact('plans'));
    }

    public function create() { return view('admin.plans.edit', ['plan' => new SubscriptionPlan(['is_active' => true, 'interval' => 'month'])]); }
    public function edit(SubscriptionPlan $plan) { return view('admin.plans.edit', compact('plan')); }

    public function store(Request $request)
    {
        $data = $this->validateData($request, null);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        SubscriptionPlan::create($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validateData($request, $plan->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }

    private function validateData(Request $request, ?int $id): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['nullable', 'string', 'max:100', Rule::unique('subscription_plans', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_usd'   => ['required', 'numeric', 'min:0'],
            'price_zwg'   => ['nullable', 'numeric', 'min:0'],
            'interval'    => ['required', Rule::in(['month', 'year'])],
            'is_active'   => ['boolean'],
        ]);
    }
}
