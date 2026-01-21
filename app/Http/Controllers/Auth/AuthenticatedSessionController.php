<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create() {

         return view('auth.login');

          }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        /** @var User $user */
        $user = Auth::user();

        // OTP pending
                if (!$user->otp_verified) {
            if (session()->has('otp_user_id') && session('otp_user_id') !== $user->id) {
                Auth::logout();
                $request->session()->invalidate();
                return redirect()->route('otp.index')
                    ->with('error', 'Please verify OTP for the previous account first.');
            }

            Auth::logout();
            $request->session()->invalidate();
            session(['otp_user_id' => $user->id]);

            return redirect()->route('otp.index')
                ->with('message', 'Please verify your email first.');
        }

        // Approval check
        if (!$user->approved) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('waitingapproval')
                ->with('error', 'Your account is awaiting approval.');
        }

        // Fully authenticated
        session()->forget('otp_user_id');
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('user')) return redirect()->route('user.userproducts.dashboard');
        return redirect()->route('waitingapproval');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('otp_user_id');

        return redirect('/');
    }
}
