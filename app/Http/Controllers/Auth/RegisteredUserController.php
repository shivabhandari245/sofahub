<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    protected OtpService $otpService;

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
    public function store(Request $request)
    {
        if (session()->has('otp_user_id')) {
            return redirect()->route('otp.index')
                ->with('error', 'Please verify OTP before creating another account.');
        }

        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
        ]);

        $request->session()->invalidate();

        // Create user (OTP not verified, not approved)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_verified' => false,
            'approved' => false,
        ]);

        // Generate & send OTP immediately
        $this->otpService->generate($user);

        // Store in session for verification
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.index')
            ->with('message', 'Registration successful! OTP has been sent. Please verify.');
    }
}
