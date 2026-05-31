<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const PRICING_KEYS = [
        'price_hourly'   => ['label' => '1 Hour',           'min' => 0.01],
        'price_monthly'  => ['label' => '1 Month',          'min' => 0.01],
        'price_termly'   => ['label' => '1 Term (3 months)','min' => 0.01],
        'price_zwg_rate' => ['label' => 'ZWG exchange rate (1 USD = X ZWG)', 'min' => 1],
    ];

    public function index()
    {
        $settings = Setting::whereIn('key', array_keys(self::PRICING_KEYS))->pluck('value', 'key');
        $fields   = self::PRICING_KEYS;

        return view('admin.settings.pricing', compact('settings', 'fields'));
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (self::PRICING_KEYS as $key => $meta) {
            $rules[$key] = ['required', 'numeric', 'min:' . $meta['min']];
        }

        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.pricing')
            ->with('success', 'Pricing updated successfully.');
    }
}
