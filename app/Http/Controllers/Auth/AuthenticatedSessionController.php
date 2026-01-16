<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {


        $request->authenticate();
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

    
        // If not approved
        if (!$user->approved) {
            return redirect()->route('waitingapproval')
                             ->with('error', 'Your account is not approved yet.');
        }

       
        if (!$user->otp_verified) {
            session(['otp_user_id' => $user->id]);
            return redirect()->route('otp.index')
                             ->with('message', 'Please verify OTP sent to your email.');
        }

        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('user')) {
            return redirect()->route('user.userproducts.dashboard');
        }

        return redirect()->route('waitingapproval');
    }

    public function destroy()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }
}
