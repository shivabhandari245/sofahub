<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function create()
    {
        // OTP pending → redirect
        if (session()->has('otp_user_id')) {
            $user = User::find(session('otp_user_id'));

            if ($user && !$user->otp_verified) {
                return redirect()->route('otp.index');
            }

            session()->forget('otp_user_id');
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // Create user (not logged in yet)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_verified' => false,
            'approved' => false,
        ]);

        // Generate OTP
        $this->otpService->generate($user);

        // Store OTP session
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.index')
            ->with('message', 'Registration successful! Please verify OTP sent to your email.');
    }
}
