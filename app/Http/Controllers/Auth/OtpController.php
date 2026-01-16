<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OtpService;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    // Show OTP form
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to verify OTP.');
        }

        return view('auth.verify-otp');
    }

    // Verify OTP
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $user = Auth::user();

        if (!$this->otpService->validate($user, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }
/** @var \App\Models\User $user */
        $user->otp_verified = true;
        $user->save();

        return redirect()->route(
            $user->hasRole('admin') ? 'admin.dashboard' : 'user.userproducts.dashboard'
        );
    }

    // Resend OTP
    public function resend(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            $this->otpService->resend($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to resend OTP.'], 500);
        }

        return response()->json(['message' => 'A new OTP has been sent to your email.']);
    }
}
