<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserApprovalController extends Controller
{
    public function pending(Request $request)
    {
    
        $users = User::where('approved', false)->get();
        return view('admin.pending-users', compact('users'));
    }

    public function approve(User $user)
    {
        $user->approved = true;
        $user->approved_by =Auth::id();
        $user->approved_at = now();
        $user->save();

        return back()->with('success', 'User has been approved.');
    }
}
