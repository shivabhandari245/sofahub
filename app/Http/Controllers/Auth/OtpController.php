<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    // Show OTP form
    public function index()
    {
        if (!session()->has('otp_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('otp_user_id'));
        if (!$user || $user->otp_verified) {
            session()->forget('otp_user_id');
            return redirect()->route('login');
        }

        return view('auth.verify-otp');
    }

    // Verify OTP
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        if (!session()->has('otp_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('otp_user_id'));
        if (!$user) {
            session()->forget('otp_user_id');
            return redirect()->route('login');
        }

        // Validate OTP
        if (!$this->otpService->validate($user, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // OTP valid → mark verified
        $user->otp_verified = true;
        $user->save();

        session()->forget('otp_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        // Admin approval check
        if (!$user->approved) {
            return redirect()->route('waitingapproval')
                ->with('message', 'OTP verified! Your account is awaiting admin approval.');
        }

        // Redirect based on role
        return redirect()->intended(
            $user->hasRole('admin')
                ? route('admin.dashboard')
                : route('user.userproducts.dashboard')
        );
    }

    // Resend OTP
    public function resend()
    {
        if (!session()->has('otp_user_id')) {
            return response()->json(['error' => 'OTP session expired.'], 403);
        }

        $user = User::find(session('otp_user_id'));
        if (!$user) {
            session()->forget('otp_user_id');
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            $this->otpService->resend($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }

        return response()->json(['message' => 'A new OTP has been sent to your email.']);
    }
}
