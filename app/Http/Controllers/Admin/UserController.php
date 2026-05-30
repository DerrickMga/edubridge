<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role, fn($q,$r) => $q->where('role',$r))
            ->when($request->search, fn($q,$s) => $q->where('name','like',"%$s%")->orWhere('email','like',"%$s%"))
            ->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)  { return view('admin.users.show', compact('user')); }

    public function edit(User $user)  { return view('admin.users.edit', compact('user')); }

    public function update(Request $request, User $user)
    {
        $user->update($request->validate([
            'name'      => 'required|string|max:255',
            'role'      => 'required|in:student,teacher,admin',
            'is_active' => 'boolean',
        ]));
        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
