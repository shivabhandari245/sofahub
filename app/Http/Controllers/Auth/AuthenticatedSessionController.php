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
    public function create()
    {
        // OTP pending? Check DB
        if (session()->has('otp_user_id')) {
            $user = User::find(session('otp_user_id'));

            if ($user && !$user->otp_verified) {
                return redirect()->route('otp.index');
            }

            session()->forget('otp_user_id');
        }

        if (Auth::check()) {
            $user = Auth::user();

            if (!$user->approved) {
                Auth::logout();
                return redirect()->route('waitingapproval');
            }

            if (!$user->otp_verified) {
                Auth::logout();
                session(['otp_user_id' => $user->id]);
                return redirect()->route('otp.index');
            }

            return $this->redirectBasedOnRole($user);
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if (!$user->approved) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('waitingapproval')
                ->with('error', 'Your account is not approved yet.');
        }

        if (!$user->otp_verified) {
            Auth::logout();
            $request->session()->invalidate();
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

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('otp_user_id');

        return redirect('/');
    }
}
