<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Course, Payment, User};

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'students'  => User::where('role','student')->count(),
                'teachers'  => User::where('role','teacher')->count(),
                'courses'   => Course::count(),
                'revenue'   => Payment::where('status','paid')->sum('amount'),
            ],
            'recentUsers'    => User::latest()->take(10)->get(),
            'recentPayments' => Payment::with(['user','course'])->latest()->take(10)->get(),
        ]);
    }
}
