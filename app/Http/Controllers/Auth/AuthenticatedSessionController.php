<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

public function store(LoginRequest $request): RedirectResponse
{
   
    $request->authenticate();
    $request->session()->regenerate();

    /** @var User $user */
        $user = Auth::user();
    if (!$user->approved) {
        Auth::logout();

        return redirect()
            ->route('waitingapproval')
            ->with('error', 'Your account is not approved yet.');
    }

    
    if ($user->hasRole('admin')) {
        return redirect()->intended(route('admin.dashboard'));
    }

    if ($user->hasRole('user')) {
        return redirect()->intended(route('user.userproducts.dashboard'));
    }

   
    return redirect()->route('waitingapproval');
}


  


   
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}