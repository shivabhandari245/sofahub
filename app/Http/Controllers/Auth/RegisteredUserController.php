<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    // Show registration form
    public function create()
    {
        return view('auth.register');
    }

    // Handle registration
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_verified' => false,
            'approved' => false,
        ]);

        // Log in user
        Auth::login($user);

        // Generate OTP
        $this->otpService->generate($user);

        // Store user ID in session for fallback
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.index')
            ->with('message', 'Registration successful! An OTP has been sent to your email.');
    }
}
