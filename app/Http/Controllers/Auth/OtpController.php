<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

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

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $user = User::find(session('otp_user_id'));
        if (!$user) return redirect()->route('login');

        if (!$this->otpService->validate($user, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $user->otp_verified = true;
        $user->save();

        session()->forget('otp_user_id');

        Auth::login($user);

        return redirect()->route(
            $user->hasRole('admin') ? 'admin.dashboard' : 'user.userproducts.dashboard'
        );
    }

    public function resend(Request $request)
    {
        $user = User::find(session('otp_user_id'));
        if (!$user) return response()->json(['error' => 'User not found.'], 404);

        try {
            $this->otpService->resend($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }

        return response()->json(['message' => 'A new OTP has been sent to your email.']);
    }
}
