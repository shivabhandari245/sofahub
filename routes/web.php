<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {

    // Guest → show homepage
    if (!Auth::check()) {
        return view('index');
    }

    /** @var User $user */
    $user = Auth::user();

    // If user is not approved → waiting page
    if (!$user->approved) {
        Auth::logout();
        return redirect()->route('waitingapproval');
    }

    // If OTP pending → OTP page
    if (!$user->otp_verified) {
        Auth::logout();
        session(['otp_user_id' => $user->id]);
        return redirect()->route('otp.index');
    }

    // Fully verified & approved → role-based redirect
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('user')) {
        return redirect()->route('user.userproducts.dashboard');
    }

    // Default fallback
    return redirect()->route('waitingapproval');
});


Route::middleware('web')->group(function () {
    

    // OTP routes
    Route::get('/verify-otp', [OtpController::class, 'index'])->name('otp.index');
    Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

    // Waiting approval
    Route::get('/waitingapproval', function () {
        return view('auth.waitingapproval');
    })->name('waitingapproval');
});
// Include auth, admin, user routes
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/user.php';
