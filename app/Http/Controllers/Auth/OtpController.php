<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    // Show OTP verification form
    public function index()
    {
        $userId = session('otp_user_id');

        if (!$userId || !User::find($userId)) {
            return redirect()->route('login')->withErrors([
                'otp' => 'Session expired or invalid. Please login again.'
            ]);
        }

        return view('auth.verify-otp');
    }

    // Verify submitted OTP
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('otp_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'otp' => 'User not found. Please login again.'
            ]);
        }

        if (!$this->otpService->validate($user, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        Auth::login($user);
        session()->forget('otp_user_id');

        return match ($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'user' => redirect()->intended(route('user.userproducts.dashboard')),
            default => redirect()->route('waitingapproval'),
        };
    }

    // Resend OTP via AJAX
    public function resend(Request $request)
    {
        $userId = session('otp_user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'error' => 'User not found. Please login again.'
            ], 404);
        }

        $key = 'otp-resend:' . $user->id;

        if (! $this->otpService->canResend($user)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Please wait {$seconds} seconds before resending OTP.",
                'seconds' => $seconds
            ], 429);
        }

        try {
            $this->otpService->resend($user);
        } catch (\Exception $e) {
            Log::error('OTP resend failed for user '.$user->id.': '.$e->getMessage());
            return response()->json([
                'error' => 'Failed to resend OTP. Please try again later.'
            ], 500);
        }

        return response()->json([
            'message' => 'A new OTP has been sent to your email.'
        ]);
    }
}
