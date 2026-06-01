<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);
        $request->session()->put('locale', $locale);
        return back();
    }
}
