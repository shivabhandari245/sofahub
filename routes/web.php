<?php

use App\Http\Controllers\ProfileController;



use Illuminate\Support\Facades\Route;



use App\Http\Controllers\Auth\OtpController;

use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if (!$user->approved) return redirect()->route('waitingapproval');
        if (!$user->otp_verified) return redirect()->route('otp.index');

        return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'user.userproducts.dashboard');
    }

    return view('index'); 
});


// Show OTP verification form
Route::get('/verify-otp', [OtpController::class, 'index'])
    ->name('otp.index');

// Handle OTP verification
Route::post('/verify-otp', [OtpController::class, 'verify'])
    ->name('otp.verify');

Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

Route::get('/waitingapproval', function () {
    return view('auth.waitingapproval');
})->name('waitingapproval');

require __DIR__.'/auth.php';


require __DIR__.'/admin.php';

require __DIR__.'/user.php';