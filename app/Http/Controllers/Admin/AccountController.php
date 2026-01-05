<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    
    public function index(Request $request)
    {
        $query = User::with('roles')->where('approved', true);

     
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->get();
        $pendingUsers = User::where('approved', false)->get();
        $roles = Role::pluck('name');

        // Edit user if requested
        $editUser = null;
        if ($request->filled('edit_id')) {
            $editUser = User::find($request->edit_id);
        }

        return view('admin.account', compact('users', 'pendingUsers', 'roles', 'editUser'));
    }

    // Store a new user (pending by default)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|array',
            'role.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'approved' => false,
        ]);

        $user->syncRoles($request->role);

        return redirect()->route('accounts')->with('success', 'User created successfully and pending approval.');
    }

    // Update existing user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|array',
            'role.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        $user->syncRoles($request->role);

        return redirect()->route('accounts')->with('success', 'User updated successfully.');
    }

    // Delete user
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('accounts')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('accounts')->with('success', 'User deleted successfully.');
    }

    // Approve pending user
    public function approve(User $user)
    {
        if ($user->approved) {
            return redirect()->route('accounts')->with('error', 'User is already approved.');
        }

        $user->update([
            'approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('accounts')->with('success', 'User approved successfully.');
    }
}
