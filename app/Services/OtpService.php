<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;

class OtpService
{
    protected int $otpLength = 6;
    protected int $otpExpiryMinutes = 5;

    // Generate OTP and send email
    public function generate(User $user)
    {
        $otp = random_int(100000, 999999);

        $user->update([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes($this->otpExpiryMinutes),
            'otp_verified' => false,
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to {$user->email}: " . $e->getMessage());
        }

        // Only log OTP in local/dev
        if (app()->environment('local')) {
            Log::info("OTP for {$user->email}: $otp");
        }

        return true;
    }

    // Validate OTP with rate limiting
    public function validate(User $user, string $otp): bool
    {
        $key = 'otp-attempt:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return false; // Block after 5 attempts
        }

        $isValid = $user->otpIsValid($otp);

        if ($isValid) {
            $user->markOtpVerified();
            RateLimiter::clear($key);
        } else {
            RateLimiter::hit($key, 300); // 5 minute lockout
        }

        return $isValid;
    }

    public function canResend(User $user, int $maxAttempts = 1, int $decaySeconds = 60): bool
{
    $key = 'otp-resend:' . $user->id;

    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
        return false;
    }

    RateLimiter::hit($key, $decaySeconds);

    return true;
}

public function resend(User $user)
{
    $otp = random_int(100000, 999999);

    $user->update([
        'otp_code' => Hash::make($otp),
        'otp_expires_at' => now()->addMinutes(5),
        'otp_verified' => false,
    ]);

    try {
        if ($user->email) {
            Mail::to($user->email)->send(new OtpMail($otp));
        } else {
            Log::warning("OTP resend skipped: User {$user->id} has no email");
        }
    } catch (\Exception $e) {
        Log::error("Failed to resend OTP to {$user->email}: ".$e->getMessage());
    }

    if (app()->environment('local')) {
        Log::info("OTP for {$user->email}: $otp");
    }
}

}
