<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with('course')->withCount('redemptions')->latest()->paginate(25);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $courses = Course::orderBy('title')->get(['id', 'title']);
        return view('admin.coupons.edit', ['coupon' => new Coupon(), 'courses' => $courses]);
    }

    public function edit(Coupon $coupon)
    {
        $courses = Course::orderBy('title')->get(['id', 'title']);
        return view('admin.coupons.edit', compact('coupon', 'courses'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, null);
        $data['created_by'] = $request->user()->id;
        Coupon::create($data);
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validateData($request, $coupon->id));
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted.');
    }

    protected function validateData(Request $request, ?int $id): array
    {
        return $request->validate([
            'code'             => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($id)],
            'description'      => 'nullable|string|max:200',
            'type'             => ['required', Rule::in(['percent', 'fixed'])],
            'value'            => 'required|numeric|min:0',
            'currency'         => 'required|string|max:8',
            'course_id'        => 'nullable|exists:courses,id',
            'max_redemptions'  => 'nullable|integer|min:1',
            'per_user_limit'   => 'required|integer|min:1',
            'min_order_value'  => 'nullable|numeric|min:0',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',
            'is_active'        => 'nullable|boolean',
        ]) + ['is_active' => (bool) $request->boolean('is_active', true)];
    }
}
