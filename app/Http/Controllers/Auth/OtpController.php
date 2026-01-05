<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Services\OtpService;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function index()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('login')->withErrors(['otp' => 'Please login first.']);
        }

        return view('auth.verify-otp');
    }

      public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['otp' => 'User not found.']);
        }

        if (!$this->otpService->validate($user, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        // OTP is valid — mark as verified and clear OTP fields
        $user->update([
            'otp_verified' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        Auth::login($user);

        // Clear session
        session()->forget('otp_user_id');

        // Redirect based on role or default route
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->can('view_showroom_dashboard')) {
            return redirect()->route('user.userproducts.dashboard');
        }
        
        return match ($user->role) {
        'admin' => redirect()->intended(route('admin.dashboard')),
        'user'  => redirect()->intended(route('user.userproducts.dashboard')),
        default => redirect()->route('waitingapproval'),
    };

     
    }


    public function resend()
    {
        $userId = session('otp_user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $key = 'otp_resend:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['error' => "You can request a new OTP in $seconds seconds."], 429);
        }

        $this->otpService->generate($user);
        RateLimiter::hit($key, 60);

        return response()->json(['message' => 'A new OTP has been sent to your email.']);
    }
}
